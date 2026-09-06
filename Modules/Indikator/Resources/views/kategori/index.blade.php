@extends('adminlte::page')
@section('title', 'Kategori')
@section('content_header')
    <h1 class="m-0 text-dark">Kategori</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kategori</h3>
                    <div class="card-tools">
                        <a href="{{ route('kategori.create') }}" class="btn btn-primary btn-sm">Tambah Kategori</a>
                    </div>    
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">No</th>
                                <th>Nama</th>
                                <th>Kode Kategori</th>
                                <th>Skor Maksimal</th>
                                <th style="width: 150px" align="center">Aksi</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kategoris as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_kategori }}</td>
                                <td>{{ $item->kode_kategori }}</td>
                                <td>{{ $item->skor_maksimal }}</td>
                                <td style="width: 150px" align="center">
                                    <a href="{{ route('kategori.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">Hapus</button>
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