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
            foreach ($rubrics as $index => $r) {
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
            }
            if (!empty($insertData)) {
                DB::table('ucrea_student_result_details')->insert($insertData);
            }

            // Update status to Graded (IS002 is assumed Graded here)
            DB::table('ucrea_student_results')->where('id', $id)->update([
                'result_cd' => 'IS002',
                'result_cd_nm' => 'Graded',
                'eval_dt' => now()->toDateString(),
                'updated_at' => now()
            ]);

            // Here you would also calculate the radar chart and skill grades
            // based on the S/A/B/C scores and save to report_data / skills_grade.
            // For now, we leave them or update with dummy/calculated data.

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
