<?php

namespace App\Http\Controllers;

use App\Services\ServerMetrics;

class ServidorController extends Controller
{
    // Inyección de dependencias: Laravel crea el Service y te lo pasa solo
    public function __construct(private ServerMetrics $metrics) {}

    public function index()
    {
        return view('servidor', ['m' => $this->metrics->snapshot()]);
    }

    public function api()
    {
        return response()->json($this->metrics->snapshot());
    }
}