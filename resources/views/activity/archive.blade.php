@extends('layouts.app')

@section('title', 'Arsip Aktivitas — WOYuk!')

@push('styles')
<style>
    #modal-map {
        height: 320px;
        width: 100%;
        border-radius: 1rem;
        z-index: 10;
    }
</style>
@endpush

@section('content')
@include('components.navbar')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header & Lifetime Stats -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black italic uppercase tracking-wider text-white">
                    Arsip Aktivitas
                </h1>
                <p class="text-xs sm:text-sm text-gray-400 mt-1">
                    Rekam jejak lan prestasi olahraga sing wis sukses sampeyan selesaikan.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700">
                Kembali
            </a>
        </div>

        <!-- LIFETIME SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 sport-glow">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL JARAK</div>
                <div class="text-3xl font-black italic text-orange-400 font-mono mt-1">
                    {{ $totalDistanceKm }} <span class="text-xs text-gray-400 uppercase">KM</span>
                </div>
            </div>

            <div class="glass-card-dark p-5 rounded-2xl border border-gray-800">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL KALORI</div>
                <div class="text-3xl font-black italic text-emerald-400 font-mono mt-1">
                    {{ number_format($totalCalories, 0, ',', '.') }} <span class="text-xs text-gray-400 uppercase">KCAL</span>
                </div>
            </div>

            <div class="glass-card-dark p-5 rounded-2xl border border-gray-800">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL WAKTU</div>
                <div class="text-3xl font-black italic text-amber-400 font-mono mt-1">
                    {{ $totalHours }}j {{$totalMinutes }}m
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER SEGMENTED BUTTONS -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <a href="{{ route('archive.index') }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ !$sportFilter ? 'bg-orange-600 text-white sport-glow' : 'glass-card-dark text-gray-400 hover:text-white' }}">
            Semua
        </a>
        <a href="{{ route('archive.index', ['sport_type' => 'running']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ $sportFilter === 'running' ? 'bg-orange-600 text-white sport-glow' : 'glass-card-dark text-gray-400 hover:text-white' }}">
            Lari
        </a>
        <a href="{{ route('archive.index', ['sport_type' => 'walking']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ $sportFilter === 'walking' ? 'bg-orange-600 text-white sport-glow' : 'glass-card-dark text-gray-400 hover:text-white' }}">
            Jalan
        </a>
        <a href="{{ route('archive.index', ['sport_type' => 'cycling']) }}" 
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all {{ $sportFilter === 'cycling' ? 'bg-orange-600 text-white sport-glow' : 'glass-card-dark text-gray-400 hover:text-white' }}">
            Bersepeda
        </a>
    </div>

    <!-- LIST HISTORY CARD -->
    <div class="space-y-4">
        @forelse($activities as$act)
            <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:border-gray-700 transition-all">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400 flex-shrink-0">
                        @if($act->sport_type === 'running')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @elseif($act->sport_type === 'walking')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black italic uppercase text-white">{{ strtoupper($act->sport_type) }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold uppercase">Selesai</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ \Carbon\Carbon::parse($act->created_at)->translatedFormat('l, d F Y - H:i') }} WIB
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6 text-center sm:text-right w-full sm:w-auto font-mono">
                    <div>
                        <div class="text-[9px] text-gray-500 uppercase">JARAK</div>
                        <div class="text-sm font-bold text-orange-400">{{ number_format($act->distance_meters / 1000, 2, ',', '.') }} KM</div>
                    </div>
                    <div>
                        <div class="text-[9px] text-gray-500 uppercase">DURASI</div>
                        <div class="text-sm font-bold text-white">{{ gmdate("H:i:s", $act->duration_seconds) }}</div>
                    </div>
                    <div>
                        <div class="text-[9px] text-gray-500 uppercase">KALORI</div>
                        <div class="text-sm font-bold text-emerald-400">{{ $act->calories_burned }} KCAL</div>
                    </div>
                </div>

                <!-- Tombol Buka Modal Peta Rute -->
                <button type="button" onclick="openRouteModal({{ $act->id }})"
                    class="w-full sm:w-auto px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-orange-400 border border-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider transition-all sport-glow">
                    Lihat Rute
                </button>

            </div>
        @empty
            <div class="glass-card-dark p-12 rounded-3xl border border-gray-800 text-center space-y-3">
                <div class="text-gray-500 text-sm">Durung ana riwayat aktivitas sing dicathet.</div>
                <a href="{{ route('dashboard') }}" class="inline-block px-5 py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider sport-glow">
                    Mulai Aktivitas Pertama
                </a>
            </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    <div class="pt-4">
        {{ $activities->links() }}
    </div>

</main>

<!-- MODAL POP-UP VISUALISASI PETA RUTE -->
<div id="route-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="glass-card-dark max-w-2xl w-full p-6 rounded-3xl border border-gray-800 space-y-4 relative">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black italic uppercase text-orange-400" id="modal-title">DETAIL RUTE AKTIVITAS</h3>
                <p class="text-xs text-gray-400" id="modal-subtitle">Loading data rute...</p>
            </div>
            <button type="button" onclick="closeRouteModal()" class="text-gray-400 hover:text-white font-bold p-2">
                ✕
            </button>
        </div>

        <!-- Container Peta -->
        <div class="border border-gray-800 rounded-2xl overflow-hidden">
            <div id="modal-map"></div>
        </div>

        <!-- Ringkasan Stats Modal -->
        <div class="grid grid-cols-3 gap-4 text-center font-mono glass-card-dark p-3 rounded-xl border border-gray-800 text-xs">
            <div>
                <span class="text-gray-500 block text-[9px]">JARAK</span>
                <span id="modal-stat-distance" class="font-bold text-orange-400">-</span>
            </div>
            <div>
                <span class="text-gray-500 block text-[9px]">DURASI</span>
                <span id="modal-stat-duration" class="font-bold text-white">-</span>
            </div>
            <div>
                <span class="text-gray-500 block text-[9px]">KALORI</span>
                <span id="modal-stat-calories" class="font-bold text-emerald-400">-</span>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    let archiveMap = null;
    let archivePolyline = null;
    let startMarker = null;
    let finishMarker = null;

    function openRouteModal(activityId) {
        document.getElementById('route-modal').classList.remove('hidden');

        // Fetch Data Koordinat Rute dari API
        fetch(`/api/activity/${activityId}/route`)
            .then(res => res.json())
            .then(data => {
                if(data.status !== 'success') {
                    alert("Gagal memuat rute aktivitas.");
                    return;
                }

                const act = data.activity;
                const points = data.points;

                document.getElementById('modal-title').innerText = `RUTE ${act.sport_type}`;
                document.getElementById('modal-subtitle').innerText = `Total ${points.length} titik koordinat terekam`;
                document.getElementById('modal-stat-distance').innerText = `${act.distance_km} KM`;
                document.getElementById('modal-stat-duration').innerText = act.duration_str;
                document.getElementById('modal-stat-calories').innerText = `${act.calories} KCAL`;

                // Inisialisasi Peta Leaflet jika belum ada
                setTimeout(() => {
                    renderArchiveMap(points);
                }, 200);
            })
            .catch(err => {
                console.error(err);
                alert("Terjadi kesalahan koneksi saat memuat rute.");
            });
    }

    function renderArchiveMap(points) {
        if (!archiveMap) {
            archiveMap = L.map('modal-map', {
                zoomControl: true,
                attributionControl: false
            });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(archiveMap);
        }

        // Hapus layer lama jika ada
        if (archivePolyline) archiveMap.removeLayer(archivePolyline);
        if (startMarker) archiveMap.removeLayer(startMarker);
        if (finishMarker) archiveMap.removeLayer(finishMarker);

        if (points.length === 0) {
            archiveMap.setView([-6.200000, 106.816666], 13);
            return;
        }

        // Susun Pasangan Koordinat [Lat, Lng]
        const latLngs = points.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);

        // Gambar Garis Rute (Polyline Oranye)
        archivePolyline = L.polyline(latLngs, {
            color: '#f97316',
            weight: 5,
            opacity: 0.9,
            lineCap: 'round',
            lineJoin: 'round'
        }).addTo(archiveMap);

        // Marker Titik Start (Hijau)
        const startIcon = L.divIcon({
            html: `<div class="w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-lg"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        startMarker = L.marker(latLngs[0], { icon: startIcon }).addTo(archiveMap);

        // Marker Titik Finish (Merah)
        const finishIcon = L.divIcon({
            html: `<div class="w-4 h-4 bg-red-500 rounded-full border-2 border-white shadow-lg"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });
        finishMarker = L.marker(latLngs[latLngs.length - 1], { icon: finishIcon }).addTo(archiveMap);

        // Auto Zoom Fit Bounds (Peta otomatis menyesuaikan seluruh jangkauan rute)
        archiveMap.fitBounds(archivePolyline.getBounds(), { padding: [30, 30] });
    }

    function closeRouteModal() {
        document.getElementById('route-modal').classList.add('hidden');
    }
</script>
@endpush
@endsection