<?php

namespace App\Http\Controllers\Web;

use App\Models\Freguesia;
use App\Models\Municipio;
use Illuminate\Http\Request;

class FreguesiaController extends Controller
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

        $freguesias = Freguesia::orderBy('id')
            ->paginate($perPage);

        return view('freguesias.index', compact('freguesias'));
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
            return back()->withErrors(['nome' => 'Já existe uma freguesia com esse nome nesse município.'])
                ->withInput();
        }

        $freguesia->update($data);

        return redirect()->route('freguesias.index')
            ->with('success', 'Freguesia atualizada com sucesso.');
    }

    public function destroy(Freguesia $freguesia)
    {
        $freguesia->delete();

        return redirect()->route('freguesias.index')
            ->with('success', 'Freguesia apagada com sucesso.');
    }
}
