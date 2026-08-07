<?php

namespace App\Http\Controllers;

use App\Services\LocationNameService;
use Illuminate\Http\Request;

class LocationNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /api/location-name?lat=...&lng=...
     */
    public function show(Request $request, LocationNameService $locations)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $name = $locations->resolve(
            (float) $request->query('lat'),
            (float) $request->query('lng')
        );

        return response()->json([
            'name' => $name,
            'lat' => (float) $request->query('lat'),
            'lng' => (float) $request->query('lng'),
        ]);
    }
}
