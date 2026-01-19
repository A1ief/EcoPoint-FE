<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $apiUrl;
    private $timeout = 30;
    private $connectTimeout = 10;

    public function __construct()
    {
        $this->apiUrl = config('services.api_service.url');
    }

    /**
     * Cek apakah API server online
     */
    private function checkApiHealth()
    {
        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->get($this->apiUrl . '/health');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Fungsi helper untuk melakukan HTTP request
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        try {
            $http = Http::timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);

            // Tambahkan token jika ada
            if (session('token')) {
                $http = $http->withToken(session('token'));
            }

            $url = $this->apiUrl . $endpoint;
            $response = $http->{$method}($url, $data);

            return $response;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("API Connection Error: " . $e->getMessage(), [
                'url' => $url ?? '',
                'method' => $method
            ]);

            throw new \Exception('Tidak dapat terhubung ke API Server. Pastikan API berjalan di: ' . $this->apiUrl);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("API Request Error: " . $e->getMessage());
            throw new \Exception('Error saat request ke API: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("General API Error: " . $e->getMessage());
            throw $e;
        }
    }

    // INDEX - Tampilkan semua user
    public function index(Request $request)
    {
        try {
            $url = '/users';

            $params = [];
            if ($request->has('search')) {
                $params['search'] = $request->search;
            }

            $response = $this->makeRequest('get', $url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $users = $data['data'] ?? [];

                return view('dashboard.user.index', [
                    'users' => $users,
                    'totalUsers' => count($users),
                    'apiStatus' => 'online'
                ]);
            }

            return view('dashboard.user.index', [
                'users' => [],
                'totalUsers' => 0,
                'apiStatus' => 'error',
                'error' => 'API Error: ' . $response->status()
            ]);
        } catch (\Exception $e) {
            return view('dashboard.user.index', [
                'users' => [],
                'totalUsers' => 0,
                'apiStatus' => 'offline',
                'error' => $e->getMessage()
            ]);
        }
    }

    // CREATE - Tampilkan form tambah user
    public function create()
    {
        return view('dashboard.user.create');
    }

    // STORE - Simpan user baru ke API
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'alamat' => 'nullable|string',
            'role' => 'required|string',
            'password' => 'required|min:6',
            'is_active' => 'required|in:0,1'
        ]);

        try {
            $response = $this->makeRequest('post', '/users', $validated);

            if ($response->successful()) {
                return redirect()->route('users.index')
                    ->with('success', 'User berhasil ditambahkan');
            }

            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal menambahkan user';
            return back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // SHOW - Tampilkan detail user
    public function show($id)
    {
        try {
            $response = $this->makeRequest('get', "/users/{$id}");

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['data'] ?? $data;

                return view('dashboard.user.show', compact('user'));
            }

            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }

    // EDIT - Tampilkan form edit user
    public function edit($id)
    {
        try {
            $response = $this->makeRequest('get', "/users/{$id}");

            if ($response->successful()) {
                $data = $response->json();
                $user = $data['data'] ?? $data;

                return view('dashboard.user.edit', compact('user'));
            }

            return redirect()->route('users.index')
                ->with('error', 'User tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }

    // UPDATE - Update user di API
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'alamat' => 'nullable|string',
            'role' => 'required|string',
            'is_active' => 'nullable|boolean'
        ]);

        // Konversi checkbox ke boolean
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $response = $this->makeRequest('put', "/users/{$id}", $validated);

            if ($response->successful()) {
                return redirect()->route('users.index')
                    ->with('success', 'User berhasil diupdate');
            }

            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal mengupdate user';

            return back()->with('error', $errorMessage);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // DESTROY - Hapus user dari API
    public function destroy($id)
    {
        try {
            $response = $this->makeRequest('delete', "/users/{$id}");

            if ($response->successful()) {
                return redirect()->route('users.index')
                    ->with('success', 'User berhasil dihapus');
            }

            return redirect()->route('users.index')
                ->with('error', 'Gagal menghapus user');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
