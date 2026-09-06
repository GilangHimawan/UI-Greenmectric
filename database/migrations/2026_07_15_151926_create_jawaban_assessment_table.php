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

        Schema::create('jawaban_assessment', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('assessment_id');
            $table->foreign('assessment_id')->references('id')->on('assessment');
            $table->integer('unit_id');
            $table->foreign('unit_id')->references('id')->on('unit');
            $table->unsignedBigInteger('dijawab_oleh');
            $table->foreign('dijawab_oleh')->references('id')->on('users');
            $table->text('jawaban')->nullable();
            $table->smallInteger('skor_diperoleh');
            $table->string('path_file', 255)->nullable();
            $table->string('nama_file_asli', 255)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diubah_pada')->useCurrent();
            $table->unique(['assessment_id', 'unit_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_assessment');
    }
};
