<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = Branch::query();

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user) {
            $branchIds = $user->getAccessibleBranchLmsIds();
            if ($branchIds !== null) {
                $query->whereIn('id_lms', $branchIds);
            }
        }

        if ($search = $request->query('search')) {
            $search = trim($search);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('id_lms', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
        }

        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'id_lms' => 'nullable|string',
            'email' => 'nullable|email',
            'hotline' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $branch = Branch::create($validated);
        return response()->json($branch, 201);
    }

    public function show($id)
    {
        return response()->json(Branch::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'id_lms' => 'nullable|string',
            'email' => 'nullable|email',
            'hotline' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $branch->update($validated);
        return response()->json($branch);
    }

    public function destroy($id)
    {
        Branch::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}

