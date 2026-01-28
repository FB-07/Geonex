<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;


class AuthController extends Controller
{
    public function create()
    {
        return view('auth.create');
    }

    public function changeLang($langcode)
    {
        App::setLocale($langcode);
        session()->put("lang_code", $langcode);
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'gfuser_alias' => $request->username,
            'password' => $request->password
        ];

        // $remember = $request->filled('remember-me');

        // if (Auth::attempt($credentials, $remember)) {
        if (Auth::attempt($credentials)) {

            return redirect()->intended('map')->with(['success' => trans('auth.loginSuccess')]);
        }

        return redirect()->back()->with('error', trans('auth.invalidPassword'));
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/login')->with(['success' => trans('auth.logoutSuccess')]);
    }
}