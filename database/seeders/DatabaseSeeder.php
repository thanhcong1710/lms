<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\LmsClass;
use App\Models\Student;
use App\Models\Contract;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Login Users
        User::updateOrCreate(
            ['email' => 'admin@lms.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('secret'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'giaovientest@cmsedu.vn'],
            [
                'name' => 'giaovientest',
                'password' => Hash::make('@12345678'),
            ]
        );

        // 2. Create Branches
        $branch1 = Branch::create([
            'name' => 'CMS Phan Xích Long',
            'id_lms' => 'HCM_PXL',
            'status' => 'US001',
        ]);

        $branch2 = Branch::create([
            'name' => 'CMS Nguyễn Chí Thanh',
            'id_lms' => 'HN_NCT',
            'status' => 'US001',
        ]);

        // 3. Create Teachers
        $teacher1 = Teacher::create([
            'ins_name' => 'Teacher Giaovientest',
            'id_lms' => 'giaovientest',
            'branch_id_lms' => 'HCM_PXL',
            'email' => 'giaovientest@cmsedu.vn',
            'phone' => '0912345678',
            'head' => 'Y',
            'status' => 'US001',
        ]);

        $teacher2 = Teacher::create([
            'ins_name' => 'Teacher Minh Trang',
            'id_lms' => 'trangm',
            'branch_id_lms' => 'HN_NCT',
            'email' => 'trangm@cmsedu.vn',
            'phone' => '0987654321',
            'head' => 'N',
            'status' => 'US001',
        ]);

        // 4. Create Classes
        $class1 = LmsClass::create([
            'cls_name' => 'U-Crea PXL K01',
            'class_seq' => 1024,
            'level_name' => 'Level 1',
            'cls_type' => 'CT001',
            'teacher_id_lms' => 'giaovientest',
            'branch_id_lms' => 'HCM_PXL',
            'cls_status' => 'US001',
        ]);

        $class2 = LmsClass::create([
            'cls_name' => 'i-Garten NCT K02',
            'class_seq' => 2048,
            'level_name' => 'Level 2',
            'cls_type' => 'CT002',
            'teacher_id_lms' => 'trangm',
            'branch_id_lms' => 'HN_NCT',
            'cls_status' => 'US001',
        ]);

        // 5. Create Students
        $student1 = Student::create([
            'name' => 'Nguyễn Văn An',
            'date_of_birth' => '2018-05-15',
            'gender' => 'M',
            'accounting_id' => 'ACC-001234',
            'id_lms' => 'STU_AN',
        ]);

        $student2 = Student::create([
            'name' => 'Lê Thị Bình',
            'date_of_birth' => '2019-09-20',
            'gender' => 'F',
            'accounting_id' => 'ACC-005678',
            'id_lms' => 'STU_BINH',
        ]);

        // 6. Create Contracts
        Contract::create([
            'student_id' => $student1->id,
            'class_id' => $class1->id,
            'branch_id' => $branch1->id,
            'enrolment_start_date' => '2026-06-01',
            'enrolment_last_date' => '2026-12-01',
            'valid_cd' => 'VC005',
            'status' => 'SS001',
            'remark' => 'First sync record',
        ]);

        Contract::create([
            'student_id' => $student2->id,
            'class_id' => $class2->id,
            'branch_id' => $branch2->id,
            'enrolment_start_date' => '2026-07-01',
            'enrolment_last_date' => '2027-01-01',
            'valid_cd' => 'VC005',
            'status' => 'SS001',
            'remark' => 'Second sync record',
        ]);
    }
}
