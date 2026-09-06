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

        Schema::create('hasil_assessment', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('periode_id');
            $table->foreign('periode_id')->references('id')->on('periode_assessment');
            $table->integer('unit_id');
            $table->foreign('unit_id')->references('id')->on('unit');
            $table->smallInteger('total_skor');
            $table->smallInteger('skor_maksimal');
            $table->decimal('persentase', 5, 2);
            $table->enum('status', ["draft","submitted","verified"]);
            $table->string('path_file', 255)->nullable();
            $table->string('nama_file', 255)->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diubah_pada')->useCurrent();
            $table->unique(['periode_id', 'unit_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_assessment');
    }
};
