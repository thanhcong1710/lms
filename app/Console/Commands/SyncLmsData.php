<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLmsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:lms-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync core LMS data (branches, classes, teachers, students, contracts) from crawled evaluation data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting LMS Data Sync from crawled evaluations...");

        DB::beginTransaction();
        try {
            // 1. Ensure a default branch exists (if no specific branch mapping is available)
            $defaultBranchId = DB::table('branches')->insertGetId([
                'name' => 'Trung Tâm Mặc Định',
                'status' => 'US001',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->info("Created default branch ID: $defaultBranchId");

            // 2. Sync Teachers & Classes from igbh_weekly_evals
            $evals = DB::table('igbh_weekly_evals')
                ->select('class_seq', 'class_nm', 'teacher_nm')
                ->whereNotNull('class_seq')
                ->whereNotNull('class_nm')
                ->groupBy('class_seq', 'class_nm', 'teacher_nm')
                ->get();

            $this->info("Processing {$evals->count()} class/teacher mappings...");

            foreach ($evals as $eval) {
                // Find or create teacher
                $teacherId = null;
                if ($eval->teacher_nm) {
                    $teacher = DB::table('teachers')->where('ins_name', $eval->teacher_nm)->first();
                    if ($teacher) {
                        $teacherId = $teacher->id;
                    } else {
                        $teacherId = DB::table('teachers')->insertGetId([
                            'ins_name' => $eval->teacher_nm,
                            'branch_id' => $defaultBranchId,
                            'status' => 'US001',
                            'head' => 'N',
                            'ins_name' => $eval->teacher_nm,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Find or create class
                $class = DB::table('classes')->where('class_seq', $eval->class_seq)->first();
                if (!$class) {
                    $classId = DB::table('classes')->insertGetId([
                        'class_seq' => $eval->class_seq,
                        'cls_name' => $eval->class_nm,
                        'teacher_id' => $teacherId,
                        'branch_id' => $defaultBranchId,
                        'cls_status' => 'US001',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $classId = $class->id;
                    // Update teacher if missing
                    if (!$class->teacher_id && $teacherId) {
                        DB::table('classes')->where('id', $classId)->update([
                            'teacher_id' => $teacherId,
                            'branch_id' => $defaultBranchId
                        ]);
                    }
                }
            }

            // 3. Sync Students & Contracts from igbh_weekly_eval_details
            $details = DB::table('igbh_weekly_eval_details as d')
                ->join('igbh_weekly_evals as e', 'd.weekly_eval_id', '=', 'e.id')
                ->select('d.stu_seq', 'd.stu_nm', 'e.class_seq')
                ->whereNotNull('d.stu_seq')
                ->groupBy('d.stu_seq', 'd.stu_nm', 'e.class_seq')
                ->get();

            $this->info("Processing {$details->count()} student/contract mappings...");

            foreach ($details as $d) {
                // Find or create student
                $student = DB::table('students')->where('id_lms', $d->stu_seq)->first();
                if (!$student) {
                    // It's safer to store the legacy stu_seq into id_lms, 
                    // since our local DB generates its own auto-increment `id`.
                    $studentId = DB::table('students')->insertGetId([
                        'id_lms' => $d->stu_seq,
                        'name' => $d->stu_nm ?? 'Unknown Student',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $studentId = $student->id;
                }

                // Link to contract
                $class = DB::table('classes')->where('class_seq', $d->class_seq)->first();
                if ($class) {
                    $contractExists = DB::table('contracts')
                        ->where('student_id', $studentId)
                        ->where('class_id', $class->id)
                        ->exists();

                    if (!$contractExists) {
                        DB::table('contracts')->insert([
                            'student_id' => $studentId,
                            'class_id' => $class->id,
                            'branch_id' => $defaultBranchId,
                            'status' => 'SS002', // Active
                            'valid_cd' => 'VC005',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            $this->info("LMS Data Sync completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Sync failed: " . $e->getMessage());
        }
    }
}
