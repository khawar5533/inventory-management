<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Location; 
class FloorController extends Controller
{
    /**
     * Load the main view and inject the AddFloor Vue component.
     *
     * This is used to serve the main layout (Blade view) with the Vue component
     * that handles floor creation and management.
     */
    public function loadFooterForm()
    {
       $locations = Location::select('id', 'name')->get();
        return view('layouts.app', [
            'defaultComponent' => 'AddFloor',
            'locations' => $locations,
        ]);
    }

    /**
     * Return all floor records along with their related location.
     *
     * This is used for listing all floors on the frontend with associated location names.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return Floor::with('location')->get(); // Eager loads the related location
    }

    /**
     * Store a new floor in the database.
     *
     * Validates the request and creates a new floor record with a linked location.
     * Returns the created floor with its associated location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
        ]);

        $floor = Floor::create($request->only('location_id', 'name'));

        return response()->json($floor->load('location')); // Include location in response
    }

    /**
     * Update an existing floor in the database.
     *
     * Validates the request, updates the floor details, and returns the updated record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name' => 'required|string|max:255',
        ]);

        $floor = Floor::findOrFail($id);

        $floor->update([
            'name' => $request->name,
            'location_id' => $request->location_id
        ]);

        return response()->json([
            'message' => 'Floor updated successfully.',
            'floor' => $floor->load('location')
        ]);
    }


    /**
     * Delete a floor from the database.
     *
     * Removes the selected floor record permanently (or soft deletes if enabled).
     *
     * @param  \App\Models\Floor  $floor
     * @return \Illuminate\Http\JsonResponse
     */
   public function destroy($id)
    {
        $floor = Floor::findOrFail($id);
        $floor->delete();

        return response()->json(['message' => 'Floor deleted successfully.']);
    }

   
}

