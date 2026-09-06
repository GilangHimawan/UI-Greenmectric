<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Self Assessment GreenMetric</title>
    <style>
        @page {
            margin: 30px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #bdbdbd;
            padding: 7px;
            vertical-align: middle;
        }

        th {
            background: #0B6E4F;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 2px;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }

        .header {
            margin-bottom: 15px;
        }

        .title {
            color: #0B6E4F;
            font-size: 20px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 14px;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        .box {
            border: 1px solid #dcdcdc;
            background: #f8f8f8;
            text-align: center;
            padding: 15px;
        }

        .summary td {
            border: none;
        }

        .section-title {
            background: #0B6E4F;
            color: white;
            padding: 7px 10px;
            font-size: 13px;
            margin-top: 18px;
            margin-bottom: 8px;
        }

        .total {
            background: #E8F5E9;
            font-weight: bold;
        }

        .progress {
            width: 100%;
            border: 1px solid #999;
            height: 18px;
        }

        .progress-bar {
            background: #2E7D32;
            color: white;
            height: 18px;
            line-height: 18px;
            text-align: center;
            font-size: 10px;
        }

        .footer {
            margin-top: 50px;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    {{-- ==================== HEADER ==================== --}}
    <table class="no-border">
        <tr>
            <td width="90">
                <img src="{{ public_path('assets/img/logo.png') }}" width="70">
            </td>
            <td>
                <div class="title">GREENMETRIC SELF ASSESSMENT REPORT</div>
                <div class="subtitle">POLITEKNIK NEGERI BANYUWANGI</div>
                <div>Laporan Hasil Self Assessment UI GreenMetric World University Rankings</div>
            </td>
        </tr>
    </table>
    <hr>

    {{-- ==================== INFO PERIODE ==================== --}}
    <table class="no-border">
        <tr>
            <td width="150">Periode Assessment</td>
            <td>: {{ $periode->tahun }}</td>
        </tr>
        <tr>
            <td>Unit</td>
            <td>: {{ $unitId ? optional($units->find($unitId))->nama_unit : 'Semua Unit' }}</td>
        </tr>
        <tr>
            <td>Tanggal Cetak</td>
            <td>: {{ now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    {{-- ==================== RINGKASAN ASSESSMENT ==================== --}}
    <div class="section-title">RINGKASAN ASSESSMENT</div>

    <table class="summary">
        <tr>
            <td width="33%">
                <div class="box">
                    <b>Total Skor</b><br><br>
                    <h2>{{ number_format($grandTotal) }}</h2>
                </div>
            </td>
            <td width="33%">
                <div class="box">
                    <b>Skor Maksimal</b><br><br>
                    <h2>{{ number_format($grandMaksimal) }}</h2>
                </div>
            </td>
            <td width="34%">
                <div class="box">
                    <b>Persentase</b><br><br>
                    <h2>{{ $overall }}%</h2>
                </div>
            </td>
        </tr>
    </table>

    {{-- ==================== REKAP NILAI PER KATEGORI ==================== --}}
    <div class="section-title">REKAP NILAI PER KATEGORI</div>

    <table>
        <thead>
            <tr>
                <th width="6%">No</th>
                <th>Kategori</th>
                <th width="14%">Nilai</th>
                <th width="14%">Maksimal</th>
                <th width="18%">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($laporan as $row)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['maksimal'] }}</td>
                    <td class="text-center">{{ $row['persentase'] }}%</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">TOTAL</td>
                <td class="text-center">{{ $grandTotal }}</td>
                <td class="text-center">{{ $grandMaksimal }}</td>
                <td class="text-center">{{ $overall }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- ==================== PROGRESS ASSESSMENT ==================== --}}
    <div class="section-title">PROGRESS ASSESSMENT</div>

    <table class="no-border">
        <tr>
            <td width="100%">
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min($overall, 100) }}%;">
                        {{ $overall }}%
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <br>

    {{-- ==================== KESIMPULAN HASIL ASSESSMENT ==================== --}}
    <div class="section-title">KESIMPULAN HASIL ASSESSMENT</div>

    <table>
        <tr>
            <th width="30%">Persentase</th>
            <th>Kategori Penilaian</th>
        </tr>
        <tr>
            <td class="text-center">{{ $overall }}%</td>
            <td>
                @if($overall >= 80)
                    <b>Sangat Baik</b><br>
                    Pelaksanaan Self Assessment telah memenuhi sebagian besar indikator
                    UI GreenMetric dengan sangat baik dan direkomendasikan untuk
                    dipertahankan serta ditingkatkan.
                @elseif($overall >= 60)
                    <b>Baik</b><br>
                    Pelaksanaan Self Assessment sudah berjalan dengan baik,
                    namun masih terdapat beberapa indikator yang perlu ditingkatkan.
                @elseif($overall >= 40)
                    <b>Cukup</b><br>
                    Pelaksanaan Assessment cukup baik namun masih memerlukan
                    perbaikan pada beberapa kategori agar memperoleh nilai optimal.
                @else
                    <b>Perlu Peningkatan</b><br>
                    Nilai assessment masih rendah sehingga diperlukan evaluasi
                    serta peningkatan pada sebagian besar indikator.
                @endif
            </td>
        </tr>
    </table>
    <br>

    {{-- ==================== RINGKASAN NILAI ==================== --}}
    <div class="section-title">RINGKASAN NILAI</div>

    <table>
        <tr>
            <th width="40%">Komponen</th>
            <th width="20%">Nilai</th>
            <th>Keterangan</th>
        </tr>
        <tr>
            <td>Total Skor Assessment</td>
            <td class="text-center">{{ $grandTotal }}</td>
            <td>Akumulasi seluruh skor indikator</td>
        </tr>
        <tr>
            <td>Skor Maksimal</td>
            <td class="text-center">{{ $grandMaksimal }}</td>
            <td>Total skor maksimum seluruh indikator</td>
        </tr>
        <tr>
            <td>Persentase Capaian</td>
            <td class="text-center">{{ $overall }}%</td>
            <td>Persentase pencapaian assessment</td>
        </tr>
    </table>
    <br><br>

    {{-- ==================== CATATAN & TANDA TANGAN ==================== --}}
    <table class="no-border">
        <tr>
            <td width="55%">
                <b>Catatan :</b><br><br>
                Laporan ini dihasilkan secara otomatis oleh
                <b>Sistem Self Assessment UI GreenMetric</b>
                Politeknik Negeri Banyuwangi.
                Seluruh nilai dihitung berdasarkan jawaban assessment
                yang telah diinput pada periode <b>{{ $periode->tahun }}</b>.
            </td>
            <td width="45%" class="text-center">
                Banyuwangi, {{ now()->translatedFormat('d F Y') }}
                <br><br><br><br><br>
                _____________________________<br>
                <b>Pimpinan</b>
            </td>
        </tr>
    </table>

    {{-- ==================== FOOTER ==================== --}}
    <div class="footer">
        <hr>
        <table class="no-border">
            <tr>
                <td>Dicetak oleh : <b>{{ auth()->user()->name }}</b></td>
                <td class="text-right">Sistem Self Assessment GreenMetric</td>
            </tr>
        </table>
    </div>

    {{-- ==================== LAMPIRAN A: DETAIL JAWABAN ==================== --}}
    @if(!empty($detailJawaban) && count($detailJawaban) > 0)
        <div class="page-break"></div>

        <h2 style="text-align:center">LAMPIRAN A</h2>
        <h3 style="text-align:center">DETAIL JAWABAN SELF ASSESSMENT</h3>
        <br>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Kategori</th>
                    <th width="10%">Kode</th>
                    <th>Indikator</th>
                    <th width="15%">Jawaban</th>
                    <th width="10%">Nilai</th>
                    <th width="10%">Maksimal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detailJawaban as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item['kategori'] }}</td>
                        <td class="text-center">{{ $item['kode'] }}</td>
                        <td>{{ $item['indikator'] }}</td>
                        <td>{{ $item['jawaban'] }}</td>
                        <td class="text-center">{{ $item['nilai'] }}</td>
                        <td class="text-center">{{ $item['maksimal'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>