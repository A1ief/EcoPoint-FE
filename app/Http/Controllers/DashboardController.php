<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http as FacadesHttp;

class DashboardController extends Controller
{


    private $apiUrl;
    public function __construct()
    {
        $this->apiUrl = config('services.api_service.url');
    }
    public function index()
    {

        $token = session('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Session habis, silakan login lagi.');
        }

        $response = FacadesHttp::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get("{$this->apiUrl}/users");

        // dd($response->json());
        return view('dashboard.index');
    }
}
