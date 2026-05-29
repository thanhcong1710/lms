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
        Schema::create('igbh_test_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('test_seq')->unique()->comment('Seq của bài kiểm tra gốc');
            $table->json('sectors')->nullable()->comment('Danh sách Đơn vị (VD: {"A": "Phép cộng", "B": "..."})');
            $table->timestamps();
        });

        Schema::create('igbh_test_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('test_seq');
            $table->string('question_type', 50)->comment('curriculum | thinking');
            $table->integer('sort_no')->comment('Số thứ tự');
            
            // Curriculum specific
            $table->string('sector', 50)->nullable()->comment('Đơn vị (A, B, C, D, E)');
            $table->string('type_cd', 50)->nullable()->comment('ST001(Non-word), ST002(Word)');
            $table->string('answer', 50)->nullable()->comment('Đáp án đúng (1-5)');
            
            // Thinking specific
            $table->json('areas')->nullable()->comment('Các lĩnh vực (A, B, C, D, E, F)');
            
            // Common
            $table->string('difficulty', 50)->nullable()->comment('Độ khó (DC001: High, DC002: Med, DC003: Low)');
            $table->integer('standard_point')->default(0)->comment('Điểm chuẩn');
            
            $table->timestamps();
            
            $table->index(['test_seq', 'question_type']);
        });

        Schema::create('igbh_test_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('test_seq');
            $table->string('comment_type', 50)->comment('total | unit');
            $table->string('condition_value', 50)->comment('Với total: số đếm (5 EA, 4 EA...), Với unit: sectorCd (SC001, SC002...)');
            $table->text('good_comment')->nullable();
            $table->text('weak_comment')->nullable();
            
            $table->timestamps();
            $table->index(['test_seq', 'comment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('igbh_test_comments');
        Schema::dropIfExists('igbh_test_questions');
        Schema::dropIfExists('igbh_test_configs');
    }
};
