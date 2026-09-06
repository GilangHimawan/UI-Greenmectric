<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\OpsiJawaban;

class IndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            /*
            |--------------------------------------------------------------------------
            | SI - Setting and Infrastructure (Total 1100)
            | Sesuai Questionnaire UI GreenMetric 2026
            |--------------------------------------------------------------------------
            */
            [
                'kategori' => 'SI', 'kode' => 'SI1', 'poin' => 200, 'wajib_file' => true,
                'pertanyaan' => 'The ratio of open space area to total area',
                'opsi' => ['≤ 1%', '> 1 - 80%', '> 80 - 90%', '> 90 - 95%', '> 95%'],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI2', 'poin' => 100, 'wajib_file' => true,
                'pertanyaan' => 'Total area on campus covered in forest vegetation used for research, teaching, and/or community engagement',
                'opsi' => ['≤ 2%', '> 2 - 10%', '> 10 - 25%', '> 25 - 35%', '> 35%'],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI3', 'poin' => 200, 'wajib_file' => true,
                'pertanyaan' => 'Total area on campus covered in planted vegetation',
                'opsi' => ['≤ 10%', '> 10 - 20%', '> 20 - 30%', '> 30 - 50%', '> 50%'],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI4', 'poin' => 200, 'wajib_file' => false,
                'pertanyaan' => 'The total open space area divided by the total campus population',
                'opsi' => ['≤ 10 m²/person', '> 10 - 20 m²/person', '> 20 - 40 m²/person', '> 40 - 70 m²/person', '> 70 m²/person'],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI5', 'poin' => 100, 'wajib_file' => true,
                'pertanyaan' => 'Campus facilities for disabled, special needs and/or maternity care',
                'opsi' => [
                    'None',
                    'Policy is in place',
                    'Facilities are in the planning stage',
                    'Facilities are partially available and operated',
                    'Facilities exist in all buildings and are fully operated',
                ],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI6', 'poin' => 100, 'wajib_file' => true,
                'pertanyaan' => 'Security and safety facilities',
                'opsi' => [
                    'Passive security and safety system',
                    'Security and safety infrastructure (CCTV, emergency hotline/button) available and fully functioning',
                    'Security and safety infrastructure (CCTV, emergency hotline/button, certified personnel, fire extinguisher, hydrant) available and fully functioning',
                    'Security and safety infrastructure available and fully functioning and security responding time for accidents, crime, fire, and natural disasters is more than 5 minutes',
                    'Security and safety infrastructure available and fully functioning and security responding time for accidents, crime, fire, and natural disasters is less than 5 minutes',
                ],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI7', 'poin' => 100, 'wajib_file' => true,
                'pertanyaan' => "Health infrastructure facilities for students, academics and administrative staffs' well-being",
                'opsi' => [
                    'Health infrastructure (first aid) is not available',
                    'Health infrastructure (first aid, emergency room, clinic and personnel) are available',
                    'Health infrastructure (first aid, emergency room, clinic, and certified personnel) are available',
                    'Health infrastructure (first aid, emergency room, clinic, hospital and certified personnel) are available',
                    'Health infrastructure available (first aid, emergency room, clinic, hospital and certified personnel), system and accessible for public',
                ],
            ],
            [
                'kategori' => 'SI', 'kode' => 'SI8', 'poin' => 100, 'wajib_file' => true,
                'pertanyaan' => 'Conservation: plant (flora), animal (fauna), or wildlife, genetic resources for food and agriculture secured in either medium or long-term conservation facilities',
                'opsi' => [
                    'Conservation program in preparation',
                    'Conservation program 1-25% implemented',
                    'Conservation program 25-50% implemented',
                    'Conservation program 50-75% implemented',
                    'Conservation program >75% implemented',
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | EC, WS, WR, TR, ED, GD
            | Placeholder sementara — akan dilengkapi menyusul dengan format yang sama
            | seperti SI di atas (pertanyaan + opsi asli dari kuesioner).
            |--------------------------------------------------------------------------
            */
            ['kategori'=>'EC','kode'=>'EC1','pertanyaan'=>'Use of energy-efficient appliances','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'EC','kode'=>'EC2','pertanyaan'=>'Smart building implementation','poin'=>300,'wajib_file'=>true],
            ['kategori'=>'EC','kode'=>'EC3','pertanyaan'=>'Number of renewable energy sources','poin'=>300,'wajib_file'=>true],
            ['kategori'=>'EC','kode'=>'EC4','pertanyaan'=>'Electricity usage per campus population','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'EC','kode'=>'EC5','pertanyaan'=>'Renewable energy production ratio','poin'=>200,'wajib_file'=>true],

            ['kategori'=>'WS','kode'=>'WS1','pertanyaan'=>'3R program for university waste','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'WS','kode'=>'WS2','pertanyaan'=>'Program to reduce paper and plastic','poin'=>300,'wajib_file'=>true],
            ['kategori'=>'WS','kode'=>'WS3','pertanyaan'=>'Organic waste treatment','poin'=>300,'wajib_file'=>true],
            ['kategori'=>'WS','kode'=>'WS4','pertanyaan'=>'Inorganic waste treatment','poin'=>300,'wajib_file'=>true],
            ['kategori'=>'WS','kode'=>'WS5','pertanyaan'=>'Toxic waste treatment','poin'=>300,'wajib_file'=>true],

            ['kategori'=>'WR','kode'=>'WR1','pertanyaan'=>'Water absorption area','poin'=>100,'wajib_file'=>true],
            ['kategori'=>'WR','kode'=>'WR2','pertanyaan'=>'Water conservation program','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'WR','kode'=>'WR3','pertanyaan'=>'Water recycling program','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'WR','kode'=>'WR4','pertanyaan'=>'Water-efficient appliances','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'WR','kode'=>'WR5','pertanyaan'=>'Consumption of treated water','poin'=>200,'wajib_file'=>true],

            ['kategori'=>'TR','kode'=>'TR1','pertanyaan'=>'Combustion-engine vehicles ratio','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'TR','kode'=>'TR2','pertanyaan'=>'Shuttle services','poin'=>250,'wajib_file'=>true],
            ['kategori'=>'TR','kode'=>'TR3','pertanyaan'=>'Zero Emission Vehicles availability','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'TR','kode'=>'TR4','pertanyaan'=>'Zero Emission Vehicles ratio','poin'=>200,'wajib_file'=>false],
            ['kategori'=>'TR','kode'=>'TR5','pertanyaan'=>'Ground parking area ratio','poin'=>200,'wajib_file'=>true],

            ['kategori'=>'ED','kode'=>'ED1','pertanyaan'=>'Ratio of sustainability courses','poin'=>200,'wajib_file'=>false],
            ['kategori'=>'ED','kode'=>'ED2','pertanyaan'=>'Ratio of sustainability research funding','poin'=>200,'wajib_file'=>false],
            ['kategori'=>'ED','kode'=>'ED3','pertanyaan'=>'Ratio of sustainability publications','poin'=>200,'wajib_file'=>true],
            ['kategori'=>'ED','kode'=>'ED4','pertanyaan'=>'Number of sustainability events','poin'=>100,'wajib_file'=>true],
            ['kategori'=>'ED','kode'=>'ED5','pertanyaan'=>'Student sustainability activities','poin'=>150,'wajib_file'=>true],

            ['kategori'=>'GD','kode'=>'GD1','pertanyaan'=>'Budget for sustainability efforts','poin'=>200,'wajib_file'=>false],
            ['kategori'=>'GD','kode'=>'GD2','pertanyaan'=>'University sustainability website','poin'=>200,'wajib_file'=>false],
            ['kategori'=>'GD','kode'=>'GD3','pertanyaan'=>'Sustainability report','poin'=>100,'wajib_file'=>true],
            ['kategori'=>'GD','kode'=>'GD4','pertanyaan'=>'Financial report','poin'=>100,'wajib_file'=>false],
            ['kategori'=>'GD','kode'=>'GD5','pertanyaan'=>'Sustainability office availability','poin'=>100,'wajib_file'=>true],

        ];

        $urutanIndikator = [];

        foreach ($data as $item) {

            $kategori = Kategori::where('kode_kategori', $item['kategori'])->first();

            if (!$kategori) {
                continue;
            }

            $urutanIndikator[$item['kategori']] = ($urutanIndikator[$item['kategori']] ?? 0) + 1;

            $indikator = Indikator::updateOrCreate(
                [
                    'kode_indikator' => $item['kode']
                ],
                [
                    'kategori_id'   => $kategori->id,
                    'pertanyaan'    => $item['pertanyaan'],
                    'poin_maksimal' => $item['poin'],
                    'tipe_jawaban'  => 'pilihan_ganda',
                    'wajib_file'    => $item['wajib_file'] ?? true,
                    'status'        => 'Aktif',
                    'urutan'        => $urutanIndikator[$item['kategori']],
                    'dibuat_pada'   => now(),
                    'diubah_pada'   => now(),
                ]
            );

            // Indikator yang sudah punya teks opsi asli dari kuesioner -> pakai itu.
            if (!empty($item['opsi'])) {

                $jumlahOpsi = count($item['opsi']);

                foreach ($item['opsi'] as $index => $labelOpsi) {

                    $nomorOpsi = $index + 1;

                    // Skor mengikuti rumus resmi GreenMetric:
                    // Skor = (nomor opsi terpilih / jumlah opsi) x poin maksimal indikator.
                    $skor = (int) round(($nomorOpsi / $jumlahOpsi) * $item['poin']);

                    OpsiJawaban::updateOrCreate(
                        [
                            'indikator_id' => $indikator->id,
                            'urutan'       => $nomorOpsi,
                        ],
                        [
                            'label'      => $labelOpsi,
                            'nilai_skor' => $skor,
                        ]
                    );
                }
            }
        }
    }
}
