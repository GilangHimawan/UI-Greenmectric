<?php

namespace App\Http\Controllers\Autentikasi;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * Display login page.
     * 
     * @return Renderable
     */
    public function show()
    {
        return view('autentikasi.login');
    }

    /**
     * Handle account login request
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->getCredentials();

            if(!Auth::validate($credentials)):
                return redirect()->to('login')
                    ->with('error', 'Username atau password salah.');
            endif;

            $user = Auth::getProvider()->retrieveByCredentials($credentials);

            Auth::login($user);


            DB::table('sessions')
                ->where('user_id', Auth::user()->id)
                ->where('id', '!=', Session::getId())->delete();


            return $this->authenticated($request, $user);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->to('login')
                ->with('error', 'Gagal login. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Handle response after user authenticated
     * 
     * @param Request $request
     * @param Auth $user
     * 
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, $user) 
    {
        return redirect()->intended();
    }
}