<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PostalCode::with('city.county');

        if ($request->has('code')) {
            $query->where('code', 'like', $request->code . '%');
        }

        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return $query->paginate(20);
    }

    public function show(PostalCode $postalCode)
    {
        return $postalCode->load('city.county');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
            'city_id' => 'required|exists:cities,id'
        ]);

        $postalCode = PostalCode::create($validated);
        return response()->json($postalCode->load('city.county'), 201);
    }

    public function update(Request $request, PostalCode $postalCode)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
            'city_id' => 'required|exists:cities,id'
        ]);

        $postalCode->update($validated);
        return response()->json($postalCode->load('city.county'));
    }

    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();
        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        $query = PostalCode::with('city.county');

        if ($request->has('q')) {
            $search = $request->q;
            $query->where('code', 'like', $search . '%')
                ->orWhereHas('city', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        return $query->paginate(20);
    }
}