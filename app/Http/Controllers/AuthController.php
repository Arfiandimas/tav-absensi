<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
 
            if (
                $request->email === env('ADMIN_EMAIL') &&
                $request->password === env('ADMIN_PASSWORD')
            ) {
                session(['is_logged_in' => true]);
                return redirect('/');
            }

            return redirect()->route('login')->with(['status'=> 'error', 'message'=> 'Email atau password salah.']);
        } catch (\Throwable $th) {
            abort(400);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/');
    }
}
