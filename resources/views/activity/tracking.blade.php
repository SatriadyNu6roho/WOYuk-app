@extends('layouts.app')

@section('title', 'Tracking Aktivitas — WOYuk!')

@push('styles')
<style>
    /* Styling khusus Peta Leaflet Realtime Dark Theme */
    #map {
        height: 240px;
        width: 100%;
        border-radius: 1.25rem;
        z-index: 10;
    }
    .leaflet-container {
        background: #030712 !important;
    }

    /* Marker Custom Lokasi Akurat Presisi */
    .user-location-marker {
        background-color: #f97316;
        border: 3px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 15px rgba(249, 115, 22, 0.9);
    }

    /* Keyframe Animasi Getaran (Shake) Alert Modal */
    @keyframes alertShake {
        0% { transform: scale(0.9) translateX(0); opacity: 0; }
        20% { transform: scale(1) translateX(-10px); opacity: 1; }
        40% { transform: scale(1) translateX(10px); }
        60% { transform: scale(1) translateX(-6px); }
        80% { transform: scale(1) translateX(6px); }
        100% { transform: scale(1) translateX(0); }
    }

    .animate-alert-shake {
        animation: alertShake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-950 text-white flex flex-col justify-between p-4 sm:p-6 max-w-lg mx-auto space-y-6">
    
    <!-- TOP BAR: Status, Sport Badge, & Tombol Navigasi Keluar KECIL -->
    <div class="glass-card-dark p-4 sm:p-5 rounded-3xl border border-gray-800/90 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-pulse shadow-md shadow-emerald-500/50"></span>
            <div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Mode Olahraga</span>
                <span class="text-sm font-black italic uppercase text-orange-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-orange-500 rounded-full inline-block"></span>
                    {{ $sportConfig['name'] }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right border-l border-gray-800/80 pl-3 sm:pl-4">
                <span class="text-[10px] text-gray-400 uppercase font-mono block">Status GPS</span>
                <span id="gps-status" class="text-xs font-bold text-amber-400 uppercase font-mono animate-pulse">
                    GOLEKI GPS...
                </span>
            </div>

            <!-- NAVIGASI KECIL TOMBOL KELUAR / BATALKAN AKTIVITAS -->
            <button type="button" onclick="openCancelModal()" data-no-loader="true" title="Batalkan / Keluar" 
                class="w-9 h-9 rounded-2xl bg-red-600/20 hover:bg-red-600/40 border border-red-500/40 text-red-400 flex items-center justify-center transition-all cursor-pointer active:scale-95 shrink-0 ml-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- CONTAINER PETA INTERAKTIF REALTIME -->
    <div class="glass-card-dark p-2 rounded-3xl border border-gray-800/90 shadow-2xl relative overflow-hidden">
        <div id="map" class="border border-gray-800/80"></div>
    </div>

    <!-- LIVE METRICS DISPLAY (3 METRIK) -->
    <div class="py-2 space-y-6 text-center">
        
        <!-- Primary Indicator: Duration / Timer -->
        <div class="glass-card-dark py-4 px-6 rounded-3xl border border-gray-800/90 space-y-1 sport-glow">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL DURASI</div>
            <div class="text-5xl sm:text-6xl font-black italic tracking-wider text-white font-mono drop-shadow-md" id="live-timer">
                00:00:00
            </div>
        </div>

        <!-- Secondary Indicators Grid (Jarak, Pace/Kecepatan, Kalori) -->
        <div class="grid grid-cols-2 gap-3">
            
            <!-- Distance -->
            <div class="glass-card-dark p-4 rounded-3xl border border-gray-800/90">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">JARAK (KM)</div>
                <div class="text-3xl font-black italic text-orange-400 font-mono mt-1 drop-shadow-sm" id="live-distance">
                    0,00
                </div>
            </div>

            <!-- Primary Metric (Pace / Speed) -->
            <div class="glass-card-dark p-4 rounded-3xl border border-gray-800/90">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ $sportConfig['primary_metric'] === 'speed' ? 'KECEPATAN (KM/H)' : 'PACE (MIN/KM)' }}
                </div>
                <div class="text-3xl font-black italic text-amber-400 font-mono mt-1 drop-shadow-sm" id="live-metric">
                    {{ $sportConfig['primary_metric'] === 'speed' ? '0.0' : '00:00' }}
                </div>
            </div>

            <!-- Calories (Full Width di bawahnya) -->
            <div class="glass-card-dark p-4 rounded-3xl border border-gray-800/90 col-span-2">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">KALORI TERBAKAR</div>
                <div class="text-3xl font-black italic text-emerald-400 font-mono mt-1 drop-shadow-sm" id="live-calories">
                    0 KCAL
                </div>
            </div>

        </div>

    </div>

    <!-- BOTTOM CONTROL BAR -->
    <div class="glass-card-dark p-5 rounded-3xl border border-gray-800/90 space-y-4">
        <p class="text-[11px] text-gray-400 text-center leading-tight">
            Ojo mandek <strong class="text-orange-400">Nek durung menang</strong>.
        </p>

        <!-- TOMBOL HENTIKAN AKTIVITAS -->
        <button type="button" onclick="openStopModal()" data-no-loader="true"
            class="w-full bg-red-600 hover:bg-red-500 text-white font-bold py-4 rounded-2xl uppercase tracking-wider text-xs transition-all shadow-lg shadow-red-600/30 border border-red-500/50 flex items-center justify-center gap-2 cursor-pointer active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
            Hentikan Aktivitas
        </button>
    </div>

</div>

<!-- MODAL CANCEL -->
<div id="cancel-activity-modal" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden transition-all">
    <div id="cancel-modal-card" class="glass-card-dark max-w-sm w-full p-6 sm:p-8 rounded-3xl border border-gray-800 shadow-2xl space-y-5 text-center relative">
        <div class="w-14 h-14 rounded-2xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-black italic uppercase text-white tracking-wider">YAKIN METU?</h3>
            <p class="text-xs text-gray-300 mt-2 leading-relaxed">Yen sampeyan metu saiki, aktivitas olahraga iki bakal dibatalkan lan data ora bakal tersimpan.</p>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="button" onclick="closeCancelModal()" class="w-1/2 py-3 bg-gray-900 hover:bg-gray-800 text-gray-300 font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700 transition-colors cursor-pointer">TIDAK</button>
            <a href="{{ route('dashboard') }}" class="w-1/2 py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg shadow-red-600/30 cursor-pointer text-center block">YA, METU</a>
        </div>
    </div>
</div>

<!-- MODAL STOP -->
<div id="stop-activity-modal" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden transition-all">
    <div id="stop-modal-card" class="glass-card-dark max-w-sm w-full p-6 sm:p-8 rounded-3xl border border-gray-800 shadow-2xl space-y-5 text-center relative">
        <div class="w-14 h-14 rounded-2xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400 mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-xl font-black italic uppercase text-white tracking-wider">AKHIRI AKTIVITAS?</h3>
            <p class="text-xs text-gray-300 mt-2 leading-relaxed">Apakah sampeyan yakin ingin mengakhiri lan menyimpan sesi olahraga iki?</p>
        </div>
        <form id="stop-activity-form" action="{{ route('activity.stop', $activeActivity->id) }}" method="POST" class="pt-2">
            @csrf
            <div class="flex items-center gap-3">
                <button type="button" onclick="closeStopModal()" class="w-1/2 py-3 bg-gray-900 hover:bg-gray-800 text-gray-300 font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700 transition-colors cursor-pointer">TIDAK</button>
                <button type="submit" class="w-1/2 py-3 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all sport-glow cursor-pointer text-center block">YA, RAMPUNG</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let activeMap = null;
    let userMarker = null;
    let polylineRoute = null;
    let pathCoordinates = [];
    let startTimeMillis = {{ \Carbon\Carbon::parse($activeActivity->start_time)->getTimestamp() * 1000 }};

    let totalDistanceMeters = 0;
    let lastPosition = null;
    let pendingPoints = [];
    let elapsedSeconds = 0;
    let isSyncing = false;
    let primaryMetric = "{{ $sportConfig['primary_metric'] }}";
    let initialFixCounter = 0;

    document.addEventListener("DOMContentLoaded", function() {
        initTrackingMap();
        startLiveTimer();
        startAutoSync();
        setupStopFormSync();
    });

    function getHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function openCancelModal() {
        const modal = document.getElementById('cancel-activity-modal');
        const card = document.getElementById('cancel-modal-card');
        modal.classList.remove('hidden');
        card.classList.remove('animate-alert-shake');
        void card.offsetWidth;
        card.classList.add('animate-alert-shake');
    }

    function closeCancelModal() {
        document.getElementById('cancel-activity-modal').classList.add('hidden');
    }

    function openStopModal() {
        const modal = document.getElementById('stop-activity-modal');
        const card = document.getElementById('stop-modal-card');
        modal.classList.remove('hidden');
        card.classList.remove('animate-alert-shake');
        void card.offsetWidth;
        card.classList.add('animate-alert-shake');
    }

    function closeStopModal() {
        document.getElementById('stop-activity-modal').classList.add('hidden');
    }

    function initTrackingMap() {
        activeMap = L.map('map', { zoomControl: false, attributionControl: false }).setView([-6.200000, 106.816666], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, subdomains: ['a', 'b', 'c']
        }).addTo(activeMap);

        polylineRoute = L.polyline([], { color: '#f97316', weight: 5, opacity: 0.9, lineCap: 'round' }).addTo(activeMap);

        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = Math.round(position.coords.accuracy);
                    const speed = position.coords.speed || 0;
                    const timestamp = new Date().toISOString();

                    const gpsStatusEl = document.getElementById('gps-status');
                    if (gpsStatusEl) {
                        if (accuracy <= 25) {
                            gpsStatusEl.innerText = `GPS AKTIF (${accuracy}M)`;
                            gpsStatusEl.className = "text-xs font-bold text-emerald-400 uppercase font-mono";
                        } else {
                            gpsStatusEl.innerText = `GPS LEMAH (${accuracy}M)`;
                            gpsStatusEl.className = "text-xs font-bold text-amber-400 uppercase font-mono";
                        }
                    }

                    const newLatLng = [lat, lng];

                    if (initialFixCounter < 2) {
                        initialFixCounter++;
                        lastPosition = { lat, lng };
                        activeMap.setView(newLatLng, 17);
                        const userIcon = L.divIcon({ className: 'user-location-marker', iconSize: [16, 16], iconAnchor: [8, 8] });
                        if (userMarker) userMarker.setLatLng(newLatLng);
                        else userMarker = L.marker(newLatLng, { icon: userIcon }).addTo(activeMap);
                        return;
                    }

                    if (lastPosition) {
                        const dist = getHaversineDistance(lastPosition.lat, lastPosition.lng, lat, lng);
                        if (accuracy <= 30 && dist >= 3.5 && dist > (accuracy * 0.35)) {
                            totalDistanceMeters += dist;
                            lastPosition = { lat, lng };
                            pathCoordinates.push(newLatLng);
                            polylineRoute.setLatLngs(pathCoordinates);
                        }
                    } else {
                        lastPosition = { lat, lng };
                        pathCoordinates.push(newLatLng);
                        polylineRoute.setLatLngs(pathCoordinates);
                    }

                    pendingPoints.push({ latitude: lat, longitude: lng, speed: speed, accuracy: accuracy, timestamp: timestamp });

                    activeMap.setView(newLatLng, 17);
                    const userIcon = L.divIcon({ className: 'user-location-marker', iconSize: [16, 16], iconAnchor: [8, 8] });
                    if (userMarker) userMarker.setLatLng(newLatLng);
                    else userMarker = L.marker(newLatLng, { icon: userIcon }).addTo(activeMap);

                    updateLiveUI();
                },
                function(error) {
                    const gpsStatusEl = document.getElementById('gps-status');
                    if (gpsStatusEl) {
                        gpsStatusEl.innerText = "GPS OFF / UNKNOWN";
                        gpsStatusEl.className = "text-xs font-bold text-red-400 uppercase font-mono";
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }

    function updateLiveUI() {
        const distKm = totalDistanceMeters / 1000;
        const liveDistEl = document.getElementById('live-distance');
        if (liveDistEl) liveDistEl.innerText = distKm.toFixed(2).replace('.', ',');

        const liveMetricEl = document.getElementById('live-metric');
        if (liveMetricEl) {
            if (primaryMetric === 'speed') {
                const speedKmh = elapsedSeconds > 0 ? (distKm / (elapsedSeconds / 3600)) : 0;
                liveMetricEl.innerText = speedKmh.toFixed(1);
            } else {
                if (distKm > 0 && elapsedSeconds > 0) {
                    const secPerKm = elapsedSeconds / distKm;
                    const pMin = String(Math.floor(secPerKm / 60)).padStart(2, '0');
                    const pSec = String(Math.floor(secPerKm % 60)).padStart(2, '0');
                    liveMetricEl.innerText = `${pMin}:${pSec}`;
                } else {
                    liveMetricEl.innerText = '00:00';
                }
            }
        }
    }

    function startLiveTimer() {
        setInterval(() => {
            const now = Date.now();
            elapsedSeconds = Math.floor((now - startTimeMillis) / 1000);
            const hours = String(Math.floor(elapsedSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((elapsedSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(elapsedSeconds % 60).padStart(2, '0');

            const timerEl = document.getElementById('live-timer');
            if (timerEl) timerEl.innerText = `${hours}:${minutes}:${seconds}`;

            updateLiveUI();
        }, 1000);
    }

    function startAutoSync() {
        setInterval(() => { syncDataToBackend(); }, 8000);
    }

    async function syncDataToBackend() {
        if (isSyncing) return;
        isSyncing = true;
        const pointsToSend = [...pendingPoints];
        pendingPoints = [];

        try {
            const response = await fetch("{{ route('activity.sync', $activeActivity->id) }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
                body: JSON.stringify({ points: pointsToSend, total_distance_meters: totalDistanceMeters, duration_seconds: elapsedSeconds })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'success') {
                    const calEl = document.getElementById('live-calories');
                    if (calEl && data.calories_burned !== undefined) {
                        calEl.innerText = `${data.calories_burned} KCAL`;
                    }
                }
            } else {
                pendingPoints = [...pointsToSend, ...pendingPoints];
            }
        } catch (e) {
            pendingPoints = [...pointsToSend, ...pendingPoints];
        } finally {
            isSyncing = false;
        }
    }

    function setupStopFormSync() {
        const stopForm = document.getElementById('stop-activity-form');
        if (stopForm) {
            stopForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                await syncDataToBackend();
                stopForm.submit();
            });
        }
    }
</script>
@endpush
@endsection