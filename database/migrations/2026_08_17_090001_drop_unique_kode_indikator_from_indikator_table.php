<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perbaikan bug: kolom kode_indikator sebelumnya UNIQUE secara global,
     * padahal IndikatorController::update() sengaja membuat baris indikator
     * BARU (bukan mengubah di tempat) begitu sebuah indikator pernah dipakai
     * di suatu periode assessment -- supaya histori periode lama tidak ikut
     * berubah. Baris lama ditandai status "Nonaktif", baris baru "Aktif",
     * dan keduanya boleh berbagi kode_indikator yang sama (mis. IND-001
     * versi lama & versi baru). Constraint unique di level DB membuat
     * proses ini SELALU gagal (Duplicate entry) begitu kode_indikator tidak
     * diubah. Keunikan kode_indikator sekarang divalidasi di level aplikasi,
     * dan hanya wajib unik di antara indikator yang berstatus "Aktif".
     */
    public function up(): void
    {
        Schema::table('indikator', function (Blueprint $table) {
            $table->dropUnique(['kode_indikator']);
        });
    }

    public function down(): void
    {
        Schema::table('indikator', function (Blueprint $table) {
            $table->unique('kode_indikator');
        });
    }
};
