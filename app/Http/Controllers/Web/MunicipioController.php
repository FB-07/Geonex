<?php

namespace App\Http\Controllers\Web;

use App\Models\Municipio;
use App\Models\Distrito;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $this->perPage($request);

        $municipios = Municipio::with(['distrito'])
            ->when($request->filled('q'), fn ($q) => $q->where('nome', 'like', '%' . $request->q . '%'))
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->distrito_id))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $distritos = Distrito::orderBy('nome')->pluck('nome', 'id');

        return view('municipios.index', compact('municipios', 'distritos'));
    }

    public function edit(Municipio $municipio)
    {
        $distritos = Distrito::orderBy('nome')->get();
        return view('municipios.edit', compact('municipio', 'distritos'));
    }

    public function update(Request $request, Municipio $municipio)
    {
        $data = $request->validate([
            'distrito_id' => ['required', 'integer', 'exists:distritos,id'],
            'nome'        => ['required', 'string', 'max:255'],
        ]);

        $exists = Municipio::where('distrito_id', $data['distrito_id'])
            ->where('nome', $data['nome'])
            ->where('id', '!=', $municipio->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => __('technical.flash.municipio.duplicate')])
                ->withInput();
        }

        $municipio->update($data);

        return redirect()->route('municipios.index')
            ->with('success', __('technical.flash.municipio.updated'));
    }

    public function destroy(Municipio $municipio)
    {
        if ($municipio->freguesias()->exists()) {
            return back()->with('error', __('technical.flash.municipio.inUse'));
        }

        $municipio->delete();

        return redirect()->route('municipios.index')
            ->with('success', __('technical.flash.municipio.deleted'));
    }
}
