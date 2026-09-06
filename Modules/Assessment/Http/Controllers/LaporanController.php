<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessment\Entities\Assessment;
use Modules\Assessment\Entities\Jawaban;
use Modules\Assessment\Entities\Periode;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Unit;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Halaman laporan assessment.
     */

    public function index(Request $request)
    {
    return view(
        'assessment::Laporan.index',
        $this->getData($request)
    );
    }
    private function getData(Request $request)
    {
        $periodes = Periode::orderByDesc('tahun')->get();

        if ($request->filled('periode')) {
            $periode = Periode::findOrFail($request->periode);
        } else {
            $periode = Periode::aktif();

            if (!$periode) {
                $periode = Periode::orderByDesc('tahun')->first();
            }
        }

        $jawaban = collect();

        $units = Unit::orderBy('nama_unit')->get();

        $unitId = $request->unit;

        $jawaban = collect();

        if ($periode) {

            $query = Jawaban::with([
                'assessment.indikator.kategori',
                'unit'
            ])
            ->whereHas('assessment', function($q) use ($periode){
                $q->where('periode_id',$periode->id);
            });

            if($unitId){
                $query->where('unit_id',$unitId);
            }

            $jawaban = $query->get();
        }

        $laporan=[];

        foreach($jawaban as $item){

            if(!$item->assessment) continue;

            $indikator=$item->assessment->indikator;

            // Kategori & poin maksimal diutamakan dari snapshot (dibekukan saat
            // jawaban disimpan) supaya tidak ikut berubah kalau master indikator
            // diedit setelah periode ini berlalu. Untuk data lama (sebelum kolom
            // snapshot ada), fallback ke data master seperti sebelumnya.
            $kategori = optional($indikator)->kategori;

            if(!$kategori) continue;

            $poinMaksimal = $item->poin_maksimal_snapshot ?? optional($indikator)->poin_maksimal ?? 0;

            if(!isset($laporan[$kategori->id])){

                $laporan[$kategori->id]=[
                    'kategori'=>$kategori->nama_kategori,
                    'total'=>0,
                    'maksimal'=>0,
                    'persentase'=>0
                ];

            }

            $laporan[$kategori->id]['total']+=$item->skor_diperoleh;
            $laporan[$kategori->id]['maksimal']+=$poinMaksimal;

        }

        $grandTotal=0;
        $grandMaksimal=0;

        foreach($laporan as &$row){

            $row['persentase']=$row['maksimal']
                ? round($row['total']/$row['maksimal']*100,2)
                :0;

            $grandTotal+=$row['total'];
            $grandMaksimal+=$row['maksimal'];

        }

        $overall=$grandMaksimal
            ? round($grandTotal/$grandMaksimal*100,2)
            :0;

        $detailJawaban = collect();

        foreach ($jawaban as $item) {

            if (!$item->assessment) {
                continue;
            }

            $indikator = $item->assessment->indikator;

            // Teks pertanyaan/kode/poin diambil dari snapshot dulu (histori
            // dibekukan saat jawaban disimpan). Kalau snapshot belum ada
            // (jawaban lama, disimpan sebelum fitur ini ada), fallback ke
            // data master indikator seperti perilaku sebelumnya.
            $kode        = $item->kode_indikator_snapshot ?? optional($indikator)->kode_indikator;
            $pertanyaan  = $item->pertanyaan_snapshot ?? optional($indikator)->pertanyaan;
            $tipeJawaban = $item->tipe_jawaban_snapshot ?? optional($indikator)->tipe_jawaban;
            $maksimal    = $item->poin_maksimal_snapshot ?? optional($indikator)->poin_maksimal;

            if (!$kode && !$pertanyaan) {
                continue;
            }

            // Untuk pilihan ganda, tampilkan teks label jawaban (bukan id opsi mentah).
            $teksJawaban = $item->jawaban;

            if ($tipeJawaban === 'pilihan_ganda') {
                if ($item->opsi_label_snapshot) {
                    $teksJawaban = $item->opsi_label_snapshot;
                } elseif ($indikator) {
                    $opsi = $indikator->opsiJawaban()->find($item->jawaban);
                    $teksJawaban = $opsi->label ?? $item->jawaban;
                }
            }

            $detailJawaban->push([

                    'kategori' => optional(optional($indikator)->kategori)->nama_kategori,

                    'kode' => $kode,

                    'indikator' => $pertanyaan,

                    'jawaban' => $teksJawaban,

                    'nilai' => $item->skor_diperoleh,

                    'maksimal' => $maksimal,

                ]);
        }

        return compact(
            'periodes',
            'periode',
            'units',
            'unitId',
            'laporan',
            'grandTotal',
            'grandMaksimal',
            'overall',
            'detailJawaban'
        );
    }
    public function preview(Request $request)
    {
        try {
            $data = $this->getData($request);

            $pdf = Pdf::loadView(
                'assessment::Laporan.pdf',
                $data
            );

            $pdf->setPaper('A4','portrait');

            return $pdf->stream('laporan-assessment.pdf');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal membuat pratinjau PDF laporan. Silakan coba lagi.');
        }
    }

    public function download(Request $request)
    {
        try {
            $data = $this->getData($request);

            $pdf = Pdf::loadView(
                'assessment::Laporan.pdf',
                $data
            );

            $pdf->setPaper('A4','portrait');

            return $pdf->download('laporan-assessment.pdf');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal mengunduh PDF laporan. Silakan coba lagi.');
        }
    }

}