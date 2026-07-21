<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;

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
        $branch = Branch::updateOrCreate(
            ['id_lms' => $request->input('membId')],
            [
                'name' => $request->input('cntrNm'),
                'email' => $request->input('cntrEmail'),
                'hotline' => $request->input('cntrContact'),
                'status' => $request->input('cntrStat') == 'US001' ? 1 : 0,
            ]
        );
        return response()->json(['status' => 'SUCCESS']);
    }

    public function centerModAction(Request $request)
    {
        return $this->centerRegAction($request);
    }

    public function teacherRegAction(Request $request)
    {
        $teacher = Teacher::updateOrCreate(
            ['id_lms' => $request->input('membId')],
            [
                'ins_name' => $request->input('tchNm'),
                'email' => $request->input('tchEmail'),
                'phone' => $request->input('tchContact'),
                'status' => $request->input('tchStat') == 'US001' ? 1 : 0,
                'head' => $request->input('head') == 'Y' ? 1 : 0,
                'branch_id_lms' => $request->input('cntrId'),
            ]
        );
        return response()->json(['status' => 'SUCCESS']);
    }

    public function teacherModAction(Request $request)
    {
        return $this->teacherRegAction($request);
    }

    public function classRegAction(Request $request)
    {
        $classLms = LmsClass::create([
            'cls_name' => $request->input('clsNm'),
            'teacher_id_lms' => $request->input('clsTch'),
            'level_name' => $request->input('clsLevel'),
            'cls_status' => $request->input('clsStat') == 'US001' ? 'active' : 'inactive',
            'cls_type' => $request->input('clsType'),
            'branch_id_lms' => $request->input('cntrId'),
        ]);

        $classLms->class_seq = $classLms->id;
        $classLms->save();

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'classSeq' => $classLms->id,
            ]
        ]);
    }

    public function classModAction(Request $request)
    {
        $classSeq = $request->input('classSeq');
        if ($classSeq) {
            $classLms = LmsClass::where('class_seq', $classSeq)->orWhere('id', $classSeq)->first();
            if ($classLms) {
                $classLms->update([
                    'cls_name' => $request->input('clsNm'),
                    'pre_teacher_id_lms' => $request->input('prevClsTch'),
                    'teacher_id_lms' => $request->input('clsTch'),
                    'level_name' => $request->input('clsLevel'),
                    'cls_status' => $request->input('clsStat') == 'US001' ? 'active' : 'inactive',
                    'cls_type' => $request->input('clsType'),
                    'branch_id_lms' => $request->input('cntrId'),
                ]);
            }
        }
        return response()->json(['status' => 'SUCCESS']);
    }

    public function studentRegAction(Request $request)
    {
        $student = Student::updateOrCreate(
            ['accounting_id' => $request->input('stuId')],
            [
                'name' => $request->input('stuNm'),
                'date_of_birth' => $request->input('stuBirthDt'),
                'gender' => $request->input('stuGen'),
            ]
        );

        $student->id_lms = $student->id;
        $student->save();

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'stuSeq' => $student->id,
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
