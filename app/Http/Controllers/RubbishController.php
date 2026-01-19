<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RubbishController extends Controller
{
    private string $apiUrl;
    private string $imgUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_service.url');
        $this->imgUrl = config('services.api_image.url');
    }

    /* =========================
        INDEX
    ========================= */
    public function index()
    {
        $token = session('token');

        if (!$token) {
            return $this->redirectLogin();
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->get($this->apiUrl . '/sampah');

        if ($response->status() === 401) {
            return $this->redirectLogin();
        }

        if ($response->failed()) {
            abort(500, 'Gagal mengambil data sampah');
        }

        return view('dashboard.rubbish.index', [
            'data'   => $response->json('data') ?? [],
            'imgUrl' => $this->imgUrl,
        ]);
    }

    /* =========================
        CREATE
    ========================= */
    public function create()
    {
        return view('dashboard.rubbish.create');
    }

    /* =========================
        STORE
    ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'kriteria' => 'required|string',
            'berat'    => 'required|numeric',
            'jenis'    => 'required|string',
            'foto'     => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $http = Http::acceptJson()
            ->withToken(session('token'));

        if ($request->hasFile('foto')) {
            $http->attach(
                'foto',
                fopen($request->file('foto')->getRealPath(), 'r'),
                $request->file('foto')->getClientOriginalName()
            );
        }

        $response = $http->post($this->apiUrl . '/sampah', [
            'kriteria' => $request->kriteria,
            'berat'    => $request->berat,
            'jenis'    => $request->jenis,
        ]);

        if ($response->failed()) {
            return back()->withErrors('Gagal menyimpan data')->withInput();
        }

        return redirect()
            ->route('rubbish')
            ->with('success', 'Data berhasil disimpan!');
    }

    /* =========================
        EDIT
    ========================= */
    public function edit($id)
    {
        $token = session('token');

        if (!$token) {
            return $this->redirectLogin();
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->get($this->apiUrl . '/sampah/' . $id);

        if ($response->status() === 401) {
            return $this->redirectLogin();
        }

        if ($response->failed()) {
            abort(404, 'Data tidak ditemukan');
        }

        return view('dashboard.rubbish.edit', [
            'data'   => $response->json('data'),
            'imgUrl' => $this->imgUrl,
        ]);
    }

    /* =========================
        UPDATE
    ========================= */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kriteria' => 'required|string',
            'berat'    => 'required|numeric|min:0',
            'jenis'    => 'required|string',
            'foto'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $http = Http::acceptJson()
            ->withToken(session('token'));

        // attach foto kalau ada
        if ($request->hasFile('foto')) {
            $http->attach(
                'foto',
                fopen($request->file('foto')->getRealPath(), 'r'),
                $request->file('foto')->getClientOriginalName()
            );
        }

        // ⬇️ PENTING: POST + _method PUT
        $response = $http->post($this->apiUrl . '/sampah/' . $id, [
            '_method'  => 'PUT',
            'kriteria' => $request->kriteria,
            'berat'    => $request->berat,
            'jenis'    => $request->jenis,
        ]);

        // dd($response->json());

        if ($response->failed()) {
            return back()->withErrors('Gagal memperbarui data')->withInput();
        }

        return redirect()
            ->route('rubbish')
            ->with('success', 'Data berhasil diperbarui!');
    }

    /* =========================
        DELETE
    ========================= */
    public function destroy($id)
    {
        $token = session('token');

        if (!$token) {
            return $this->redirectLogin();
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->delete($this->apiUrl . '/sampah/' . $id);

        if ($response->status() === 401) {
            return $this->redirectLogin();
        }

        if ($response->failed()) {
            abort(404, 'Data tidak ditemukan');
        };

        return redirect()
            ->route('rubbish')
            ->with('success', 'Data berhasil dihapus!');
    }


    /* =========================
        HELPER
    ========================= */
    private function redirectLogin()
    {
        session()->forget('token');

        return redirect()
            ->route('login')
            ->with('error', 'Session habis, silakan login ulang.');
    }
}
