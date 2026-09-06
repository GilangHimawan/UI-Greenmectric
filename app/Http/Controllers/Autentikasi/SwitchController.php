<?php

namespace App\Http\Controllers\Autentikasi;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Core\User;

class SwitchController extends Controller
{
    
    /**
     * Handle switching role
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function perform(Request $request)
    {
		//dd($request);
        $request->validate([
            'role' => 'required',
        ]);

        try {
            auth()->user()->role_aktif = $request->role;

            if (auth()->user()->save()) {
                return redirect()->route('dashboard.index')->with('success', 'Berhasil beralih peran');
            }
            return redirect()->route('dashboard.index')->with('warning', 'Gagal beralih peran');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('dashboard.index')->with('error', 'Gagal beralih peran. Silakan coba lagi.');
        }
    }

}