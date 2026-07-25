<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IgbhEvaluationController extends Controller
{
    /**
     * Get list of IG.BH test results.
     */
    public function getResults(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 20);

        $query = DB::table('igbh_student_results')
            ->leftJoin('igbh_tests', 'igbh_student_results.test_seq', '=', 'igbh_tests.test_seq')
            ->select('igbh_student_results.*', 'igbh_tests.test_nm');

        // Role-based filtering
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            $studentQuery = $user->scopeStudents(\App\Models\Student::query());
            $studentNames = $studentQuery->pluck('name')->toArray();
            $query->whereIn('igbh_student_results.stu_nm', $studentNames);
        }

        // Search logic
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('igbh_student_results.stu_nm', 'like', "%{$search}%")
                  ->orWhere('igbh_student_results.reg_name', 'like', "%{$search}%")
                  ->orWhere('igbh_tests.test_nm', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc('igbh_student_results.reg_date');

        $results = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem()
            ]
        ]);
    }

    /**
     * Get detail of a specific result.
     */
    public function getResultDetail($id)
    {
        $result = DB::table('igbh_student_results')
            ->leftJoin('igbh_tests', 'igbh_student_results.test_seq', '=', 'igbh_tests.test_seq')
            ->where('igbh_student_results.id', $id)
            ->select('igbh_student_results.*', 'igbh_tests.test_nm')
            ->first();

        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'Result not found'], 404);
        }

        // Get the detailed rubrics
        $details = DB::table('igbh_student_result_details')
            ->where('igbh_student_result_id', $id)
            ->get();

        $testConfig = clone $result; // just fallback
        $testConfigRow = DB::table('igbh_test_configs')->where('test_seq', $result->test_seq)->first();
        $testQuestions = DB::table('igbh_test_questions')->where('test_seq', $result->test_seq)->orderBy('sort_no')->get();
        $testComments = DB::table('igbh_test_comments')->where('test_seq', $result->test_seq)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'general' => $result,
                'details' => $details,
                'test_config' => $testConfigRow,
                'test_questions' => $testQuestions,
                'test_comments' => $testComments
            ]
        ]);
    }

    /**
     * Save/Update scores for a test result.
     */
    public function saveGrade(Request $request, $id)
    {
        $result = DB::table('igbh_student_results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'Result not found'], 404);
        }

        $evalDt = $request->input('eval_dt', now()->toDateString());
        $assignedLevel = $request->input('assigned_level');
        $curriculumInputs = $request->input('curriculum', []);
        $thinkingInputs = $request->input('thinking', []);

        DB::beginTransaction();
        try {
            // Delete old details
            DB::table('igbh_student_result_details')->where('igbh_student_result_id', $id)->delete();

            $insertData = [];
            $correctCount = 0;
            $subjectTotal = 0;
            $thinkingTotal = 0;

            // 1. Process Curriculum
            foreach ($curriculumInputs as $item) {
                $qNo = $item['question_no'];
                $ansVal = $item['assigned_score'];
                $unit = $item['unit'] ?? null;
                $seqId = $item['seq_id'] ?? null;

                // Lookup correct answer dynamically from test_questions
                $correctAnswer = DB::table('igbh_test_questions')
                    ->where('test_seq', $result->test_seq)
                    ->where('question_type', 'curriculum')
                    ->where('sort_no', $qNo)
                    ->value('answer');

                // If not found in DB, accept what they entered
                if (!$correctAnswer) {
                    $isCorrect = 'O'; 
                } else {
                    $isCorrect = ($ansVal == $correctAnswer) ? 'O' : 'X';
                }

                if ($isCorrect === 'O') {
                    // Fetch point value dynamically
                    $point = DB::table('igbh_test_questions')
                        ->where('test_seq', $result->test_seq)
                        ->where('question_type', 'curriculum')
                        ->where('sort_no', $qNo)
                        ->value('standard_point') ?? 2;
                    $subjectTotal += $point;
                    $correctCount++;
                }

                $insertData[] = [
                    'igbh_student_result_id' => $id,
                    'question_no' => strval($qNo),
                    'question_type' => 'curriculum',
                    'seq_id' => $seqId,
                    'assigned_score' => $ansVal,
                    'unit' => $unit,
                    'is_correct' => $isCorrect,
                    'max_score' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // 2. Process Thinking
            foreach ($thinkingInputs as $item) {
                $qNo = $item['question_no'];
                $scoreVal = (int)($item['assigned_score'] ?? 0);
                $maxScore = $item['max_score'] ?? 5;
                $seqId = $item['seq_id'] ?? null;

                $thinkingTotal += $scoreVal;

                $insertData[] = [
                    'igbh_student_result_id' => $id,
                    'question_no' => strval($qNo),
                    'question_type' => 'thinking',
                    'seq_id' => $seqId,
                    'assigned_score' => strval($scoreVal),
                    'unit' => null,
                    'is_correct' => null,
                    'max_score' => $maxScore,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertData)) {
                DB::table('igbh_student_result_details')->insert($insertData);
            }

            $totalScore = $subjectTotal + $thinkingTotal;

            // Update main result row
            DB::table('igbh_student_results')->where('id', $id)->update([
                'eval_dt' => $evalDt,
                'assigned_level' => $assignedLevel,
                'subject_total' => $subjectTotal,
                'thinking_total' => $thinkingTotal,
                'total_score' => $totalScore,
                'updated_at' => now()
            ]);

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get initial data for dialog creation (branches, classes, teachers, tests, contracts).
     */
    public function getInitData(Request $request)
    {
        $user = \App\Http\Controllers\AuthController::resolveUser($request);

        // 1. Branches
        $branchQuery = \App\Models\Branch::query();
        if ($user) {
            $branchIds = $user->getAccessibleBranchLmsIds();
            if ($branchIds !== null) {
                $branchQuery->whereIn('id_lms', $branchIds);
            }
        }
        $branches = $branchQuery->select('id', 'name', 'id_lms')->orderBy('name')->get();

        // 2. Classes (with teacher relation)
        $classQuery = \App\Models\LmsClass::query();
        if ($user) {
            $user->scopeClasses($classQuery);
        }
        $classes = $classQuery->with('teacher')
            ->select('id', 'cls_name', 'class_seq', 'level_name', 'cls_type', 'branch_id', 'branch_id_lms', 'teacher_id', 'teacher_id_lms')
            ->orderBy('cls_name')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'cls_name' => $c->cls_name,
                    'class_seq' => $c->class_seq,
                    'level_name' => $c->level_name,
                    'cls_type' => $c->cls_type,
                    'branch_id' => $c->branch_id,
                    'branch_id_lms' => $c->branch_id_lms,
                    'teacher_id' => $c->teacher_id,
                    'teacher_id_lms' => $c->teacher_id_lms,
                    'teacher_name' => $c->teacher->ins_name ?? $c->teacher_id_lms,
                ];
            });

        // 3. Teachers
        $teacherQuery = \App\Models\Teacher::query();
        if ($user) {
            if ($user->isTeacher() && $user->teacher_id) {
                $teacherQuery->where('id', $user->teacher_id);
            } elseif ($user->isTeamLeader()) {
                $branchIds = $user->getAccessibleBranchLmsIds();
                if ($branchIds !== null) {
                    $teacherQuery->whereIn('branch_id_lms', $branchIds);
                }
            }
        }
        $teachers = $teacherQuery->select('id', 'ins_name as name', 'id_lms', 'branch_id_lms')->orderBy('ins_name')->get();

        // 4. Tests (Diagnostic / PT tests)
        $tests = DB::table('igbh_tests')
            ->select('id', 'test_seq', 'test_nm', 'level_cd')
            ->orderBy('test_nm')
            ->get();

        // 5. Contracts with students
        $contracts = \App\Models\Contract::with('student')
            ->where('status', '!=', 'SS004')
            ->get()
            ->map(function ($cnt) {
                return [
                    'class_id' => $cnt->class_id,
                    'student_id' => $cnt->student_id,
                    'student_name' => $cnt->student->name ?? '',
                    'student_lms_id' => $cnt->student->id_lms ?? '',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'branches' => $branches,
                'classes' => $classes,
                'teachers' => $teachers,
                'tests' => $tests,
                'contracts' => $contracts,
            ]
        ]);
    }

    /**
     * Create a new pending IG.BH result stub.
     */
    public function createResult(Request $request)
    {
        $testSeq = $request->input('test_seq');
        $stuId = $request->input('student_id');
        $teacherName = $request->input('teacher_name');
        $evalDt = $request->input('eval_dt', now()->toDateString());
        $classId = $request->input('class_id');

        $student = DB::table('students')->where('id', $stuId)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy học sinh'], 404);
        }

        $test = DB::table('igbh_tests')->where('test_seq', $testSeq)->first();
        if (!$test) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài test'], 404);
        }

        $class = null;
        if ($classId) {
            $class = DB::table('classes')->where('id', $classId)->first();
        }

        $maxSeq = DB::table('igbh_student_results')->max('result_seq');
        $resultSeq = $maxSeq ? ($maxSeq + 1) : 20001;

        $newId = DB::table('igbh_student_results')->insertGetId([
            'result_seq'      => $resultSeq,
            'test_seq'        => $testSeq,
            'stu_seq'         => $stuId,
            'stu_nm'          => $student->name,
            'stu_birth_dt'    => $student->birth ?? null,
            'reg_name'        => $teacherName,
            'eval_dt'         => $evalDt,
            'reg_date'        => now(),
            'total_score'     => 0,
            'subject_total'   => 0,
            'thinking_total'  => 0,
            'assigned_level'  => null,
            'class_type_cd'   => $class ? $class->cls_type : null,
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $newId
            ]
        ]);
    }
}
