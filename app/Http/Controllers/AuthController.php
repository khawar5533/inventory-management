<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function loadLogin()
    {
        // Vue will load only Login component (no layout)
        return view('layouts.app', ['defaultComponent' => 'Login']);
    }

    public function showRegister()
    {
        // Vue will load Register component (can use layout or similar to Login if needed)
        return view('layouts.app', ['defaultComponent' => 'Register']);
    }
}

