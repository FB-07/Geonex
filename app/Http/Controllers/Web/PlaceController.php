<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceController extends Controller
{
    private function perPage(Request $request): int
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }

        return max((int) $request->session()->get('resultsPerPage', 10), 1);
    }

    public function index(Request $request)
    {
        $perPage = $this->perPage($request);

        $places = Place::orderBy('id')
            ->paginate($perPage);

        return view('places.index', compact('places'));
    }

    public function edit(Place $place)
    {
        return view('places.edit', compact('place'));
    }

    public function update(Request $request, Place $place)
    {
        $data = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('places', 'nome')->ignore($place->id),
            ],
        ]);

        $place->update($data);

        return redirect()->route('places.index')
            ->with('success', 'Place atualizado com sucesso.');
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return redirect()->route('places.index')
            ->with('success', 'Place apagado com sucesso.');
    }
}
