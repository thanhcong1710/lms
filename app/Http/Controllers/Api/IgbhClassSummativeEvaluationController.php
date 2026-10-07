<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LmsClass;

class IgbhClassSummativeEvaluationController extends Controller
{
    public function getResults(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        
        $query = DB::table('igbh_class_summative_evals as c')
            ->join('igbh_tests as t', 'c.test_seq', '=', 't.test_seq')
            ->select(
                'c.id',
                't.test_nm',
                't.level_cd',
                'c.class_nm',
                'c.teacher_nm',
                'c.eval_ymd',
                'c.status',
                'c.created_at'
            );

        // Role-based filtering
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            $classQuery = $user->scopeClasses(LmsClass::query());
            $classNames = $classQuery->pluck('cls_name')->toArray();
            $query->whereIn('c.class_nm', $classNames);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('c.class_nm', 'like', "%{$search}%")
                  ->orWhere('c.teacher_nm', 'like', "%{$search}%")
                  ->orWhere('t.test_nm', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('c.created_at', 'desc');

        $results = $query->paginate($perPage);

        return response()->json($results);
    }

    public function getInitData(Request $request)
    {
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        $classQuery = LmsClass::query();
        if ($user) {
            $user->scopeClasses($classQuery);
        }

        // For summative tests
        $tests = DB::table('igbh_tests')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('igbh_summative_themes')
                      ->whereRaw('igbh_summative_themes.test_seq = igbh_tests.test_seq');
            })
            ->select('test_seq', 'test_nm', 'level_cd')
            ->get();

        $classes = $classQuery->select('id', 'class_seq', 'cls_name', 'cls_name as class_nm', 'level_name', 'cls_type', 'product_id', 'branch_id')->get();

        return response()->json([
            'tests' => $tests,
            'classes' => $classes
        ]);
    }

    public function createResult(Request $request)
    {
        $request->validate([
            'test_seq' => 'required|integer',
            'class_seq' => 'required|integer',
            'eval_ymd' => 'required|date'
        ]);

        try {
            DB::beginTransaction();
            
            $testObj = DB::table('igbh_tests')->where('test_seq', $request->test_seq)->first();
            $classObj = DB::table('classes')->where('class_seq', $request->class_seq)->first();

            if (!$testObj || !$classObj) {
                return response()->json(['message' => 'Test or class not found'], 404);
            }

            // Check if exists
            $exists = DB::table('igbh_class_summative_evals')
                ->where('test_seq', $request->test_seq)
                ->where('class_seq', $request->class_seq)
                ->first();

            if ($exists) {
                return response()->json(['message' => 'Lớp này đã được khởi tạo đánh giá cho bài kiểm tra này'], 400);
            }

            $user = \App\Http\Controllers\AuthController::resolveUser($request);

            $id = DB::table('igbh_class_summative_evals')->insertGetId([
                'test_seq' => $request->test_seq,
                'class_seq' => $request->class_seq,
                'class_nm' => $classObj->cls_name,
                'teacher_nm' => $user ? $user->name : '',
                'eval_ymd' => $request->eval_ymd,
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Also fetch students and create igbh_summative_results records if they don't exist
            $students = DB::table('contracts as c')
                ->join('students as s', 'c.student_id', '=', 's.id')
                ->where('c.class_id', $classObj->id)
                ->whereNotIn('c.status', ['SS003', 'SS004'])
                ->select('s.stu_seq', 's.name as stu_nm')
                ->get();
                
            foreach ($students as $stu) {
                $stuExists = DB::table('igbh_summative_results')
                    ->where('test_seq', $request->test_seq)
                    ->where('stu_seq', $stu->stu_seq)
                    ->exists();
                if (!$stuExists) {
                    DB::table('igbh_summative_results')->insert([
                        'test_seq' => $request->test_seq,
                        'stu_seq' => $stu->stu_seq,
                        'stu_nm' => $stu->stu_nm,
                        'class_seq' => $request->class_seq,
                        'class_nm' => $classObj->cls_name,
                        'teacher_nm' => $user ? $user->name : '',
                        'eval_dt' => $request->eval_ymd,
                        'status' => 'draft',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    DB::table('igbh_summative_results')
                        ->where('test_seq', $request->test_seq)
                        ->where('stu_seq', $stu->stu_seq)
                        ->update(['status' => 'draft']); // Make sure status matches class
                }
            }

            DB::commit();
            return response()->json(['id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function getClassData($id)
    {
        $classEval = DB::table('igbh_class_summative_evals')->where('id', $id)->first();
        if (!$classEval) {
            return response()->json(['message' => 'Not found'], 404);
        }
        
        $testObj = DB::table('igbh_tests')->where('test_seq', $classEval->test_seq)->first();

        // Get students in this class that have a summative result for this test
        $students = DB::table('igbh_summative_results')
            ->where('test_seq', $classEval->test_seq)
            ->where('class_seq', $classEval->class_seq)
            ->select('id', 'stu_seq', 'stu_nm', 'status', 'total_score', 'eval_dt')
            ->orderBy('stu_nm')
            ->get();
            
        return response()->json([
            'session_info' => $classEval,
            'test_info' => $testObj,
            'students' => $students
        ]);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,completed'
        ]);
        
        try {
            DB::beginTransaction();
            $classEval = DB::table('igbh_class_summative_evals')->where('id', $id)->first();
            
            if (!$classEval) {
                return response()->json(['message' => 'Not found'], 404);
            }
            
            DB::table('igbh_class_summative_evals')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);
            
            // Also update all students in this class
            DB::table('igbh_summative_results')
                ->where('test_seq', $classEval->test_seq)
                ->where('class_seq', $classEval->class_seq)
                ->update(['status' => $request->status]);
                
            DB::commit();
            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function deleteResult($id)
    {
        $classEval = DB::table('igbh_class_summative_evals')->where('id', $id)->first();
        if ($classEval) {
            DB::table('igbh_summative_results')
                ->where('test_seq', $classEval->test_seq)
                ->where('class_seq', $classEval->class_seq)
                ->delete();
            DB::table('igbh_class_summative_evals')->where('id', $id)->delete();
        }
        return response()->json(['message' => 'Deleted successfully']);
    }
}
