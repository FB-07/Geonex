<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Services\MD5Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }

        $resultsPerPage = max((int) $request->session()->get('resultsPerPage', 10), 1);

        $users = DB::table('users')->paginate($resultsPerPage);
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
            'active' => ['required', 'in:0,1'],
            'username' => ['required', 'unique:App\Models\User,gfuser_alias', 'max:25'],
            'fullname' => ['required', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8', 'max:64'],
            'email' => ['required', 'email', 'unique:App\Models\User,gfuser_email', 'max:200'],
            'sex' => ['required', 'in:M,F'],
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

        return redirect('/users')->with('success', __('users.created'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return view('users/form', ['user' => $user]);
    }

    public function update(string $id)
    {
        $attributes = request()->validate([
            'active' => ['required', 'in:0,1'],
            'username' => ['required', 'max:25', Rule::unique('users', 'gfuser_alias')->ignore($id, 'gfuser_id')],
            'fullname' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:200', Rule::unique('users', 'gfuser_email')->ignore($id, 'gfuser_id')],
            'sex' => ['required', 'in:M,F'],
            'password' => ['nullable', 'confirmed', 'min:8', 'max:64'],
        ]);

        $user = User::findOrFail($id);


        $user->update([
            'gfuser_active' => $attributes['active'],
            'gfuser_alias' => $attributes['username'],
            'gfuser_fullname' => $attributes['fullname'],
            'gfuser_email' => $attributes['email'],
            'gfuser_sex' => $attributes['sex'],
        ]);

        if (! empty($attributes['password'])) {
            $user->update(['gfuser_password' => (new MD5Hasher())->make($attributes['password'])]);
        }

        return redirect('/users')->with('success', __('users.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ((string) Auth::id() === $id) {
            return back()->with('error', __('users.cannotDeleteSelf'));
        }

        User::findOrFail($id)->delete();

        return redirect('/users')->with('success', __('users.deleted'));
    }
}