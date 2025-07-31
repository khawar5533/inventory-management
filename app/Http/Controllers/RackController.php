<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Rack;
class RackController extends Controller
{
        public function loadRackForm()
    {
        // This will render the Blade layout and mount the Vue component named 'AddRoom'
        return view('layouts.app', ['defaultComponent' => 'Rack']);
    }
    public function getRooms()
    {
        $rooms = Room::select('id', 'name')->get();
        return response()->json($rooms);
    }
// List all racks
    public function index()
    {
        $racks = Rack::with('room')->latest()->get();
        return response()->json($racks);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'label' => 'required|string|max:255',
        ]);

        $rack = Rack::create($validated);

        return response()->json([
            'message' => 'Rack created successfully!',
            'rack' => $rack
        ], 201);
    }

}
