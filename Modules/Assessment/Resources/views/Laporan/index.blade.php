@extends('adminlte::page')

@section('title','Laporan Assessment')

@section('content_header')
<h1>Laporan Hasil Assessment</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">

        <div class="row">

            <div class="col-md-4">

                <label>Periode Assessment</label>

                <select class="form-control" id="periode">

                    @foreach($periodes as $item)

                        <option value="{{ $item->id }}"
                            {{ $periode->id == $item->id ? 'selected' : '' }}>

                            {{ $item->tahun }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-4">

                <label>Unit</label>

                <select class="form-control" id="unit">

                    <option value="">Semua Unit</option>

                    @foreach($units as $item)

                        <option value="{{ $item->id }}"
                            {{ $unitId == $item->id ? 'selected' : '' }}>

                            {{ $item->nama_unit }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-4 d-flex align-items-end">

                <button
                    id="btnCari"
                    class="btn btn-primary mr-2">

                    <i class="fas fa-search"></i>
                    Tampilkan

                </button>

                <a
                    id="previewPdf"
                    target="_blank"
                    class="btn btn-info mr-2">

                    <i class="fas fa-eye"></i>
                    Preview PDF

                </a>

                <a
                    id="downloadPdf"
                    class="btn btn-danger">

                    <i class="fas fa-file-pdf"></i>
                    Download PDF

                </a>

            </div>

        </div>

    </div>

</div>


<div class="row">

    <div class="col-md-7">

        <div class="card">

            <div class="card-header">

                <h5>Informasi Assessment</h5>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th width="180">

                            Nama Kampus

                        </th>

                        <td>

                            Politeknik Negeri Banyuwangi

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Periode

                        </th>

                        <td>

                            {{ $periode->tahun }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Tanggal Cetak

                        </th>

                        <td>

                            {{ now()->translatedFormat('d F Y') }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>


        <div class="card">

            <div class="card-header">

                <h5>Rekap Nilai per Kategori</h5>

            </div>

            <div class="card-body p-0">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">

                        <tr>

                            <th>Kategori</th>

                            <th width="160">Nilai</th>

                            <th width="180">Progress</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($laporan as $item)

                        <tr>

                            <td>

                                {{ $item['kategori'] }}

                            </td>

                            <td>

                                <strong>

                                    {{ $item['total'] }}

                                </strong>

                                /

                                {{ $item['maksimal'] }}

                            </td>

                            <td>

                                <div class="progress">

                                    <div class="progress-bar bg-success"

                                        style="width:{{ $item['persentase'] }}%">

                                        {{ $item['persentase'] }}%

                                    </div>

                                </div>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr class="table-success">

                            <th>

                                Grand Total

                            </th>

                            <th>

                                {{ $grandTotal }}

                                /

                                {{ $grandMaksimal }}

                            </th>

                            <th>

                                {{ $overall }} %

                            </th>

                        </tr>

                    </tfoot>

                </table>

            </div>

        </div>

    </div>


    <div class="col-md-5">

        <div class="card">

            <div class="card-header">

                <h5>Grafik Assessment</h5>

            </div>

            <div class="card-body">

                <canvas id="radarChart"></canvas>

            </div>

        </div>

    </div>

</div>


@if($unitId)

<div class="card">

    <div class="card-header">

        <h5>

            Detail Jawaban Assessment

        </h5>

    </div>

    <div class="card-body table-responsive">

        <table class="table table-bordered table-striped table-hover">

            <thead>

                <tr>

                    <th>No</th>

                    <th>Kategori</th>

                    <th>Kode</th>

                    <th>Indikator</th>

                    <th>Jawaban</th>

                    <th>Nilai</th>

                    <th>Maksimal</th>

                    <th>Persentase</th>

                </tr>

            </thead>

            <tbody>

                @foreach($detailJawaban as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $item['kategori'] }}</td>

                    <td>{{ $item['kode'] }}</td>

                    <td>{{ $item['indikator'] }}</td>

                    <td>{{ $item['jawaban'] }}</td>

                    <td class="text-center">

                        {{ $item['nilai'] }}

                    </td>

                    <td class="text-center">

                        {{ $item['maksimal'] }}

                    </td>

                    <td class="text-center">

                        {{ round(($item['nilai']/$item['maksimal'])*100,2) }}%

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

@endif

@stop


@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

function getQuery(){

    let periode=document.getElementById('periode').value;

    let unit=document.getElementById('unit').value;

    return '?periode='+periode+'&unit='+unit;

}

document.getElementById('btnCari').onclick=function(){

    window.location="{{ route('laporan.index') }}"+getQuery();

};

document.getElementById('previewPdf').onclick=function(){

    this.href="{{ route('laporan.preview') }}"+getQuery();

};

document.getElementById('downloadPdf').onclick=function(){

    this.href="{{ route('laporan.download') }}"+getQuery();

};

new Chart(document.getElementById('radarChart'),{

    type:'radar',

    data:{

        labels:[
            @foreach($laporan as $item)
                "{{ $item['kategori'] }}",
            @endforeach
        ],

        datasets:[{

            label:'Persentase',

            data:[
                @foreach($laporan as $item)
                    {{ $item['persentase'] }},
                @endforeach
            ],

            fill:true,

            borderWidth:2

        }]

    },

    options:{

        responsive:true,

        scales:{

            r:{

                beginAtZero:true,

                max:100

            }

        }

    }

});

</script>

@stop