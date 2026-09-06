<?php

namespace Modules\Assessment\Services;

use Modules\Assessment\Entities\Assessment;
use Modules\Assessment\Entities\Hasil;
use Modules\Assessment\Entities\Jawaban;
use Modules\Assessment\Entities\Periode;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Unit;

class AssessmentService
{
    /**
     * Simpan seluruh jawaban assessment untuk satu unit pada satu periode,
     * lalu hitung ulang rekap skornya di tabel hasil_assessment.
     *
     * @param  array   $jawabanInput   [indikator_id => nilai_jawaban]
     * @param  array   $fileData       [indikator_id => UploadedFile]
     * @param  array   $linkData       [indikator_id => string url]
     * @param  array   $carryFile      [indikator_id => ['path' => string, 'nama' => string]]
     *                                 File hasil "Reload Jawaban Assessment Sebelumnya" yang
     *                                 belum diganti user dengan upload baru (lihat ambilJawabanSebelumnya()).
     * @param  string  $statusTarget   'draft' atau 'submitted'
     */
    public function hitungDanSimpan(
        Periode $periode,
        Kategori $kategori,
        array $jawabanInput,
        int $userId,
        int $unitId,
        array $fileData = [],
        array $linkData = [],
        string $statusTarget = 'draft',
        array $carryFile = []
    ) {
        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $periode, $kategori, $jawabanInput, $userId, $unitId, $fileData, $linkData, $statusTarget, $carryFile
        ) {
        foreach ($kategori->indikators as $indikator) {

            $input = $jawabanInput[$indikator->id] ?? null;

            $nilaiJawaban = null;
            $skor = 0;
            $opsiLabelSnapshot = null;

            switch ($indikator->tipe_jawaban) {

                case 'pilihan_ganda':

                    $opsi = $indikator->opsiJawaban()->find($input);

                    if ($opsi) {
                        $nilaiJawaban = (string) $opsi->id;
                        $skor = $opsi->nilai_skor;
                        // Label opsi dibekukan di sini juga, supaya kalau opsi
                        // diedit/dihapus nanti, laporan lama tetap menampilkan
                        // teks pilihan yang benar-benar dipilih saat itu.
                        $opsiLabelSnapshot = $opsi->label;
                    }

                    break;

                case 'angka':

                    $nilaiJawaban = $input;

                    $skor = $input !== null && $input !== ''
                        ? min((float) $input, $indikator->poin_maksimal)
                        : 0;

                    break;

                case 'isian':
                default:

                    $nilaiJawaban = $input;
                    $skor = 0;

                    break;
            }

            /*
             |---------------------------------------------------------
             | Cari / buat baris assessment (periode + indikator)
             |---------------------------------------------------------
             */

            $assessment = Assessment::firstOrCreate([
                'periode_id'   => $periode->id,
                'indikator_id' => $indikator->id,
            ]);

            /*
             |---------------------------------------------------------
             | Tangani upload file bukti (jika ada)
             |---------------------------------------------------------
             */

            $pathFile = null;
            $namaFileAsli = null;

            /** @var \Illuminate\Http\UploadedFile|null $file */
            $file = $fileData[$indikator->id] ?? null;

            if ($file) {
                $pathFile = $file->store('assessment/' . $periode->id . '/' . $unitId, 'public');
                $namaFileAsli = $file->getClientOriginalName();
            }

            $linkBukti = trim((string) ($linkData[$indikator->id] ?? '')) ?: null;

            $jawabanLama = Jawaban::where('assessment_id', $assessment->id)
                ->where('unit_id', $unitId)
                ->first();

            // Saat draft & indikator ini tidak diisi sama sekali, jangan timpa jawaban lama.
            if ($nilaiJawaban === null && $jawabanLama) {
                continue;
            }

            // File bawaan dari "Reload Jawaban Assessment Sebelumnya" (periode lain),
            // dipakai hanya kalau tidak ada upload baru dan tidak ada file draft
            // periode berjalan yang sudah tersimpan.
            $carry = $carryFile[$indikator->id] ?? null;

            Jawaban::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'unit_id'       => $unitId,
                ],
                [
                    'dijawab_oleh'   => $userId,
                    'jawaban'        => $nilaiJawaban,
                    'skor_diperoleh' => $skor,
                    // kalau tidak ada file/link baru, pertahankan yang lama
                    'path_file'      => $pathFile ?? optional($jawabanLama)->path_file ?? ($carry['path'] ?? null),
                    'nama_file_asli' => $namaFileAsli ?? optional($jawabanLama)->nama_file_asli ?? ($carry['nama'] ?? null),
                    'link_bukti'     => $linkBukti ?? optional($jawabanLama)->link_bukti,
                    // snapshot: dibekukan sekali di sini, tidak pernah ditimpa oleh
                    // perubahan master indikator/opsi setelahnya.
                    'kode_indikator_snapshot' => $indikator->kode_indikator,
                    'pertanyaan_snapshot'     => $indikator->pertanyaan,
                    'tipe_jawaban_snapshot'   => $indikator->tipe_jawaban,
                    'poin_maksimal_snapshot'  => $indikator->poin_maksimal,
                    'opsi_label_snapshot'     => $opsiLabelSnapshot,
                ]
            );
        }

        $this->hitungHasil($periode, $kategori, $unitId, $statusTarget);

        return true;
        });
    }

    /**
     * Hitung ulang rekap skor unit untuk periode berjalan
     * dan simpan/mutakhirkan ke tabel hasil_assessment.
     *
     * Status tidak pernah diturunkan otomatis (submitted/verified tidak
     * akan berubah balik ke draft) - transisi status murni ditentukan
     * oleh aksi eksplisit user (draft/submit) yang dikirim controller.
     */
    protected function hitungHasil(Periode $periode, Kategori $kategori, int $unitId, string $statusTarget): Hasil
    {
        $totalSkor = Jawaban::where('unit_id', $unitId)
            ->whereHas('assessment', function ($q) use ($periode, $kategori) {
                $q->where('periode_id', $periode->id)
                  ->whereHas('indikator', function ($q) use ($kategori) {
                      $q->where('kategori_id', $kategori->id);
                  });
            })
            ->sum('skor_diperoleh');

        $skorMaksimal = $kategori->indikators->sum('poin_maksimal');

        $persentase = $skorMaksimal > 0
            ? round(($totalSkor / $skorMaksimal) * 100, 2)
            : 0;

        $hasilLama = Hasil::where('periode_id', $periode->id)
            ->where('unit_id', $unitId)
            ->first();

        $statusAkhir = $statusTarget;

        // Jaga-jaga: jangan pernah menurunkan status yang sudah terkunci.
        if ($hasilLama && in_array($hasilLama->status, ['submitted', 'verified'])) {
            $statusAkhir = $hasilLama->status;
        }

        return Hasil::updateOrCreate(
            [
                'periode_id' => $periode->id,
                'unit_id'    => $unitId,
            ],
            [
                'total_skor'    => $totalSkor,
                'skor_maksimal' => $skorMaksimal,
                'persentase'    => $persentase,
                'status'        => $statusAkhir,
                'dibuat_pada'   => $hasilLama->dibuat_pada ?? now(),
                'diubah_pada'   => now(),
            ]
        );
    }

    /**
     * Ambil jawaban assessment dari periode SEBELUMNYA (periode ditutup
     * terakhir yang punya data untuk unit ini) untuk dipakai sebagai
     * bahan pengisian awal form assessment yang baru.
     *
     * Pencocokan indikator dilakukan berdasarkan `kode_indikator`
     * (bukan `indikator_id`), karena indikator yang pernah dipakai di
     * suatu periode selalu "di-versi-kan" (baris lama dinonaktifkan,
     * baris baru dibuat) saat diedit -- lihat IndikatorController::update().
     * Artinya id indikator bisa berubah antar periode walau kode & makna
     * pertanyaannya tetap sama.
     *
     * Data ini TIDAK menyimpan/mengubah apa pun -- murni dikembalikan
     * untuk ditampilkan sebagai nilai awal form (lihat
     * AssessmentController::create() & reloadPrevious()).
     *
     * @return array{tahun: int|null, data: array<int, array>}
     */
    public function ambilJawabanSebelumnya(Kategori $kategori, Unit $unit): array
    {
        $periodeSebelumnya = Periode::where('status', 'ditutup')
            ->whereHas('assessments.jawaban', function ($q) use ($unit) {
                $q->where('unit_id', $unit->id);
            })
            ->orderByDesc('tahun')
            ->first();

        if (!$periodeSebelumnya) {
            return ['tahun' => null, 'data' => []];
        }

        // kode_indikator (versi lama) => Jawaban periode sebelumnya
        $jawabanLamaByKode = Jawaban::with('assessment.indikator')
            ->where('unit_id', $unit->id)
            ->whereHas('assessment', function ($q) use ($periodeSebelumnya) {
                $q->where('periode_id', $periodeSebelumnya->id);
            })
            ->get()
            ->filter(function ($j) {
                // Pakai kode dari snapshot dulu (kalau ada) -- lebih pasti benar
                // walau indikator lama sudah tidak ada relasinya untuk sebab apa pun.
                return $j->kode_indikator_snapshot || optional($j->assessment)->indikator;
            })
            ->keyBy(function ($j) {
                return $j->kode_indikator_snapshot ?: $j->assessment->indikator->kode_indikator;
            });

        $data = [];

        foreach ($kategori->indikators as $indikatorBaru) {

            $lama = $jawabanLamaByKode->get($indikatorBaru->kode_indikator);

            if (!$lama) {
                // Indikator baru (kode tidak ditemukan di assessment sebelumnya)
                // -> dibiarkan kosong, sesuai permintaan.
                continue;
            }

            $nilaiUntukForm = $lama->jawaban;

            if ($indikatorBaru->tipe_jawaban === 'pilihan_ganda') {
                // Id opsi bisa berbeda antar versi indikator, jadi dicocokkan
                // lewat teks label yang dibekukan (opsi_label_snapshot) saat
                // jawaban lama disimpan.
                $labelLama = $lama->opsi_label_snapshot;

                $opsiCocok = $labelLama
                    ? $indikatorBaru->opsiJawaban->firstWhere('label', $labelLama)
                    : null;

                $nilaiUntukForm = $opsiCocok ? $opsiCocok->id : null;
            }

            $data[$indikatorBaru->id] = [
                'jawaban'        => $nilaiUntukForm,
                'path_file'      => $lama->path_file,
                'nama_file_asli' => $lama->nama_file_asli,
                'link_bukti'     => $lama->link_bukti,
            ];
        }

        return [
            'tahun' => $periodeSebelumnya->tahun,
            'data'  => $data,
        ];
    }
}
