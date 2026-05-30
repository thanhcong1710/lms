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
        Schema::table('igbh_summative_result_details', function (Blueprint $table) {
            // Drop old column if needed, or change type to float
            $table->float('score')->change();
            
            $table->float('max_score')->nullable()->after('sort_no');
            $table->float('concept')->nullable()->after('score');
            $table->float('strategy')->nullable()->after('concept');
            $table->float('calculation')->nullable()->after('strategy');
            $table->float('expression')->nullable()->after('calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('igbh_summative_result_details', function (Blueprint $table) {
            $table->integer('score')->change();
            $table->dropColumn(['max_score', 'concept', 'strategy', 'calculation', 'expression']);
        });
    }
};
