<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\LmsClass;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = Contract::with(['student', 'lmsClass', 'branch']);

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            $classQuery = LmsClass::query();
            if ($user->isTeacher()) {
                $teacherIdLms = $user->getTeacherIdLms();
                if ($teacherIdLms) {
                    $classQuery->where('teacher_id_lms', $teacherIdLms);
                }
            } elseif ($user->isTeamLeader()) {
                $branchIds = $user->getAccessibleBranchLmsIds();
                if ($branchIds !== null) {
                    $classQuery->whereIn('branch_id_lms', $branchIds);
                }
            }
            $classIds = $classQuery->pluck('id')->toArray();
            $query->whereIn('class_id', $classIds);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $contracts = $query->orderBy('id', 'desc')->paginate($limit);

        // Map joined names for frontend
        $data = $contracts->toArray();
        $data['data'] = collect($data['data'])->map(function ($c) {
            $c['student_name'] = $c['student']['name'] ?? '';
            $c['class_name'] = $c['lms_class']['cls_name'] ?? '';
            $c['branch_name'] = $c['branch']['name'] ?? '';
            unset($c['student'], $c['lms_class'], $c['branch']);
            return $c;
        })->toArray();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $contract = Contract::create($request->all());
        return response()->json($contract, 201);
    }

    public function show($id)
    {
        return response()->json(Contract::with(['student', 'lmsClass', 'branch'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->all());
        return response()->json($contract);
    }

    public function destroy($id)
    {
        Contract::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
