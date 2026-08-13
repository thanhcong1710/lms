<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;
use App\Models\Contract;
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
        if (!$teacher && $request->input('tchEmail')) {
            $teacher = Teacher::where('email', $request->input('tchEmail'))->first();
        }

        $teacherData = [
            'id_lms' => $request->input('membId'),
            'ins_name' => $request->input('tchNm'),
            'email' => $request->input('tchEmail'),
            'phone' => $request->input('tchContact'),
            'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
            'head' => $request->input('head') == 'Y' ? 1 : 0,
            'branch_id_lms' => $request->input('cntrId'),
        ];

        if (!$teacher) {
            $teacher = Teacher::create($teacherData);
        } else {
            $teacher->update($teacherData);
        }

        $this->createOrUpdateTeacherUser($teacher, $request);
        return response()->json(['status' => 'SUCCESS']);
    }

    public function teacherModAction(Request $request)
    {
        $teacher = Teacher::where('id_lms', $request->input('membId'))->first();
        if (!$teacher && $request->input('tchEmail')) {
            $teacher = Teacher::where('email', $request->input('tchEmail'))->first();
        }

        $teacherData = [
            'id_lms' => $request->input('membId'),
            'ins_name' => $request->input('tchNm'),
            'email' => $request->input('tchEmail'),
            'phone' => $request->input('tchContact'),
            'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
            'head' => $request->input('head') == 'Y' ? 1 : 0,
            'branch_id_lms' => $request->input('cntrId'),
        ];

        if ($teacher) {
            $teacher->update($teacherData);
        } else {
            $teacher = Teacher::create($teacherData);
        }

        $this->createOrUpdateTeacherUser($teacher, $request);
        return response()->json(['status' => 'SUCCESS']);
    }

    public function createOrUpdateTeacherUser($teacher, Request $request = null)
    {
        $cntrId = $request ? $request->input('cntrId') : $teacher->branch_id_lms;
        $branch = Branch::where('id_lms', $cntrId)->first();
        
        $email = $request ? $request->input('tchEmail') : $teacher->email;
        if (empty($email)) {
            $email = $teacher->id_lms . '@cms.vn';
        }

        $tchNm = $request ? $request->input('tchNm') : $teacher->ins_name;
        $isHead = $request ? ($request->input('head') == 'Y') : ($teacher->head == 1);
        $role = $isHead ? 'team_leader' : 'teacher';
        $status = $request ? ($request->input('tchStat') == 'US001' ? 1 : 0) : ($teacher->status ?? 1);

        $hrmId = $request ? ($request->input('hrmId') ?: $request->input('hrm_id') ?: $request->input('membId')) : $teacher->id_lms;

        $userData = [
            'name' => $tchNm ?: $teacher->ins_name,
            'email' => $email,
            'hrm_id' => $hrmId ?: $teacher->id_lms,
            'role' => $role,
            'branch_id' => $branch ? $branch->id : null,
            'teacher_id' => $teacher->id,
            'status' => $status,
        ];
        
        if ($request && $request->has('tchPasswd') && $request->input('tchPasswd')) {
            $userData['password'] = Hash::make($request->input('tchPasswd'));
        }

        $user = User::where('teacher_id', $teacher->id)->first();
        if (!$user && !empty($hrmId)) {
            $user = User::where('hrm_id', $hrmId)->first();
        }
        if (!$user && !empty($email)) {
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
        return $user;
    }

    public function classRegAction(Request $request)
    {
        $branch = Branch::where('id_lms', $request->input('cntrId'))->first();
        $teacher = Teacher::where('id_lms', $request->input('clsTch'))->first();

        $classLms = LmsClass::where('cls_name', $request->input('clsNm'))
                            ->where('branch_id_lms', $request->input('cntrId'))
                            ->first();

        $classData = [
            'cls_name' => $request->input('clsNm'),
            'teacher_id_lms' => $request->input('clsTch'),
            'teacher_id' => $teacher ? $teacher->id : null,
            'level_name' => $request->input('clsLevel'),
            'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
            'cls_type' => $request->input('clsType'),
            'branch_id_lms' => $request->input('cntrId'),
            'branch_id' => $branch ? $branch->id : null,
        ];

        if (!$classLms) {
            $classLms = LmsClass::create($classData);
            $classLms->class_seq = $classLms->id;
            $classLms->save();
        } else {
            $classLms->update($classData);
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
        $branch = Branch::where('id_lms', $request->input('cntrId'))->first();
        $teacher = Teacher::where('id_lms', $request->input('clsTch'))->first();

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

        $classData = [
            'cls_name' => $request->input('clsNm'),
            'pre_teacher_id_lms' => $request->input('prevClsTch'),
            'teacher_id_lms' => $request->input('clsTch'),
            'teacher_id' => $teacher ? $teacher->id : null,
            'level_name' => $request->input('clsLevel'),
            'cls_status' => $request->input('clsStat') == 'US001' ? 'US001' : 'US002',
            'cls_type' => $request->input('clsType'),
            'branch_id_lms' => $request->input('cntrId'),
            'branch_id' => $branch ? $branch->id : null,
        ];

        if ($classLms) {
            $classLms->update($classData);
        } else {
            $classLms = LmsClass::create($classData);
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
        if (!$student && $request->input('hStuSeq')) {
            $student = Student::where('id_lms', $request->input('hStuSeq'))->orWhere('id', $request->input('hStuSeq'))->first();
        }
        
        if (!$student) {
            $student = Student::create([
                'accounting_id' => $request->input('stuId'),
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
            ]);

            $student->id_lms = $student->id;
            $student->save();
        } else {
            $student->update([
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
                'accounting_id' => $request->input('stuId') ?: $student->accounting_id,
            ]);
        }

        $this->createOrUpdateStudentContract($student, $request);

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
                'accounting_id' => $request->input('stuId') ?: $student->accounting_id,
            ]);
        } else {
            $student = Student::create([
                'accounting_id' => $request->input('stuId'),
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
            ]);
            $student->id_lms = $student->id;
            $student->save();
        }

        $this->createOrUpdateStudentContract($student, $request);

        return response()->json(['status' => 'SUCCESS']);
    }

    private function createOrUpdateStudentContract(Student $student, Request $request)
    {
        $classSeq = $request->input('classSeq');
        $classLms = $classSeq ? LmsClass::where('class_seq', $classSeq)->orWhere('id', $classSeq)->first() : null;

        $cntrId = $request->input('cntrId') ?: $request->input('hMembId') ?: ($classLms ? $classLms->branch_id_lms : null);
        $branch = $cntrId ? Branch::where('id_lms', $cntrId)->orWhere('id', $cntrId)->first() : null;

        $contractData = [
            'class_id' => $classLms ? $classLms->id : null,
            'branch_id' => $branch ? $branch->id : null,
        ];

        if ($request->filled('startDt')) {
            $contractData['enrolment_start_date'] = $request->input('startDt');
        }
        if ($request->filled('endDt')) {
            $contractData['enrolment_last_date'] = $request->input('endDt');
        }
        if ($request->filled('validCd')) {
            $contractData['valid_cd'] = $request->input('validCd');
        }
        if ($request->filled('stuStat')) {
            $contractData['status'] = $request->input('stuStat');
        }
        if ($request->has('remark')) {
            $contractData['remark'] = $request->input('remark');
        }

        $contract = Contract::where('student_id', $student->id)->first();
        if ($contract) {
            $contract->update(array_filter($contractData, fn($v) => !is_null($v)));
        } else {
            Contract::create(array_merge([
                'student_id' => $student->id,
                'valid_cd' => 'VC005',
                'status' => 'SS002',
            ], $contractData));
        }
    }
}
