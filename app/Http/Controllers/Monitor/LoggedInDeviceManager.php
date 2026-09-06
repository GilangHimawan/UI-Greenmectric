<?php

namespace App\Http\Controllers\Monitor;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class LoggedInDeviceManager extends Controller
{
    /**
     * Display a listing of the currently logged in devices.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $devices = \DB::table('sessions')
            ->where('user_id', \Auth::user()->id)
            ->get()->reverse();

        return view('logged-in-devices.list')
                ->with('devices', $devices)
                ->with('current_session_id', \Session::getId());
    }


    /**
     * Logout a session based on session id.
     *
     * @return \Illuminate\Http\Response
     */
    public function logoutDevice(Request $request, $device_id)
    {
        try {
            \DB::table('sessions')
                ->where('id', $device_id)->delete();

            return redirect('/logged-in-devices')->with('success', 'Perangkat berhasil di-logout.');
        } catch (\Throwable $e) {
            report($e);
            return redirect('/logged-in-devices')->with('error', 'Gagal logout perangkat. Silakan coba lagi.');
        }
    }



    /**
     * Logouts a user from all other devices except the current one.
     *
     * @return \Illuminate\Http\Response
     */
    public function logoutAllDevices(Request $request)
    {
        try {
            \DB::table('sessions')
                ->where('user_id', \Auth::user()->id)
                ->where('id', '!=', \Session::getId())->delete();

            return redirect('/logged-in-devices')->with('success', 'Semua perangkat lain berhasil di-logout.');
        } catch (\Throwable $e) {
            report($e);
            return redirect('/logged-in-devices')->with('error', 'Gagal logout semua perangkat. Silakan coba lagi.');
        }
    }

}
