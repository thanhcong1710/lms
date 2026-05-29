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

        return response()->json([
            'status' => 'success',
            'data' => [
                'general' => $result,
                'details' => $details
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
            $thinkingTotal = 0;

            // 1. Process Curriculum
            foreach ($curriculumInputs as $item) {
                $qNo = $item['question_no'];
                $ansVal = $item['assigned_score'];
                $unit = $item['unit'] ?? null;
                $seqId = $item['seq_id'] ?? null;

                // Lookup correct answer dynamically from other records of this test
                $correctAnswer = DB::table('igbh_student_result_details')
                    ->join('igbh_student_results', 'igbh_student_result_details.igbh_student_result_id', '=', 'igbh_student_results.id')
                    ->where('igbh_student_results.test_seq', $result->test_seq)
                    ->where('igbh_student_result_details.question_no', $qNo)
                    ->where('igbh_student_result_details.question_type', 'curriculum')
                    ->where('igbh_student_result_details.is_correct', 'O')
                    ->value('igbh_student_result_details.assigned_score');

                // If not found in DB, try to find the most common correct answer, or fallback to standard mapping
                if (!$correctAnswer) {
                    // Fallback default templates or accept what they entered
                    $isCorrect = 'O'; 
                } else {
                    $isCorrect = ($ansVal == $correctAnswer) ? 'O' : 'X';
                }

                if ($isCorrect === 'O') {
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

            $subjectTotal = $correctCount * 2;
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
     * Get initial data for dialog creation.
     */
    public function getInitData()
    {
        $students = DB::table('students')->select('id', 'name')->orderBy('name')->get();
        $teachers = DB::table('teachers')->select('id', 'ins_name as name')->orderBy('ins_name')->get();
        $tests = DB::table('igbh_tests')->orderBy('test_nm')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'students' => $students,
                'teachers' => $teachers,
                'tests' => $tests
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

        $student = DB::table('students')->where('id', $stuId)->first();
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy học sinh'], 404);
        }

        $test = DB::table('igbh_tests')->where('test_seq', $testSeq)->first();
        if (!$test) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài test'], 404);
        }

        $maxSeq = DB::table('igbh_student_results')->max('result_seq');
        $resultSeq = $maxSeq ? ($maxSeq + 1) : 20001;

        $newId = DB::table('igbh_student_results')->insertGetId([
            'result_seq' => $resultSeq,
            'test_seq' => $testSeq,
            'stu_seq' => $stuId,
            'stu_nm' => $student->name,
            'stu_birth_dt' => $student->birth ?? null,
            'reg_name' => $teacherName,
            'eval_dt' => $evalDt,
            'reg_date' => now(),
            'total_score' => 0,
            'subject_total' => 0,
            'thinking_total' => 0,
            'assigned_level' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $newId
            ]
        ]);
    }
}
