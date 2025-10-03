<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register (client only)
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'email'          => 'required|email|max:120|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'company_name'   => 'nullable|string|max:120',
            'contact_name'   => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),   // <-- hashed here
            'role'           => 'client',
            'company_name'   => $data['company_name'] ?? null,
            'contact_name'   => $data['contact_name'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registered successfully');
    }

    // Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Using Auth::attempt automatically checks Hash::check
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }
}
