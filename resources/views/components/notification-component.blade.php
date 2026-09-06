{{--
    Komponen notifikasi global (toast SweetAlert2).
    Komponen ini otomatis menampilkan notifikasi sukses / gagal / peringatan / info
    untuk SETIAP halaman yang memakai layout adminlte::page, tanpa perlu di-include manual.

    Mendukung berbagai nama key session flash yang dipakai di berbagai controller
    pada project ini (agar semua konsisten tampil sebagai toast), yaitu:
      - Sukses   : success, success_message, sukses
      - Gagal    : error, error_message, gagal
      - Peringatan: warning, warning_message, peringatan
      - Info     : info, info_message, informasi
    Serta menampilkan error validasi (message bag $errors) sebagai toast gagal.
--}}
@php
    $__collect = function (array $keys) {
        $out = [];
        foreach ($keys as $key) {
            $val = session($key);
            if (is_null($val)) {
                continue;
            }
            if (is_iterable($val)) {
                foreach ($val as $v) {
                    $out[] = (string) $v;
                }
            } else {
                $out[] = (string) $val;
            }
        }
        return $out;
    };

    $__successMsgs = $__collect(['success', 'success_message', 'sukses']);
    $__errorMsgs   = $__collect(['error', 'error_message', 'gagal']);
    $__warningMsgs = $__collect(['warning', 'warning_message', 'peringatan']);
    $__infoMsgs    = $__collect(['info', 'info_message', 'informasi']);

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $__validationError) {
            $__errorMsgs[] = $__validationError;
        }
    }
@endphp

@push('js')
    <script>
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });

        function toast_show(icon, message) {
            Toast.fire({
                icon: icon,
                title: message
            });
        }

        function success_message(message) { toast_show('success', message); }
        function info_message(message)    { toast_show('info', message); }
        function error_message(message)   { toast_show('error', message); }
        function warning_message(message) { toast_show('warning', message); }

        // Menangkap error JavaScript yang tidak tertangani di sisi client,
        // supaya pengguna tetap mendapat notifikasi bila ada yang gagal berjalan.
        window.addEventListener('error', function (event) {
            error_message('Terjadi kesalahan pada halaman. Silakan muat ulang atau hubungi admin jika berlanjut.');
            console.error('Uncaught error:', event.error || event.message);
        });
        window.addEventListener('unhandledrejection', function (event) {
            error_message('Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.');
            console.error('Unhandled promise rejection:', event.reason);
        });
    </script>

    @foreach ($__successMsgs as $m)
        <script>success_message('{{ addslashes($m) }}');</script>
    @endforeach

    @foreach ($__errorMsgs as $m)
        <script>error_message('{{ addslashes($m) }}');</script>
    @endforeach

    @foreach ($__warningMsgs as $m)
        <script>warning_message('{{ addslashes($m) }}');</script>
    @endforeach

    @foreach ($__infoMsgs as $m)
        <script>info_message('{{ addslashes($m) }}');</script>
    @endforeach
@endpush
