<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('county');

        if ($request->has('county_id')) {
            $query->where('county_id', $request->county_id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $query->paginate(20);
    }

    public function show(City $city)
    {
        return $city->load('postalCodes');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id'
        ]);

        $city = City::create($validated);
        return response()->json($city->load('county'), 201);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id'
        ]);

        $city->update($validated);
        return response()->json($city->load('county'));
    }

    public function destroy(City $city)
    {
        $city->delete();
        return response()->json(null, 204);
    }
}