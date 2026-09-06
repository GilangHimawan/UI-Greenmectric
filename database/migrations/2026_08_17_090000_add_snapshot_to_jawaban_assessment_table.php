<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom snapshot pada jawaban_assessment.
     *
     * Tujuan: begitu jawaban disimpan, teks pertanyaan/opsi & poin maksimal
     * yang berlaku SAAT ITU ikut "dibekukan" di baris jawaban ini. Sehingga
     * kalau master indikator diedit di kemudian hari, laporan periode yang
     * sudah lewat tetap menampilkan pertanyaan & nilai versi lama (tidak
     * ikut berubah). Kolom ini hanya diisi sekali saat simpan, tidak pernah
     * ditimpa ulang oleh perubahan master data.
     */
    public function up(): void
    {
        Schema::table('jawaban_assessment', function (Blueprint $table) {
            $table->string('kode_indikator_snapshot', 10)->nullable()->after('assessment_id');
            $table->text('pertanyaan_snapshot')->nullable()->after('jawaban');
            $table->string('tipe_jawaban_snapshot', 20)->nullable()->after('pertanyaan_snapshot');
            $table->smallInteger('poin_maksimal_snapshot')->nullable()->after('tipe_jawaban_snapshot');
            $table->string('opsi_label_snapshot', 500)->nullable()->after('poin_maksimal_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_assessment', function (Blueprint $table) {
            $table->dropColumn([
                'kode_indikator_snapshot',
                'pertanyaan_snapshot',
                'tipe_jawaban_snapshot',
                'poin_maksimal_snapshot',
                'opsi_label_snapshot',
            ]);
        });
    }
};
