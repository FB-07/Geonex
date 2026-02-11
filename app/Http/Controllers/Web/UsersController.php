<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Services\MD5Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }

        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);

        $users = DB::table('gfuser')->paginate($resultsPerPage);
        return view('users/index', [
            'users' => $users,
            'user' => $user
        ]);
    }

    public function show(string $id)
    {
        // $user = User::findOrFail($id);
        // return view('users/show', ['user' => $user]);
    }

    public function create()
    {
        return view('users/form');
    }

    public function store()
    {
        $attributes = request()->validate([
            'active' => ['required'],
            'username' => ['required', 'unique:App\Models\User,gfuser_alias', 'max:25'],
            'fullname' => ['required', 'max:255'],
            'password' => ['required', 'max:64'],
            'password_confirmation' => ['required', 'max:64'],
            'email' => ['required', 'email', 'unique:App\Models\User,gfuser_email', 'max:200'],
            'sex' => ['required'],
        ]);

        $hasher = new MD5Hasher();

        User::create([
            'gfuser_active' => $attributes['active'],
            'gfuser_alias' => $attributes['username'],
            'gfuser_fullname' => $attributes['fullname'],
            'gfuser_password' => $hasher->make($attributes['password']),
            'gfuser_sex' => $attributes['sex'],
            'gfuser_email' => $attributes['email'],
        ]);

        return redirect('/users')->with('success', 'Profile created successfully');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return view('users/form', ['user' => $user]);
    }

    public function update(string $id)
    {
        $attributes = request()->validate([
            'active' => ['required'],
            'username' => ['required', 'max:25'],
            'fullname' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:200'],
            'sex' => ['required'],
        ]);

        $user = User::findOrFail($id);


        $user->update([
            'gfuser_active' => $attributes['active'],
            'gfuser_alias' => $attributes['username'],
            'gfuser_fullname' => $attributes['fullname'],
            'gfuser_email' => $attributes['email'],
            'gfuser_sex' => $attributes['sex'],
        ]);

        //ddd($attributes);

        return redirect('/users')->with('success', 'Profile updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();

        return redirect('/users')->with('success', 'Profile deleted successfully');
    }
}