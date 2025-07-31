<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Room;

class RoomController extends Controller
{
   // Load the main layout and pass default Vue component
public function loadRoomForm()
{
    // This will render the Blade layout and mount the Vue component named 'AddRoom'
    return view('layouts.app', ['defaultComponent' => 'AddRoom']);
}

// Fetch list of all floors (used in dropdown)
public function list()
{
    // Return only id and name of floors for dropdown selection
    return Floor::select('id', 'name')->get();
}

// Fetch all rooms along with their associated floor name
public function getRoomList()
{
    // Get rooms with their related floor (only id and name of floor), used in table view
    $rooms = Room::with('floor:id,name')
                ->select('id', 'name', 'floor_id')
                ->get();

    return response()->json($rooms);
}

// Store a new room in the database
public function store(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'floor_id' => 'required|exists:floors,id', // Floor must exist
        'name'     => 'required|string|max:255',   // Room name is required
    ]);

    // Create the room
    $room = Room::create($validated);

    // Return success response
    return response()->json([
        'message' => 'Room added successfully.',
        'room' => $room
    ]);
}

// Update an existing room in the database
public function update(Request $request, Room $room)
{
    // Validate incoming data
    $request->validate([
        'name' => 'required|string|max:255',
        'floor_id' => 'required|exists:floors,id',
    ]);

    // Update room with validated data
    $room->update($request->only('name', 'floor_id'));

    // Return the updated room with its floor
    return response()->json($room->load('floor'));
}
// Delete room record
public function destroy(Room $room)
{
    $room->delete(); // Soft delete

    return response()->json(['message' => 'Room deleted successfully.']);
}


}
