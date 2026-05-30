<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IgbhSummativeEvaluationController extends Controller
{
    public function getResults(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $query = DB::table('igbh_summative_results as r')
            ->join('igbh_tests as t', 'r.test_seq', '=', 't.test_seq')
            ->select(
                'r.id',
                't.test_nm',
                't.level_cd',
                'r.stu_nm',
                'r.class_nm',
                'r.teacher_nm',
                'r.total_score',
                'r.eval_dt',
                'r.created_at',
                'r.updated_at'
            );

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('r.stu_nm', 'like', "%{$search}%")
                  ->orWhere('r.class_nm', 'like', "%{$search}%")
                  ->orWhere('r.teacher_nm', 'like', "%{$search}%")
                  ->orWhere('t.test_nm', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('r.created_at', 'desc');

        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    public function getReport($id)
    {
        $result = DB::table('igbh_summative_results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $testObj = DB::table('igbh_tests')->where('test_seq', $result->test_seq)->first();
        $result->test_nm = $testObj ? $testObj->test_nm : null;
        $result->level_cd = $testObj ? $testObj->level_cd : null;

        // Fetch themes (configs)
        $themes = DB::table('igbh_summative_themes')
            ->where('test_seq', $result->test_seq)
            ->orderBy('sort_no')
            ->get();

        // Fetch student's weekly details regardless of class_seq (handles transferred students)
        $detailsQuery = DB::table('igbh_weekly_eval_details as d')
            ->join('igbh_weekly_evals as e', 'd.weekly_eval_id', '=', 'e.id')
            ->select('d.*', 'e.each_cd')
            ->where('d.stu_seq', $result->stu_seq)
            ->get();

        $details = $detailsQuery->keyBy('each_cd');

        $reportData = [];
        $totalWorkbook = 0;
        $maxWorkbook = 0;
        
        $sumAttitude = ['listen' => 0, 'join' => 0, 'express' => 0, 'coop' => 0];
        $sumDetection = ['normal' => 0, 'leadersh' => 0, 'math' => 0, 'creative' => 0];
        $weeksWithData = 0;

        foreach ($themes as $theme) {
            $weekNum = $theme->sort_no;
            $eachCd = 'SE' . str_pad($weekNum, 3, '0', STR_PAD_LEFT);
            $detail = $details->get($eachCd);

            $maxWorkbook += $theme->theme_point;
            
            if ($detail) {
                $totalWorkbook += $detail->workbook;
                $sumAttitude['listen'] += $detail->attd_listen;
                $sumAttitude['join'] += $detail->attd_join;
                $sumAttitude['express'] += $detail->attd_express;
                $sumAttitude['coop'] += $detail->attd_coop;

                $sumDetection['normal'] += $detail->detect_normal;
                $sumDetection['leadersh'] += $detail->detect_leadersh;
                $sumDetection['math'] += $detail->detect_math;
                $sumDetection['creative'] += $detail->detect_creative;

                $weeksWithData++;
            }

            $reportData[] = [
                'week' => $weekNum,
                'theme_desc' => $theme->theme_desc,
                'max_score' => $theme->theme_point,
                'score' => $detail ? $detail->workbook : 0,
                'attitude' => $detail ? [
                    'listen' => $detail->attd_listen,
                    'join' => $detail->attd_join,
                    'express' => $detail->attd_express,
                    'coop' => $detail->attd_coop
                ] : null,
                'detection' => $detail ? [
                    'normal' => $detail->detect_normal,
                    'leadersh' => $detail->detect_leadersh,
                    'math' => $detail->detect_math,
                    'creative' => $detail->detect_creative
                ] : null
            ];
        }

        // Calculate averages for the radar chart (out of 5)
        $avgAttitude = [
            'listen' => $weeksWithData > 0 ? round($sumAttitude['listen'] / $weeksWithData, 1) : 0,
            'join' => $weeksWithData > 0 ? round($sumAttitude['join'] / $weeksWithData, 1) : 0,
            'express' => $weeksWithData > 0 ? round($sumAttitude['express'] / $weeksWithData, 1) : 0,
            'coop' => $weeksWithData > 0 ? round($sumAttitude['coop'] / $weeksWithData, 1) : 0,
        ];

        $avgDetection = [
            'normal' => $weeksWithData > 0 ? round($sumDetection['normal'] / $weeksWithData, 1) : 0,
            'leadersh' => $weeksWithData > 0 ? round($sumDetection['leadersh'] / $weeksWithData, 1) : 0,
            'math' => $weeksWithData > 0 ? round($sumDetection['math'] / $weeksWithData, 1) : 0,
            'creative' => $weeksWithData > 0 ? round($sumDetection['creative'] / $weeksWithData, 1) : 0,
        ];

        // Fetch subjective eval details
        $subjectiveDetails = DB::table('igbh_summative_result_details')
            ->where('summative_result_id', $result->id)
            ->orderBy('sort_no')
            ->get();
            
        $subjectiveAnalysis = $result->subjective_analysis ? json_decode($result->subjective_analysis, true) : null;
            
        $subjectiveTotal = [
            'max_score' => 0,
            'score' => 0,
            'concept' => 0,
            'strategy' => 0,
            'calculation' => 0,
            'expression' => 0
        ];
        
        foreach ($subjectiveDetails as $sub) {
            $subjectiveTotal['max_score'] += $sub->max_score;
            $subjectiveTotal['score'] += $sub->score;
            $subjectiveTotal['concept'] += $sub->concept;
            $subjectiveTotal['strategy'] += $sub->strategy;
            $subjectiveTotal['calculation'] += $sub->calculation;
            $subjectiveTotal['expression'] += $sub->expression;
        }

        return response()->json([
            'student_info' => $result,
            'report_data' => $reportData,
            'subjective_data' => $subjectiveDetails,
            'subjective_analysis' => $subjectiveAnalysis,
            'summary' => [
                'workbook_score' => $totalWorkbook,
                'max_workbook' => $maxWorkbook,
                'avg_attitude' => $avgAttitude,
                'avg_detection' => $avgDetection,
                'weeks_evaluated' => $weeksWithData,
                'subjective_total' => $subjectiveTotal
            ]
        ]);
    }
}
