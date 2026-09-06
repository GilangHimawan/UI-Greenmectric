@extends('adminlte::page')
@section('title', 'Indikator')
@section('content_header')
    <h1 class="m-0 text-dark">Indikator</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Indikator</h3>
                    <div class="card-tools">
                        <a href="{{ route('indikator.create') }}" class="btn btn-primary btn-sm">Tambah Indikator</a>
                    </div>    
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">No</th>
                                <th>Kode Indikator</th>
                                <th>Pertanyaan</th>
                                <th>Poin Maksimal</th>
                                <th>wajib file</th>
                                <th>Status</th>
                                <th style="width: 150px" align="center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($indikators as $indikator)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $indikator->kode_indikator }}</td>
                                    <td>{{ $indikator->pertanyaan }}</td>
                                    <td>{{ $indikator->poin_maksimal }}</td>
                                    <td>{{ $indikator->wajib_file ? 'Ya' : 'Tidak' }}</td>
                                    <td>{{ $indikator->status }}</td>
                                    <td align="center">
                                        <a href="{{ route('indikator.edit', $indikator->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('indikator.destroy', $indikator->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin Menonaktifkan indikator ini?')">Nonaktifkan</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
