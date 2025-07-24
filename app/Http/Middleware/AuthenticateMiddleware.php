<?php

// app/Http/Middleware/AuthenticateMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
class AuthenticateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            // Build correct redirect URL regardless of subfolder or domain
            $loginUrl = url('/'); // Handles base path automatically

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return redirect($loginUrl)->with('error', 'Please login to access this page.');
        }

        return $next($request);
    }
}

