<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Indikator\Entities\Unit;
use Modules\Indikator\Entities\Kategori;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Kategori::pluck('id', 'kode_kategori');

        Unit::updateOrCreate(
            ['kode_unit' => 'DIR'],
            [
                'nama_unit'   => 'Direktorat',
                'kategori_id' => $kategori['GD'],
            ]
        );

        Unit::updateOrCreate(
            ['kode_unit' => 'JTI'],
            [
                'nama_unit'   => 'Jurusan Teknik Informatika',
                'kategori_id' => $kategori['SI'],
            ]
        );

        Unit::updateOrCreate(
            ['kode_unit' => 'JTS'],
            [
                'nama_unit'   => 'Jurusan Teknik Sipil',
                'kategori_id' => $kategori['EC'],
            ]
        );

        Unit::updateOrCreate(
            ['kode_unit' => 'JTM'],
            [
                'nama_unit'   => 'Jurusan Teknik Mesin',
                'kategori_id' => $kategori['WS'],
            ]
        );

        Unit::updateOrCreate(
            ['kode_unit' => 'JAB'],
            [
                'nama_unit'   => 'Jurusan Agribisnis',
                'kategori_id' => $kategori['WR'],
            ]
        );

        Unit::updateOrCreate(
            ['kode_unit' => 'UPT01'],
            [
                'nama_unit'   => 'UPT Perpustakaan',
                'kategori_id' => $kategori['ED'], // BUKAN ER
            ]
        );
    }
}