<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    // Load the main Vue view and set the default component
    public function loadLocationForm()
    {
        return view('layouts.app', ['defaultComponent' => 'AddLocation']);
    }

    // Fetch all locations (for Vue table display)
    public function index()
    {
        $locations = Location::latest()->get();
        return response()->json($locations);
    }

    // Store a new location
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $location = Location::create($validated);

        return response()->json([
            'message' => 'Location added successfully.',
            'location' => $location
        ]);
    }

     public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $location->update($request->all());

        return response()->json(['location' => $location]);
    }

    // Delete a location
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json(['message' => 'Location deleted successfully.']);
    }
}
