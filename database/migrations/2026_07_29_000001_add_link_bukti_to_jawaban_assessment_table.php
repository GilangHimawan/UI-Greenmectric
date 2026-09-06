<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawaban_assessment', function (Blueprint $table) {
            // Alternatif dari upload file, untuk bukti berupa link (gdrive, dsb) saat file terlalu besar.
            $table->string('link_bukti', 500)->nullable()->after('nama_file_asli');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_assessment', function (Blueprint $table) {
            $table->dropColumn('link_bukti');
        });
    }
};
