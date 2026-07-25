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

        // Role-based filtering
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            $classQuery = $user->scopeClasses(\App\Models\LmsClass::query());
            $classNames = $classQuery->pluck('cls_name')->toArray();
            $query->whereIn('r.class_nm', $classNames);
        }

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

    public function getFormData($id)
    {
        $result = DB::table('igbh_summative_results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $testObj = DB::table('igbh_tests')->where('test_seq', $result->test_seq)->first();
        $result->test_nm = $testObj ? $testObj->test_nm : null;
        $result->level_cd = $testObj ? $testObj->level_cd : null;

        $subjectiveDetails = DB::table('igbh_summative_result_details')
            ->where('summative_result_id', $result->id)
            ->orderBy('sort_no')
            ->get();

        $analysis = $result->subjective_analysis ? json_decode($result->subjective_analysis, true) : null;

        $themes = DB::table('igbh_summative_themes')
            ->where('test_seq', $result->test_seq)
            ->orderBy('sort_no')
            ->get();

        $detailsQuery = DB::table('igbh_weekly_eval_details as d')
            ->join('igbh_weekly_evals as e', 'd.weekly_eval_id', '=', 'e.id')
            ->select('d.*', 'e.each_cd')
            ->where('e.test_seq', $result->test_seq)
            ->where('d.stu_seq', $result->stu_seq)
            ->get();
        
        $details = $detailsQuery->keyBy('each_cd');

        $weeklyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $eachCd = 'SE' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $detail = $details->get($eachCd);
            $theme = $themes->firstWhere('sort_no', $i);
            
            $weeklyData[] = [
                'sort_no' => $i,
                'each_cd' => $eachCd,
                'max_score' => $theme ? $theme->theme_point : 3,
                'workbook' => $detail ? $detail->workbook : null,
            ];
        }

        return response()->json([
            'student_info' => $result,
            'subjective_data' => $subjectiveDetails,
            'weekly_data' => $weeklyData,
            'teacher_comment' => $analysis['teacher_comment'] ?? ''
        ]);
    }

    public function saveFormData(Request $request, $id)
    {
        $result = DB::table('igbh_summative_results')->where('id', $id)->first();
        if (!$result) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $request->validate([
            'subjective_data' => 'required|array',
            'weekly_data' => 'nullable|array',
            'teacher_comment' => 'nullable|string',
            'eval_dt' => 'nullable|date'
        ]);

        DB::beginTransaction();
        try {
            DB::table('igbh_summative_result_details')->where('summative_result_id', $id)->delete();

            $insertData = [];
            $totalSubjectiveScore = 0;

            foreach ($request->subjective_data as $sub) {
                $score = ($sub['concept'] ?? 0) + ($sub['strategy'] ?? 0) + ($sub['calculation'] ?? 0) + ($sub['expression'] ?? 0);
                $totalSubjectiveScore += $score;
                
                $insertData[] = [
                    'summative_result_id' => $id,
                    'sort_no' => $sub['sort_no'],
                    'max_score' => $sub['max_score'] ?? 16,
                    'score' => $score,
                    'concept' => $sub['concept'] ?? 0,
                    'strategy' => $sub['strategy'] ?? 0,
                    'calculation' => $sub['calculation'] ?? 0,
                    'expression' => $sub['expression'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($insertData)) {
                DB::table('igbh_summative_result_details')->insert($insertData);
            }

            $analysis = $result->subjective_analysis ? json_decode($result->subjective_analysis, true) : [];
            $analysis['teacher_comment'] = $request->teacher_comment;

            DB::table('igbh_summative_results')->where('id', $id)->update([
                'eval_dt' => $request->eval_dt ?? $result->eval_dt,
                'subjective_analysis' => json_encode($analysis, JSON_UNESCAPED_UNICODE)
            ]);

            // Save Weekly Data
            if ($request->has('weekly_data')) {
                foreach ($request->weekly_data as $wd) {
                    $weekObj = DB::table('igbh_weekly_evals')
                        ->where('test_seq', $result->test_seq)
                        ->where('class_seq', $result->class_seq)
                        ->where('each_cd', $wd['each_cd'])
                        ->first();
                    
                    if (!$weekObj) {
                        $weekId = DB::table('igbh_weekly_evals')->insertGetId([
                            'test_seq' => $result->test_seq,
                            'class_seq' => $result->class_seq,
                            'class_nm' => $result->class_nm,
                            'each_cd' => $wd['each_cd'],
                            'each_cd_nm' => 'Tuần thứ ' . $wd['sort_no'],
                            'eval_ymd' => $request->eval_dt ?? now(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $weekObj = (object)['id' => $weekId];
                    }

                    $detailExists = DB::table('igbh_weekly_eval_details')
                        ->where('weekly_eval_id', $weekObj->id)
                        ->where('stu_seq', $result->stu_seq)
                        ->exists();

                    if (!$detailExists) {
                        DB::table('igbh_weekly_eval_details')->insert([
                            'weekly_eval_id' => $weekObj->id,
                            'stu_seq' => $result->stu_seq,
                            'stu_nm' => $result->stu_nm,
                            'workbook' => $wd['workbook'] ?? 0,
                            'attd_listen' => 5,
                            'attd_join' => 5,
                            'attd_express' => 5,
                            'attd_coop' => 5,
                            'detect_normal' => 5,
                            'detect_leadersh' => 5,
                            'detect_math' => 5,
                            'detect_creative' => 5,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('igbh_weekly_eval_details')
                            ->where('weekly_eval_id', $weekObj->id)
                            ->where('stu_seq', $result->stu_seq)
                            ->update([
                                'workbook' => $wd['workbook'] ?? 0,
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            // Calculate BTM and LTM
            // Sort the 5 subjective questions by total score descending
            $subjArr = $request->subjective_data;
            usort($subjArr, function($a, $b) {
                $scoreA = ($a['concept'] ?? 0) + ($a['strategy'] ?? 0) + ($a['calculation'] ?? 0) + ($a['expression'] ?? 0);
                $scoreB = ($b['concept'] ?? 0) + ($b['strategy'] ?? 0) + ($b['calculation'] ?? 0) + ($b['expression'] ?? 0);
                if ($scoreA == $scoreB) return $a['sort_no'] <=> $b['sort_no'];
                return $scoreB <=> $scoreA; // Descending
            });

            if (count($subjArr) >= 5) {
                $btm1 = $subjArr[0];
                $btm2 = $subjArr[1];
                $ltm1 = $subjArr[3];
                $ltm2 = $subjArr[4];

                $getOpinion = function($avg) {
                    if ($avg >= 3.5) return 'Tốt';
                    if ($avg >= 2.5) return 'Khá';
                    if ($avg > 0) return 'Cần cố gắng';
                    return '';
                };

                $calcAvg = function($q1, $q2, $key) use ($getOpinion) {
                    $v1 = $q1[$key] ?? 0;
                    $v2 = $q2[$key] ?? 0;
                    $avg = round(($v1 + $v2) / 2, 1);
                    return [$v1, $v2, $avg, $getOpinion($avg)];
                };

                $calcTotalAvg = function($q1, $q2) use ($getOpinion) {
                    $v1 = (($q1['concept'] ?? 0) + ($q1['strategy'] ?? 0) + ($q1['calculation'] ?? 0) + ($q1['expression'] ?? 0)) / 4;
                    $v2 = (($q2['concept'] ?? 0) + ($q2['strategy'] ?? 0) + ($q2['calculation'] ?? 0) + ($q2['expression'] ?? 0)) / 4;
                    $avg = round(($v1 + $v2) / 2, 1);
                    return [round($v1, 1), round($v2, 1), $avg, $getOpinion($avg)];
                };

                $analysis['btm'] = [
                    'q1_label' => 'No. ' . $btm1['sort_no'],
                    'q2_label' => 'No. ' . $btm2['sort_no'],
                    'concept' => $calcAvg($btm1, $btm2, 'concept'),
                    'strategy' => $calcAvg($btm1, $btm2, 'strategy'),
                    'calculation' => $calcAvg($btm1, $btm2, 'calculation'),
                    'expression' => $calcAvg($btm1, $btm2, 'expression'),
                    'average' => $calcTotalAvg($btm1, $btm2)
                ];

                $analysis['ltm'] = [
                    'q1_label' => 'No. ' . $ltm1['sort_no'],
                    'q2_label' => 'No. ' . $ltm2['sort_no'],
                    'concept' => $calcAvg($ltm1, $ltm2, 'concept'),
                    'strategy' => $calcAvg($ltm1, $ltm2, 'strategy'),
                    'calculation' => $calcAvg($ltm1, $ltm2, 'calculation'),
                    'expression' => $calcAvg($ltm1, $ltm2, 'expression'),
                    'average' => $calcTotalAvg($ltm1, $ltm2)
                ];
            }

            DB::table('igbh_summative_results')->where('id', $id)->update([
                'eval_dt' => $request->eval_dt ?? $result->eval_dt,
                'subjective_analysis' => json_encode($analysis, JSON_UNESCAPED_UNICODE)
            ]);

            // Update total score
            // We need to re-sum everything just like in WeeklyEvalController, or just add the diff.
            // A quick re-sum for safety:
            $allWeeks = DB::table('igbh_weekly_eval_details as d')
                ->join('igbh_weekly_evals as e', 'd.weekly_eval_id', '=', 'e.id')
                ->where('e.test_seq', $result->test_seq)
                ->where('d.stu_seq', $result->stu_seq)
                ->get();

            $totalWorkbook = 0;
            $sumAttitude = 0;
            $sumDetection = 0;
            $weekCount = $allWeeks->count();

            if ($weekCount > 0) {
                foreach ($allWeeks as $w) {
                    $totalWorkbook += $w->workbook;
                    $sumAttitude += ($w->attd_listen + $w->attd_join + $w->attd_express + $w->attd_coop);
                    $sumDetection += ($w->detect_normal + $w->detect_leadersh + $w->detect_math + $w->detect_creative);
                }
                $avgAttitude = ($sumAttitude / $weekCount) / 4 * 2;
                $avgDetection = ($sumDetection / $weekCount) / 4 * 2;
            } else {
                $avgAttitude = 0;
                $avgDetection = 0;
            }

            $finalTotalScore = round($totalWorkbook + $avgAttitude + $avgDetection + $totalSubjectiveScore, 1);

            DB::table('igbh_summative_results')->where('id', $id)->update([
                'total_score' => $finalTotalScore
            ]);

            DB::commit();
            return response()->json(['message' => 'Saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
