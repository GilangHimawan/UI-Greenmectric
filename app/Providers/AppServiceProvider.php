<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Beberapa controller pada project ini memanggil ->withSuccess() / ->withError() /
        // ->withWarning() / ->withInfo() pada RedirectResponse. Method tersebut TIDAK ada
        // secara bawaan di Laravel, sehingga sebelumnya menyebabkan fatal error
        // "Call to undefined method" setiap kali dipanggil (mis. saat membuat/mengubah/
        // menghapus user & permission). Macro di bawah ini menambahkan method tersebut
        // sekaligus menaruh pesannya ke session dengan key yang dikenali oleh komponen
        // notifikasi global (resources/views/components/notification-component.blade.php).
        RedirectResponse::macro('withSuccess', function ($message) {
            /** @var RedirectResponse $this */
            return $this->with('success', $message);
        });

        RedirectResponse::macro('withError', function ($message) {
            /** @var RedirectResponse $this */
            return $this->with('error', $message);
        });

        RedirectResponse::macro('withWarning', function ($message) {
            /** @var RedirectResponse $this */
            return $this->with('warning', $message);
        });

        RedirectResponse::macro('withInfo', function ($message) {
            /** @var RedirectResponse $this */
            return $this->with('info', $message);
        });
    }
}