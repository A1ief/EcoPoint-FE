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

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post("{$this->apiUrl}/register", [
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $validated['password_confirmation'],
        ]);

        // dd($response->json());

        if ($response->successful()) {
            return redirect()->route('login')->with('success', 'Register berhasil!');
        } else {
            return redirect()->route('register')->with('error', 'Register gagal!');
        }
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

    public function logout()
    {
        session()->flush();
        return redirect()->route('login')->with('success', 'Logout berhasil!');
    }
}
