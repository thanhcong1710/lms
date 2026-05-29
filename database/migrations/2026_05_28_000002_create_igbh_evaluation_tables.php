<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. igbh_tests
        Schema::create('igbh_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_nm', 255)->comment('Tên bài kiểm tra');
            $table->integer('test_seq')->unique()->comment('Seq của bài kiểm tra gốc');
            $table->string('level_cd', 50)->nullable()->comment('Mã level nếu có');
            $table->timestamps();
        });

        // 2. igbh_student_results
        Schema::create('igbh_student_results', function (Blueprint $table) {
            $table->id();
            $table->integer('result_seq')->unique()->comment('Seq của kết quả gốc');
            $table->integer('test_seq');
            $table->integer('stu_seq');
            $table->string('stu_nm', 255)->comment('Tên học sinh');
            $table->string('stu_birth_dt', 50)->nullable()->comment('Ngày sinh học sinh');
            $table->string('reg_name', 255)->nullable()->comment('Người đăng ký / Giáo viên');
            $table->date('eval_dt')->nullable()->comment('Ngày thi');
            $table->timestamp('reg_date')->nullable()->comment('Ngày đăng ký');
            
            $table->integer('total_score')->default(0);
            $table->integer('subject_total')->default(0);
            $table->integer('thinking_total')->default(0);
            
            $table->string('assigned_level', 50)->nullable()->comment('Level bài tập được gán');
            $table->string('quarter_cd', 50)->nullable();
            $table->string('quarter_cd_nm', 255)->nullable();
            $table->string('class_type_cd', 50)->nullable();
            
            $table->timestamps();
        });

        // 3. igbh_student_result_details
        Schema::create('igbh_student_result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('igbh_student_result_id')->constrained('igbh_student_results')->onDelete('cascade');
            
            $table->string('question_no', 50)->comment('Số thứ tự câu hỏi');
            $table->string('question_type', 50)->comment('Loại câu hỏi: curriculum hoặc thinking');
            $table->integer('seq_id')->nullable()->comment('saSeq hoặc taSeq');
            
            // Score values
            $table->string('assigned_score', 50)->nullable()->comment('Đáp án học sinh (đối với môn học) hoặc Điểm thực tế (đối với tư duy)');
            
            // Additional attributes
            $table->string('unit', 50)->nullable()->comment('Đơn vị phân loại của câu hỏi (A, B, C...)');
            $table->string('is_correct', 10)->nullable()->comment('Đánh giá O hoặc X');
            $table->integer('max_score')->nullable()->comment('Điểm tối đa của câu hỏi tư duy');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('igbh_student_result_details');
        Schema::dropIfExists('igbh_student_results');
        Schema::dropIfExists('igbh_tests');
    }
};
