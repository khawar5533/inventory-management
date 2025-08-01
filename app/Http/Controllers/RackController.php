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
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'label' => 'required|string|max:255',
        ]);

        $rack = Rack::create([
            'room_id' => $request->room_id,
            'label' => $request->label,
        ]);

        return response()->json([
            'message' => 'Rack added successfully.',
            'rack' => $rack->load('room') // <- Load the room relationship
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'label' => 'required|string|max:255',
        ]);

        $rack = Rack::findOrFail($id);
        $rack->update([
            'room_id' => $request->room_id,
            'label' => $request->label,
        ]);

        return response()->json([
            'message' => 'Rack updated successfully.',
            'rack' => $rack->load('room') // include room if needed
        ]);
    }
    public function destroy($id)
    {
        $rack = Rack::findOrFail($id);
        $rack->delete(); // this does soft delete

        return response()->json([
            'message' => 'Rack deleted (soft) successfully.'
        ]);
    }


}
