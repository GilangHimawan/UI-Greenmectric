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

        Schema::create('unit', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('nama_unit', 150);
            $table->string('kode_unit', 20)->unique();
            $table->smallInteger('kategori_id')->index();
            $table->foreign('kategori_id')->references('id')->on('kategori');
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
        Schema::dropIfExists('unit');
    }
};
