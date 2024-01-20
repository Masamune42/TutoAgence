<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\String\b;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function doLogin(LoginRequest $request)
    {
        $credentials = $request->validated();
        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route(('admin.property.index')));
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects'
        ])->onlyInput('email');
    }

    public function logout()
    {
        Auth::logout();
        return to_route('login')->with('success', 'Vous êtes déconnecté');
    }
}
