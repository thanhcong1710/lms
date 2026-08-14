<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IgbhWeeklyEvaluationController extends Controller
{
    public function getResults(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $query = DB::table('igbh_weekly_evals as e')
            ->join('igbh_tests as t', 'e.test_seq', '=', 't.test_seq')
            ->select(
                'e.id',
                't.test_nm as evalNm',
                't.level_cd as levelCd',
                'e.class_nm as classNm',
                'e.teacher_nm as teacherNm',
                'e.each_cd_nm as eachCdNm',
                'e.eval_ymd as evalYmd',
                'e.created_at'
            )
            ->selectRaw('(SELECT COUNT(*) FROM igbh_weekly_eval_details WHERE weekly_eval_id = e.id) as graded_cnt')
            ->selectRaw('(SELECT COUNT(*) FROM contracts c JOIN classes cl ON c.class_id = cl.id WHERE cl.class_seq = e.class_seq AND c.status != "SS004") as total_cnt');

        // Role-based filtering
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            $classQuery = $user->scopeClasses(\App\Models\LmsClass::query());
            $classNames = $classQuery->pluck('cls_name')->toArray();
            $query->whereIn('e.class_nm', $classNames);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('e.class_nm', 'like', "%{$search}%")
                  ->orWhere('e.teacher_nm', 'like', "%{$search}%")
                  ->orWhere('t.test_nm', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('e.created_at', 'desc');

        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    public function getInitData(Request $request)
    {
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        $classQuery = \App\Models\LmsClass::query();
        if ($user) {
            $user->scopeClasses($classQuery);
        }

        // For summative tests (where weekly evaluations apply)
        // From previous analysis, summative tests have configs in igbh_summative_themes
        $tests = DB::table('igbh_tests')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('igbh_summative_themes')
                      ->whereRaw('igbh_summative_themes.test_seq = igbh_tests.test_seq');
            })
            ->select('test_seq', 'test_nm', 'level_cd')
            ->get();

        $classes = $classQuery->select('id', 'class_seq', 'cls_name', 'cls_name as class_nm', 'level_name', 'cls_type', 'product_id', 'branch_id')->get();

        $weeks = [];
        for ($i = 1; $i <= 12; $i++) {
            $cd = 'SE' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $weeks[] = [
                'each_cd' => $cd,
                'each_cd_nm' => 'Tuần thứ ' . $i
            ];
        }

        return response()->json([
            'tests' => $tests,
            'classes' => $classes,
            'weeks' => $weeks
        ]);
    }

    public function createResult(Request $request)
    {
        $request->validate([
            'test_seq' => 'required|integer',
            'class_seq' => 'required|integer',
            'each_cd' => 'required|string',
            'eval_ymd' => 'required|date'
        ]);

        $testObj = DB::table('igbh_tests')->where('test_seq', $request->test_seq)->first();
        $classObj = DB::table('classes')->where('class_seq', $request->class_seq)->first();

        // Check if already exists
        $exists = DB::table('igbh_weekly_evals')
            ->where('test_seq', $request->test_seq)
            ->where('class_seq', $request->class_seq)
            ->where('each_cd', $request->each_cd)
            ->first();

        if ($exists) {
            return response()->json(['id' => $exists->id], 200);
        }

        $weekNum = (int) str_replace('SE', '', $request->each_cd);

        $id = DB::table('igbh_weekly_evals')->insertGetId([
            'test_seq' => $request->test_seq,
            'class_seq' => $request->class_seq,
            'class_nm' => $classObj ? $classObj->cls_name : null,
            'each_cd' => $request->each_cd,
            'each_cd_nm' => 'Tuần thứ ' . $weekNum,
            'eval_ymd' => $request->eval_ymd,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['id' => $id], 201);
    }

    public function getResultDetail($id)
    {
        $eval = DB::table('igbh_weekly_evals')->where('id', $id)->first();
        if (!$eval) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $testObj = DB::table('igbh_tests')->where('test_seq', $eval->test_seq)->first();
        $eval->test_nm = $testObj ? $testObj->test_nm : null;

        $details = DB::table('igbh_weekly_eval_details')
            ->where('weekly_eval_id', $id)
            ->get();

        // If no details, load students from class
        if ($details->isEmpty()) {
            $students = DB::table('contracts as c')
                ->join('students as s', 'c.student_id', '=', 's.id')
                ->join('classes as cl', 'c.class_id', '=', 'cl.id')
                ->where('cl.class_seq', $eval->class_seq)
                ->where('c.status', '!=', 'SS004') // assuming SS004 is cancelled/dropped
                ->select('s.id_lms as stu_seq', 's.name as stu_nm')
                ->get();

            $details = $students->map(function ($s) {
                return [
                    'stu_seq' => $s->stu_seq,
                    'stu_nm' => $s->stu_nm,
                    'workbook' => 0,
                    'attd_listen' => 5,
                    'attd_join' => 5,
                    'attd_express' => 5,
                    'attd_coop' => 5,
                    'detect_normal' => 5,
                    'detect_leadersh' => 5,
                    'detect_math' => 5,
                    'detect_creative' => 5,
                ];
            });
        }

        return response()->json([
            'evaluation' => $eval,
            'students' => $details
        ]);
    }

    public function saveGrade(Request $request, $id)
    {
        $eval = DB::table('igbh_weekly_evals')->where('id', $id)->first();
        if (!$eval) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $request->validate([
            'students' => 'required|array',
            'eval_ymd' => 'nullable|date'
        ]);

        if ($request->has('eval_ymd') && $request->eval_ymd) {
            DB::table('igbh_weekly_evals')->where('id', $id)->update(['eval_ymd' => $request->eval_ymd]);
            $eval->eval_ymd = $request->eval_ymd;
        }

        DB::beginTransaction();
        try {
            DB::table('igbh_weekly_eval_details')->where('weekly_eval_id', $id)->delete();

            $insertData = [];
            foreach ($request->students as $student) {
                $insertData[] = [
                    'weekly_eval_id' => $id,
                    'stu_seq' => $student['stu_seq'],
                    'stu_nm' => $student['stu_nm'] ?? null,
                    'workbook' => $student['workbook'] ?? 0,
                    'attd_listen' => $student['attd_listen'] ?? 5,
                    'attd_join' => $student['attd_join'] ?? 5,
                    'attd_express' => $student['attd_express'] ?? 5,
                    'attd_coop' => $student['attd_coop'] ?? 5,
                    'detect_normal' => $student['detect_normal'] ?? 5,
                    'detect_leadersh' => $student['detect_leadersh'] ?? 5,
                    'detect_math' => $student['detect_math'] ?? 5,
                    'detect_creative' => $student['detect_creative'] ?? 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Sync with summative results so it shows up in Summative list
                $summativeExists = DB::table('igbh_summative_results')
                    ->where('test_seq', $eval->test_seq)
                    ->where('stu_seq', $student['stu_seq'])
                    ->first();

                if (!$summativeExists) {
                    $summativeId = DB::table('igbh_summative_results')->insertGetId([
                        'test_seq' => $eval->test_seq,
                        'stu_seq' => $student['stu_seq'],
                        'stu_nm' => $student['stu_nm'] ?? null,
                        'class_seq' => $eval->class_seq,
                        'class_nm' => $eval->class_nm,
                        'teacher_nm' => $eval->teacher_nm ?? null,
                        'total_score' => 0,
                        'eval_dt' => $eval->eval_ymd,
                        'status' => 'D',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $summativeId = $summativeExists->id;
                    DB::table('igbh_summative_results')
                        ->where('id', $summativeId)
                        ->update([
                            'stu_nm' => $student['stu_nm'] ?? null,
                            'class_seq' => $eval->class_seq,
                            'class_nm' => $eval->class_nm,
                            'teacher_nm' => $eval->teacher_nm ?? null,
                            'eval_dt' => $eval->eval_ymd,
                            'updated_at' => now(),
                        ]);
                }

                $studentUpdates[] = [
                    'stu_seq' => $student['stu_seq'],
                    'summativeId' => $summativeId
                ];
            }

            if (!empty($insertData)) {
                DB::table('igbh_weekly_eval_details')->insert($insertData);
            }

            // Recalculate total_score after inserting details
            if (!empty($studentUpdates)) {
                foreach ($studentUpdates as $su) {
                    $allWeeks = DB::table('igbh_weekly_eval_details as d')
                        ->join('igbh_weekly_evals as e', 'd.weekly_eval_id', '=', 'e.id')
                        ->where('e.test_seq', $eval->test_seq)
                        ->where('d.stu_seq', $su['stu_seq'])
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

                    $subjectiveScore = DB::table('igbh_summative_result_details')
                        ->where('summative_result_id', $su['summativeId'])
                        ->sum('score');

                    $finalTotalScore = round($totalWorkbook + $avgAttitude + $avgDetection + $subjectiveScore, 1);

                    DB::table('igbh_summative_results')
                        ->where('id', $su['summativeId'])
                        ->update(['total_score' => $finalTotalScore]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error saving grades: ' . $e->getMessage()], 500);
        }
    }
}
