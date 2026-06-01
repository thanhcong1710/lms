<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Contract;
use App\Models\LmsClass;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = Student::query();

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user && !$user->isAdmin()) {
            // Get accessible student IDs via contracts/classes
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
            $studentIds = Contract::whereIn('class_id', $classIds)->pluck('student_id')->unique()->toArray();
            $query->whereIn('id', $studentIds);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('id_lms', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $student = Student::create($request->all());
        return response()->json($student, 201);
    }

    public function show($id)
    {
        return response()->json(Student::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->all());
        return response()->json($student);
    }

    public function destroy($id)
    {
        Student::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
