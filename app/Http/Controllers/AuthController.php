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

        $data = $response->json();
        if ($response->successful() && $data['status'] === true) {
            session([
                'token' => $data['token'],
                'user' => [
                    'id' => $data['data']['id'],
                    'name' => $data['data']['name'],
                    'email' => $data['data']['email'],
                ],
                'role' => $data['role'] ?? null,
            ]);

            return redirect()->route('dashboard')->with([
                'swal' => [
                    'icon' => 'success',
                    'title' => 'Login Berhasil!',
                    'text' => 'Selamat datang, ' . ($dataUser['name'] ?? 'User') . '!',
                    'timer' => 2000
                ]
            ]);
        }

        return back()->withErrors([
            'login' => $data['message'] ?? 'Email atau password salah!',
        ])->with([
            'swal' => [
                'icon' => 'error',
                'title' => 'Login Gagal!',
                'text' => $data['message'] ?? 'Email atau password salah!',
            ]
        ]);
    }
}
