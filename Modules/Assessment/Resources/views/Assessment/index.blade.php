@extends('adminlte::page')

@section('title', 'Assessment')

@section('content_header')
    <h1 class="m-0 text-dark">Assessment</h1>
@stop

@section('content')

<div class="row">
    <div class="col-lg-12">

        <div class="card shadow-sm">

            {{-- Header --}}
            <div class="card-header d-flex justify-content-between align-items-center">

                <div>
                    <strong>Periode :</strong>

                    @if($periode)
                        {{ $periode->tahun }}
                        <span class="badge badge-success ml-1">Aktif</span>
                    @else
                        <span class="text-muted">Belum ada periode aktif</span>
                    @endif
                </div>

                @can('periode.store')

                    @if(!$periode)

                        <button class="btn btn-success btn-sm"
                                data-toggle="modal"
                                data-target="#modalPeriode">

                            <i class="fas fa-play"></i>
                            Mulai Periode

                        </button>

                    @else
                        @can('periode.end')
                            <form action="{{ route('periode.end',$periode->id) }}"
                                method="POST"
                                onsubmit="return confirm('Tutup periode ini?')">

                                @csrf
                                @method('PATCH')

                                <button class="btn btn-danger btn-sm">

                                    <i class="fas fa-stop"></i>
                                    Tutup Periode

                                </button>

                            </form>
                        @endcan
                    @endif

                @endcan

            </div>

            <div class="card-body">

                {{-- Progress --}}
                <div class="border rounded p-3 bg-light mb-4">

                    <div class="d-flex justify-content-between">

                        <strong>Progress Assessment</strong>

                        <strong>{{ $persentase ?? 0 }}%</strong>

                    </div>
                    <div class="progress mt-2" style="height: 15px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $persentase ?? 0 }}%"
                             aria-valuenow="{{ $persentase ?? 0 }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>

                </div>

                {{-- Tidak ada periode --}}
                @if(!$periode)

                    <div class="text-center py-5">

                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>

                        <h5>Belum ada periode assessment.</h5>

                    </div>

                {{-- Tidak ada kategori --}}
                @elseif($kategoris->isEmpty())

                    <div class="text-center py-5">

                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>

                        <h5>Belum ada kategori.</h5>

                    </div>

                @else

                    {{-- TAB KATEGORI --}}
                    <ul class="nav nav-pills mb-3">

                        @foreach($kategoris as $kategori)

                            <li class="nav-item">

                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   data-toggle="pill"
                                   href="#kategori{{ $kategori->id }}">

                                    {{ $kategori->kode_kategori }}

                                </a>

                            </li>

                        @endforeach

                    </ul>

                    {{-- Isi Tab --}}
                    <div class="tab-content">

                        @foreach($kategoris as $kategori)

                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                 id="kategori{{ $kategori->id }}">

                                <h4 class="mb-3">

                                    {{ $kategori->nama_kategori }}

                                    <small class="text-muted">

                                        ({{ $kategori->kode_kategori }})

                                    </small>

                                </h4>

                                <div class="table-responsive">

                                    <table class="table table-bordered table-hover">

                                        <thead class="bg-light">

                                            <tr>

                                                <th width="50">No</th>
                                                <th>Pertanyaan</th>
                                                <th width="120">Tipe</th>
                                                <th width="120">Status</th>
                                                <th width="170">Terakhir Diubah</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            @forelse($kategori->indikators as $indikator)

                                                @php
                                                    $jawaban = $jawabanExisting[$indikator->id] ?? null;
                                                @endphp

                                                <tr>

                                                    <td>{{ $loop->iteration }}</td>

                                                    <td>{{ $indikator->pertanyaan }}</td>

                                                    <td>

                                                        @switch($indikator->tipe_jawaban)

                                                            @case('pilihan_ganda')
                                                                <span class="badge badge-primary">
                                                                    Pilihan Ganda
                                                                </span>
                                                                @break

                                                            @case('angka')
                                                                <span class="badge badge-success">
                                                                    Angka
                                                                </span>
                                                                @break

                                                            @case('isian')
                                                                <span class="badge badge-secondary">
                                                                    Isian
                                                                </span>
                                                                @break

                                                        @endswitch

                                                    </td>

                                                    <td>

                                                        @if($jawaban)

                                                            <span class="badge badge-success">
                                                                Sudah Diisi
                                                            </span>

                                                        @else

                                                            <span class="badge badge-secondary">
                                                                Belum Diisi
                                                            </span>

                                                        @endif

                                                    </td>

                                                    <td>

                                                        @if($jawaban)

                                                            {{ \Carbon\Carbon::parse($jawaban->diubah_pada)->format('d-m-Y H:i') }}

                                                        @else

                                                            -

                                                        @endif

                                                    </td>

                                                </tr>

                                            @empty

                                                <tr>

                                                    <td colspan="5" class="text-center">

                                                        Tidak ada indikator.

                                                    </td>

                                                </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        @endforeach

                    </div>

                        @if($periode)
                            <div class="mt-4 text-right">
                                <button class="btn btn-success" onclick="mulaiAssessment()">
                                    <i class="fas fa-play-circle"></i>
                                    Mulai Assessment
                                </button>
                            </div>
                        @endif

                    @section('js')
                    <script>
                    function mulaiAssessment(){
                        if(confirm('Apakah Anda yakin ingin memulai Assessment pada periode ini?')){
                            window.location.href = "{{ route('assessment.create') }}";
                        }
                    }
                    </script>
                    @endsection


                @endif

            </div>

        </div>

    </div>
</div>

{{-- Modal --}}
@can('periode.index')
<div class="modal fade" id="modalPeriode" tabindex="-1" role="dialog" aria-labelledby="modalPeriodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">

            <form action="{{ route('periode.store') }}" method="POST">
                @csrf

                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="modalPeriodeLabel">
                        <i class="fas fa-calendar-plus"></i>
                        Buat Periode Assessment
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        <h6>
                            <i class="fas fa-info-circle"></i>
                            Informasi
                        </h6>

                        <ul class="mb-0">
                            <li>Periode assessment akan dibuat.</li>
                            <li>Seluruh indikator yang berstatus <b>Aktif</b> akan dijadikan template assessment.</li>
                            <li>Perubahan indikator setelah periode dimulai tidak akan mempengaruhi periode ini.</li>
                            <li>Pastikan seluruh indikator telah diperiksa sebelum melanjutkan.</li>
                        </ul>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jumlah Kategori</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $jumlahKategori }}"
                                       readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jumlah Indikator Aktif</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $jumlahIndikator }}"
                                       readonly>
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Tahun Assessment</label>

                        <input type="number"
                               class="form-control"
                               name="tahun"
                               value="{{ date('Y') }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>

                        <textarea name="keterangan"
                                  rows="3"
                                  class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>
@endcan

@endsection