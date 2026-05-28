<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UcreaEvaluationController extends Controller
{
    /**
     * Get list of UCREA tests.
     */
    public function getResults(Request $request)
    {
        $status = $request->query('status', 'completed'); // 'pending' or 'completed'
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 20);

        $query = DB::table('ucrea_student_results')
            ->leftJoin('ucrea_tests', function($join) {
                $join->on('ucrea_student_results.test_cd', '=', 'ucrea_tests.test_cd')
                     ->on('ucrea_student_results.level_cd', '=', 'ucrea_tests.level_cd')
                     ->on('ucrea_student_results.test_seq', '=', 'ucrea_tests.test_seq');
            })
            ->select('ucrea_student_results.*', 'ucrea_tests.test_nm');

        // Filter by status (IS002 is Graded/Completed, IS004 etc. For pending we might need other logic, but let's assume IS002 and IS004 are completed)
        if ($status === 'completed') {
            $query->whereIn('ucrea_student_results.result_cd', ['IS002', 'IS003', 'IS004']);
        } else {
            // For pending, normally there's a different code like IS001. We'll filter for non-completed
            $query->whereNotIn('ucrea_student_results.result_cd', ['IS002', 'IS003', 'IS004']);
        }

        // Search logic
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('ucrea_student_results.stu_nm', 'like', "%{$search}%")
                  ->orWhere('ucrea_student_results.memb_nm', 'like', "%{$search}%")
                  ->orWhere('ucrea_tests.test_nm', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc('ucrea_student_results.eval_dt');

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
        $result = DB::table('ucrea_student_results')
            ->leftJoin('ucrea_tests', function($join) {
                $join->on('ucrea_student_results.test_cd', '=', 'ucrea_tests.test_cd')
                     ->on('ucrea_student_results.level_cd', '=', 'ucrea_tests.level_cd')
                     ->on('ucrea_student_results.test_seq', '=', 'ucrea_tests.test_seq');
            })
            ->where('ucrea_student_results.id', $id)
            ->select('ucrea_student_results.*', 'ucrea_tests.test_nm', 'ucrea_tests.level_cd_nm')
            ->first();

        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'Result not found'], 404);
        }

        // Get the detailed rubrics
        $details = DB::table('ucrea_student_result_details')
            ->where('ucrea_student_result_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'general' => $result,
                'rubrics' => $details
            ]
        ]);
    }

    /**
     * Save newly graded test from frontend
     */
    public function saveGrade(Request $request, $id)
    {
        $result = DB::table('ucrea_student_results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['status' => 'error', 'message' => 'Result not found'], 404);
        }

        $rubrics = $request->input('rubrics', []);
        
        DB::beginTransaction();
        try {
            // Delete old details if any
            DB::table('ucrea_student_result_details')->where('ucrea_student_result_id', $id)->delete();

            $insertData = [];
            $scoresByKey = [];
            $skillsGradeList = [];

            $letterGrades = ['S', 'A', 'B', 'C', 'L'];
            $scoreMap = ['S' => 100, 'A' => 85, 'B' => 70, 'C' => 55, 'L' => 30];

            foreach ($rubrics as $index => $r) {
                // Find index of selected score option
                $scoreIndex = array_search($r['score'], $r['options']);
                if ($scoreIndex === false) {
                    $grade = 'B';
                } else {
                    $grade = $letterGrades[$scoreIndex] ?? 'B';
                }

                $points = $scoreMap[$grade] ?? 70;
                $scoresByKey[$r['key']] = $points;

                // Add to details
                $insertData[] = [
                    'ucrea_student_result_id' => $id,
                    'question_no' => strval($index + 1),
                    'main_category' => $r['main'],
                    'sub_category' => $r['sub'],
                    'rubric_name' => $r['name'],
                    'assigned_score' => $r['score'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Add long skill name grade
                $skillsGradeList[] = [
                    'skill' => $r['skillName'] ?? $r['name'],
                    'grade' => $grade
                ];
            }

            if (!empty($insertData)) {
                DB::table('ucrea_student_result_details')->insert($insertData);
            }

            // Set report name based on test_cd
            $nameMap = ['TT001' => 'Đầu kỳ', 'TT002' => 'Giữa kỳ', 'TT003' => 'Cuối kỳ'];
            $reportDataName = $nameMap[$result->test_cd] ?? 'Đầu kỳ';

            $avgCoBan = (($scoresByKey['sk11'] ?? 70) + ($scoresByKey['sk12'] ?? 70) + ($scoresByKey['sk13'] ?? 70)) / 3;
            $avgToanHoc = (($scoresByKey['kn11'] ?? 70) + ($scoresByKey['kn12'] ?? 70) + ($scoresByKey['kn13'] ?? 70) + ($scoresByKey['kn14'] ?? 70) + ($scoresByKey['kn15'] ?? 70)) / 5;
            $avgLogic = (($scoresByKey['sk21'] ?? 70) + ($scoresByKey['sk22'] ?? 70) + ($scoresByKey['sk23'] ?? 70) + ($scoresByKey['sk24'] ?? 70)) / 4;
            $avgSangTao = (($scoresByKey['sk31'] ?? 70) + ($scoresByKey['sk32'] ?? 70) + ($scoresByKey['sk33'] ?? 70) + ($scoresByKey['sk34'] ?? 70)) / 4;

            $getGradeFromScore = function($avgScore) {
                if ($avgScore >= 90) return 'S';
                if ($avgScore >= 80) return 'A';
                if ($avgScore >= 65) return 'B';
                if ($avgScore >= 50) return 'C';
                return 'L';
            };

            $gradeCoBan = $getGradeFromScore($avgCoBan);
            $gradeToanHoc = $getGradeFromScore($avgToanHoc);
            $gradeLogic = $getGradeFromScore($avgLogic);
            $gradeSangTao = $getGradeFromScore($avgSangTao);

            $skillsGradeList[] = ['skill' => 'Tư duy cơ bản', 'grade' => $gradeCoBan];
            $skillsGradeList[] = ['skill' => 'Tư duy toán học', 'grade' => $gradeToanHoc];
            $skillsGradeList[] = ['skill' => 'Tư duy logic', 'grade' => $gradeLogic];
            $skillsGradeList[] = ['skill' => 'Tư duy sáng tạo', 'grade' => $gradeSangTao];

            $reportDataObj = [
                'name' => $reportDataName,
                'kn11' => strval($scoresByKey['kn11'] ?? 70),
                'kn12' => strval($scoresByKey['kn12'] ?? 70),
                'kn13' => strval($scoresByKey['kn13'] ?? 70),
                'kn14' => strval($scoresByKey['kn14'] ?? 70),
                'kn15' => strval($scoresByKey['kn15'] ?? 70),
                'kn21' => '0',
                'kn22' => '0',
                'kn23' => '0',
                'kn24' => '0',
                'sk11' => strval($scoresByKey['sk11'] ?? 70),
                'sk12' => strval($scoresByKey['sk12'] ?? 70),
                'sk13' => strval($scoresByKey['sk13'] ?? 70),
                'sk21' => strval($scoresByKey['sk21'] ?? 70),
                'sk22' => strval($scoresByKey['sk22'] ?? 70),
                'sk23' => strval($scoresByKey['sk23'] ?? 70),
                'sk24' => strval($scoresByKey['sk24'] ?? 70),
                'sk31' => strval($scoresByKey['sk31'] ?? 70),
                'sk32' => strval($scoresByKey['sk32'] ?? 70),
                'sk33' => strval($scoresByKey['sk33'] ?? 70),
                'sk34' => strval($scoresByKey['sk34'] ?? 70),
            ];

            // Update status to Graded (IS002 is assumed Graded here) along with calculated data
            DB::table('ucrea_student_results')->where('id', $id)->update([
                'result_cd' => 'IS002',
                'result_cd_nm' => 'Nhập nhận xét',
                'eval_dt' => now()->toDateString(),
                'report_data' => json_encode($reportDataObj, JSON_UNESCAPED_UNICODE),
                'skills_grade' => json_encode($skillsGradeList, JSON_UNESCAPED_UNICODE),
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
     * Get initial data (students, teachers, classes, tests) for creating evaluation
     */
    public function getInitData()
    {
        $students = DB::table('students')->select('id', 'name')->orderBy('name')->get();
        $teachers = DB::table('teachers')->select('id', 'ins_name as name')->orderBy('ins_name')->get();
        $classes = DB::table('classes')->select('id', 'cls_name as name')->orderBy('cls_name')->get();
        $tests = DB::table('ucrea_tests')->orderBy('test_nm')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'students' => $students,
                'teachers' => $teachers,
                'classes' => $classes,
                'tests' => $tests
            ]
        ]);
    }

    /**
     * Create a new pending evaluation record
     */
    public function createResult(Request $request)
    {
        $test = DB::table('ucrea_tests')->where('id', $request->input('test_id'))->first();
        if (!$test) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy bài test được chọn'], 404);
        }

        $maxSeq = DB::table('ucrea_student_results')->max('result_seq');
        $resultSeq = $maxSeq ? ($maxSeq + 1) : 1001;

        $newId = DB::table('ucrea_student_results')->insertGetId([
            'test_cd' => $test->test_cd,
            'level_cd' => $test->level_cd,
            'test_seq' => $test->test_seq,
            'result_seq' => $resultSeq,
            'memb_nm' => $request->input('memb_nm'),
            'stu_nm' => $request->input('stu_nm'),
            'class_nm' => $request->input('class_nm'),
            'eval_dt' => $request->input('eval_dt'),
            'result_cd' => 'IS001',
            'result_cd_nm' => 'Chưa nhập',
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
