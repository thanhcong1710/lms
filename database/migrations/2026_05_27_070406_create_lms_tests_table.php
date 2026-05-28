<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_type'); // UCREA, IG.BH
            $table->string('test_cd')->nullable();
            $table->string('level_cd')->nullable();
            $table->string('test_seq')->nullable();
            $table->string('name');
            $table->string('pdf_url')->nullable();
            $table->string('local_pdf_path')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_tests');
    }
};
