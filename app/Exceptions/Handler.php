<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * Akses ditolak KARENA TIDAK PUNYA IZIN (middleware permission / role)
     * dialihkan otomatis:
     * - sudah login -> dashboard
     * - belum login -> halaman login
     *
     * Ini SENGAJA dibatasi hanya untuk exception permission, bukan semua
     * response 403. abort(403, '...') yang dipakai untuk aturan bisnis
     * (mis. "assessment sudah disubmit") harus tetap mengembalikan 403
     * apa adanya, bukan dialihkan diam-diam.
     */
    public function render($request, Throwable $e)
    {
        $tidakPunyaIzin = $e instanceof AuthorizationException
            || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException;

        if ($tidakPunyaIzin && !$request->expectsJson()) {

            if (auth()->check()) {
                return redirect()
                    ->route('dashboard.index')
                    ->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            }

            return redirect()->route('login.show');
        }

        return parent::render($request, $e);
    }
}