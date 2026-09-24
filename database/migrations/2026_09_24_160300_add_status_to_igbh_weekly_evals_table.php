<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('igbh_weekly_evals', function (Blueprint $table) {
            $table->string('status', 20)->default('Draft')->after('eval_ymd')->comment('Draft, Completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('igbh_weekly_evals', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
