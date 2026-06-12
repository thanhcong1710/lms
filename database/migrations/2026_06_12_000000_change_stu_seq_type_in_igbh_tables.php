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
        Schema::table('igbh_weekly_eval_details', function (Blueprint $table) {
            $table->string('stu_seq', 100)->change();
        });

        Schema::table('igbh_summative_results', function (Blueprint $table) {
            $table->string('stu_seq', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('igbh_weekly_eval_details', function (Blueprint $table) {
            $table->integer('stu_seq')->change();
        });

        Schema::table('igbh_summative_results', function (Blueprint $table) {
            $table->integer('stu_seq')->change();
        });
    }
};
