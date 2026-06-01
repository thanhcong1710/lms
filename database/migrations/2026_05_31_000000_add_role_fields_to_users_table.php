<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'team_leader', 'teacher'])->default('admin')->after('email');
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->unsignedBigInteger('teacher_id')->nullable()->after('branch_id');
            $table->tinyInteger('status')->default(1)->after('teacher_id');

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropColumn(['role', 'branch_id', 'teacher_id', 'status']);
        });
    }
};
