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

        Schema::create('assessment', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('periode_id');
            $table->foreign('periode_id')->references('id')->on('periode_assessment');
            $table->integer('indikator_id');
            $table->foreign('indikator_id')->references('id')->on('indikator');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diubah_pada')->useCurrent();
            $table->unique(['periode_id', 'indikator_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment');
    }
};
