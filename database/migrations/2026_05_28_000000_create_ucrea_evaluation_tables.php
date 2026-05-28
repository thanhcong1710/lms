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
        // 1. ucrea_tests: Define the test types (e.g. TT001, TT002) and Levels (L4, etc)
        Schema::create('ucrea_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_cd', 50)->comment('Mã bài kiểm tra (TT001, TT002...)');
            $table->string('test_nm', 255)->comment('Tên bài kiểm tra');
            $table->string('level_cd', 50)->comment('Level (L1, L2, L3, L4...)');
            $table->string('level_cd_nm', 255)->comment('Tên Level (3 y/o, 4 y/o...)');
            $table->integer('test_seq')->comment('Seq của bài kiểm tra gốc');
            $table->timestamps();
        });

        // 2. ucrea_student_results: Store the general result of a student's test
        Schema::create('ucrea_student_results', function (Blueprint $table) {
            $table->id();
            $table->string('test_cd', 50);
            $table->string('level_cd', 50);
            $table->integer('test_seq');
            $table->integer('result_seq')->unique()->comment('Seq của kết quả gốc');
            
            $table->string('memb_nm', 255)->comment('Tên giáo viên');
            $table->string('stu_nm', 255)->comment('Tên học sinh');
            $table->string('class_nm', 255)->nullable()->comment('Tên lớp');
            $table->date('eval_dt')->nullable()->comment('Ngày kiểm tra');
            
            $table->string('result_cd', 50)->comment('Trạng thái (IS002, IS004...)');
            $table->string('result_cd_nm', 255)->nullable()->comment('Tên trạng thái');
            
            // Raw radar chart JSON & Skill Grades JSON extracted from viewReport.html
            $table->json('report_data')->nullable()->comment('Dữ liệu biểu đồ % các kỹ năng');
            $table->json('skills_grade')->nullable()->comment('Dữ liệu đánh giá chữ (A, B, C...)');

            $table->timestamps();
        });

        // 3. ucrea_student_result_details: Detailed chosen rubric scores (S, A, B, C)
        Schema::create('ucrea_student_result_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ucrea_student_result_id')->constrained('ucrea_student_results')->onDelete('cascade');
            
            $table->string('question_no', 50)->comment('Số thứ tự / vấn đề số');
            $table->string('main_category', 255)->comment('Lĩnh vực kiểm tra');
            $table->string('sub_category', 255)->comment('Lĩnh vực chi tiết');
            $table->string('rubric_name', 255)->comment('Nội dung đánh giá / Tên Rubric');
            
            $table->string('assigned_score', 100)->nullable()->comment('Điểm giáo viên chọn (VD: 2, 3-4, order O, 0-1)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucrea_student_result_details');
        Schema::dropIfExists('ucrea_student_results');
        Schema::dropIfExists('ucrea_tests');
    }
};
