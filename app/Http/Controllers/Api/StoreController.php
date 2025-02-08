<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Postcode;
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

    /**
     * Return stores near to a postcode (based solely on proximity).
     *
     * GET /api/stores/near/{postcode}
     *
     * This method looks up the postcode in the postcodes table,
     * calculates the distance from the postcode to each store using the Haversine formula,
     * orders the results by distance, and returns them.
     */
    public function nearByPostcode(string $postcode)
    {
        // Look up the postcode record.
        $postcodeRecord = Postcode::where('pcd', $postcode)->first();

        if (!$postcodeRecord) {
            return response()->json(['error' => 'Postcode not found.'], 404);
        }

        $lat = $postcodeRecord->lat;
        $long = $postcodeRecord->long;

        // Haversine formula to calculate distance (in kilometers).
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`))))";

        // Get all stores ordered by proximity (distance) without filtering on max_delivery_distance.
        $stores = Store::select()
            ->selectRaw("$haversine as distance", [$lat, $long, $lat])
            ->orderBy('distance', 'asc')
            ->paginate(20);

        return response()->json($stores);
    }

    /**
     * Return stores that can deliver to a given postcode.
     *
     * GET /api/stores/delivery/{postcode}
     *
     * This method looks up the postcode, calculates the distance to each store,
     * and then filters the stores to only include those where the calculated distance
     * is less than or equal to the store's max_delivery_distance.
     */
    public function deliveryByPostcode(string $postcode)
    {
        // Look up the postcode record.
        $postcodeRecord = Postcode::where('pcd', $postcode)->first();

        if (!$postcodeRecord) {
            return response()->json(['error' => 'Postcode not found.'], 404);
        }

        $lat = $postcodeRecord->lat;
        $long = $postcodeRecord->long;

        // Haversine formula to calculate distance (in kilometers).
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`))))";

        // Using the best performing query
        // Tested on 100,000 stores over 10,000 test cases
        $stores = Store::select()
            ->selectRaw("$haversine as distance", [$lat, $long, $lat])
            ->whereRaw("$haversine <= max_delivery_distance", [$lat, $long, $lat])
            ->orderBy('distance', 'asc')
            ->paginate(20);

        // $stores = Store::selectRaw("*, $haversine as distance", [$lat, $long, $lat])
        //     ->groupBy('stores.id')
        //     ->havingRaw("distance <= max_delivery_distance")
        //     ->orderBy('distance', 'asc')
        //     ->paginate(20);

        return response()->json($stores);
    }
}
