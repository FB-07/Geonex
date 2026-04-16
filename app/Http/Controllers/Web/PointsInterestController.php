<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Controller;
use App\Models\Pontos_Interesse;
use App\Models\Place;
use App\Models\SearchHistory;
use App\Jobs\ProcessPlaces;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PointsInterestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int)$request->input('resultsPerPage'));
        }

        $perPage = max((int)$request->session()->get('resultsPerPage', 10), 1);
        $userId  = (int) auth()->user()->gfuser_id;

        $groupsPage = Pontos_Interesse::query()
            ->leftJoin('distritos as d', 'd.id', '=', 'pontos_interesse.distrito_id')
            ->leftJoin('municipios as m', 'm.id', '=', 'pontos_interesse.municipio_id')
            ->leftJoin('freguesias as f', 'f.id', '=', 'pontos_interesse.freguesia_id')
            ->where('pontos_interesse.gfuser_id', $userId)
            ->select(
                'pontos_interesse.id',
                'pontos_interesse.nome',
                'pontos_interesse.distrito_id',
                'pontos_interesse.municipio_id',
                'pontos_interesse.freguesia_id',
                'pontos_interesse.icon',
                'pontos_interesse.image',
                'd.nome as distrito_nome',
                'm.nome as municipio_nome',
                'f.nome as freguesia_nome'
            )
            ->groupBy(
                'pontos_interesse.id',
                'pontos_interesse.nome',
                'pontos_interesse.distrito_id',
                'pontos_interesse.municipio_id',
                'pontos_interesse.freguesia_id',
                'd.nome',
                'm.nome',
                'f.nome'
            )
            ->orderBy('pontos_interesse.nome')
            ->paginate($perPage);

        $allPlaces = DB::connection('mysql2')
            ->table('ponto_interesse_place as pip')
            ->join('places as p', 'p.place_id', '=', 'pip.place_id')
            ->join('pontos_interesse as pi', 'pi.id', '=', 'pip.ponto_interesse_id')
            ->where('pi.gfuser_id', $userId)
            ->select('pi.nome', 'p.*', 'pi.icon')
            ->get()
            ->groupBy('nome');

        return view('interest.index', [
            'groupsPage' => $groupsPage,
            'placesByGroup' => $allPlaces,
        ]);
    }

    public function create()
    {
        $icons = [
            'location',
            'map',
            'cart',
            'fast-food',
            'storefront',
            'restaurant',
            'cafe',
            'beer',
            'football',
            'bed',
            'car',
            'bus',
            'train',
            'bicycle',
            'walk',
            'camera',
            'barbell',
            'information-circle',
            'help-circle',
            'star',
            'flag',
            'globe',
            'trail-sign',
            'ticket',
            'phone-portrait',
            'pricetag',
            'hammer',
            'boat',
            'shield',
            'paw',
            'heart',
            'skull',
            'albums',
            'american-football',
            'rocket',
            'attach',
            'book',
            'brush',
            'calendar',
            'call',
            'build',
            'bug',
            'bowling-ball',
            'eye',
            'man',
            'woman',
            'leaf',
            'musical-notes',
            'pin',
            'ribbon',
        ];

        return view('interest.form-pesquisa', compact('icons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'      => 'required|string|max:255',
            'color'     => 'required|string|size:7',
            'pesquisa'  => 'required|string|max:255',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
            'icon'      => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return redirect()->route('interest.create')
                ->withErrors(['nome' => 'Utilizador não autenticado.']);
        }

        $userId = (int)$user->gfuser_id;
        $conn = DB::connection('mysql2');

        $distritoId = !empty($data['distrito'])
            ? $conn->table('distritos')->where('nome', $data['distrito'])->value('id')
            : null;

        $municipioId = !empty($data['municipio'])
            ? $conn->table('municipios')->where('nome', $data['municipio'])->value('id')
            : null;

        $freguesiaId = !empty($data['freguesia'])
            ? $conn->table('freguesias')->where('nome', $data['freguesia'])->value('id')
            : null;

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->storeAs('pontos_interesse', time() . '_' . $request->file('image')->getClientOriginalName(), 'public')
            : null;

        $poi = Pontos_Interesse::updateOrCreate(
            [
                'gfuser_id' => $userId,
                'nome'      => $data['nome'],
            ],
            [
                'color'        => $data['color'],
                'icon'         => $data['icon'],
                'image'        => $imagePath,
                'distrito_id'  => $distritoId,
                'municipio_id' => $municipioId,
                'freguesia_id' => $freguesiaId,
            ]
        );

        $existingPlaces = Place::query()
            ->when($distritoId, fn($q) => $q->where('distrito_id', $distritoId))
            ->when($municipioId, fn($q) => $q->where('municipio_id', $municipioId))
            ->when($freguesiaId, fn($q) => $q->where('freguesia_id', $freguesiaId))
            ->where('name', 'LIKE', '%' . $data['pesquisa'] . '%')
            ->get();

        if ($existingPlaces->isNotEmpty()) {
            foreach ($existingPlaces as $place) {
                DB::connection('mysql2')->table('ponto_interesse_place')->updateOrInsert([
                    'ponto_interesse_id' => $poi->id,
                    'place_id' => $place->place_id,
                ]);

                SearchHistory::create([
                    'gfuser_id' => $poi->gfuser_id,
                    'place_id' => $place->place_id,
                    'query' => $data['pesquisa'],
                ]);
            }
        } else {
            ProcessPlaces::dispatch(
                $poi->id,
                $data['pesquisa'],
                $distritoId,
                $municipioId,
                $freguesiaId
            );
        }

        return redirect()->route('interest.index')
            ->with('success', 'Ponto de Interesse criado com sucesso.');
    }

    public function edit($nome)
    {
        $userId = (int) auth()->user()->gfuser_id;

        $icons = [
            'location',
            'map',
            'cart',
            'fast-food',
            'storefront',
            'restaurant',
            'cafe',
            'beer',
            'football',
            'bed',
            'car',
            'bus',
            'train',
            'bicycle',
            'walk',
            'camera',
            'barbell',
            'information-circle',
            'help-circle',
            'star',
            'flag',
            'globe',
            'trail-sign',
            'ticket',
            'phone-portrait',
            'pricetag',
            'hammer',
            'boat',
            'shield',
            'paw',
            'heart',
            'skull',
            'albums',
            'american-football',
            'rocket',
            'attach',
            'book',
            'brush',
            'calendar',
            'call',
            'build',
            'bug',
            'bowling-ball',
            'eye',
            'man',
            'woman',
            'leaf',
            'musical-notes',
            'pin',
            'ribbon',
        ];

        $poi = Pontos_Interesse::where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();

        if (!$poi) {
            return redirect()->route('interest.index')
                ->withErrors(['nome' => 'POI não encontrado.']);
        }

        return view('interest.edit', ['poi' => $poi, 'icons' => $icons]);
    }

    public function update(Request $request, $nome)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'color' => 'nullable|string|size:7',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $userId = (int) auth()->user()->gfuser_id;

        $poi = Pontos_Interesse::where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();

        if (!$poi) {
            return redirect()->route('interest.index')
                ->withErrors(['nome' => 'POI não encontrado.']);
        }

        if ($request->hasFile('image')) {
            if ($poi->image) {
                Storage::disk('public')->delete($poi->image);
            }

            $path = $request->file('image')->store('poi', 'public');
            $poi->image = $path;
        }

        $poi->update([
            'nome' => $request->input('nome'),
            'color' => $request->input('color'),
            'icon' => $request->input('icon'),
            'image' => $poi->image
        ]);

        return redirect()->route('interest.index')
            ->with('success', 'POI atualizado com sucesso.');
    }

    public function addPlacesForm($nome)
    {
        $userId = auth()->user()->gfuser_id;
        $conn = DB::connection('mysql2');

        $poi = Pontos_Interesse::where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->firstOrFail();

        $distritos  = $conn->table('distritos')->pluck('nome', 'id');
        $municipios = $conn->table('municipios')->pluck('nome', 'id');
        $freguesias = $conn->table('freguesias')->pluck('nome', 'id');

        return view('interest.add-places', compact('poi', 'distritos', 'municipios', 'freguesias'));
    }

    public function addPlaces(Request $request, $nome)
    {
        $data = $request->validate([
            'pesquisa'  => 'required|string|max:255',
            'distrito'  => 'nullable|integer',
            'municipio' => 'nullable|integer',
            'freguesia' => 'nullable|integer',
        ]);

        $userId = auth()->user()->gfuser_id;

        $poi = Pontos_Interesse::where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->firstOrFail();

        $conn = DB::connection('mysql2');

        $existingPlaces = Place::query()
            ->when($data['distrito'] ?? null, fn($q) => $q->where('distrito_id', $data['distrito'] ?? null))
            ->when($data['municipio'] ?? null, fn($q) => $q->where('municipio_id', $data['municipio'] ?? null))
            ->when($data['freguesia'] ?? null, fn($q) => $q->where('freguesia_id', $data['freguesia'] ?? null))
            ->where('name', 'LIKE', '%' . $data['pesquisa'] . '%')
            ->get();

        if ($existingPlaces->isNotEmpty()) {
            foreach ($existingPlaces as $place) {
                DB::connection('mysql2')->table('ponto_interesse_place')->updateOrInsert([
                    'ponto_interesse_id' => $poi->id,
                    'place_id' => $place->place_id,
                ]);

                SearchHistory::create([
                    'gfuser_id' => $userId,
                    'place_id' => $place->place_id,
                    'query' => $data['pesquisa'],
                ]);
            }
        } else {
            ProcessPlaces::dispatch(
                $poi->id,
                $data['pesquisa'],
                $data['distrito'] ?? null,
                $data['municipio'] ?? null,
                $data['freguesia'] ?? null
            );
        }

        return redirect()->route('interest.index')
            ->with('success', 'Locais adicionados com sucesso.');
    }

    public function detachPlace(Request $request, $poiId)
    {
        $userId = auth()->user()->gfuser_id;

        $poi = Pontos_Interesse::where('id', $poiId)
            ->where('gfuser_id', $userId)
            ->firstOrFail();

        $places = $request->input('places', []);

        if (!empty($places)) {
            $poi->places()->detach($places);
        }

        return redirect()->back()->with('success', 'Locais removidos com sucesso.');
    }

    public function destroy($nome)
    {
        $userId = (int) auth()->user()->gfuser_id;

        $poi = Pontos_Interesse::where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();

        if ($poi) {
            DB::connection('mysql2')->table('ponto_interesse_place')
                ->where('ponto_interesse_id', $poi->id)
                ->delete();
            $poi->delete();
        }

        return redirect()->route('interest.index')
            ->with('success', 'POI apagado com sucesso.');
    }

    public function names(Request $request)
    {
        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['names' => []], 200);
        }

        $userId = (int) $user->gfuser_id;

        $names = DB::connection('mysql2')
            ->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->selectRaw('MIN(nome) as nome')
            ->groupBy(DB::raw('LOWER(nome)'))
            ->orderByRaw('MIN(nome) ASC')
            ->pluck('nome')
            ->values();

        return response()->json(['names' => $names], 200);
    }

    public function placesByName(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'distrito'  => 'nullable|string|max:120',
            'municipio' => 'nullable|string|max:120',
            'freguesia' => 'nullable|string|max:160',
        ]);

        $user = auth()->user();
        if (!$user || !isset($user->gfuser_id)) {
            return response()->json(['places' => []], 200);
        }

        $userId = (int) $user->gfuser_id;

        $q = DB::connection('mysql2')
            ->table('ponto_interesse_place as pip')
            ->join('places as p', 'p.place_id', '=', 'pip.place_id')
            ->join('pontos_interesse as pi', 'pi.id', '=', 'pip.ponto_interesse_id')
            ->where('pi.gfuser_id', $userId)
            ->whereRaw('LOWER(pi.nome) = ?', [mb_strtolower($data['name'])])
            ->select(
                'p.place_id',
                'p.name',
                'p.formatted_address',
                'p.lat',
                'p.lng',
                'p.types',
                'pi.color',
                'pi.icon',
                'pi.image'
            );

        if (!empty($data['distrito']))  $q->where('pi.distrito_id', $data['distrito']);
        if (!empty($data['municipio'])) $q->where('pi.municipio_id', $data['municipio']);
        if (!empty($data['freguesia'])) $q->where('pi.freguesia_id', $data['freguesia']);

        $places = $q->get()->map(function ($p) {
            if ($p->image) {
                $p->image = asset('storage/' . $p->image);
            }
            return $p;
        });

        return response()->json(['places' => $places], 200);
    }

    public function check(Request $request)
    {
        $userId = auth()->id();
        $nome = $request->input('nome');

        $q = DB::connection('mysql2')->table('pontos_interesse')
            ->where('gfuser_id', $userId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->exists();

        return response()->json(['exists' => $q]);
    }

    private function extractCpDigits(?string $formattedAddress): ?string
    {
        if (!$formattedAddress) return null;
        if (!preg_match('/\b(\d{4})\s*[-\s]\s*(\d{3})\b/u', $formattedAddress, $m)) return null;
        return $m[1] . $m[2];
    }

    private function cpToHierarchy($conn, string $cpDigits): ?object
    {
        return $conn->table('codigos_postais as cp')
            ->join('freguesias as f', 'f.id', '=', 'cp.freguesia_id')
            ->join('municipios as m', 'm.id', '=', 'f.municipio_id')
            ->join('distritos as d', 'd.id', '=', 'm.distrito_id')
            ->where('cp.codigo', $cpDigits)
            ->select(['f.id as freguesia_id', 'm.id as municipio_id', 'd.id as distrito_id'])
            ->first();
    }
}
