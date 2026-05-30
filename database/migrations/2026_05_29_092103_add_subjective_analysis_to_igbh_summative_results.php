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
        Schema::table('igbh_summative_results', function (Blueprint $table) {
            $table->json('subjective_analysis')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('igbh_summative_results', function (Blueprint $table) {
            $table->dropColumn('subjective_analysis');
        });
    }
};
