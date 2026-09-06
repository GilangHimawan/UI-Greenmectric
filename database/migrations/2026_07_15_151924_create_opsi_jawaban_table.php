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

        Schema::create('opsi_jawaban', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('indikator_id');
            $table->foreign('indikator_id')->references('id')->on('indikator');
            $table->string('label', 500);
            $table->smallInteger('nilai_skor');
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
        Schema::dropIfExists('opsi_jawaban');
    }
};
