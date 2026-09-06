{{-- Info periode & unit --}}
<div class="card card-outline card-primary mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Periode</strong><br>
                @if ($periode)
                    {{ $periode->tahun }}
                    @if ($periode->status === 'aktif')
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($periode->status) }}</span>
                    @endif
                @else
                    <span class="text-muted">Belum ada periode</span>
                @endif
            </div>
            <div class="col-md-4">
                <strong>Unit</strong><br>
                {{ optional($unitPemilik)->nama_unit ?? '-' }}
                ({{ optional($unitPemilik)->kode_unit ?? '-' }})
            </div>
            <div class="col-md-4">
                <strong>Kategori</strong><br>
                {{ $kategori->nama_kategori }} ({{ $kategori->kode_kategori }})
            </div>
        </div>
    </div>
</div>

{{-- Banner Mode Referensi: melihat kategori milik unit lain --}}
@if ($userKategori && !$milikSendiri)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Mode Referensi.</strong>
        Kategori ini hanya dapat dilihat.
        Jawaban dapat diblok dan disalin menggunakan Ctrl+C.
        Anda tidak memiliki hak mengubah data kategori ini.
    </div>
@endif

{{-- Banner: user tidak terhubung ke unit manapun --}}
@if (!$userKategori)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Mode Lihat Saja.</strong>
        Akun Anda belum terhubung ke unit manapun, sehingga hanya dapat
        melihat data assessment seluruh kategori tanpa bisa mengubahnya.
    </div>
@endif

{{-- Banner Terkunci: kategori milik sendiri tapi sudah submitted --}}
@if ($milikSendiri && $submitted)
    <div class="alert alert-warning">
        <i class="fas fa-lock"></i>
        <strong>Assessment sudah disubmit.</strong>
        Data tidak dapat diubah lagi untuk periode ini.
    </div>
@endif

{{-- Banner: kategori sendiri tapi periode sedang tidak aktif --}}
@if ($milikSendiri && !$submitted && $readonly)
    <div class="alert alert-secondary">
        <i class="fas fa-hourglass-half"></i>
        <strong>Periode assessment sedang tidak aktif.</strong>
        Pengisian hanya bisa dilakukan saat periode berstatus aktif.
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terdapat kesalahan pada isian Anda:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Indikator - {{ $kategori->nama_kategori }}</h3>

        @if (!$readonly && ($bisaReload ?? false))
            <a
                href="{{ route('assessment.reload-previous', ['kategori' => $kategori->id]) }}"
                class="btn btn-outline-secondary btn-sm"
                title="Muat jawaban dari assessment periode sebelumnya sebagai bahan awal pengisian. Anda tetap bisa mengubahnya sebelum menyimpan."
            >
                <i class="fas fa-history"></i> Reload Jawaban Assessment Sebelumnya
            </a>
        @endif
    </div>

    <form action="{{ route('assessment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card-body" @if($readonly) style="user-select:text;" @endif>
            @forelse ($kategori->indikators as $i => $indikator)

                @php
                    $jawabanLama = $jawabanExisting->get($indikator->id);
                @endphp

                <div class="border rounded p-3 mb-3">
                    <label for="jawaban_{{ $indikator->id }}">
                        <strong>{{ $indikator->kode_indikator }}.</strong>
                        {{ $indikator->pertanyaan }}
                        @if (!$readonly)
                            <span class="text-danger">*</span>
                        @endif
                        <span class="badge badge-secondary">Maks. {{ $indikator->poin_maksimal }} poin</span>
                        @if (!empty($jawabanLama->dari_reload))
                            <span class="badge badge-info">
                                <i class="fas fa-history"></i> Dimuat dari assessment sebelumnya
                            </span>
                        @endif
                    </label>

                    {{-- Tipe: Pilihan Ganda --}}
                    @if ($indikator->tipe_jawaban === 'pilihan_ganda')
                        <div class="mt-2">

                            @foreach($indikator->opsiJawaban as $opsi)

                                <div class="custom-control custom-radio mb-2">

                                    <input
                                        type="radio"
                                        class="custom-control-input @error('jawaban.'.$indikator->id) is-invalid @enderror"
                                        id="opsi_{{ $indikator->id }}_{{ $opsi->id }}"
                                        name="jawaban[{{ $indikator->id }}]"
                                        value="{{ $opsi->id }}"
                                        {{ old('jawaban.'.$indikator->id, optional($jawabanLama)->jawaban) == $opsi->id ? 'checked' : '' }}
                                        {{ $readonly ? 'disabled' : 'required' }}
                                    >

                                    <label
                                        class="custom-control-label w-100"
                                        for="opsi_{{ $indikator->id }}_{{ $opsi->id }}"
                                        style="user-select:text;"
                                    >
                                        <div class="d-flex justify-content-between">

                                            <span>[{{ $opsi->urutan }}] {{ $opsi->label }}</span>

                                            <span class="badge badge-primary">
                                                {{ $opsi->nilai_skor }} poin
                                            </span>

                                        </div>
                                    </label>

                                </div>

                            @endforeach

                        </div>

                        @error('jawaban.'.$indikator->id)
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                    {{-- Tipe: Angka --}}
                    @elseif ($indikator->tipe_jawaban === 'angka')
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="jawaban[{{ $indikator->id }}]"
                            id="jawaban_{{ $indikator->id }}"
                            class="form-control @error('jawaban.'.$indikator->id) is-invalid @enderror"
                            value="{{ old('jawaban.'.$indikator->id, optional($jawabanLama)->jawaban) }}"
                            {{ $readonly ? 'readonly' : 'required' }}
                        >

                    {{-- Tipe: Isian bebas --}}
                    @else
                        <textarea
                            name="jawaban[{{ $indikator->id }}]"
                            id="jawaban_{{ $indikator->id }}"
                            class="form-control @error('jawaban.'.$indikator->id) is-invalid @enderror"
                            rows="2"
                            {{ $readonly ? 'readonly' : 'required' }}
                        >{{ old('jawaban.'.$indikator->id, optional($jawabanLama)->jawaban) }}</textarea>
                    @endif

                    @error('jawaban.' . $indikator->id)
                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                    @enderror

                    {{-- Upload file / link bukti: hanya tampil saat bisa diedit --}}
                    @if ($indikator->wajib_file)
                        <div class="mt-2">

                            @if (!$readonly)

                                <div class="row">
                                    <div class="col-md-7">
                                        <label for="file_{{ $indikator->id }}" class="mb-1">
                                            File bukti dukung
                                            @if (!optional($jawabanLama)->path_file && !optional($jawabanLama)->link_bukti)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <input
                                            type="file"
                                            name="file[{{ $indikator->id }}]"
                                            id="file_{{ $indikator->id }}"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx"
                                            class="form-control @error('file.'.$indikator->id) is-invalid @enderror"
                                        >
                                        <small class="form-text text-muted">
                                            Format: pdf, doc, docx, xls, xlsx. Maks. 2 MB.
                                        </small>
                                        @error('file.' . $indikator->id)
                                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-5">
                                        <label for="link_{{ $indikator->id }}" class="mb-1">
                                            Atau link bukti (jika file terlalu besar)
                                        </label>
                                        <input
                                            type="url"
                                            name="link[{{ $indikator->id }}]"
                                            id="link_{{ $indikator->id }}"
                                            placeholder="https://drive.google.com/..."
                                            value="{{ old('link.'.$indikator->id, optional($jawabanLama)->link_bukti) }}"
                                            class="form-control @error('link.'.$indikator->id) is-invalid @enderror"
                                        >
                                        @error('link.' . $indikator->id)
                                            <span class="text-danger d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            @else
                                <label class="mb-1 d-block">File / Link bukti dukung</label>
                            @endif

                            @if (optional($jawabanLama)->path_file)
                                <div class="mt-1">
                                    <i class="fas fa-paperclip"></i>
                                    File tersimpan:
                                    <a href="{{ Storage::url($jawabanLama->path_file) }}" target="_blank" style="user-select:text;">
                                        {{ $jawabanLama->nama_file_asli }}
                                    </a>
                                    @if (!$readonly)
                                        <small class="text-muted">(unggah file baru untuk menggantinya)</small>
                                    @endif
                                </div>

                                @if (!empty($jawabanLama->dari_reload) && !$readonly)
                                    {{-- File bawaan dari periode sebelumnya: dipakai saat disimpan
                                         KECUALI pengguna mengunggah file baru untuk indikator ini. --}}
                                    <input type="hidden" name="carry_file[{{ $indikator->id }}]" value="{{ $jawabanLama->path_file }}">
                                    <input type="hidden" name="carry_file_nama[{{ $indikator->id }}]" value="{{ $jawabanLama->nama_file_asli }}">
                                @endif
                            @endif

                            @if (optional($jawabanLama)->link_bukti)
                                <div class="mt-1">
                                    <i class="fas fa-link"></i>
                                    Link tersimpan:
                                    <a href="{{ $jawabanLama->link_bukti }}" target="_blank" rel="noopener" style="user-select:text;">
                                        {{ $jawabanLama->link_bukti }}
                                    </a>
                                </div>
                            @endif

                            @if ($readonly && !optional($jawabanLama)->path_file && !optional($jawabanLama)->link_bukti)
                                <div class="mt-1 text-muted">
                                    <i class="fas fa-times-circle"></i>
                                    Belum ada file/link yang diunggah.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            @empty
                <div class="alert alert-warning mb-0">
                    Belum ada indikator aktif untuk kategori ini.
                </div>
            @endforelse
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('assessment.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div>
                @if ($milikSendiri && $submitted)
                    <a href="{{ route('assessment.export', ['kategori' => $kategori->id]) }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Download Laporan (Excel)
                    </a>
                @endif

                @if (!$readonly && $kategori->indikators->isNotEmpty())
                    <button type="submit" name="aksi" value="draft" class="btn btn-outline-primary">
                        <i class="fas fa-save"></i> Simpan Draft
                    </button>
                    <button
                        type="submit"
                        name="aksi"
                        value="submit"
                        class="btn btn-primary"
                        onclick="return confirm('Setelah disubmit, jawaban tidak dapat diubah lagi. Lanjutkan?')"
                    >
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>