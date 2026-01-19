<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PointController extends Controller
{
    private $apiUrl;
    private $timeout = 30;
    private $connectTimeout = 10;
    
    public function __construct()
    {
        $this->apiUrl = config('services.api_service.url');
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
            
        } catch (\Exception $e) {
            Log::error("General API Error: " . $e->getMessage());
            throw $e;
        }
    }

    // INDEX - Tampilkan semua transaksi poin
    public function index(Request $request)
    {
        try {
            $params = [];
            
            if ($request->has('search')) {
                $params['search'] = $request->search;
            }
            
            if ($request->has('status')) {
                $params['status'] = $request->status;
            }
            
            if ($request->has('id_user')) {
                $params['id_user'] = $request->id_user;
            }
            
            if ($request->has('per_page')) {
                $params['per_page'] = $request->per_page;
            }

            $response = $this->makeRequest('get', '/points', $params);
            
            if ($response->successful()) {
                $data = $response->json();
                
                return view('dashboard.point.index', [
                    'points' => $data['data'] ?? [], // Ganti 'poins' ke 'points'
                    'totalPoints' => $data['meta']['total_points'] ?? 0,
                    'totalBerat' => $data['meta']['total_berat'] ?? 0,
                    'currentPage' => $data['meta']['current_page'] ?? 1,
                    'totalPages' => $data['meta']['total_pages'] ?? 1,
                    'totalRecords' => $data['meta']['total'] ?? 0,
                    'apiStatus' => 'online'
                ]);
            }
            
            return view('dashboard.point.index', [
                'points' => [], // Ganti 'poins' ke 'points'
                'totalPoints' => 0,
                'totalBerat' => 0,
                'apiStatus' => 'error',
                'error' => 'API Error: ' . $response->status()
            ]);
            
        } catch (\Exception $e) {
            return view('dashboard.point.index', [
                'points' => [], // Ganti 'poins' ke 'points'
                'totalPoints' => 0,
                'totalBerat' => 0,
                'apiStatus' => 'offline',
                'error' => $e->getMessage()
            ]);
        }
    }

    // CREATE - Tampilkan form tambah transaksi poin
    public function create()
    {
        try {
            // Get users for dropdown
            $usersResponse = $this->makeRequest('get', '/users');
            $users = $usersResponse->successful() ? ($usersResponse->json()['data'] ?? []) : [];
            
            // Get sampah types for dropdown
            $sampahResponse = $this->makeRequest('get', '/sampah');
            $sampahTypes = $sampahResponse->successful() ? ($sampahResponse->json()['data'] ?? []) : [];
            
            return view('dashboard.point.create', compact('users', 'sampahTypes'));
            
        } catch (\Exception $e) {
            return redirect()->route('point.index') // PERBAIKI: point.index bukan points.index
                ->with('error', 'Tidak dapat memuat data: ' . $e->getMessage());
        }
    }

    // STORE - Simpan transaksi poin baru ke API
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|integer',
            'id_sampah' => 'required|integer',
            'berat' => 'required|numeric|min:0.1',
            'deskripsi' => 'required|string|max:255',
            'point' => 'required|integer|min:0'
        ]);

        try {
            $response = $this->makeRequest('post', '/points', $validated); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                return redirect()->route('point.index') // PERBAIKI: point.index bukan poin.index
                    ->with('success', 'Transaksi poin berhasil ditambahkan');
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal menambahkan transaksi poin';
            
            return back()->withInput()->with('error', $errorMessage);
            
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // EDIT - Tampilkan form edit transaksi poin
    public function edit($id)
    {
        try {
            $response = $this->makeRequest('get', "/points/{$id}"); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                $data = $response->json();
                $point = $data['data'] ?? $data; // Ganti $poin ke $point
                
                // Get users for dropdown
                $usersResponse = $this->makeRequest('get', '/users');
                $users = $usersResponse->successful() ? ($usersResponse->json()['data'] ?? []) : [];
                
                // Get sampah types for dropdown
                $sampahResponse = $this->makeRequest('get', '/sampah');
                $sampahTypes = $sampahResponse->successful() ? ($sampahResponse->json()['data'] ?? []) : [];
                
                return view('dashboard.point.edit', compact('point', 'users', 'sampahTypes')); // PERBAIKI: dashboard.point.edit
            }
            
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', 'Transaksi poin tidak ditemukan');
            
        } catch (\Exception $e) {
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $e->getMessage());
        }
    }

    // UPDATE - Update transaksi poin di API
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_user' => 'required|integer',
            'id_sampah' => 'required|integer',
            'berat' => 'required|numeric|min:0.1',
            'deskripsi' => 'required|string|max:255',
            'point' => 'required|integer|min:0',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        try {
            $response = $this->makeRequest('put', "/points/{$id}", $validated); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                return redirect()->route('point.index') // PERBAIKI: point.index
                    ->with('success', 'Transaksi poin berhasil diupdate');
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal mengupdate transaksi poin';
            
            return back()->with('error', $errorMessage);
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // DESTROY - Hapus transaksi poin dari API
    public function destroy($id)
    {
        try {
            $response = $this->makeRequest('delete', "/points/{$id}"); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                return redirect()->route('point.index') // PERBAIKI: point.index
                    ->with('success', 'Transaksi poin berhasil dihapus');
            }
            
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', 'Gagal menghapus transaksi poin');
            
        } catch (\Exception $e) {
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $e->getMessage());
        }
    }

    // APPROVE - Approve transaksi poin
    public function approve($id)
    {
        try {
            $response = $this->makeRequest('post', "/points/{$id}/approve"); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                return redirect()->route('point.index') // PERBAIKI: point.index
                    ->with('success', 'Transaksi poin berhasil disetujui');
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal menyetujui transaksi poin';
            
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $errorMessage);
            
        } catch (\Exception $e) {
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $e->getMessage());
        }
    }

    // REJECT - Reject transaksi poin
    public function reject($id)
    {
        try {
            $response = $this->makeRequest('post', "/points/{$id}/reject"); // PERBAIKI: /points bukan /poins
            
            if ($response->successful()) {
                return redirect()->route('point.index') // PERBAIKI: point.index
                    ->with('success', 'Transaksi poin berhasil ditolak');
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? 'Gagal menolak transaksi poin';
            
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $errorMessage);
            
        } catch (\Exception $e) {
            return redirect()->route('point.index') // PERBAIKI: point.index
                ->with('error', $e->getMessage());
        }
    }
}