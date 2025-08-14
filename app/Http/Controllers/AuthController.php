<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Load the login view with the Login Vue component.
     * This renders the base layout and instructs Vue to load only the Login component.
     *
     * @return \Illuminate\View\View
     */
    public function loadLogin()
    {
        session()->flash('message', 'Please login to continue');

        return view('layouts.app', ['defaultComponent' => 'Login']);
    }

    /**
     * Load the registration view with the Register Vue component.
     * This renders the base layout and instructs Vue to load the Register component.
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('layouts.app', ['defaultComponent' => 'Register']);
    }

    /**
     * Load the registration view with the Role Vue component.
     * This renders the base layout and instructs Vue to load the Role component.
     *
     * @return \Illuminate\View\View
     */
    public function showRoleForm()
    {
        return view('layouts.app', ['defaultComponent' => 'Role']);
    }

    // Load User Profile
    public function showUserProfile()
    {
        return view('layouts.app', ['defaultComponent' => 'UserProfile']);
    }

    /**
     * Return the list of users as JSON for Vue to consume via fetch/AJAX.
     */
    public function getListUsers()
    {
        $users = User::select('id', 'name')->get(); // Only return required fields

        return response()->json($users);
    }

    /**
     * Handle user registration.
     * Validates input, creates a new user, and returns a JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'company' => $request->company,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle user login request.
     *
     * This function validates user input, checks credentials against the database,
     * logs the user in using Laravel's auth system, and returns a JSON response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request)
    {
        try {
            // Step 1: Validate email and password input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            // Step 2: Return validation error if input is invalid
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Step 3: Find the user by email
            $user = User::where('email', $request->email)->first();

            // Step 4: Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            // Step 5: Log the user in
            auth()->login($user);

            // Step 6: Generate dynamic URL for redirection
            $redirectUrl = url('/dashboard'); // Automatically generates full path like http://localhost/laravel/inventory-management/dashboard

            // Step 7: Return success response
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            // Step 8: Catch any server errors
            return response()->json([
                'success' => false,
                'message' => 'Server error: '.$e->getMessage(),
            ], 500);
        }
    }

    // Logout function
    public function logout(Request $request)
    {
        Auth::logout(); // Logs out the current user

        // Clear all session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login page (with flash message if needed)
        return redirect()->route('login')->with('status', 'Logged out successfully.');
    }

    // get user role
    public function getUserRoles(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $roles = $user->roles()->pluck('name');

            return response()->json([
                'roles' => $roles,
                'userName' => $user->name,
                'user_image' => $user->user_image ? $user->user_image : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // get all profile data
    public function getProfileData()
    {
        return response()->json(auth()->user());
    }

    // For udate user profile
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'user_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('user_image')) {
            // Delete old image if exists
            if ($user->user_image && \Storage::disk('public')->exists($user->user_image)) {
                \Storage::disk('public')->delete($user->user_image);
            }
            $validated['user_image'] = $request->file('user_image')->store('users', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    // Delete Profile image
    public function deleteImage($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_image && Storage::disk('public')->exists($user->user_image)) {
            Storage::disk('public')->delete($user->user_image);
            $user->user_image = null;
            $user->save();

            return response()->json(['message' => 'Old image deleted successfully']);
        }

        return response()->json(['message' => 'No image found'], 404);
    }

    // update password
    public function updatePassword(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed', // confirms with new_password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = auth()->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        // Update to new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}
