<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = Teacher::query();

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user) {
            if ($user->isTeacher() && $user->teacher_id) {
                $query->where('id', $user->teacher_id);
            } elseif ($user->isTeamLeader()) {
                $branchIds = $user->getAccessibleBranchLmsIds();
                if ($branchIds !== null) {
                    $query->whereIn('branch_id_lms', $branchIds);
                }
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ins_name', 'LIKE', "%{$search}%")
                  ->orWhere('id_lms', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ins_name' => 'required|string',
            'id_lms' => 'required|string',
            'branch_id_lms' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'head' => 'nullable|string',
            'status' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $teacher = Teacher::create($validated);

        // Automatically create a user account for the teacher
        $username = $request->id_lms;
        $email = $request->email ?: $username . '@lms.local';
        $isHead = $request->head === 'Y' || $request->head == 1;

        // Only create if user doesn't already exist
        $existingUser = User::where('email', $email)->orWhere('name', $username)->first();
        if (!$existingUser) {
            $user = User::create([
                'name' => $username,
                'email' => $email,
                'password' => Hash::make('@12345678'),
                'role' => $isHead ? 'team_leader' : 'teacher',
                'teacher_id' => $teacher->id,
                'branch_id' => null,
                'status' => 1,
            ]);
            $teacher->update(['user_id' => $user->id]);
        }

        return response()->json($teacher, 201);
    }

    public function show($id)
    {
        return response()->json(Teacher::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $validated = $request->validate([
            'ins_name' => 'sometimes|required|string',
            'id_lms' => 'nullable|string',
            'branch_id_lms' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'head' => 'nullable|string',
            'status' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $teacher->update($validated);

        if ($teacher->user_id) {
            $isHead = $teacher->head === 'Y' || $teacher->head == 1;
            User::where('id', $teacher->user_id)->update([
                'role' => $isHead ? 'team_leader' : 'teacher',
            ]);
        }

        return response()->json($teacher);
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}

