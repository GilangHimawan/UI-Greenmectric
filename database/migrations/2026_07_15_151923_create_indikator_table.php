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

        Schema::create('indikator', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->smallInteger('kategori_id');
            $table->foreign('kategori_id')->references('id')->on('kategori');
            $table->string('kode_indikator', 10)->unique()->nullable();
            $table->text('pertanyaan');
            $table->smallInteger('poin_maksimal')->nullable();
            $table->tinyInteger('wajib_file')->default(0);
            $table->enum('tipe_jawaban', ["pilihan_ganda","isian","angka"])->default('pilihan_ganda');
            $table->enum('status', ["Aktif","Nonaktif"])->default('Aktif');
            $table->tinyInteger('urutan');
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
        Schema::dropIfExists('indikator');
    }
};
