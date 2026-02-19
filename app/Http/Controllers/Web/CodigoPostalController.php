<?php

namespace App\Http\Controllers\Web;

use App\Models\CodigoPostal;
use App\Models\Freguesia;
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

        $cps = CodigoPostal::orderBy('codigo')
            ->paginate($perPage);

        return view('cp.index', compact('cps'));
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
