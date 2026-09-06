<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\OpsiJawaban;

/**
 * Seeder skala generik (0/25/50/75/100%) — HANYA dipakai sebagai placeholder
 * untuk indikator yang belum punya teks opsi asli dari kuesioner (lihat
 * IndikatorSeeder untuk kategori yang sudah lengkap, seperti SI).
 *
 * Begitu sebuah kategori sudah dilengkapi teks opsi asli langsung di
 * IndikatorSeeder (key 'opsi'), indikator tersebut dilewati di sini
 * supaya tidak ditimpa oleh skala generik.
 */
class OpsiJawabanSeeder extends Seeder
{
    protected array $skalaLabel = [
        1 => 'Belum ada / tidak tersedia',
        2 => 'Sudah direncanakan, belum berjalan',
        3 => 'Sudah berjalan sebagian',
        4 => 'Sudah berjalan penuh, belum terdokumentasi',
        5 => 'Sudah berjalan penuh dan terdokumentasi',
    ];

    public function run(): void
    {
        $indikators = Indikator::where('tipe_jawaban', 'pilihan_ganda')
            ->doesntHave('opsiJawaban')
            ->get();

        foreach ($indikators as $indikator) {
            foreach ($this->skalaLabel as $urutan => $label) {

                OpsiJawaban::updateOrCreate(
                    [
                        'indikator_id' => $indikator->id,
                        'urutan'       => $urutan,
                    ],
                    [
                        'label'      => $label,
                        'nilai_skor' => (int) round(($urutan / 5) * $indikator->poin_maksimal),
                    ]
                );
            }
        }
    }
}
