<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Indikator\Entities\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            [
                'kode_kategori' => 'SI',
                'nama_kategori' => 'Setting and Infrastructure',
                'skor_maksimal' => 1100,
            ],
            [
                'kode_kategori' => 'EC',
                'nama_kategori' => 'Energy and Climate Change',
                'skor_maksimal' => 2000,
            ],
            [
                'kode_kategori' => 'WS',
                'nama_kategori' => 'Waste',
                'skor_maksimal' => 1700,
            ],
            [
                'kode_kategori' => 'WR',
                'nama_kategori' => 'Water',
                'skor_maksimal' => 1100,
            ],
            [
                'kode_kategori' => 'TR',
                'nama_kategori' => 'Transportation',
                'skor_maksimal' => 1700,
            ],
            [
                'kode_kategori' => 'ED',
                'nama_kategori' => 'Education and Research',
                'skor_maksimal' => 1300,
            ],
            [
                'kode_kategori' => 'GD',
                'nama_kategori' => 'Governance and Digitalization',
                'skor_maksimal' => 1100,
            ],
        ];

        foreach ($kategori as $item) {
            Kategori::updateOrCreate(
                ['kode_kategori' => $item['kode_kategori']],
                $item
            );
        }
    }
}