<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class IntegrationController extends Controller
{
    public function getToken(Request $request)
    {
        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'accessToken' => 'fake-token-for-crm-integration',
            ]
        ]);
    }

    public function centerRegAction(Request $request)
    {
        $branch = Branch::where('id_lms', $request->input('membId'))->first();
        if (!$branch) {
            Branch::create([
                'id_lms' => $request->input('membId'),
                'name' => $request->input('cntrNm'),
                'email' => $request->input('cntrEmail'),
                'hotline' => $request->input('cntrContact'),
                'status' => $request->input('cntrStat') == 'US001' ? 1 : 0,
            ]);
        }
        return response()->json(['status' => 'SUCCESS']);
    }

    public function centerModAction(Request $request)
    {
        $branch = Branch::where('id_lms', $request->input('membId'))->first();
        if ($branch) {
            $branch->update([
                'name' => $request->input('cntrNm'),
                'email' => $request->input('cntrEmail'),
                'hotline' => $request->input('cntrContact'),
                'status' => $request->input('cntrStat') == 'US001' ? 1 : 0,
            ]);
        }
        return response()->json(['status' => 'SUCCESS']);
    }

    public function teacherRegAction(Request $request)
    {
        $teacher = Teacher::where('id_lms', $request->input('membId'))->first();
        if (!$teacher) {
            $teacher = Teacher::create([
                'id_lms' => $request->input('membId'),
                'ins_name' => $request->input('tchNm'),
                'email' => $request->input('tchEmail'),
                'phone' => $request->input('tchContact'),
                'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
                'head' => $request->input('head') == 'Y' ? 1 : 0,
                'branch_id_lms' => $request->input('cntrId'),
            ]);
            $this->createOrUpdateTeacherUser($teacher, $request);
        }
        return response()->json(['status' => 'SUCCESS']);
    }

    public function teacherModAction(Request $request)
    {
        $teacher = Teacher::where('id_lms', $request->input('membId'))->first();
        if ($teacher) {
            $teacher->update([
                'ins_name' => $request->input('tchNm'),
                'email' => $request->input('tchEmail'),
                'phone' => $request->input('tchContact'),
                'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
                'head' => $request->input('head') == 'Y' ? 1 : 0,
                'branch_id_lms' => $request->input('cntrId'),
            ]);
            $this->createOrUpdateTeacherUser($teacher, $request);
        }
        return response()->json(['status' => 'SUCCESS']);
    }

    private function createOrUpdateTeacherUser($teacher, Request $request)
    {
        $branch = Branch::where('id_lms', $request->input('cntrId'))->first();
        $email = $request->input('tchEmail');
        if (empty($email)) {
            $email = $request->input('membId') . '@cms.vn';
        }

        $role = $request->input('head') == 'Y' ? 'team_leader' : 'teacher';
        
        $userData = [
            'name' => $request->input('tchNm'),
            'email' => $email,
            'role' => $role,
            'branch_id' => $branch ? $branch->id : null,
            'teacher_id' => $teacher->id,
            'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
        ];
        
        if ($request->has('tchPasswd') && $request->input('tchPasswd')) {
            $userData['password'] = Hash::make($request->input('tchPasswd'));
        }

        $user = User::where('teacher_id', $teacher->id)->first();
        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            $user->update($userData);
        } else {
            if (!isset($userData['password'])) {
                $userData['password'] = Hash::make('@12345678');
            }
            $user = User::create($userData);
        }

        $teacher->update(['user_id' => $user->id]);
    }

    public function classRegAction(Request $request)
    {
        $classLms = LmsClass::where('cls_name', $request->input('clsNm'))
                            ->where('branch_id_lms', $request->input('cntrId'))
                            ->first();

        if (!$classLms) {
            $classLms = LmsClass::create([
                'cls_name' => $request->input('clsNm'),
                'teacher_id_lms' => $request->input('clsTch'),
                'level_name' => $request->input('clsLevel'),
                'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
                'cls_type' => $request->input('clsType'),
                'branch_id_lms' => $request->input('cntrId'),
            ]);

            $classLms->class_seq = $classLms->id;
            $classLms->save();
        } else {
            $classLms->update([
                'teacher_id_lms' => $request->input('clsTch'),
                'level_name' => $request->input('clsLevel'),
                'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
                'cls_type' => $request->input('clsType'),
            ]);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'classSeq' => $classLms->class_seq ?? $classLms->id,
            ]
        ]);
    }

    public function classModAction(Request $request)
    {
        $classSeq = $request->input('classSeq');
        $classLms = null;
        if ($classSeq) {
            $classLms = LmsClass::where('class_seq', $classSeq)->orWhere('id', $classSeq)->first();
        }
        if (!$classLms) {
            $classLms = LmsClass::where('cls_name', $request->input('clsNm'))
                                ->where('branch_id_lms', $request->input('cntrId'))
                                ->first();
        }

        if ($classLms) {
            $classLms->update([
                'cls_name' => $request->input('clsNm'),
                'pre_teacher_id_lms' => $request->input('prevClsTch'),
                'teacher_id_lms' => $request->input('clsTch'),
                'level_name' => $request->input('clsLevel'),
                'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
                'cls_type' => $request->input('clsType'),
                'branch_id_lms' => $request->input('cntrId'),
            ]);
        } else {
            $classLms = LmsClass::create([
                'cls_name' => $request->input('clsNm'),
                'teacher_id_lms' => $request->input('clsTch'),
                'pre_teacher_id_lms' => $request->input('prevClsTch'),
                'level_name' => $request->input('clsLevel'),
                'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
                'cls_type' => $request->input('clsType'),
                'branch_id_lms' => $request->input('cntrId'),
            ]);
            $classLms->class_seq = $classLms->id;
            $classLms->save();
        }

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'classSeq' => $classLms->class_seq ?? $classLms->id,
            ]
        ]);
    }

    public function studentRegAction(Request $request)
    {
        $student = Student::where('accounting_id', $request->input('stuId'))->first();
        
        if (!$student) {
            $student = Student::create([
                'accounting_id' => $request->input('stuId'),
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
            ]);

            $student->id_lms = $student->id;
            $student->save();
        }

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'stuSeq' => $student->id_lms ?? $student->id,
            ]
        ]);
    }

    public function studentModAction(Request $request)
    {
        $student = Student::where('accounting_id', $request->input('stuId'))->first();
        if (!$student && $request->input('hStuSeq')) {
            $student = Student::where('id_lms', $request->input('hStuSeq'))->orWhere('id', $request->input('hStuSeq'))->first();
        }

        if ($student) {
            $student->update([
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
                'accounting_id' => $request->input('stuId'),
            ]);
        }
        return response()->json(['status' => 'SUCCESS']);
    }
}
