<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('users/show', ['user' => $user]);
    }

    public function edit()
    {
        $user = Auth::user();
        return view('users/form', ['user' => $user]);
    }

    public function update()
    {
        $attributes = request()->validate([
            'active' => ['required'],
            'username' => ['required', 'max:25'],
            'fullname' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:200'],
            'sex' => ['required'],
        ]);

        User::where('gfuser_id', Auth::user()->gfuser_id)
            ->update([
                'gfuser_active' => $attributes['active'],
                'gfuser_alias' => $attributes['username'],
                'gfuser_fullname' => $attributes['fullname'],
                'gfuser_email' => $attributes['email'],
                'gfuser_sex' => $attributes['sex'],
            ]);

        return redirect('/profile')->with('success', 'Profile updated successfully');
    }
}
