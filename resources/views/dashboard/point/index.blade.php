@extends('layouts.app')
@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Daftar Transaksi Poin</h3>
                <p class="text-sm text-gray-500 mt-1">Total: <span
                        class="font-semibold text-green-600">{{ $totalRecords ?? 0 }}</span> transaksi poin</p>
                <p class="text-sm text-gray-500 mt-1">Total Poin: <span
                        class="font-semibold text-blue-600">{{ $totalPoints ?? 0 }}</span> poin</p>
                <p class="text-sm text-gray-500 mt-1">Total Berat: <span
                        class="font-semibold text-blue-600">{{ number_format($totalBerat ?? 0, 2) }} kg</span></p>
            </div>
            <a href="{{ route('point.create') }}"
                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 shadow-md hover:shadow-xl flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                <span>Tambah Transaksi Poin</span>
            </a>
        </div>

        <!-- API Status Alert -->
        @if($apiStatus === 'offline')
            <div class="bg-red-50 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ $error ?? 'Tidak dapat terhubung ke API server' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter & Search -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <form method="GET" action="{{ route('point.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Transaksi</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari berdasarkan deskripsi atau aksi..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Records per page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data per halaman</label>
                    <select name="per_page"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('point.index') }}"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            @if($apiStatus === 'online' && count($points) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-green-600 to-emerald-600 text-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">User</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Sampah</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Berat (kg)</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Poin</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Deskripsi</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Tanggal</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($points as $index => $point)
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        #{{ $point['id'] ?? ($index + 1) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                @if(isset($point['user']['name']))
                                                    <span class="text-blue-700 font-bold text-sm">
                                                        {{ strtoupper(substr($point['user']['name'], 0, 2)) }}
                                                    </span>
                                                @else
                                                    <span class="text-blue-700 font-bold text-sm">U{{ $point['id_user'] ?? '' }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    {{ $point['user']['name'] ?? 'User #' . ($point['id_user'] ?? '') }}
                                                </p>
                                                <p class="text-xs text-gray-500">ID: {{ $point['id_user'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                @if(isset($point['sampah']['nama']))
                                                    <span class="text-green-700 font-bold text-sm">
                                                        {{ strtoupper(substr($point['sampah']['nama'], 0, 2)) }}
                                                    </span>
                                                @else
                                                    <span class="text-green-700 font-bold text-sm">S{{ $point['id_sampah'] ?? '' }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    {{ $point['sampah']['nama'] ?? 'Sampah #' . ($point['id_sampah'] ?? '') }}
                                                </p>
                                                <p class="text-xs text-gray-500">ID: {{ $point['id_sampah'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 font-semibold">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ number_format($point['berat'] ?? 0, 2) }} kg
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 font-semibold">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                            {{ $point['point'] ?? 0 }} poin
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $status = $point['status'] ?? 'pending';
                                            $statusColors = [
                                                'pending' => ['bg' => 'yellow-100', 'text' => 'yellow-800', 'dot' => 'yellow-500'],
                                                'approved' => ['bg' => 'green-100', 'text' => 'green-800', 'dot' => 'green-500'],
                                                'rejected' => ['bg' => 'red-100', 'text' => 'red-800', 'dot' => 'red-500']
                                            ];
                                            $color = $statusColors[$status] ?? $statusColors['pending'];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-{{ $color['bg'] }} text-{{ $color['text'] }}">
                                            <span class="w-2 h-2 bg-{{ $color['dot'] }} rounded-full mr-2"></span>
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $point['deskripsi'] ?? '' }}">
                                        {{ $point['deskripsi'] ?? $point['aksi'] ?? 'Tidak ada deskripsi' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if(isset($point['created_at']))
                                            {{ \Carbon\Carbon::parse($point['created_at'])->translatedFormat('d/m/Y H:i') }}
                                        @elseif(isset($point['tanggal']))
                                            {{ \Carbon\Carbon::parse($point['tanggal'])->translatedFormat('d/m/Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center space-x-2">
                                            @if($status === 'pending')
                                                <!-- Approve -->
                                                <form action="{{ route('point.approve', $point['id']) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors"
                                                        title="Approve"
                                                        onclick="return confirm('Yakin ingin menyetujui transaksi ini?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </button>
                                                </form>

                                                <!-- Reject -->
                                                <form action="{{ route('point.reject', $point['id']) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition-colors"
                                                        title="Reject"
                                                        onclick="return confirm('Yakin ingin menolak transaksi ini?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Edit -->
                                            <a href="{{ route('point.edit', $point['id']) }}"
                                                class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('point.destroy', $point['id']) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus transaksi poin ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors"
                                                    title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($totalPages > 1)
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Menampilkan <span class="font-medium">{{ (($currentPage - 1) * 10) + 1 }}</span> sampai 
                                <span class="font-medium">{{ min($currentPage * 10, $totalRecords) }}</span> dari
                                <span class="font-medium">{{ $totalRecords }}</span> data
                            </div>
                            <div class="flex space-x-2">
                                <!-- Previous Button -->
                                @if($currentPage > 1)
                                    <a href="{{ route('point.index', array_merge(request()->except('page'), ['page' => $currentPage - 1])) }}"
                                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Sebelumnya
                                    </a>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                        Sebelumnya
                                    </span>
                                @endif

                                <!-- Page Numbers -->
                                @for($i = 1; $i <= min($totalPages, 5); $i++)
                                    <a href="{{ route('point.index', array_merge(request()->except('page'), ['page' => $i])) }}"
                                        class="px-3 py-1 rounded-lg transition-colors {{ $i == $currentPage ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $i }}
                                    </a>
                                @endfor

                                @if($totalPages > 5)
                                    <span class="px-3 py-1 text-gray-500">...</span>
                                    <a href="{{ route('point.index', array_merge(request()->except('page'), ['page' => $totalPages])) }}"
                                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                        {{ $totalPages }}
                                    </a>
                                @endif

                                <!-- Next Button -->
                                @if($currentPage < $totalPages)
                                    <a href="{{ route('point.index', array_merge(request()->except('page'), ['page' => $currentPage + 1])) }}"
                                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Selanjutnya
                                    </a>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                                        Selanjutnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Empty state or error -->
                <div class="px-6 py-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @if($apiStatus === 'offline')
                        <p class="text-lg font-medium text-red-600">API Server Offline</p>
                        <p class="text-sm text-gray-600 mt-2">Tidak dapat mengambil data transaksi poin</p>
                    @else
                        <p class="text-lg font-medium">Tidak ada data transaksi poin</p>
                        <p class="text-sm text-gray-600 mt-2">Mulai dengan menambahkan transaksi poin baru</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Status Legend -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Legenda Status</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                        Pending
                    </span>
                    <span class="text-sm text-gray-600">Menunggu persetujuan</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        Approved
                    </span>
                    <span class="text-sm text-gray-600">Telah disetujui</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                        Rejected
                    </span>
                    <span class="text-sm text-gray-600">Telah ditolak</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit filter form on select change
        document.querySelector('select[name="per_page"]').addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush