<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TecnicalController extends Controller
{
    public function area()
    {
        return view('tecnical.index');
    }
}