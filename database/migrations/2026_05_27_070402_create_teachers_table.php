<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ins_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_lms')->nullable();
            $table->string('branch_id_lms')->nullable();
            $table->string('status')->default('US001');
            $table->string('head')->default('N');
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
