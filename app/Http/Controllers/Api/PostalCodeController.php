<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PostalCode::with('county');

        if ($request->has('code')) {
            $query->where('code', 'like', $request->code . '%');
        }

        if ($request->has('settlement')) {
            $query->where('settlement', 'like', '%' . $request->settlement . '%');
        }

        if ($request->has('county_id')) {
            $query->where('county_id', $request->county_id);
        }

        return $query->paginate(20);
    }

    public function show(PostalCode $postalCode)
    {
        return $postalCode->load('county');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
            'settlement' => 'required|string|max:255',
            'county_id' => 'nullable|exists:counties,id'
        ]);

        $postalCode = PostalCode::create($validated);
        return response()->json($postalCode->load('county'), 201);
    }

    public function update(Request $request, PostalCode $postalCode)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
            'settlement' => 'required|string|max:255',
            'county_id' => 'nullable|exists:counties,id'
        ]);

        $postalCode->update($validated);
        return response()->json($postalCode->load('county'));
    }

    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();
        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        $query = PostalCode::with('county');

        if ($request->has('q')) {
            $search = $request->q;
            $query->where('code', 'like', $search . '%')
                ->orWhere('settlement', 'like', '%' . $search . '%');
        }

        return $query->paginate(20);
    }
}