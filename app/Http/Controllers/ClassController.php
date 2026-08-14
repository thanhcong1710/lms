<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LmsClass;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = LmsClass::query();

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user) {
            $user->scopeClasses($query);
        }

        if ($clsTypeGroup = $request->query('cls_type_group')) {
            if ($clsTypeGroup === 'ucrea') {
                $query->where('product_id', 1);
            } elseif ($clsTypeGroup === 'igaten') {
                $query->where('product_id', 2);
            } elseif (in_array($clsTypeGroup, ['black_hold', 'bh', 'bright_heading'])) {
                $query->where('product_id', 3);
            } elseif ($clsTypeGroup === 'demo') {
                $query->where('product_id', 100);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cls_name', 'LIKE', "%{$search}%")
                  ->orWhere('class_seq', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $data = $request->all();
        if (!empty($data['branch_id_lms']) && empty($data['branch_id'])) {
            $branch = \App\Models\Branch::where('id_lms', $data['branch_id_lms'])->first();
            if ($branch) $data['branch_id'] = $branch->id;
        }
        if (!empty($data['teacher_id_lms']) && empty($data['teacher_id'])) {
            $teacher = \App\Models\Teacher::where('id_lms', $data['teacher_id_lms'])->first();
            if ($teacher) $data['teacher_id'] = $teacher->id;
        }
        if (empty($data['class_seq'])) {
            $maxSeq = LmsClass::max('class_seq');
            $data['class_seq'] = $maxSeq ? ($maxSeq + 1) : 90001;
        }
        if (empty($data['cls_status'])) {
            $data['cls_status'] = '1';
        }
        $class = LmsClass::create($data);
        return response()->json($class, 201);
    }

    public function show($id)
    {
        return response()->json(LmsClass::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $class = LmsClass::findOrFail($id);
        $data = $request->all();
        if (!empty($data['branch_id_lms'])) {
            $branch = \App\Models\Branch::where('id_lms', $data['branch_id_lms'])->first();
            if ($branch) $data['branch_id'] = $branch->id;
        }
        if (!empty($data['teacher_id_lms'])) {
            $teacher = \App\Models\Teacher::where('id_lms', $data['teacher_id_lms'])->first();
            if ($teacher) $data['teacher_id'] = $teacher->id;
        }
        $class->update($data);
        return response()->json($class);
    }

    public function destroy(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        LmsClass::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
