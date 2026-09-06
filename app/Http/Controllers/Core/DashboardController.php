<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessment\Entities\Periode;
use Modules\Assessment\Entities\Jawaban;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Modules\Assessment\Entities\Assessment;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periode = Periode::aktif();

        if (!$periode) {
            return view('dashboard.dashboard', $this->emptyDashboardData());
        }

        $jumlahKategori = $this->jumlahKategori();

        $jumlahIndikator = $this->jumlahIndikator();

        $jumlahTerisi = $this->jumlahIndikatorTerisi($periode);

        $persentase = $this->persentaseAssessment($periode);

        $progressKategori = $this->progressKategori($periode);

        $chartRadar = $this->chartRadar($progressKategori);

        $chartPie = $this->chartPie($progressKategori);

        $riwayat = $this->riwayatAktivitas($periode);

        return view('dashboard.dashboard', compact(
            'periode',
            'jumlahKategori',
            'jumlahIndikator',
            'jumlahTerisi',
            'persentase',
            'progressKategori',
            'chartRadar',
            'chartPie',
            'riwayat'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    private function emptyDashboardData()
    {
        return [
            'periode' => null,

            'jumlahKategori' => 0,

            'jumlahIndikator' => 0,

            'jumlahTerisi' => 0,

            'persentase' => 0,

            // Harus Collection
            'progressKategori' => collect(),

            // Radar Chart
            'chartRadar' => [
                'labels' => [],
                'data' => [],
            ],

            // Pie Chart
            'chartPie' => [
                'labels' => [],
                'data' => [],
            ],

            'riwayat' => collect(),
        ];
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    private function jumlahKategori()
    {
        return Kategori::count();
    }

    private function jumlahIndikator()
    {
        return Indikator::count();
    }
    private function jumlahIndikatorTerisi($periode)
    {
        return Jawaban::whereHas('assessment', function ($q) use ($periode) {

            $q->where('periode_id', $periode->id);

        })->count();
    }

    private function persentaseAssessment($periode)
    {
        /*
        |--------------------------------------------------------------------------
        | Total skor yang diperoleh
        |--------------------------------------------------------------------------
        */

        $totalSkor = Jawaban::whereHas('assessment', function ($q) use ($periode) {

            $q->where('periode_id', $periode->id);

        })->sum('skor_diperoleh');



        /*
        |--------------------------------------------------------------------------
        | Total skor maksimal
        |--------------------------------------------------------------------------
        */

        $totalMaksimal = Assessment::where('periode_id', $periode->id)
            ->join('indikator', 'assessment.indikator_id', '=', 'indikator.id')
            ->sum('indikator.poin_maksimal');



        /*
        |--------------------------------------------------------------------------
        | Persentase
        |--------------------------------------------------------------------------
        */

        if ($totalMaksimal == 0) {
            return 0;
        }

        return round(($totalSkor / $totalMaksimal) * 100, 2);
    }

    private function progressKategori($periode)
    {
        $hasil = collect();

        $kategoris = Kategori::with([
            'indikators.assessments.jawaban'
        ])->get();

        foreach ($kategoris as $kategori) {

            $totalSkor = 0;
            $totalMaksimal = 0;

            foreach ($kategori->indikators as $indikator) {

                $totalMaksimal += $indikator->poin_maksimal;

                $assessment = $indikator->assessments
                    ->where('periode_id', $periode->id)
                    ->first();

                if ($assessment) {

                    $totalSkor += $assessment->jawaban->sum('skor_diperoleh');

                }
            }

            $hasil->push([

                'id' => $kategori->id,

                'kode' => $kategori->kode_kategori,

                'nama' => $kategori->nama_kategori,

                'total' => $totalSkor,

                'maksimal' => $totalMaksimal,

                'persentase' => $totalMaksimal > 0
                    ? round(($totalSkor / $totalMaksimal) * 100, 2)
                    : 0,

            ]);
        }

        return $hasil;
    }

    private function chartRadar($progressKategori)
    {
        return [

            'labels' => $progressKategori
                ->pluck('kode')
                ->values(),

            'data' => $progressKategori
                ->pluck('persentase')
                ->values(),

        ];
    }
    private function chartPie($progressKategori)
    {
        return [

            'labels' => $progressKategori
                ->pluck('kode')
                ->values(),

            'data' => $progressKategori
                ->pluck('total')
                ->values(),

        ];
    }

    private function riwayatAktivitas($periode)
    {
        return Jawaban::with([
                'assessment.indikator.kategori',
                'penjawab',
                'unit'
            ])
            ->whereHas('assessment', function ($q) use ($periode) {

                $q->where('periode_id', $periode->id);

            })
            ->orderByDesc('diubah_pada')
            ->limit(10)
            ->get();
    }
}