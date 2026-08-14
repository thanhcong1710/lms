<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedTinyInteger('product_id')->nullable()->after('cls_type');
        });

        // Tự động map data cũ
        // CT002 hoặc có DEMO -> DEMO (100)
        DB::table('classes')
            ->where('cls_type', 'CT002')
            ->orWhere('cls_name', 'like', '%DEMO%')
            ->update(['product_id' => 100]);
            
        // CT003 hoặc có IG -> IG (2)
        DB::table('classes')
            ->whereNull('product_id')
            ->where(function($q) {
                $q->where('cls_type', 'CT003')
                  ->orWhere('cls_name', 'like', '%IG%');
            })
            ->update(['product_id' => 2]);
            
        // CT004 hoặc có BH -> BH (3)
        DB::table('classes')
            ->whereNull('product_id')
            ->where(function($q) {
                $q->where('cls_type', 'CT004')
                  ->orWhere('cls_name', 'like', '%BH%');
            })
            ->update(['product_id' => 3]);
        
        // UC là phần còn lại hoặc CT001
        DB::table('classes')->whereNull('product_id')->update(['product_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }
};
