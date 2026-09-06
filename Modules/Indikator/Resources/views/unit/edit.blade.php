@extends('adminlte::page')
@section('title', 'Edit Unit')
@section('content_header')
    <h1 class="m-0 text-dark">Edit Unit</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Unit</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('unit.update', $unit->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="kode_unit">Kode Unit</label>
                            <input type="text" name="kode_unit" id="kode_unit" class="form-control @error('kode_unit') is-invalid @enderror" value="{{ old('kode_unit', $unit->kode_unit) }}" required>
                            @error('kode_unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="nama_unit">Nama Unit</label>
                            <input type="text" name="nama_unit" id="nama_unit" class="form-control @error('nama_unit') is-invalid @enderror" value="{{ old('nama_unit', $unit->nama_unit) }}" required>
                            @error('nama_unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="kategori_id">Kategori</label>
                            <select name="kategori_id" id="kategori_id" class="form-control @error('kategori_id') is-invalid @enderror" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id', $unit->kategori_id) == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <span class="invalid-feedback" role="alert">
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