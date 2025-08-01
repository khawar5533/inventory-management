<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rack;
use App\Models\Box;

class BoxController extends Controller
{
    /**
     * Render the main layout and mount the Vue component for managing boxes.
     *
     * @return \Illuminate\View\View
     */
    public function loadBoxForm()
    {
        // This will render the Blade layout and mount the Vue component named 'Box'
        return view('layouts.app', ['defaultComponent' => 'Box']);
    }

    /**
     * Get a list of all boxes with their associated rack.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function boxList()
    {
        // Load the related rack data for each box
        $boxes = Box::with('rack')->get();

        return response()->json($boxes);
    }

    /**
     * Store a new box record in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'rack_id' => 'required|exists:racks,id',
                'label' => 'required|string|max:255',
            ]);

            // Create the box
            $box = Box::create([
                'rack_id' => $request->rack_id,
                'label' => $request->label,
            ]);

            // Return success response with the box and its rack
            return response()->json([
                'message' => 'Box added successfully.',
                'box' => $box->load('rack'),
            ]);
        } catch (\Exception $e) {
            // Return error response on failure
            return response()->json([
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing box record.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the box or fail with 404
        $box = Box::findOrFail($id);

        // Validate the request data
        $request->validate([
            'rack_id' => 'required|exists:racks,id',
            'label' => 'required|string|max:255',
        ]);

        // Update the box details
        $box->update([
            'rack_id' => $request->rack_id,
            'label' => $request->label,
        ]);

        // Return updated box with rack data
        return response()->json([
            'message' => 'Box updated successfully.',
            'box' => $box->load('rack'),
        ]);
    }

    /**
     * Soft delete a box by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the box or fail
        $box = Box::findOrFail($id);

        // Perform soft delete
        $box->delete();

        return response()->json([
            'message' => 'Box deleted successfully.'
        ]);
    }
}


