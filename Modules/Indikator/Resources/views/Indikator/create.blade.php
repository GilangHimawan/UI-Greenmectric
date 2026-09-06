@extends('adminlte::page')

@section('title', 'Tambah Indikator')

@section('content_header')
    <h1 class="m-0 text-dark">Tambah Indikator</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('indikator.store') }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Informasi Dasar</h5>
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategori</label>

                        <select
                            name="kategori_id"
                            id="kategori_id"
                            class="form-control"
                            required>

                            <option value="">
                                -- Pilih Kategori --
                            </option>

                            @foreach($kategoris as $kategori)
                                <option
                                    value="{{ $kategori->id }}"
                                    data-kode="{{ $kategori->kode_kategori }}">
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kode Indikator</label>

                        <input
                            type="text"
                            name="kode_indikator"
                            id="kode_indikator"
                            class="form-control"
                            >
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label>Pertanyaan / Deskripsi Indikator</label>

                        <textarea
                            name="pertanyaan"
                            class="form-control"
                            rows="4"
                            required></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Poin Maksimal</label>

                        <input
                            type="number"
                            name="poin_maksimal"
                            class="form-control"
                            min="0"
                            >
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Urutan Tampil</label>

                        <input
                            type="number"
                            name="urutan"
                            class="form-control"
                            min="1"
                            value="1"
                            >
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Tipe Jawaban</h5>
        </div>

        <div class="card-body">

            <div class="form-group">
                <label>Tipe Jawaban</label>

                <select
                    name="tipe_jawaban"
                    id="tipe_jawaban"
                    class="form-control">

                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="isian">Isian (teks bebas)</option>
                    <option value="angka">Angka</option>

                </select>
            </div>

            <div id="opsi-section">

                <div id="opsi-wrapper">

                    <div class="row opsi-item mb-2">

                        <div class="col-md-8">
                            <input
                                type="text"
                                name="opsi_label[]"
                                class="form-control"
                                placeholder="Label Pilihan">
                        </div>

                        <div class="col-md-3">
                            <input
                                type="number"
                                name="opsi_nilai[]"
                                class="form-control"
                                placeholder="Nilai Skor">
                        </div>

                        <div class="col-md-1">
                            <button
                                type="button"
                                class="btn btn-danger hapus-opsi">
                                ×
                            </button>
                        </div>

                    </div>

                </div>

                <button
                    type="button"
                    id="tambah-opsi"
                    class="btn btn-success">
                    Tambah Opsi Jawaban
                </button>

            </div>

        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Pengaturan Tambahan</h5>
        </div>

        <div class="card-body">

            <div class="form-check mb-3">

                <input
                    type="checkbox"
                    name="wajib_file"
                    value="1"
                    class="form-check-input"
                    id="wajib_file">

                <label
                    class="form-check-label"
                    for="wajib_file">

                    Wajib Upload Bukti Dokumen

                </label>

            </div>

        </div>
    </div>

    <div class="mt-3 mb-4">

        <a
            href="{{ route('indikator.index') }}"
            class="btn btn-secondary">

            Kembali

        </a>

        <button
            type="submit"
            class="btn btn-primary">

            Simpan Indikator

        </button>

    </div>

</form>

@stop

@section('js')

<script>

document.getElementById('kategori_id')
.addEventListener('change', function () {

    let selected = this.options[this.selectedIndex];
    let kode = selected.dataset.kode ?? '';

    document.getElementById('kode_indikator').value = kode + ' ';
});

function toggleOpsiSection() {
    let tipe = document.getElementById('tipe_jawaban').value;
    let section = document.getElementById('opsi-section');
    section.style.display = (tipe === 'pilihan_ganda') ? 'block' : 'none';
}

document.getElementById('tipe_jawaban')
    .addEventListener('change', toggleOpsiSection);

toggleOpsiSection();

document.getElementById('tambah-opsi')
.addEventListener('click', function() {

    let html = `
        <div class="row opsi-item mb-2">

            <div class="col-md-8">
                <input
                    type="text"
                    name="opsi_label[]"
                    class="form-control"
                    placeholder="Label Pilihan">
            </div>

            <div class="col-md-3">
                <input
                    type="number"
                    name="opsi_nilai[]"
                    class="form-control"
                    placeholder="Nilai Skor">
            </div>

            <div class="col-md-1">
                <button
                    type="button"
                    class="btn btn-danger hapus-opsi">
                    ×
                </button>
            </div>

        </div>
    `;

    document
        .getElementById('opsi-wrapper')
        .insertAdjacentHTML('beforeend', html);
});

document.addEventListener('click', function(e){

    if(e.target.classList.contains('hapus-opsi')) {

        e.target.closest('.opsi-item').remove();

    }

});

</script>

@stop