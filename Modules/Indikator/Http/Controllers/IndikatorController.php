<?php

namespace Modules\Indikator\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\OpsiJawaban;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IndikatorController extends Controller
{
    public function index()
    {
        $indikators = Indikator::with('kategori')->get();

        return view('indikator::indikator.index', compact('indikators'));
    }

    public function create()
    {
        $kategoris = Kategori::all();

        return view('indikator::indikator.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id'     => 'required|exists:kategori,id',
            'kode_indikator'  => [
                'sometimes',
                Rule::unique('indikator', 'kode_indikator')->where('status', 'Aktif'),
            ],
            'pertanyaan'      => 'required',
            'poin_maksimal'   => 'numeric',
            'tipe_jawaban'    => 'required|in:pilihan_ganda,isian,angka',
            'wajib_file'      => 'sometimes|boolean',
            'urutan'          => 'required|integer',
        ]);

        try {
            $indikator = Indikator::create([
                'kategori_id'    => $request->kategori_id,
                'kode_indikator' => $request->kode_indikator,
                'pertanyaan'     => $request->pertanyaan,
                'poin_maksimal'  => $request->poin_maksimal,
                'tipe_jawaban'   => $request->tipe_jawaban,
                'wajib_file'     => $request->has('wajib_file'),
                'status'         => 'Aktif',
                'urutan'         => $request->urutan,
            ]);

            if (
                $request->tipe_jawaban == 'pilihan_ganda' &&
                $request->has('opsi_label')
            ) {
                foreach ($request->opsi_label as $index => $label) {
                    if (empty($label)) {
                        continue;
                    }

                    $nilaiSkor = $request->opsi_nilai[$index] ?? 0;

                    if ($nilaiSkor < 0 || $nilaiSkor > $request->poin_maksimal) {
                        return redirect()
                            ->back()
                            ->withInput()
                            ->with('error', 'Nilai skor harus antara 0 dan poin maksimal.')
                            ->withErrors(['opsi_nilai' => 'Nilai skor harus antara 0 dan poin maksimal.']);
                    }

                    OpsiJawaban::create([
                        'indikator_id' => $indikator->id,
                        'label'        => $label,
                        'nilai_skor'   => $nilaiSkor,
                    ]);
                }
            }

            return redirect()
                ->route('indikator.index')
                ->with('success', 'Indikator berhasil ditambahkan');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan indikator. Silakan coba lagi.');
        }
    }

    public function show(int $id)
    {
        $indikator = Indikator::with('kategori')->findOrFail($id);

        return view('indikator::indikator.show', compact('indikator'));
    }

    public function edit(int $id)
    {
        $indikator = Indikator::with('opsiJawaban')->findOrFail($id);
        $kategoris = Kategori::all();

        return view('indikator::indikator.edit', compact('indikator', 'kategoris'));
    }

    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'kategori_id'    => 'required|exists:kategori,id',
                'kode_indikator' => [
                    'sometimes',
                    Rule::unique('indikator', 'kode_indikator')->where('status', 'Aktif')->ignore($id),
                ],
                'pertanyaan'     => 'required',
                'poin_maksimal'  => 'required|numeric|min:0',
                'tipe_jawaban'   => 'required',
                'wajib_file'     => 'sometimes|boolean',
                'urutan'         => 'required|integer',
            ]);

            $indikator = Indikator::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Apakah indikator sudah pernah digunakan?
            |--------------------------------------------------------------------------
            */

            $dipakai = $indikator->assessments()->exists();

            /*
            |--------------------------------------------------------------------------
            | BELUM PERNAH DIGUNAKAN
            |--------------------------------------------------------------------------
            */

            if (!$dipakai) {

                $indikator->update([
                    'kategori_id'    => $request->kategori_id,
                    'kode_indikator' => $request->kode_indikator,
                    'pertanyaan'     => $request->pertanyaan,
                    'poin_maksimal'  => $request->poin_maksimal,
                    'tipe_jawaban'   => $request->tipe_jawaban,
                    'wajib_file'     => $request->has('wajib_file'),
                    'urutan'         => $request->urutan,
                    'diubah_pada'    => now(),
                ]);

                $indikator->opsiJawaban()->delete();

                if (
                        $request->tipe_jawaban == 'pilihan_ganda' &&
                        $request->has('opsi_label')
                    ) {
                    foreach ($request->opsi_label as $index => $label) {
                        if (empty($label)) {
                            continue;
                        }

                        $nilai = $request->opsi_nilai[$index] ?? 0;

                        if ($nilai > $request->poin_maksimal) {
                            return back()
                                ->withInput()
                                ->withErrors([
                                    'opsi_nilai' => 'Nilai skor tidak boleh melebihi poin maksimal.'
                                ]);
                        }

                        OpsiJawaban::create([
                            'indikator_id' => $indikator->id,
                            'label'        => $label,
                            'nilai_skor'   => $nilai,
                        ]);
                    }
                }

            }

            /*
            |--------------------------------------------------------------------------
            | SUDAH PERNAH DIGUNAKAN
            |--------------------------------------------------------------------------
            */

            else {

                $indikator->update([
                    'status'      => 'Nonaktif',
                    'diubah_pada' => now(),
                ]);

                $indikatorBaru = Indikator::create([
                    'kategori_id'    => $request->kategori_id,
                    'kode_indikator' => $request->kode_indikator,
                    'pertanyaan'     => $request->pertanyaan,
                    'poin_maksimal'  => $request->poin_maksimal,
                    'tipe_jawaban'   => $request->tipe_jawaban,
                    'wajib_file'     => $request->has('wajib_file'),
                    'status'         => 'Aktif',
                    'urutan'         => $request->urutan,
                    'dibuat_pada'    => now(),
                    'diubah_pada'    => now(),
                ]);

                if (
                        $request->tipe_jawaban == 'pilihan_ganda' &&
                        $request->has('opsi_label')
                    ) {
                    foreach ($request->opsi_label as $index => $label) {
                        if (empty($label)) {
                            continue;
                        }

                        $nilai = $request->opsi_nilai[$index] ?? 0;

                        if ($nilai > $request->poin_maksimal) {
                            return back()
                                ->withInput()
                                ->withErrors([
                                    'opsi_nilai' => 'Nilai skor tidak boleh melebihi poin maksimal.'
                                ]);
                        }

                        OpsiJawaban::create([
                            'indikator_id' => $indikatorBaru->id,
                            'label'        => $label,
                            'nilai_skor'   => $nilai,
                        ]);
                    }
                }
            }

            return redirect()
                ->route('indikator.index')
                ->with('success', 'Indikator berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui indikator. Silakan coba lagi.');
        }
    }

    public function destroy(int $id)
    {
        try {
            $indikator = Indikator::findOrFail($id);
            // Catatan: indikator tidak dihapus permanen, hanya dinonaktifkan
            // (baik yang sudah maupun belum pernah dipakai di assessment).
            $indikator->update([
                'status' => 'Nonaktif',
            ]);

            return redirect()
                ->route('indikator.index')
                ->with('success', 'Indikator berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('indikator.index')
                ->with('error', 'Gagal menghapus indikator. Silakan coba lagi.');
        }
    }
}