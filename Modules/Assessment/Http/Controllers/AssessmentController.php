<?php

namespace Modules\Assessment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Assessment\Entities\Hasil;
use Modules\Assessment\Entities\Jawaban;
use Modules\Assessment\Entities\Periode;
use Modules\Assessment\Services\AssessmentService;
use Modules\Assessment\Services\LaporanExcelService;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Unit;
use Modules\Indikator\Entities\Indikator;
use Modules\Assessment\Entities\Assessment;

class AssessmentController extends Controller
{
    protected $assessmentService;

    public function __construct(AssessmentService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    public function index()
{
    $pengguna = auth()->user();
    $periode = Periode::aktif();

    $jumlahKategori = Kategori::count();
    $jumlahIndikator = Indikator::where('status', 'Aktif')->count();

    $persentase = 0;
    $jawabanExisting = collect();

    if ($pengguna->hasRole('admin') || $pengguna->hasRole('pimpinan')) {

        $kategoris = $this->kategoriSemua();

        if ($periode) {

            $jawabanExisting = $this->jawabanSemua($periode);

            $persentase = $this->persentaseSemua($periode);
        }

    } else {

        $unit = $pengguna->getUnit;

        if (!$unit || !$unit->kategori_id) {

            $kategoris = Kategori::with([
                'indikators' => function ($q) {
                    $q->where('status', 'Aktif')
                        ->orderBy('urutan');
                }
            ])->get();

        } else {

            $kategori = Kategori::with([
                'indikators' => function ($q) {
                    $q->where('status', 'Aktif')
                        ->orderBy('urutan');
                }
            ])->find($unit->kategori_id);

            $kategoris = $kategori
                ? collect([$kategori])
                : collect();

            if ($periode) {

                $jawabanExisting = $this->jawabanUnit($periode, $unit);

                $persentase = $this->persentaseUnit($periode, $kategori, $unit);
            }
        }
    }

    return view(
        'assessment::Assessment.index',
        compact(
            'periode',
            'kategoris',
            'jawabanExisting',
            'persentase',
            'jumlahKategori',
            'jumlahIndikator'
        )
    );
}
    public function create(){
        $pengguna = auth()->user();
        $periode  = Periode::aktif() ?? Periode::orderByDesc('tahun')->first();

        $unitSaya     = $pengguna->getUnit;
        $userKategori = optional($unitSaya)->kategori_id;

        $currentKategori = (int) request('kategori', $userKategori ?? 0);

        if (!$currentKategori) {
            $currentKategori = optional(Kategori::orderBy('kode_kategori')->first())->id;
        }

        abort_if(!$currentKategori, 404, 'Belum ada kategori terdaftar di sistem.');

        $kategoriList = Kategori::with(['indikators.opsiJawaban'])
            ->orderBy('kode_kategori')
            ->get();

        $periodeBisaDiisi = $periode && $periode->status === 'aktif';

        $dataPerKategori = [];

        $reloadData       = session('reload_jawaban_data', []);
        $reloadKategoriId = (int) session('reload_jawaban_kategori', 0);

        foreach ($kategoriList as $kategori) {

            $unitPemilik = Unit::where('kategori_id', $kategori->id)->first();

            $statusHasil = ($periode && $unitPemilik)
                ? $this->statusHasil($periode->id, $unitPemilik->id)
                : 'draft';

            $milikSendiri = $userKategori !== null && $kategori->id === (int) $userKategori;
            $submitted    = $this->isLocked($statusHasil);
            $readonly     = !$milikSendiri || $submitted || !$periodeBisaDiisi;

            $jawabanExisting = collect();

            if ($periode && $unitPemilik) {
                $jawabanExisting = Jawaban::with('assessment')
                    ->whereHas('assessment', function ($q) use ($periode) {
                        $q->where('periode_id', $periode->id);
                    })
                    ->where('unit_id', $unitPemilik->id)
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->assessment->indikator_id;
                    });
            }

            if ($milikSendiri && !$readonly && $reloadKategoriId === $kategori->id && !empty($reloadData)) {
                foreach ($reloadData as $indikatorId => $isi) {
                    $transient = new Jawaban([
                        'jawaban'        => $isi['jawaban'] ?? null,
                        'path_file'      => $isi['path_file'] ?? null,
                        'nama_file_asli' => $isi['nama_file_asli'] ?? null,
                        'link_bukti'     => $isi['link_bukti'] ?? null,
                    ]);
                    // Penanda: nilai ini hasil reload dari periode lain (bukan draft
                    // periode berjalan), dipakai _konten.blade.php untuk menyertakan
                    // file lama lewat hidden input carry_file saat form disimpan.
                    $transient->dari_reload = true;

                    $jawabanExisting->put((int) $indikatorId, $transient);
                }
            }

            $bisaReload = false;

            if ($milikSendiri && !$readonly && $unitPemilik) {
                $bisaReload = Periode::where('status', 'ditutup')
                    ->whereHas('assessments.jawaban', function ($q) use ($unitPemilik) {
                        $q->where('unit_id', $unitPemilik->id);
                    })
                    ->exists();
            }

            $dataPerKategori[$kategori->id] = [
                'kategori'        => $kategori,
                'unitPemilik'     => $unitPemilik,
                'jawabanExisting' => $jawabanExisting,
                'readonly'        => $readonly,
                'submitted'       => $submitted,
                'milikSendiri'    => $milikSendiri,
                'bisaReload'      => $bisaReload,
            ];
        }

        return view('assessment::Assessment.form', compact(
            'periode',
            'kategoriList',
            'dataPerKategori',
            'currentKategori',
            'userKategori'
        ));
    }

    /**
     * Simpan Jawaban Assessment (draft atau submit).
     */
    public function store(Request $request)
    {
        $pengguna = auth()->user();
        $periode  = Periode::aktif();

        abort_if(!$periode, 404);

        $unit = $pengguna->getUnit;

        abort_if(!$unit, 404, 'Anda belum terhubung dengan unit manapun.');

        // Guard: kalau assessment unit ini sudah disubmit, tolak semua perubahan
        $statusSaatIni = $this->statusHasil($periode->id, $unit->id);

        abort_if(
            $this->isLocked($statusSaatIni),
            403,
            'Assessment sudah disubmit dan tidak dapat diubah lagi.'
        );

        $kategori = Kategori::with([
            'indikators.opsiJawaban'
        ])->findOrFail($unit->kategori_id);

        $aksi     = $request->input('aksi', 'draft'); // 'draft' atau 'submit'
        $isSubmit = $aksi === 'submit';

        $validator = Validator::make(
            $request->all(),
            $this->aturanValidasi($kategori, $isSubmit),
            [],
            $this->labelAtribut($kategori->indikators)
        );

        // Validasi silang: saat submit, indikator yang wajib evidence
        // harus punya salah satu dari: file baru, link baru, atau file/link lama.
        if ($isSubmit) {
            $validator->after(function ($validator) use ($request, $kategori, $unit) {
                foreach ($kategori->indikators as $indikator) {

                    if (!$indikator->wajib_file) {
                        continue;
                    }

                    $adaFileBaru = $request->hasFile("file.$indikator->id");
                    $adaLinkBaru = filled($request->input("link.$indikator->id"));
                    $adaFileBawaanReload = filled($request->input("carry_file.$indikator->id"));

                    $adaBuktiLama = Jawaban::whereHas('assessment', function ($q) use ($indikator) {
                            $q->where('indikator_id', $indikator->id);
                        })
                        ->where('unit_id', $unit->id)
                        ->where(function ($q) {
                            $q->whereNotNull('path_file')->orWhereNotNull('link_bukti');
                        })
                        ->exists();

                    if (!$adaFileBaru && !$adaLinkBaru && !$adaFileBawaanReload && !$adaBuktiLama) {
                        $validator->errors()->add(
                            "file.$indikator->id",
                            "Evidence untuk {$indikator->kode_indikator} wajib diisi (upload file atau isi link)."
                        );
                    }
                }
            });
        }

        $validator->validate();

        try {
            // File bawaan dari "Reload Jawaban Assessment Sebelumnya" (lihat
            // _konten.blade.php & AssessmentService::ambilJawabanSebelumnya()).
            // Dipakai hanya untuk indikator yang tidak diberi upload baru.
            $carryFile = [];
            foreach ((array) $request->input('carry_file', []) as $indikatorId => $path) {
                if (!$path) {
                    continue;
                }
                $carryFile[$indikatorId] = [
                    'path' => $path,
                    'nama' => $request->input("carry_file_nama.$indikatorId"),
                ];
            }

            $this->assessmentService->hitungDanSimpan(
                $periode,
                $kategori,
                $request->jawaban ?? [],
                $pengguna->id,
                $unit->id,
                $request->file('file') ?? [],
                $request->input('link') ?? [],
                $isSubmit ? 'submitted' : 'draft',
                $carryFile
            );

            return redirect()
                ->route('assessment.create', ['kategori' => $kategori->id])
                ->with(
                    'success',
                    $isSubmit
                        ? 'Assessment berhasil disubmit dan tidak dapat diubah lagi.'
                        : 'Draft berhasil disimpan.'
                );
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('assessment.create', ['kategori' => $kategori->id])
                ->with('error', 'Gagal menyimpan jawaban assessment. Silakan coba lagi.');
        }
    }

    /**
     * "Reload Jawaban Assessment Sebelumnya"
     *
     * Ambil jawaban dari periode sebelumnya (yang sudah ditutup) untuk unit
     * milik pengguna, lalu simpan sementara di flash session supaya
     * dipakai sebagai nilai awal form pada request berikutnya (create()).
     * Tidak menyimpan/mengubah data apa pun di assessment yang lama maupun
     * yang sedang berjalan.
     */
    public function reloadPrevious(Request $request)
    {
        $pengguna = auth()->user();
        $unit     = $pengguna->getUnit;

        abort_if(!$unit, 404, 'Anda belum terhubung dengan unit manapun.');

        $kategoriId = (int) $request->query('kategori');

        $kategori = Kategori::with(['indikators.opsiJawaban'])->find($kategoriId);

        if (!$kategori || (int) $unit->kategori_id !== $kategori->id) {
            abort(403, 'Anda tidak berhak mengisi kategori ini.');
        }

        try {
            $hasil = $this->assessmentService->ambilJawabanSebelumnya($kategori, $unit);

            if (empty($hasil['data'])) {
                return redirect()
                    ->route('assessment.create', ['kategori' => $kategori->id])
                    ->with('warning', 'Belum ditemukan data assessment sebelumnya (periode yang sudah ditutup) untuk dimuat.');
            }

            return redirect()
                ->route('assessment.create', ['kategori' => $kategori->id])
                ->with(
                    'success',
                    'Jawaban assessment tahun ' . $hasil['tahun'] . ' berhasil dimuat ke form. '
                    . 'Periksa kembali dan ubah jika perlu sebelum menyimpan.'
                )
                ->with('reload_jawaban_data', $hasil['data'])
                ->with('reload_jawaban_kategori', $kategori->id);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('assessment.create', ['kategori' => $kategori->id])
                ->with('error', 'Gagal memuat jawaban assessment sebelumnya. Silakan coba lagi.');
        }
    }

    /**
     * Unduh rekap assessment kategori (milik sendiri atau kategori lain
     * yang sedang dilihat) dalam format Excel.
     */
    public function exportExcel(Request $request)
    {
        $pengguna = auth()->user();
        $periode  = Periode::aktif() ?? Periode::orderByDesc('tahun')->first();

        abort_if(!$periode, 404, 'Belum ada data periode.');

        $unitSaya     = $pengguna->getUnit;
        $kategoriId   = (int) $request->query('kategori', optional($unitSaya)->kategori_id);

        $kategori = Kategori::findOrFail($kategoriId);
        $unitPemilik = Unit::where('kategori_id', $kategoriId)->first();

        abort_if(!$unitPemilik, 404, 'Unit pemilik kategori ini tidak ditemukan.');

        try {
            // Ambil indikator PERSIS seperti yang terdaftar di periode ini
            // (lewat tabel assessment), bukan daftar indikator kategori yang
            // "live" -- supaya kalau indikator sudah diedit/di-versi-kan
            // setelah periode ini, laporan periode lama tidak menampilkan
            // baris ganda (versi lama & versi baru sekaligus).
            $indikatorPeriode = Indikator::with('opsiJawaban')
                ->whereIn('id', function ($q) use ($periode, $kategoriId) {
                    $q->select('indikator_id')
                        ->from('assessment')
                        ->where('periode_id', $periode->id)
                        ->whereIn('indikator_id', function ($q2) use ($kategoriId) {
                            $q2->select('id')->from('indikator')->where('kategori_id', $kategoriId);
                        });
                })
                ->orderBy('urutan')
                ->get();

            $jawabanByIndikator = Jawaban::with('assessment')
                ->whereHas('assessment', function ($q) use ($periode) {
                    $q->where('periode_id', $periode->id);
                })
                ->where('unit_id', $unitPemilik->id)
                ->get()
                ->keyBy(function ($item) {
                    return $item->assessment->indikator_id;
                });

            return (new LaporanExcelService)->build(
                $periode,
                $kategori,
                $unitPemilik,
                $indikatorPeriode,
                $jawabanByIndikator
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal membuat file Excel. Silakan coba lagi.');
        }
    }

    /**
     * Riwayat Assessment
     */
    public function riwayat()
    {
        $unit = auth()->user()->getUnit;

        $riwayat = collect();

        if ($unit) {

            $riwayat = Jawaban::where('unit_id', $unit->id)
                ->with('assessment.periode')
                ->get()
                ->groupBy(function ($item) {
                    return $item->assessment->periode_id;
                });
        }

        return view(
            'assessment::Assessment.riwayat',
            compact('riwayat', 'unit')
        );
    }

    /**
     * Validasi Jawaban.
     * $wajibLengkap = true saat submit (semua wajib diisi),
     * false saat simpan draft (boleh sebagian / kosong).
     */
    private function aturanValidasi($kategori, $isSubmit)
    {
        $rules = [];

        foreach ($kategori->indikators as $indikator) {

            /*
            |--------------------------------------------------------------------------
            | SAVE DRAFT
            |--------------------------------------------------------------------------
            | Tidak ada field yang diwajibkan.
            */

            if (!$isSubmit) {

                $rules["jawaban.{$indikator->id}"] = 'nullable';

                $rules["file.{$indikator->id}"] =
                    'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048';

                $rules["link.{$indikator->id}"] =
                    'nullable|url';

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | SUBMIT
            |--------------------------------------------------------------------------
            | Jawaban wajib diisi.
            */

            $rules["jawaban.{$indikator->id}"] = 'required';

            /*
            |--------------------------------------------------------------------------
            | FILE
            |--------------------------------------------------------------------------
            | Jangan langsung required di sini.
            |
            | Karena file lama bisa saja sudah tersedia.
            | Pengecekan file/link dilakukan di validator->after().
            */

            $rules["file.{$indikator->id}"] =
                'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048';

            $rules["link.{$indikator->id}"] =
                'nullable|url';
        }

        return $rules;
    }

    /**
     * Label Error
     */
    private function labelAtribut($indikators): array
    {
        $label = [];

        foreach ($indikators as $indikator) {

            $label["jawaban.$indikator->id"] =
                $indikator->kode_indikator . ' - ' . $indikator->pertanyaan;

            $label["file.$indikator->id"] =
                'File bukti ' . $indikator->kode_indikator;

            $label["link.$indikator->id"] =
                'Link bukti ' . $indikator->kode_indikator;
        }

        return $label;
    }

    /**
     * Ambil status hasil_assessment untuk unit tertentu pada periode tertentu.
     * Default 'draft' jika belum pernah ada rekap.
     */
    private function statusHasil(?int $periodeId, ?int $unitId): string
    {
        if (!$periodeId || !$unitId) {
            return 'draft';
        }

        return optional(
            Hasil::where('periode_id', $periodeId)
                ->where('unit_id', $unitId)
                ->first()
        )->status ?? 'draft';
    }

    /**
     * Status yang dianggap terkunci (tidak bisa diubah lagi).
     */
    private function isLocked(string $status): bool
    {
        return in_array($status, ['submitted', 'verified']);
    }

    private function kategoriSemua()
{
    return Kategori::with([
        'indikators' => function ($q) {
            $q->where('status', 'Aktif')
                ->orderBy('urutan');
        }
    ])->get();
}
    private function jawabanUnit($periode, $unit)
{
    return Jawaban::with('assessment')
        ->where('unit_id', $unit->id)
        ->whereHas('assessment', function ($q) use ($periode) {

            $q->where('periode_id', $periode->id);

        })
        ->orderByDesc('diubah_pada')
        ->get()
        ->groupBy(function ($item) {

            return $item->assessment->indikator_id;

        })
        ->map(function ($items) {

            return $items->first();

        });
}
private function jawabanSemua($periode)
{
    return Jawaban::with('assessment')
        ->whereHas('assessment', function ($q) use ($periode) {

            $q->where('periode_id', $periode->id);

        })
        ->orderByDesc('diubah_pada')
        ->get()
        ->groupBy(function ($item) {

            return $item->assessment->indikator_id;

        })
        ->map(function ($items) {

            return $items->first();

        });
}

    private function persentaseSemua($periode)
{
    $totalSkor = Jawaban::whereHas('assessment', function ($q) use ($periode) {

        $q->where('periode_id', $periode->id);

    })->sum('skor_diperoleh');

    $totalMaksimal = Assessment::where('periode_id', $periode->id)
        ->join('indikator', 'assessment.indikator_id', '=', 'indikator.id')
        ->sum('indikator.poin_maksimal');

    return $totalMaksimal > 0
        ? round(($totalSkor / $totalMaksimal) * 100, 2)
        : 0;
}

    private function persentaseUnit($periode, $kategori, $unit)
{
    if (!$kategori || !$unit) {
        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Total skor yang diperoleh unit ini
    |--------------------------------------------------------------------------
    */

    $totalSkor = Jawaban::where('unit_id', $unit->id)
        ->whereHas('assessment', function ($q) use ($periode) {
            $q->where('periode_id', $periode->id);
        })
        ->sum('skor_diperoleh');


    /*
    |--------------------------------------------------------------------------
    | Total skor maksimal kategori
    |--------------------------------------------------------------------------
    */

    $totalMaksimal = $kategori->indikators
        ->where('status', 'Aktif')
        ->sum('poin_maksimal');


    /*
    |--------------------------------------------------------------------------
    | Hitung persentase
    |--------------------------------------------------------------------------
    */

    if ($totalMaksimal <= 0) {
        return 0;
    }

    $persentase = ($totalSkor / $totalMaksimal) * 100;

    return round(min($persentase, 100), 2);
}
}
