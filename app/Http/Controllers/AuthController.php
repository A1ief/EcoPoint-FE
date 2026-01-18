<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private $apiUrl;
    public function __construct()
    {
        $this->apiUrl = config('services.api_service.url');
    }

    public function showlogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        //    dd($request->all());
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post("{$this->apiUrl}/login", [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        // dd($response->json());

        $data = $response->json();
        if ($response->successful() && $data['status'] === true) {
            session([
                'token' => $data['token'],
                'user' => [
                    'id' => $data['data']['id_user'],
                    'name' => $data['data']['nama'],
                    'email' => $data['data']['email'],
                ],
                'role' => $data['role'] ?? null,
            ]);

            // dd($response->json());

            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }


        return back()->with('error', 'Login gagal!');
    }
}
