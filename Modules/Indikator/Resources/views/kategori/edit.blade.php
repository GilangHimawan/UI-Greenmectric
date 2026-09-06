@extends('adminlte::page')
@section('title', 'Edit Kategori')
@section('content_header')
    <h1 class="m-0 text-dark">Edit Kategori</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Kategori</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('kategori.update', $kategori->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" value="{{ $kategori->nama_kategori }}" required>
                        </div>
                        <div class="form-group">
                            <label for="kode_kategori">Kode Kategori</label>
                            <input type="text" name="kode_kategori" id="kode_kategori" class="form-control" value="{{ $kategori->kode_kategori }}" required>
                        </div>
                        <div class="form-group">
                            <label for="skor_maksimal">Skor Maksimal</label>
                            <input type="number" name="skor_maksimal" id="skor_maksimal" class="form-control" value="{{ $kategori->skor_maksimal }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
