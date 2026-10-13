<?php

namespace App\Http\Controllers\Web;

use App\Models\CodigoPostal;
use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CodigoPostalController extends Controller
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

        $cps = CodigoPostal::with('freguesia.municipio.distrito')
            ->when($request->filled('q'), fn ($q) => $q->where('codigo', 'like', '%' . $request->q . '%'))
            ->when($request->filled('distrito_id'), fn ($q) => $q->whereHas('freguesia.municipio', fn ($m) => $m->where('distrito_id', $request->distrito_id)))
            ->when($request->filled('municipio_id'), fn ($q) => $q->whereHas('freguesia', fn ($f) => $f->where('municipio_id', $request->municipio_id)))
            ->when($request->filled('freguesia_id'), fn ($q) => $q->where('freguesia_id', $request->freguesia_id))
            ->orderBy('codigo')
            ->paginate($perPage)
            ->withQueryString();

        $distritos = Distrito::orderBy('nome')->pluck('nome', 'id');
        $municipios = Municipio::query()
            ->when($request->filled('distrito_id'), fn ($q) => $q->where('distrito_id', $request->distrito_id))
            ->orderBy('nome')
            ->pluck('nome', 'id');
        $freguesias = Freguesia::query()
            ->when($request->filled('municipio_id'), fn ($q) => $q->where('municipio_id', $request->municipio_id))
            ->when(! $request->filled('municipio_id') && $request->filled('distrito_id'), fn ($q) => $q->whereHas('municipio', fn ($m) => $m->where('distrito_id', $request->distrito_id)))
            ->orderBy('nome')
            ->pluck('nome', 'id');

        return view('cp.index', compact('cps', 'distritos', 'municipios', 'freguesias'));
    }

    public function edit(CodigoPostal $codigo_postal)
    {
        $freguesias = Freguesia::with('municipio.distrito')->orderBy('nome')->get();
        return view('cp.edit', compact('codigo_postal', 'freguesias'));
    }

    public function update(Request $request, CodigoPostal $codigo_postal)
    {
        $data = $request->validate([
            'codigo' => [
                'required', 'string', 'max:16',
                Rule::unique('codigos_postais', 'codigo')->ignore($codigo_postal->codigo, 'codigo'),
            ],
            'freguesia_id' => ['required', 'integer', 'exists:freguesias,id'],
        ]);

        $codigo_postal->update($data);

        return redirect()->route('codigos-postais.index')
            ->with('success', 'Código postal atualizado com sucesso.');
    }

    public function destroy(CodigoPostal $codigo_postal)
    {
        $codigo_postal->delete();

        return redirect()->route('codigos-postais.index')
            ->with('success', 'Código postal apagado com sucesso.');
    }
}
