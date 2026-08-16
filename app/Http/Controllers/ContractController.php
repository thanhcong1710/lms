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
        if ($user) {
            $user->scopeContracts($query);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('crm_id', 'LIKE', "%{$search}%");
                })->orWhereHas('lmsClass', function ($cq) use ($search) {
                    $cq->where('cls_name', 'LIKE', "%{$search}%");
                });
            });
        }

        $contracts = $query->orderBy('id', 'desc')->paginate($limit);

        // Map joined names for frontend
        $data = $contracts->toArray();
        $data['data'] = collect($data['data'])->map(function ($c) {
            $c['student_name'] = $c['student']['name'] ?? '';
            $c['student_crm_id'] = $c['student']['crm_id'] ?? '';
            $c['class_name'] = $c['lms_class']['cls_name'] ?? '';
            $c['branch_name'] = $c['branch']['name'] ?? '';
            unset($c['student'], $c['lms_class'], $c['branch']);
            return $c;
        })->toArray();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:lms_classes,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $contract = Contract::create($request->all());
        return response()->json($contract, 201);
    }

    public function show($id)
    {
        return response()->json(Contract::with(['student', 'lmsClass', 'branch'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'class_id' => 'sometimes|required|exists:lms_classes,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        $contract = Contract::findOrFail($id);
        
        // Filter out null values which might come from empty strings
        $data = array_filter($request->all(), function($value) {
            return $value !== null && $value !== '';
        });
        
        $contract->update($data);
        return response()->json($contract);
    }

    public function destroy(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        Contract::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
