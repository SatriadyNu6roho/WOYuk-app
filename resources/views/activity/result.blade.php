@extends('layouts.app')

@section('title', 'Hasil Aktivitas — WOYuk!')

@push('styles')
<style>
    #result-map {
        height: 260px;
        width: 100%;
        border-radius: 1.25rem;
        z-index: 10;
    }
    .leaflet-container {
        background: #030712 !important;
    }
    canvas {
        touch-action: none;
    }
</style>
@endpush

@php
    $rawTime = $activity->end_time ?? $activity->updated_at ?? now();
    $endTime = \Carbon\Carbon::parse($rawTime)->timezone('Asia/Jakarta');
    $hour = (int) $endTime->format('H');
    $minute = (int) $endTime->format('i');$totalMinutes = ($hour * 60) +$minute;

    if ($totalMinutes >= 180 && $totalMinutes <= 720) {$timeCategory = 'PAGI';
    } elseif ($totalMinutes >= 721 && $totalMinutes <= 1080) {$timeCategory = 'SORE';
    } else {
        $timeCategory = 'MALAM';
    }

    $sportName = strtoupper($sportConfig['name'] ?? 'LARI');$sessionTitle = $sportName . ' ' .$timeCategory;

    $paceSec =$activity->distance_meters > 0 ? ($activity->duration_seconds / ($activity->distance_meters / 1000)) : 0;
    $pMin = str_pad(floor($paceSec / 60), 2, '0', STR_PAD_LEFT);$pSec = str_pad(floor($paceSec % 60), 2, '0', STR_PAD_LEFT);$displayUserName = strtoupper(optional($user->profile)->name ?? $user->name ?? 'ATLET WOYUK!');
@endphp

@section('content')
<div class="min-h-screen bg-gray-950 text-white flex flex-col justify-between p-4 sm:p-6 max-w-lg mx-auto space-y-6">
    
    <!-- TOP BAR: HEADER HASIL AKTIVITAS -->
    <div class="glass-card-dark p-4 sm:p-5 rounded-3xl border border-gray-800/90 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-orange-600/20 border border-orange-500/40 flex items-center justify-center overflow-hidden p-1 shrink-0">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="Logo Icon" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Ringkasan Sesi</span>
                <span class="text-sm font-black italic uppercase text-orange-500">
                    {{ $sessionTitle }} RAMPUNG!
                </span>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 border border-gray-700/80 text-xs font-bold text-gray-300 transition-all">
            Dashboard
        </a>
    </div>

    <!-- BANNER CELEBRATION NOTIFICATION -->
    @if(isset($targetAchievedType) &&$targetAchievedType)
        <div class="bg-gradient-to-r from-amber-500/20 via-yellow-500/30 to-amber-500/20 border border-amber-500/60 p-4 rounded-3xl flex items-center justify-between text-amber-300 shadow-xl animate-bounce">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎉</span>
                <div>
                    <div class="text-xs font-black italic uppercase tracking-wider text-white">TARGET HARIAN TERCAPAI!</div>
                    <div class="text-[10px] text-amber-200">
                        @if($targetAchievedType === 'both')
                            Selamat! Target Jarak & Kalori harianmu berhasil ditembus dalam sesi ini!
                        @elseif($targetAchievedType === 'distance')
                            Selamat! Target Jarak harianmu berhasil ditembus dalam sesi ini!
                        @else
                            Selamat! Target Kalori harianmu berhasil ditembus dalam sesi ini!
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- PETA JALUR GPS HISTORI -->
    <div class="glass-card-dark p-2 rounded-3xl border border-gray-800/90 shadow-2xl relative overflow-hidden">
        <div id="result-map" class="border border-gray-800/80"></div>
    </div>

    <!-- METRIK HASIL AKTIVITAS -->
    <div class="space-y-4">
        <!-- Durasi Utama -->
        <div class="glass-card-dark py-4 px-6 rounded-3xl border border-gray-800/90 text-center space-y-1 sport-glow">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL DURASI</div>
            <div class="text-4xl sm:text-5xl font-black italic tracking-wider text-white font-mono">
                {{ gmdate('H:i:s', $activity->duration_seconds) }}
            </div>
        </div>

        <!-- Grid Stat 3 Kolom -->
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="glass-card-dark p-4 rounded-2xl border border-gray-800/90">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">JARAK TOTAL</div>
                <div class="text-2xl font-black italic text-orange-400 font-mono mt-1">
                    {{ number_format($activity->distance_meters / 1000, 2, ',', '.') }} KM
                </div>
            </div>

            <div class="glass-card-dark p-4 rounded-2xl border border-gray-800/90">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    {{ $activity->sport_type === 'cycling' ? 'KECEPATAN RATA-RATA' : 'PACE RATA-RATA' }}
                </div>
                <div class="text-2xl font-black italic text-amber-400 font-mono mt-1">
                    @if($activity->sport_type === 'cycling')
                        {{ number_format($activity->avg_speed_kmh, 1, '.', '') }} KM/H
                    @else
                        {{ $pMin }}:{{$pSec }} /KM
                    @endif
                </div>
            </div>

            <div class="glass-card-dark p-4 rounded-2xl border border-gray-800/90 col-span-2">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">KALORI TERBAKAR</div>
                <div class="text-2xl font-black italic text-emerald-400 font-mono mt-1">
                    {{ $activity->calories_burned }} KCAL
                </div>
            </div>
        </div>
    </div>

    <!-- AREA KARTU KOMPOSIT HTML5 CANVAS -->
    <div class="glass-card-dark p-5 rounded-3xl border border-gray-800/90 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-orange-400">
                Kartu Pencapaian
            </h3>

            <div class="flex items-center bg-gray-900/90 p-1 rounded-xl border border-gray-800 gap-1">
                <button type="button" onclick="setVisualMode(1)" id="btn-mode-1" 
                    class="px-3 py-1 rounded-lg text-xs font-bold transition-all bg-orange-600 text-white shadow">
                    1
                </button>
                <button type="button" onclick="setVisualMode(2)" id="btn-mode-2" 
                    class="px-3 py-1 rounded-lg text-xs font-bold transition-all text-gray-400 hover:text-white">
                    2
                </button>
            </div>
        </div>

        <!-- KONTROL LAYER ACTIVE SELECTOR -->
        <div class="space-y-2">
            <div class="flex items-center justify-between bg-gray-900/80 p-1.5 rounded-2xl border border-gray-800/80 text-[10px] font-bold uppercase tracking-wider">
                <span class="text-gray-400 pl-2">Layer Aktif:</span>
                <div class="flex items-center gap-1">
                    <button type="button" onclick="setActiveLayer('photo')" id="layer-btn-photo" class="px-2.5 py-1 rounded-xl bg-orange-500 text-black shadow">
                        Foto
                    </button>
                    <button type="button" onclick="setActiveLayer('route')" id="layer-btn-route" class="px-2.5 py-1 rounded-xl text-gray-400 hover:text-white hidden">
                        Rute
                    </button>
                    <button type="button" onclick="setActiveLayer('metrics')" id="layer-btn-metrics" class="px-2.5 py-1 rounded-xl text-gray-400 hover:text-white">
                        Metrik
                    </button>
                </div>
            </div>

            <!-- DUAL INPUT: GALERI & KAMERA LANGSUNG -->
            <input type="file" id="bg-photo-input" accept="image/*" class="hidden" onchange="handleImageUpload(event)">
            <input type="file" id="camera-photo-input" accept="image/*" capture="environment" class="hidden" onchange="handleImageUpload(event)">
            
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="document.getElementById('camera-photo-input').click()" 
                    class="py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kamera
                </button>

                <button type="button" onclick="document.getElementById('bg-photo-input').click()" 
                    class="py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-300 rounded-xl text-xs font-bold border border-gray-700 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Galeri
                </button>

                <button type="button" onclick="resetTransformations()" 
                    class="py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-400 hover:text-white rounded-xl text-xs font-bold border border-gray-700 transition-all cursor-pointer text-center" title="Reset Perbesaran/Posisi">
                    Reset
                </button>
            </div>
            <p class="text-[10px] text-gray-500 text-center">Geser & pinch-zoom elemen layer aktif di atas kartu!</p>
        </div>

        <div class="flex justify-center my-2 relative">
            <canvas id="composite-canvas" width="1080" height="1920" class="w-full max-w-[280px] h-auto rounded-2xl shadow-2xl border border-gray-800 cursor-grab active:cursor-grabbing"></canvas>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <button type="button" onclick="shareCardImage()" 
                class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-bold py-3.5 rounded-2xl uppercase tracking-wider text-xs transition-all sport-glow flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                Bagikan ke Story / Chat
            </button>

            <button type="button" onclick="downloadCardImage()" 
                class="w-full bg-gray-900 hover:bg-gray-800 text-gray-300 font-bold py-3.5 rounded-2xl uppercase tracking-wider text-xs border border-gray-700/80 transition-all flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh Kartu
            </button>
        </div>

        <a href="{{ route('dashboard') }}" 
            class="w-full bg-gray-900 hover:bg-gray-800 text-gray-400 hover:text-gray-200 font-bold py-3 rounded-2xl uppercase tracking-wider text-xs transition-all flex items-center justify-center gap-2 text-center block">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const routePoints = @json($points);
    let uploadedImage = null;
    
    let cardLogoImage = new Image();
    cardLogoImage.src = "{{ asset('images/logo.svg') }}";
    cardLogoImage.onload = function() { renderCompositeCard(); };

    let currentMode = 1;
    let activeLayer = 'photo';

    let imgScale = 1.0;
    let imgOffsetX = 0;
    let imgOffsetY = 0;

    let routeScale = 1.0;
    let routeOffsetX = 0;
    let routeOffsetY = 0;

    let metricsScale = 1.0;
    let metricsOffsetX = 0;
    let metricsOffsetY = 0;

    let isDragging = false;
    let startX = 0, startY = 0;
    let initialPinchDist = 0;
    let initialScale = 1.0;

    document.addEventListener("DOMContentLoaded", function() {
        initResultMap();
        initCanvasGestures();
        renderCompositeCard();
    });

    function setActiveLayer(layer) {
        activeLayer = layer;
        
        const btnPhoto = document.getElementById('layer-btn-photo');
        const btnRoute = document.getElementById('layer-btn-route');
        const btnMetrics = document.getElementById('layer-btn-metrics');

        const activeClass = "px-2.5 py-1 rounded-xl bg-orange-500 text-black font-bold shadow";
        const inactiveClass = "px-2.5 py-1 rounded-xl text-gray-400 hover:text-white";

        if (btnPhoto) btnPhoto.className = (layer === 'photo') ? activeClass : inactiveClass;
        if (btnRoute) btnRoute.className = (layer === 'route') ? activeClass : inactiveClass;
        if (btnMetrics) btnMetrics.className = (layer === 'metrics') ? activeClass : inactiveClass;
    }

    function setVisualMode(mode) {
        currentMode = mode;
        const btn1 = document.getElementById('btn-mode-1');
        const btn2 = document.getElementById('btn-mode-2');
        const btnRoute = document.getElementById('layer-btn-route');

        if (mode === 1) {
            btn1.className = "px-3 py-1 rounded-lg text-xs font-bold transition-all bg-orange-600 text-white shadow";
            btn2.className = "px-3 py-1 rounded-lg text-xs font-bold transition-all text-gray-400 hover:text-white";
            if (btnRoute) btnRoute.classList.add('hidden');
            if (activeLayer === 'route') setActiveLayer('photo');
        } else {
            btn2.className = "px-3 py-1 rounded-lg text-xs font-bold transition-all bg-orange-600 text-white shadow";
            btn1.className = "px-3 py-1 rounded-lg text-xs font-bold transition-all text-gray-400 hover:text-white";
            if (btnRoute) btnRoute.classList.remove('hidden');
        }

        renderCompositeCard();
    }

    function initResultMap() {
        const map = L.map('result-map', { zoomControl: false, attributionControl: false });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, subdomains: ['a', 'b', 'c']
        }).addTo(map);

        if (routePoints && routePoints.length > 0) {
            const latLngs = routePoints.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
            const polyline = L.polyline(latLngs, { color: '#f97316', weight: 5, opacity: 0.9 }).addTo(map);
            map.fitBounds(polyline.getBounds(), { padding: [30, 30] });
        } else {
            map.setView([-6.200000, 106.816666], 13);
        }
    }

    function handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                uploadedImage = img;
                imgScale = 1.0; imgOffsetX = 0; imgOffsetY = 0;
                setActiveLayer('photo');
                renderCompositeCard();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    function resetTransformations() {
        imgScale = 1.0; imgOffsetX = 0; imgOffsetY = 0;
        routeScale = 1.0; routeOffsetX = 0; routeOffsetY = 0;
        metricsScale = 1.0; metricsOffsetX = 0; metricsOffsetY = 0;
        renderCompositeCard();
    }

    function getActiveScale() {
        if (activeLayer === 'photo') return imgScale;
        if (activeLayer === 'route') return routeScale;
        return metricsScale;
    }

    function updateActiveScale(newScale) {
        if (activeLayer === 'photo') imgScale = newScale;
        else if (activeLayer === 'route') routeScale = newScale;
        else if (activeLayer === 'metrics') metricsScale = newScale;
    }

    function updateActiveOffset(deltaX, deltaY) {
        if (activeLayer === 'photo') { imgOffsetX += deltaX; imgOffsetY += deltaY; }
        else if (activeLayer === 'route') { routeOffsetX += deltaX; routeOffsetY += deltaY; }
        else if (activeLayer === 'metrics') { metricsOffsetX += deltaX; metricsOffsetY += deltaY; }
    }

    function initCanvasGestures() {
        const canvas = document.getElementById('composite-canvas');
        if (!canvas) return;

        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                isDragging = true; startX = e.touches[0].clientX; startY = e.touches[0].clientY;
            } else if (e.touches.length === 2) {
                isDragging = false; initialPinchDist = getTouchDistance(e.touches); initialScale = getActiveScale();
            }
        });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (e.touches.length === 1 && isDragging) {
                const deltaX = (e.touches[0].clientX - startX) * 2.5;
                const deltaY = (e.touches[0].clientY - startY) * 2.5;
                updateActiveOffset(deltaX, deltaY);
                startX = e.touches[0].clientX; startY = e.touches[0].clientY;
                renderCompositeCard();
            } else if (e.touches.length === 2) {
                const currentDist = getTouchDistance(e.touches);
                if (initialPinchDist > 0) {
                    const factor = currentDist / initialPinchDist;
                    const newScale = Math.min(Math.max(initialScale * factor, 0.4), 4.0);
                    updateActiveScale(newScale);
                    renderCompositeCard();
                }
            }
        }, { passive: false });

        canvas.addEventListener('touchend', () => { isDragging = false; initialPinchDist = 0; });
        canvas.addEventListener('mousedown', (e) => { isDragging = true; startX = e.clientX; startY = e.clientY; });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const deltaX = (e.clientX - startX) * 2.5;
            const deltaY = (e.clientY - startY) * 2.5;
            updateActiveOffset(deltaX, deltaY);
            startX = e.clientX; startY = e.clientY;
            renderCompositeCard();
        });

        window.addEventListener('mouseup', () => { isDragging = false; });
        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomDelta = e.deltaY < 0 ? 0.08 : -0.08;
            const currentScale = getActiveScale();
            const newScale = Math.min(Math.max(currentScale + zoomDelta, 0.4), 4.0);
            updateActiveScale(newScale);
            renderCompositeCard();
        }, { passive: false });
    }

    function getTouchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    function drawRoutePolylineOnCanvas(ctx, points, startX, startY, width, height) {
        if (!points || points.length < 2) return;

        let minLat = Infinity, maxLat = -Infinity;
        let minLng = Infinity, maxLng = -Infinity;

        points.forEach(p => {
            const lat = parseFloat(p.latitude);
            const lng = parseFloat(p.longitude);
            if (lat < minLat) minLat = lat; if (lat > maxLat) maxLat = lat;
            if (lng < minLng) minLng = lng; if (lng > maxLng) maxLng = lng;
        });

        const latDiff = maxLat - minLat || 0.0001;
        const lngDiff = maxLng - minLng || 0.0001;

        const padding = 80;
        const availWidth = width - (padding * 2);
        const availHeight = height - (padding * 2);

        const scaleX = availWidth / lngDiff;
        const scaleY = availHeight / latDiff;
        const baseScale = Math.min(scaleX, scaleY) * routeScale;

        const centerX = startX + (width / 2) + routeOffsetX;
        const centerY = startY + (height / 2) + routeOffsetY;

        const mapWidth = lngDiff * baseScale;
        const mapHeight = latDiff * baseScale;

        const offsetX = centerX - (mapWidth / 2);
        const offsetY = centerY - (mapHeight / 2);

        // Glow Line
        ctx.beginPath();
        points.forEach((p, index) => {
            const x = offsetX + (parseFloat(p.longitude) - minLng) * baseScale;
            const y = offsetY + (maxLat - parseFloat(p.latitude)) * baseScale;
            if (index === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        });
        ctx.strokeStyle = "rgba(249, 115, 22, 0.35)";
        ctx.lineWidth = 16; ctx.lineCap = "round"; ctx.lineJoin = "round";
        ctx.stroke();

        // Main Line
        ctx.beginPath();
        points.forEach((p, index) => {
            const x = offsetX + (parseFloat(p.longitude) - minLng) * baseScale;
            const y = offsetY + (maxLat - parseFloat(p.latitude)) * baseScale;
            if (index === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        });
        ctx.strokeStyle = "#f97316";
        ctx.lineWidth = 8; ctx.lineCap = "round"; ctx.lineJoin = "round";
        ctx.stroke();
    }

    function renderCompositeCard() {
        const canvas = document.getElementById('composite-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const bgGradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
        bgGradient.addColorStop(0, "#030712");
        bgGradient.addColorStop(0.5, "#0b0f19");
        bgGradient.addColorStop(1, "#030712");
        ctx.fillStyle = bgGradient;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // LAYER 1: PHOTO BACKGROUND
        if (uploadedImage) {
            const baseScale = Math.max(canvas.width / uploadedImage.width, canvas.height / uploadedImage.height) * imgScale;
            const drawW = uploadedImage.width * baseScale;
            const drawH = uploadedImage.height * baseScale;
            const x = (canvas.width / 2) - (drawW / 2) + imgOffsetX;
            const y = (canvas.height / 2) - (drawH / 2) + imgOffsetY;

            ctx.drawImage(uploadedImage, x, y, drawW, drawH);

            const overlayGradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
            overlayGradient.addColorStop(0, "rgba(3, 7, 18, 0.75)");
            overlayGradient.addColorStop(0.5, "rgba(3, 7, 18, 0.65)");
            overlayGradient.addColorStop(1, "rgba(3, 7, 18, 0.85)");
            ctx.fillStyle = overlayGradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        // HEADER BRANDING LOGO & TEXT
        if (cardLogoImage.complete && cardLogoImage.naturalWidth !== 0) {
            ctx.drawImage(cardLogoImage, 80, 65, 200, 50);
        } else {
            ctx.fillStyle = "#f97316";
            ctx.font = "bold italic 48px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("WOYuk!", 80, 110);
        }

        ctx.fillStyle = "rgba(255, 255, 255, 0.6)";
        ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("OBAH BARENG, KONSISTEN BENDINO", 80, 145);

        // NAMA USER & AKTIVITAS SEJAJAR
        ctx.textAlign = "left";
        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 36px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("{{ $displayUserName }}", 80, 240);

        ctx.textAlign = "right";
        ctx.fillStyle = "#f97316";
        ctx.font = "bold uppercase 24px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("{{ $sessionTitle }}", 1000, 240);
        ctx.textAlign = "left";

        ctx.strokeStyle = "rgba(255, 255, 255, 0.15)";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(80, 280);
        ctx.lineTo(1000, 280);
        ctx.stroke();

        // LAYER 2: ROUTE GPS (JIKA MODE 2)
        if (currentMode === 2) {
            drawRoutePolylineOnCanvas(ctx, routePoints, 80, 320, 920, 980);
        }

        // LAYER 3: DATA METRICS TRANSFORM GROUP
        ctx.save();
        const metricsCenterX = 540;
        const metricsCenterY = currentMode === 1 ? 550 : 1550;
        
        ctx.translate(metricsCenterX + metricsOffsetX, metricsCenterY + metricsOffsetY);
        ctx.scale(metricsScale, metricsScale);
        ctx.translate(-metricsCenterX, -metricsCenterY);

        const col1X = 80;
        const col2X = 560;

        if (currentMode === 1) {
            let startY = 380;
            
            // DAWANE
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("DAWANE", col1X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ number_format($activity->distance_meters / 1000, 2, ',', '.') }} km", col1X, startY + 50);

            // RITME / AVG SPEED
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ $activity->sport_type === 'cycling' ? 'AVG SPEED' : 'RITME' }}", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            @if($activity->sport_type === 'cycling')
                ctx.fillText("{{ number_format($activity->avg_speed_kmh, 1, '.', '') }} km/h", col2X, startY + 50);
            @else
                ctx.fillText("{{ $pMin }}:{{$pSec }} /km", col2X, startY + 50);
            @endif

            startY += 150;

            // SUWÉNÉ
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("SUWÉNÉ", col1X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ gmdate('H:i:s', $activity->duration_seconds) }}", col1X, startY + 50);

            // KOBONG
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("KOBONG", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ $activity->calories_burned }} Cal", col2X, startY + 50);

        } else {
            const startY = 1440;

            // DAWANE
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("DAWANE", col1X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ number_format($activity->distance_meters / 1000, 2, ',', '.') }} km", col1X, startY + 45);

            // RITME / AVG SPEED
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ $activity->sport_type === 'cycling' ? 'AVG SPEED' : 'RITME' }}", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            @if($activity->sport_type === 'cycling')
                ctx.fillText("{{ number_format($activity->avg_speed_kmh, 1, '.', '') }} km/h", col2X, startY + 45);
            @else
                ctx.fillText("{{ $pMin }}:{{$pSec }} /km", col2X, startY + 45);
            @endif

            const row2Y = startY + 120;

            // SUWÉNÉ
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("SUWÉNÉ", col1X, row2Y);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ gmdate('H:i:s', $activity->duration_seconds) }}", col1X, row2Y + 45);

            // KOBONG
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("KOBONG", col2X, row2Y);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("{{ $activity->calories_burned }} Cal", col2X, row2Y + 45);
        }
        ctx.restore();

        // Watermark Footer
        ctx.fillStyle = "rgba(255, 255, 255, 0.5)";
        ctx.font = "500 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("Direkam menggunakan woyuk.app", 80, 1840);

        ctx.fillStyle = "#f97316";
        ctx.font = "bold italic 24px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("#OBAHBARENG", 840, 1840);
    }

    async function shareCardImage() {
        const canvas = document.getElementById('composite-canvas');
        if (!canvas) return;

        canvas.toBlob(async (blob) => {
            if (!blob) {
                alert("Gagal memproses gambar kartu.");
                return;
            }

            const fileName = `woyuk-activity-{{ $activity->id }}.png`;
            const file = new File([blob], fileName, { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: 'Pencapaian Aktivitas WOYuk!',
                        text: 'Yuk intip pencapaian aktivitasku hari ini di WOYuk! 🚀',
                        files: [file]
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        downloadCardImage();
                    }
                }
            } else {
                downloadCardImage();
            }
        }, 'image/png');
    }

    function downloadCardImage() {
        const canvas = document.getElementById('composite-canvas');
        if (!canvas) return;

        const link = document.createElement('a');
        link.download = `woyuk-activity-{{ $activity->id }}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
</script>
@endpush