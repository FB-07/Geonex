<?php

namespace App\Http\Controllers\Web;

use App\Models\Distrito;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistritoController extends Controller
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

        $distritos = Distrito::query()
            ->when($request->filled('q'), fn ($q) => $q->where('nome', 'like', '%' . $request->q . '%'))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('distritos.index', compact('distritos'));
    }

    public function edit(Distrito $distrito)
    {
        return view('distritos.edit', compact('distrito'));
    }

    public function update(Request $request, Distrito $distrito)
    {
        $data = $request->validate([
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('distritos', 'nome')->ignore($distrito->id),
            ],
        ]);

        $distrito->update($data);

        return redirect()->route('distritos.index')
            ->with('success', 'Distrito atualizado com sucesso.');
    }

    public function destroy(Distrito $distrito)
    {
        $distrito->delete();

        return redirect()->route('distritos.index')
            ->with('success', 'Distrito apagado com sucesso.');
    }
}
