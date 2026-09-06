@extends('adminlte::page')
@section('title', 'Tambah Unit')
@section('content_header')
    <h1 class="m-0 text-dark">Tambah Unit</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Unit</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('unit.store') }}" method="POST">
                        @csrf
                        <div class="form-group
">
                            <label for="kode_unit">Kode Unit</label>
                            <input type="text" name="kode_unit" id="kode_unit" class="form-control @error('kode_unit') is-invalid @enderror" value="{{ old('kode_unit') }}" required>
                            @error('kode_unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama_unit">Nama Unit</label>
                            <input type="text" name="nama_unit" id="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" value="{{ old('nama_unit') }}" required>
                            @error('nama_unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="kategori_id">Kategori</label>

                            <select name="kategori_id"
                                    id="kategori_id"
                                    class="form-control @error('kategori_id') is-invalid @enderror"
                                    required>

                                <option value="">Pilih Kategori</option>

                                @foreach($kategoris as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('kategori_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->kode_kategori }} - {{ $item->nama }}
                                    </option>
                                @endforeach

                            </select>

                            @error('kategori_id')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('unit.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop