<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = AuthController::resolveUser($request);

        if (!$user) {
            return response()->json([
                'total_branches' => 0,
                'total_teachers' => 0,
                'active_classes' => 0,
                'enrolled_students' => 0,
                'ucrea_classes' => 0,
                'igbh_classes' => 0,
            ]);
        }

        // 1. Branches Count
        if ($user->isAdmin()) {
            $totalBranches = Branch::count();
        } else {
            $branchIds = $user->getAccessibleBranchLmsIds() ?? [];
            $totalBranches = count($branchIds);
        }

        // 2. Teachers Count
        if ($user->isAdmin()) {
            $totalTeachers = Teacher::count();
        } elseif ($user->isTeamLeader()) {
            $branchIds = $user->getAccessibleBranchLmsIds() ?? [];
            $totalTeachers = Teacher::whereIn('branch_id_lms', $branchIds)->count();
        } else {
            $totalTeachers = $user->teacher_id ? 1 : 0;
        }

        // 3. Classes Count
        $classQuery = $user->scopeClasses(LmsClass::query());
        $activeClasses = (clone $classQuery)->count();
        $ucreaClasses = (clone $classQuery)->where('product_id', 1)->count();
        $igbhClasses = (clone $classQuery)->whereIn('product_id', [2, 3, 100])->count();

        // 4. Students Count
        $studentQuery = $user->scopeStudents(Student::query());
        $enrolledStudents = $studentQuery->count();

        return response()->json([
            'total_branches' => $totalBranches,
            'total_teachers' => $totalTeachers,
            'active_classes' => $activeClasses,
            'enrolled_students' => $enrolledStudents,
            'ucrea_classes' => $ucreaClasses,
            'igbh_classes' => $igbhClasses,
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
            ]
        ]);
    }
}
