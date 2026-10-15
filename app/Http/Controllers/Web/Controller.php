<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function perPage(Request $request): int
    {
        if ($request->filled('resultsPerPage')) {
            $request->session()->put('resultsPerPage', (int) $request->input('resultsPerPage'));
        }

        return max((int) $request->session()->get('resultsPerPage', 10), 1);
    }
}
