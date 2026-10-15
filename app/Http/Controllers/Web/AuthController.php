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
        abort_unless(in_array($langcode, ['en', 'pt'], true), 404);

        App::setLocale($langcode);
        session()->put("lang_code", $langcode);
        return redirect()->back();
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'gfuser_alias' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (app('hash')->needsRehash($user->gfuser_password)) {
                $user->forceFill(['gfuser_password' => app('hash')->make($request->password)])->save();
            }

            return redirect()->intended('map')
                ->with('success', trans('auth.loginSuccess'));
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', trans('auth.invalidPassword'));
    }
    
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with(['success' => trans('auth.logoutSuccess')]);
    }
}