<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertSummativeClassData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-summative-classes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old summative results into class-based summative evals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting conversion...');

        // Lấy danh sách các lớp đã có dữ liệu đánh giá cuối kỳ
        $classes = DB::table('igbh_summative_results')
            ->select('test_seq', 'class_seq', DB::raw('MAX(class_nm) as class_nm'), DB::raw('MAX(teacher_nm) as teacher_nm'), DB::raw('MAX(eval_dt) as eval_ymd'), DB::raw('MAX(status) as status'))
            ->groupBy('test_seq', 'class_seq')
            ->get();

        $count = 0;
        foreach ($classes as $c) {
            if (!$c->class_seq) continue;
            
            // Kiểm tra xem đã tồn tại trong bảng igbh_class_summative_evals chưa
            $exists = DB::table('igbh_class_summative_evals')
                ->where('test_seq', $c->test_seq)
                ->where('class_seq', $c->class_seq)
                ->exists();

            if (!$exists) {
                // Determine status. If the old records have 'completed' or 'draft', we can just use it, or default to completed.
                $status = $c->status ?: 'completed';
                if (!in_array($status, ['draft', 'completed'])) {
                    $status = 'completed';
                }
                
                DB::table('igbh_class_summative_evals')->insert([
                    'test_seq' => $c->test_seq,
                    'class_seq' => $c->class_seq,
                    'class_nm' => $c->class_nm,
                    'teacher_nm' => $c->teacher_nm,
                    'eval_ymd' => $c->eval_ymd,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Đồng bộ status cho các học sinh nếu trước đó status chưa có hoặc null
                DB::table('igbh_summative_results')
                    ->where('test_seq', $c->test_seq)
                    ->where('class_seq', $c->class_seq)
                    ->update(['status' => $status]);
                    
                $count++;
            }
        }

        $this->info("Converted $count class summative records.");
        return 0;
    }
}
