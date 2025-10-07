<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function clients()
    {
        // dd('here');
        $clients = User::where('role', 'client')->get();
        $totalClients = $clients->count();
        $activeClients = $clients->where('status', 'Active')->count();
        $inactiveClients = $clients->where('status', 'In-Active')->count();
        $joinedThisMonth = $clients->where('created_at', '>=', now()->startOfMonth())->count();
        return view('clients.index', compact('clients','totalClients','activeClients','inactiveClients','joinedThisMonth'));
    }

    public function addClient(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'email'          => 'required|email|max:120|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'company_name'   => 'nullable|string|max:120',
            'contact_name'   => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:255',
        ]);


        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),   // <-- hashed here
            'role'           => 'client',
            'company_name'   => $data['company_name'] ?? null,
            'contact_name'   => $data['contact_name'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'status'         => 'Active', // Default status
        ]);


        return redirect()->route('clients')->with('success', 'Client added successfully');
    }
}
