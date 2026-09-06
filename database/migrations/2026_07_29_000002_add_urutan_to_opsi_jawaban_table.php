<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opsi_jawaban', function (Blueprint $table) {
            // Nomor urut opsi ([1], [2], [3], ...) sesuai kuesioner resmi UI GreenMetric.
            $table->tinyInteger('urutan')->default(1)->after('indikator_id');
        });
    }

    public function down(): void
    {
        Schema::table('opsi_jawaban', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
