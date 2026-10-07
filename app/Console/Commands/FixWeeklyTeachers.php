<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixWeeklyTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-weekly-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing teacher names in weekly evals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing missing teacher names for weekly evaluations...');
        
        $evals = DB::table('igbh_weekly_evals')
            ->whereNull('teacher_nm')
            ->orWhere('teacher_nm', '')
            ->get();
            
        $fixCount = 0;
        foreach ($evals as $e) {
            $teacher = DB::table('classes as c')
                ->join('teachers as t', 'c.teacher_id', '=', 't.id')
                ->where('c.class_seq', $e->class_seq)
                ->select('t.ins_name')
                ->first();

            if ($teacher && $teacher->ins_name) {
                DB::table('igbh_weekly_evals')
                    ->where('id', $e->id)
                    ->update(['teacher_nm' => $teacher->ins_name]);
                $fixCount++;
            }
        }
        $this->info("Fixed teacher_nm for $fixCount weekly records.");
        
        return 0;
    }
}
