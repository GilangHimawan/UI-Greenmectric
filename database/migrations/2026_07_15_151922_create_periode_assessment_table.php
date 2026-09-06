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
        Schema::disableForeignKeyConstraints();

        Schema::create('periode_assessment', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->year('tahun');
            $table->enum('status', ["draft","aktif","ditutup"])->default('draft');
            $table->timestamp('dibuka_pada')->nullable();
            $table->timestamp('ditutup_pada')->nullable();
            $table->unsignedBigInteger('dibuka_oleh')->nullable();
            $table->foreign('dibuka_oleh')->references('id')->on('users');
            $table->unsignedBigInteger('ditutup_oleh')->nullable();
            $table->foreign('ditutup_oleh')->references('id')->on('users');
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diubah_pada')->useCurrent();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_assessment');
    }
};
