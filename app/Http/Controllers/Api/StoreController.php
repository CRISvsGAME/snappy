<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores = Store::paginate(20);
        return response()->json($stores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:stores',
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'is_open' => 'nullable|boolean',
            'store_type' => 'nullable|string|max:255',
            'max_delivery_distance' => 'nullable|integer',
        ]);

        $store = Store::create($data);

        return response()->json($store, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found.'], 404);
        }

        return response()->json($store);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found.'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:stores',
            'lat' => 'sometimes|required|numeric|between:-90,90',
            'long' => 'sometimes|required|numeric|between:-180,180',
            'is_open' => 'sometimes|required|boolean',
            'store_type' => 'sometimes|required|string|max:255',
            'max_delivery_distance' => 'sometimes|required|integer',
        ]);

        $store->update($data);

        return response()->json($store);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found.'], 404);
        }

        $store->delete();

        return response()->json(['message' => 'Store deleted successfully.']);
    }
}
