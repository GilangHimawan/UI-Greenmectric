@extends('adminlte::page')
@section('title', 'Unit')
@section('content_header')
    <h1 class="m-0 text-dark">Unit</h1>
@stop
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('unit.create') }}" class="btn btn-primary mb-2">
                        Tambah Unit
                    </a>
                    <table class="table table-hover table-bordered table-stripped" id="unit-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Unit</th>
                                <th>Kode Unit</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($units as $unit)
                                <tr>
                                    <td>{{ $unit->id }}</td>
                                    <td>{{ $unit->nama_unit }}</td>
                                    <td>{{ $unit->kode_unit }}</td>
                                    <td>{{ $unit->kategori->nama_kategori }}</td>
                                    <td>
                                        <a href="{{ route('unit.edit', $unit->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('unit.destroy', $unit->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus unit ini?')">Hapus</button>
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
    <script>
        $(function() {
            $('#unit-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('unit.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'nama_unit', name: 'nama_unit' },
                    { data: 'kode_unit', name: 'kode_unit' },
                    { data: 'kategori.nama_kategori', name: 'kategori.nama_kategori' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endsection
