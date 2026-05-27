<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('cls_name');
            $table->string('teacher_id_lms')->nullable();
            $table->string('pre_teacher_id_lms')->nullable();
            $table->string('level_name')->nullable();
            $table->string('cls_type')->nullable();
            $table->string('cls_status')->default('US001');
            $table->string('branch_id_lms')->nullable();
            $table->integer('class_seq')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
