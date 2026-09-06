@extends('adminlte::page')

@section('title', 'Isi Penilaian')

@section('content_header')
    <h1 class="m-0 text-dark">Isi Penilaian Assessment</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">

        {{-- Switcher Kategori: semua kategori bisa dibuka siapa saja.
             Tombol dibuat besar & menampilkan nama lengkap (bukan cuma kode).
             Pindah kategori TIDAK reload halaman — murni toggle display via JS,
             karena semua kategori sudah dirender sekaligus di bawah. --}}
        <div class="card card-outline card-secondary mb-3">
            <div class="card-body">
                <div class="row" id="kategori-switcher">
                    @foreach ($kategoriList as $kat)
                        <div class="col-md-4 col-lg-3 mb-3"><a href="?kategori={{ $kat->id }}" data-kategori-id="{{ $kat->id }}" class="kategori-tab d-block p-3 rounded text-decoration-none h-100 {{ (int)$currentKategori === $kat->id ? 'btn-kategori-active' : 'btn-kategori-inactive' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="h4 mb-1 font-weight-bold">{{ $kat->kode_kategori }}</span>
                                @if ((int)$userKategori === $kat->id)
                                    <i class="fas fa-user-check" title="Kategori Anda"></i>
                                @endif
                            </div>
                            <div style="font-size: 0.95rem; line-height: 1.3;">
                                {{ $kat->nama_kategori }}
                            </div>
                        </a></div>
                    @endforeach
                </div>
            </div>
        </div>

     
        @foreach ($kategoriList as $kat)
            @php $d = $dataPerKategori[$kat->id]; @endphp

            <div
                class="konten-kategori"
                data-kategori-id="{{ $kat->id }}"
                style="{{ (int)$currentKategori === $kat->id ? '' : 'display:none' }}"
            >
                @include('assessment::Assessment._konten', [
                    'periode'         => $periode,
                    'kategori'        => $d['kategori'],
                    'unitPemilik'     => $d['unitPemilik'],
                    'jawabanExisting' => $d['jawabanExisting'],
                    'readonly'        => $d['readonly'],
                    'submitted'       => $d['submitted'],
                    'milikSendiri'    => $d['milikSendiri'],
                    'bisaReload'      => $d['bisaReload'],
                    'userKategori'    => $userKategori,
                ])
            </div>
        @endforeach

    </div>
</div>
@stop

@push('css')
<style>
    .btn-kategori-inactive {
        border: 2px solid #dee2e6;
        color: #495057;
        background-color: #fff;
        transition: all .15s ease-in-out;
    }
    .btn-kategori-inactive:hover {
        border-color: #007bff;
        background-color: #f4f8ff;
        color: #007bff;
    }
    .btn-kategori-active {
        border: 2px solid #007bff;
        background-color: #007bff;
        color: #fff;
    }
</style>
@endpush

@push('js')
<script>
(function () {
    var switcherEl = document.getElementById('kategori-switcher');

    function tampilkanKategori(id) {
        document.querySelectorAll('.konten-kategori').forEach(function (el) {
            el.style.display = (el.dataset.kategoriId === String(id)) ? '' : 'none';
        });

        switcherEl.querySelectorAll('.kategori-tab').forEach(function (tab) {
            var aktif = tab.dataset.kategoriId === String(id);
            tab.classList.toggle('btn-kategori-active', aktif);
            tab.classList.toggle('btn-kategori-inactive', !aktif);
        });
    }

    switcherEl.addEventListener('click', function (e) {
        var tab = e.target.closest('.kategori-tab');
        if (!tab) return;

        e.preventDefault();

        var id = tab.dataset.kategoriId;
        tampilkanKategori(id);

        // Update URL supaya bisa di-bookmark/refresh, tanpa memicu reload.
        window.history.pushState({}, '', tab.getAttribute('href'));
    });

    // Dukung tombol back/forward browser.
    window.addEventListener('popstate', function () {
        var params = new URLSearchParams(window.location.search);
        tampilkanKategori(params.get('kategori') || '{{ $currentKategori }}');
    });
})();
</script>
@endpush