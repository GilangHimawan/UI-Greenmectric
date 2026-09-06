@extends('adminlte::page')

@section('title', 'Dashboard GreenMetric')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Dashboard GreenMetric</h1>
            <small class="text-muted">Monitoring Self Assessment</small>
        </div>

        @if ($periode)
            <span class="badge badge-success p-2">Periode {{ $periode->tahun }}</span>
        @endif
    </div>
@stop

@section('content')

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $jumlahKategori }}</h3>
                    <p>Kategori</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $jumlahIndikator }}</h3>
                    <p>Indikator</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $jumlahTerisi }}</h3>
                    <p>Sudah Diisi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $persentase }}%</h3>
                    <p>Progress</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Radar GreenMetric</h3>
                </div>
                <div class="card-body">
                    <canvas id="radarChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi Nilai</h3>
                </div>
                <div class="card-body">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Progress Assessment per Kategori</h3>
                </div>
                <div class="card-body">
                    @forelse ($progressKategori as $kategori)
                        @php
                            if ($kategori['persentase'] >= 80) {
                                $color = 'bg-success';
                            } elseif ($kategori['persentase'] >= 60) {
                                $color = 'bg-info';
                            } elseif ($kategori['persentase'] >= 40) {
                                $color = 'bg-warning';
                            } else {
                                $color = 'bg-danger';
                            }
                        @endphp

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $kategori['kode'] }} - {{ $kategori['nama'] }}</strong>
                                <span>{{ $kategori['total'] }} / {{ $kategori['maksimal'] }}</span>
                            </div>

                            <div class="progress mt-2">
                                <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $kategori['persentase'] }}%;">
                                    {{ $kategori['persentase'] }}%
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            Belum ada data assessment.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Assessment</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Total Kategori</th>
                            <td>{{ $jumlahKategori }}</td>
                        </tr>
                        <tr>
                            <th>Total Indikator</th>
                            <td>{{ $jumlahIndikator }}</td>
                        </tr>
                        <tr>
                            <th>Sudah Terisi</th>
                            <td>{{ $jumlahTerisi }}</td>
                        </tr>
                        <tr>
                            <th>Progress</th>
                            <td>
                                <strong class="text-success">{{ $persentase }} %</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Aktivitas Assessment Terbaru</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Unit</th>
                        <th>Indikator</th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->diubah_pada)->diffForHumans() }}</td>
                            <td>{{ optional($item->penjawab)->name }}</td>
                            <td>{{ optional($item->unit)->nama_unit }}</td>
                            <td>{{ optional(optional($item->assessment)->indikator)->kode_indikator }}</td>
                            <td>
                                <span class="badge badge-success">{{ $item->skor_diperoleh }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop

@section('css')
    <style>
        #radarChart {
            width: 100% !important;
            height: 420px !important;
        }

        #pieChart {
            width: 100% !important;
            height: 420px !important;
        }

        .progress {
            height: 22px;
        }

        .progress-bar {
            font-weight: bold;
            font-size: 12px;
        }

        .small-box h3 {
            font-size: 32px;
            font-weight: bold;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        /* ======================================================
        | Radar Chart
        ====================================================== */
        const radarCtx = document.getElementById('radarChart');

        if (radarCtx) {
            new Chart(radarCtx, {
                type: 'radar',
                data: {
                    labels: @json($chartRadar['labels'] ?? []),
                    datasets: [{
                        label: 'Persentase Assessment',
                        data: @json($chartRadar['data'] ?? []),
                        fill: true,
                        backgroundColor: 'rgba(40,167,69,0.2)',
                        borderColor: '#28a745',
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#ffffff',
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#28a745',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    }
                }
            });
        }

        /* ======================================================
        | Doughnut Chart
        ====================================================== */
        const pieCtx = document.getElementById('pieChart');

        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($chartPie['labels'] ?? []),
                    datasets: [{
                        data: @json($chartPie['data'] ?? []),
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#FFC107',
                            '#F44336',
                            '#9C27B0',
                            '#009688',
                            '#795548',
                            '#607D8B'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
@stop