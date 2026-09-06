<?php

namespace Modules\Assessment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Assessment\Entities\Periode;
use Modules\Assessment\Entities\Assessment;
use Modules\Indikator\Entities\Indikator;

/**
 * PeriodeController
 *
 * Mengelola buka/tutup periode assessment.
 * Hanya admin yang bisa mengakses (diatur lewat middleware di routes).
 *
 * Alur:
 *   - index()  → daftar semua periode
 *   - create() → form untuk membuka periode baru
 *   - store()  → buka periode baru, status langsung "aktif"
 *   - end()    → tutup periode yang sedang aktif
 */
class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderByDesc('tahun')->paginate(15);

        return view('assessment::periode.index', compact('periodes'));
    }

    public function create()
    {
        return view('assessment::periode.create');
    }

    public function store(Request $request){
        $request->validate([
            'tahun' => 'required|integer|unique:periode_assessment,tahun',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if (Periode::where('status', 'aktif')->exists()) {

            return back()->with(
                'error',
                'Masih terdapat periode assessment yang aktif.'
            );

        }

        $indikators = \Modules\Indikator\Entities\Indikator::where('status', 'Aktif')
                        ->orderBy('kategori_id')
                        ->orderBy('urutan')
                        ->get();

        if ($indikators->isEmpty()) {

            return back()->with(
                'error',
                'Belum ada indikator yang aktif.'
            );

        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $indikators) {
                $periode = Periode::create([

                    'tahun'       => $request->tahun,
                    'status'      => 'aktif',
                    'dibuka_pada' => now(),
                    'dibuka_oleh' => auth()->id(),
                    'keterangan'  => $request->keterangan,

                ]);

                foreach ($indikators as $indikator) {

                    \Modules\Assessment\Entities\Assessment::create([

                        'periode_id'   => $periode->id,
                        'indikator_id' => $indikator->id,

                    ]);

                }
            });

            return redirect()
                ->route('assessment.index')
                ->with(
                    'success',
                    'Periode Assessment berhasil dimulai.'
                );
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with(
                'error',
                'Gagal memulai periode assessment. Silakan coba lagi.'
            );
        }
    }

    public function end($id)
    {
        try {
            $periode = Periode::findOrFail($id);

            $periode->update([
                'status'       => 'ditutup',
                'ditutup_pada' => now(),
                'ditutup_oleh' => auth()->id(),
            ]);

            return redirect()
                    ->route('assessment.index')
                    ->with('success', 'Periode berhasil ditutup.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                    ->route('assessment.index')
                    ->with('error', 'Gagal menutup periode. Silakan coba lagi.');
        }
    }

    
}