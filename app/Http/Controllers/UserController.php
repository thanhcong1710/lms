<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = User::with(['branch', 'teacher']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate($limit);

        $data = $users->toArray();
        $data['data'] = collect($data['data'])->map(function ($user) {
            $user['branch_name'] = $user['branch']['name'] ?? null;
            $user['teacher_name'] = $user['teacher']['ins_name'] ?? null;
            unset($user['branch'], $user['teacher']);
            return $user;
        })->toArray();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,team_leader,teacher',
            'branch_id' => 'nullable|exists:branches,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? '@12345678'),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
            'teacher_id' => $request->teacher_id,
            'status' => 1,
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        return response()->json(User::with(['branch', 'teacher'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,team_leader,teacher',
            'branch_id' => 'nullable|exists:branches,id',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'branch_id' => $request->branch_id,
            'teacher_id' => $request->teacher_id,
            'status' => $request->status ?? $user->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last admin user'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
