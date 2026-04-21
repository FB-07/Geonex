<?php

namespace App\Http\Controllers\Web;

use App\Models\Municipio;
use App\Models\Distrito;
use Illuminate\Http\Request;

class MunicipioController extends Controller
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

        $municipios = Municipio::orderBy('id')
            ->paginate($perPage);

        return view('municipios.index', compact('municipios'));
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
            return back()->withErrors(['nome' => 'Já existe um município com esse nome neste distrito.'])
                ->withInput();
        }

        $municipio->update($data);

        return redirect()->route('municipios.index')
            ->with('success', 'Município atualizado com sucesso.');
    }

    public function destroy(Municipio $municipio)
    {
        $municipio->delete();

        return redirect()->route('municipios.index')
            ->with('success', 'Município apagado com sucesso.');
    }
}
