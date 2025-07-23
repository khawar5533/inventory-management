<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
     * Handle user registration.
     * Validates input, creates a new user, and returns a JSON response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'company'  => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user = User::create([
                'name'     => $request->name,
                'company'  => $request->company,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);



            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

  /**
 * Handle user login request.
 * 
 * This function validates user input, checks credentials against the database,
 * logs the user in using Laravel's auth system, and returns a JSON response.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function loginUser(Request $request)
{
    try {
        // Step 1: Validate email and password input
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
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
            'success'      => true,
            'message'      => 'Login successful',
            'user'         => $user,
            'redirect_url' => $redirectUrl,
        ]);
    } catch (\Exception $e) {
        // Step 8: Catch any server errors
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}


}
