<?php

namespace Modules\Assessment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Assessment\Entities\Periode;
use Modules\Assessment\Entities\Jawaban;

class JawabanController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderByDesc('tahun')->get();

        return view(
            'assessment::laporan.index',
            compact('periodes')
        );
    }

    public function detailPeriode($periodeId)
    {
        $periode = Periode::findOrFail($periodeId);

        $laporan = Jawaban::selectRaw("
                unit_id,
                SUM(skor_diperoleh) total_skor
            ")
            ->whereHas('assessment', function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            })
            ->groupBy('unit_id')
            ->with('unit')
            ->get();

        return view(
            'assessment::laporan.detail_periode',
            compact(
                'periode',
                'laporan'
            )
        );
    }
}
