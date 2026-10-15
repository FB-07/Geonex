<?php

namespace App\Http\Controllers\Web;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use Illuminate\Http\Request;

class FreguesiaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $this->perPage($request);

        $freguesias = Freguesia::with(['municipio.distrito'])
            ->when($request->filled('q'), fn ($q) => $q->where('nome', 'like', '%' . $request->q . '%'))
            ->when($request->filled('distrito_id'), fn ($q) => $q->whereHas('municipio', fn ($m) => $m->where('distrito_id', $request->distrito_id)))
            ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $distritos = Distrito::orderBy('nome')->pluck('nome', 'id');
        $municipios = Municipio::query()
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->distrito_id))
            ->orderBy('nome')
            ->pluck('nome', 'id');

        return view('freguesias.index', compact('freguesias', 'distritos', 'municipios'));
    }

    public function edit(Freguesia $freguesia)
    {
        $municipios = Municipio::with('distrito')->orderBy('nome')->get();
        return view('freguesias.edit', compact('freguesia', 'municipios'));
    }

    public function update(Request $request, Freguesia $freguesia)
    {
        $data = $request->validate([
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'nome'         => ['required', 'string', 'max:255'],
        ]);

        $exists = Freguesia::where('municipio_id', $data['municipio_id'])
            ->where('nome', $data['nome'])
            ->where('id', '!=', $freguesia->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nome' => __('technical.flash.freguesia.duplicate')])
                ->withInput();
        }

        $freguesia->update($data);

        return redirect()->route('freguesias.index')
            ->with('success', __('technical.flash.freguesia.updated'));
    }

    public function destroy(Freguesia $freguesia)
    {
        if ($freguesia->codigosPostais()->exists()) {
            return back()->with('error', __('technical.flash.freguesia.inUse'));
        }

        $freguesia->delete();

        return redirect()->route('freguesias.index')
            ->with('success', __('technical.flash.freguesia.deleted'));
    }
}
