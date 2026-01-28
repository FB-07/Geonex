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
            'type' => ['required'],
            'salut' => ['max:32'],
            'speciality' => ['max:128'],
            'place' => ['max:128'],
            'clientid' => ['max:255'],
            'doctortype' => ['required', 'max:1']
        ]);

        User::where('gfuser_id', Auth::user()->gfuser_id)
            ->update([
                'gfuser_active' => $attributes['active'],
                'gfuser_alias' => $attributes['username'],
                'gfuser_fullname' => $attributes['fullname'],
                'gfuser_email' => $attributes['email'],
                'gfuser_sex' => $attributes['sex'],
                'gfuser_type' => $attributes['type'],
                'gfuser_salut' => $attributes['salut'],
                'gfuser_speciality' => $attributes['speciality'],
                'gfuser_place' => $attributes['place'],
                'gfuser_clientid' => $attributes['clientid'],
                'gfuser_doctortype' => $attributes['doctortype']
            ]);

        return redirect('/profile')->with('success', 'Profile updated successfully');
    }
}
