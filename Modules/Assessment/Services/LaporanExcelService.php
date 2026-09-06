<?php

namespace Modules\Assessment\Services;

use Illuminate\Support\Collection;
use Modules\Assessment\Entities\Periode;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Unit;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanExcelService
{
    /**
     * Bangun file Excel rekap assessment satu kategori/unit pada satu periode,
     * dengan format kolom mengikuti kuesioner resmi UI GreenMetric
     * (No, Indikator, Jawaban, Skor, Skor Maksimal, Evidence).
     *
     * @param  Collection  $indikatorPeriode  Indikator PERSIS seperti yang terdaftar
     *                                        di periode ini (lihat AssessmentController::exportExcel()),
     *                                        bukan daftar indikator kategori yang "live" -- supaya laporan
     *                                        periode lama tidak berubah/duplikat kalau master indikator
     *                                        diedit setelahnya.
     */
    public function build(
        Periode $periode,
        Kategori $kategori,
        Unit $unit,
        Collection $indikatorPeriode,
        Collection $jawabanByIndikator
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($kategori->kode_kategori, 0, 25));

        $this->tulisHeader($sheet, $periode, $kategori, $unit);
        $baris = $this->tulisTabel($sheet, $indikatorPeriode, $jawabanByIndikator);
        $this->tulisTotal($sheet, $baris);

        foreach (range('A', 'F') as $kolom) {
            $sheet->getColumnDimension($kolom)->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(60);

        $namaFile = 'Laporan-' . $kategori->kode_kategori . '-' . $unit->kode_unit . '-' . $periode->tahun . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function tulisHeader($sheet, Periode $periode, Kategori $kategori, Unit $unit): void
    {
        $sheet->setCellValue('A1', 'Laporan Self Assessment - UI GreenMetric');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Kategori');
        $sheet->setCellValue('B2', $kategori->nama_kategori . ' (' . $kategori->kode_kategori . ')');

        $sheet->setCellValue('A3', 'Unit');
        $sheet->setCellValue('B3', $unit->nama_unit . ' (' . $unit->kode_unit . ')');

        $sheet->setCellValue('A4', 'Periode');
        $sheet->setCellValue('B4', $periode->tahun);

        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
    }

    private function tulisTabel($sheet, Collection $indikatorPeriode, Collection $jawabanByIndikator): int
    {
        $headerRow = 6;

        $header = ['No', 'Indikator', 'Jawaban', 'Skor Diperoleh', 'Skor Maksimal', 'Evidence'];
        $sheet->fromArray($header, null, 'A' . $headerRow);

        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9E1F2');

        $baris = $headerRow + 1;
        $nomor = 1;

        foreach ($indikatorPeriode as $indikator) {

            $jawaban = $jawabanByIndikator->get($indikator->id);

            // Kode/pertanyaan/poin maksimal diutamakan dari snapshot (dibekukan
            // saat jawaban disimpan) supaya tidak ikut berubah kalau master
            // indikator diedit setelah periode ini. Fallback ke data master
            // untuk baris yang belum pernah dijawab (mis. indikator belum
            // sempat diisi sebelum periode ditutup).
            $kode        = optional($jawaban)->kode_indikator_snapshot ?? $indikator->kode_indikator;
            $pertanyaan  = optional($jawaban)->pertanyaan_snapshot ?? $indikator->pertanyaan;
            $poinMaksimal = optional($jawaban)->poin_maksimal_snapshot ?? $indikator->poin_maksimal;

            $teksJawaban = '-';

            if ($jawaban && $jawaban->jawaban !== null) {
                if ($indikator->tipe_jawaban === 'pilihan_ganda') {
                    if ($jawaban->opsi_label_snapshot) {
                        $teksJawaban = $jawaban->opsi_label_snapshot;
                    } else {
                        $opsi = $indikator->opsiJawaban->firstWhere('id', (int) $jawaban->jawaban);
                        $teksJawaban = $opsi
                            ? '[' . $opsi->urutan . '] ' . $opsi->label
                            : '-';
                    }
                } else {
                    $teksJawaban = $jawaban->jawaban;
                }
            }

            $evidence = '-';

            if ($jawaban) {
                if ($jawaban->path_file) {
                    $evidence = $jawaban->nama_file_asli ?: 'File terlampir';
                } elseif ($jawaban->link_bukti) {
                    $evidence = $jawaban->link_bukti;
                }
            }

            $sheet->setCellValue('A' . $baris, $nomor);
            $sheet->setCellValue('B' . $baris, $kode . ' - ' . $pertanyaan);
            $sheet->setCellValue('C' . $baris, $teksJawaban);
            $sheet->setCellValue('D' . $baris, $jawaban->skor_diperoleh ?? 0);
            $sheet->setCellValue('E' . $baris, $poinMaksimal);
            $sheet->setCellValue('F' . $baris, $evidence);

            $sheet->getStyle('B' . $baris)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C' . $baris)->getAlignment()->setWrapText(true);

            $baris++;
            $nomor++;
        }

        $sheet->getStyle('A' . $headerRow . ':F' . ($baris - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $baris;
    }

    private function tulisTotal($sheet, int $barisAkhir): void
    {
        $sheet->setCellValue('C' . $barisAkhir, 'TOTAL');
        $sheet->setCellValue('D' . $barisAkhir, '=SUM(D7:D' . ($barisAkhir - 1) . ')');
        $sheet->setCellValue('E' . $barisAkhir, '=SUM(E7:E' . ($barisAkhir - 1) . ')');

        $sheet->getStyle('C' . $barisAkhir . ':E' . $barisAkhir)->getFont()->setBold(true);
    }
}
