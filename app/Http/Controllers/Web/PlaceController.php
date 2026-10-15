<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $this->perPage($request);

        $places = Place::with(['distrito', 'municipio', 'freguesia'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('formatted_address', 'like', '%' . $request->q . '%')))
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->distrito_id))
            ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
            ->when($request->filled('freguesia_id'), fn ($q) => $q->where('freguesia_id', $request->freguesia_id))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $distritos = Distrito::orderBy('nome')->pluck('nome', 'id');
        $municipios = Municipio::query()
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->distrito_id))
            ->orderBy('nome')
            ->pluck('nome', 'id');
        $freguesias = Freguesia::query()
            ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
            ->orderBy('nome')
            ->pluck('nome', 'id');

        return view('places.index', compact('places', 'distritos', 'municipios', 'freguesias'));
    }

    public function edit(Place $place)
    {
        return view('places.edit', compact('place'));
    }

    public function update(Request $request, Place $place)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'formatted_address' => ['nullable', 'string', 'max:255'],
            'lat'               => ['nullable', 'numeric', 'between:-90,90'],
            'lng'               => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $place->update($data);

        return redirect()->route('places.index')
            ->with('success', __('technical.flash.place.updated'));
    }

    public function destroy(Place $place)
    {
        $place->delete();

        return redirect()->route('places.index')
            ->with('success', __('technical.flash.place.deleted'));
    }
}
