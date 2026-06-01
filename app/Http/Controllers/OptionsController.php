<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;

class OptionsController extends Controller
{
    /**
     * Return all branches for select boxes.
     */
    public function branches(Request $request)
    {
        $branches = Branch::select('id', 'name', 'id_lms')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $branches]);
    }

    /**
     * Return all teachers for select boxes.
     */
    public function teachers(Request $request)
    {
        $query = Teacher::select('id', 'ins_name', 'id_lms', 'branch_id_lms');

        if ($branchLms = $request->query('branch_id_lms')) {
            $query->where('branch_id_lms', $branchLms);
        }

        $teachers = $query->orderBy('ins_name')->get();

        return response()->json(['data' => $teachers]);
    }

    /**
     * Return all classes for select boxes.
     */
    public function classes(Request $request)
    {
        $query = LmsClass::select('id', 'cls_name', 'class_seq', 'branch_id_lms', 'teacher_id_lms', 'level_name');

        if ($branchLms = $request->query('branch_id_lms')) {
            $query->where('branch_id_lms', $branchLms);
        }
        if ($teacherLms = $request->query('teacher_id_lms')) {
            $query->where('teacher_id_lms', $teacherLms);
        }

        $classes = $query->orderBy('cls_name')->get();

        return response()->json(['data' => $classes]);
    }

    /**
     * Search students for select boxes (with search keyword).
     */
    public function students(Request $request)
    {
        $query = Student::select('id', 'name', 'id_lms', 'date_of_birth');

        if ($classId = $request->query('class_id')) {
            $studentIds = \App\Models\Contract::where('class_id', $classId)->pluck('student_id')->toArray();
            $query->whereIn('id', $studentIds);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('id_lms', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->limit(50)->get();

        return response()->json(['data' => $students]);
    }
}
