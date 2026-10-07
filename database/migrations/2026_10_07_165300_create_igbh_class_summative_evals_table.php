<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('igbh_class_summative_evals', function (Blueprint $table) {
            $table->id();
            $table->integer('test_seq')->comment('Bài kiểm tra summative');
            $table->integer('class_seq')->comment('Lớp');
            $table->string('class_nm', 100)->nullable()->comment('Tên lớp');
            $table->string('teacher_nm', 100)->nullable()->comment('Tên giáo viên');
            $table->date('eval_ymd')->nullable()->comment('Ngày đánh giá');
            $table->string('status', 20)->default('draft')->comment('Trạng thái: draft, completed');
            $table->timestamps();

            $table->unique(['test_seq', 'class_seq']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('igbh_class_summative_evals');
    }
};
