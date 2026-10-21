@extends('layouts.app')

@section('title', 'Arsip & Riwayat Aktivitas — WOYuk!')

@push('styles')
<!-- CDN Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    #recap-map {
        height: 320px;
        width: 100%;
        border-radius: 1rem;
    }
    
    .archive-card {
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .archive-card.hidden-filter {
        display: none !important;
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }

    .archive-container-fade {
        animation: fadeInSlide 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeInSlide {
        0% {
            opacity: 0;
            transform: translateY(14px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-num-btn {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .page-num-btn:hover:not(.active-page) {
        transform: translateY(-2px);
        background-color: rgba(31, 41, 55, 0.8);
    }
    .active-page {
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.3s ease;
        animation: pulsePill 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes pulsePill {
        0% { transform: scale(0.85); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }

    canvas {
        touch-action: none;
    }
</style>
@endpush

@section('content')
<!-- Navigation Bar Universal -->
@include('components.navbar')

<main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-32">

    <!-- HEADER SECTION & SMOOTH FILTER TABS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-800 pb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black italic uppercase text-white tracking-wider flex items-center gap-3">
                <span class="w-3 h-8 bg-orange-600 rounded-full inline-block"></span>
                Arsip & Riwayat Aktivitas
            </h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Rekapitulasi seluruh pencapaian olahraga dan jalur rute GPS kamu.</p>
        </div>

        <div class="flex items-center gap-1.5 bg-gray-950 p-1.5 rounded-2xl border border-gray-800 max-w-full overflow-x-auto" id="archive-filter-container">
            <button type="button" onclick="filterArchive('all', this)"
                class="filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ $sportFilter === 'all' || !$sportFilter ? 'bg-orange-500 text-black shadow-md' : 'text-gray-400 hover:text-white bg-transparent' }} cursor-pointer">
                Semua
            </button>
            <button type="button" onclick="filterArchive('running', this)"
                class="filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ $sportFilter === 'running' ? 'bg-orange-500 text-black shadow-md' : 'text-gray-400 hover:text-white bg-transparent' }} cursor-pointer">
                Lari
            </button>
            <button type="button" onclick="filterArchive('walking', this)"
                class="filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ $sportFilter === 'walking' ? 'bg-orange-500 text-black shadow-md' : 'text-gray-400 hover:text-white bg-transparent' }} cursor-pointer">
                Jalan
            </button>
            <button type="button" onclick="filterArchive('cycling', this)"
                class="filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 {{ $sportFilter === 'cycling' ? 'bg-orange-500 text-black shadow-md' : 'text-gray-400 hover:text-white bg-transparent' }} cursor-pointer">
                Bersepeda
            </button>
        </div>
    </div>

    <!-- RECAP SUMMARY CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card-dark p-6 rounded-3xl border border-gray-800/90 sport-glow">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL JARAK</div>
            <div class="text-3xl font-black italic text-orange-400 font-mono mt-1" id="sum-distance">
                {{ number_format($totalDistanceKm, 2, ',', '.') }} <span class="text-xs text-gray-400 font-normal">KM</span>
            </div>
        </div>
        <div class="glass-card-dark p-6 rounded-3xl border border-gray-800/90">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL KALORI</div>
            <div class="text-3xl font-black italic text-amber-400 font-mono mt-1" id="sum-calories">
                {{ number_format($totalCalories, 0, ',', '.') }} <span class="text-xs text-gray-400 font-normal">KCAL</span>
            </div>
        </div>
        <div class="glass-card-dark p-6 rounded-3xl border border-gray-800/90">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">TOTAL WAKTU AKTIF</div>
            <div class="text-3xl font-black italic text-emerald-400 font-mono mt-1" id="sum-time">
                {{ $totalHours }}J {{ $totalMinutes }}M
            </div>
        </div>
    </div>

    <!-- LIST OF ACTIVITIES CARDS -->
    <div class="glass-card-dark rounded-3xl border border-gray-800/90 overflow-hidden archive-container-fade" id="main-archive-wrapper">
        @if($activities->count() > 0)
            <div class="divide-y divide-gray-800/80" id="archive-list-container">
                @foreach($activities as $act)
                    <div class="archive-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-gray-800/20 transition-all duration-300" data-sport="{{ strtolower($act->sport_type) }}">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400 font-bold uppercase text-xs flex-shrink-0">
                                {{ substr($act->sport_type, 0, 3) }}
                            </div>
                            <div>
                                <div class="text-sm font-black italic uppercase text-white tracking-wide">
                                    {{ ucfirst($act->sport_type) }} — {{ $act->start_time ? Carbon\Carbon::parse($act->start_time)->timezone('Asia/Jakarta')->format('d M Y, H:i') : '' }} WIB
                                </div>
                                <div class="text-xs text-gray-400 font-mono mt-1">
                                   Durasi: <span class="text-gray-200 font-bold">{{ gmdate('H:i:s', $act->duration_seconds) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-6 text-center font-mono">
                            <div>
                                <div class="text-[9px] text-gray-500 uppercase">Jarak</div>
                                <div class="text-sm font-bold text-orange-400">{{ number_format($act->distance_meters / 1000, 2, ',', '.') }} KM</div>
                            </div>
                            <div>
                                <div class="text-[9px] text-gray-500 uppercase">Pace / Speed</div>
                                <div class="text-sm font-bold text-amber-400">
                                    {{ $act->sport_type === 'cycling' ? $act->avg_speed_kmh . ' KM/H' : ($act->avg_pace_min_km ?? '00:00') . ' MIN/KM' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[9px] text-gray-500 uppercase">Kalori</div>
                                <div class="text-sm font-bold text-emerald-400">{{ $act->calories_burned }} KCAL</div>
                            </div>
                        </div>

                        <!-- TOMBOL KEMBAR: LIHAT RUTE & BAGIKAN -->
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <button type="button" onclick="openRouteModal({{ $act->id }})"
                                class="flex-1 md:flex-none px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all border border-gray-700/80 shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Lihat Rute</span>
                            </button>

                            <button type="button" onclick="openShareModal({{ $act->id }})"
                                class="flex-1 md:flex-none px-4 py-2.5 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all border border-orange-500/50 shadow-md sport-glow cursor-pointer flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                </svg>
                                <span>Bagikan</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="no-filtered-data" class="hidden p-12 text-center text-gray-500 text-xs uppercase tracking-wider font-bold">
                Tidak ada riwayat untuk kategori olahraga ini.
            </div>

            <!-- CONTAINER PAGINASI KUSTOM -->
            @if($activities->hasPages())
                <div class="p-5 border-t border-gray-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs font-mono text-gray-400">
                        Menampilkan <span class="text-orange-400 font-bold">{{ $activities->firstItem() }}</span> - <span class="text-orange-400 font-bold">{{ $activities->lastItem() }}</span> dari <span class="text-white font-bold">{{ $activities->total() }}</span> aktivitas
                    </div>

                    <div class="inline-flex items-center p-1 bg-gray-950 border border-gray-800 rounded-2xl text-xs font-mono font-bold shadow-inner gap-1">
                        @if ($activities->onFirstPage())
                            <span class="px-3 py-1.5 rounded-xl text-gray-600 bg-gray-900/40 cursor-not-allowed select-none">&laquo;</span>
                        @else
                            <a href="{{ $activities->previousPageUrl() }}" onclick="animatePaginationNav(event, this.href)" class="page-num-btn px-3 py-1.5 rounded-xl text-gray-400 hover:text-white cursor-pointer">&laquo;</a>
                        @endif

                        @foreach ($activities->getUrlRange(1, $activities->lastPage()) as $page => $url)
                            @if ($page == $activities->currentPage())
                                <span class="active-page px-3.5 py-1.5 rounded-xl bg-orange-500 text-black font-black shadow-md">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" onclick="animatePaginationNav(event, this.href)" class="page-num-btn px-3.5 py-1.5 rounded-xl text-gray-400 hover:text-white cursor-pointer">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($activities->hasMorePages())
                            <a href="{{ $activities->nextPageUrl() }}" onclick="animatePaginationNav(event, this.href)" class="page-num-btn px-3.5 py-1.5 rounded-xl text-gray-400 hover:text-white cursor-pointer">&raquo;</a>
                        @else
                            <span class="px-3 py-1.5 rounded-xl text-gray-600 bg-gray-900/40 cursor-not-allowed select-none">&raquo;</span>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="p-12 text-center text-gray-500 text-xs uppercase tracking-wider font-bold">
                Belum ada riwayat aktivitas yang dicatat.
            </div>
        @endif
    </div>

</main>

<!-- MODAL 1: VISUALISASI PETA RUTE LEAFLET -->
<div id="route-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="glass-card-dark max-w-lg w-full p-6 sm:p-8 rounded-3xl border border-gray-800/90 space-y-5">
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 id="modal-title" class="text-lg font-black italic uppercase text-orange-400">Visualisasi Rute GPS</h3>
            <button type="button" onclick="closeRouteModal()" class="text-gray-400 hover:text-white text-xl font-bold px-2 cursor-pointer">&times;</button>
        </div>

        <div id="recap-map" class="border border-gray-800 rounded-2xl overflow-hidden"></div>

        <button type="button" onclick="closeRouteModal()"
            class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700/80 transition-colors cursor-pointer">
            Tutup
        </button>
    </div>
</div>

<!-- MODAL 2: KARTU PENCAPAIAN BAGIKAN (CANVAS COMPOSITE RESPONSIF DESKTOP) -->
<div id="share-modal" class="fixed inset-0 z-[9990] flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md hidden overflow-y-auto">
    <div class="glass-card-dark max-w-md md:max-w-2xl w-full p-4 sm:p-6 rounded-3xl border border-gray-800/90 space-y-4 my-auto shadow-2xl relative">
        
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-orange-400">
                Kartu Pencapaian Aktivitas
            </h3>

            <div class="flex items-center gap-2">
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
                <button type="button" onclick="closeShareModal()" class="text-gray-400 hover:text-white text-xl font-bold px-2 cursor-pointer">&times;</button>
            </div>
        </div>

        <!-- Grid 2 Kolom Responsif Desktop -->
        <div class="flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-6">
            
            <!-- KOLOM KIRI: CANVAS PREVIEW -->
            <div class="w-full md:w-5/12 flex flex-col items-center justify-center space-y-2">
                <div class="flex justify-center relative w-full">
                    <canvas id="composite-canvas" width="1080" height="1920" class="w-full max-w-[210px] sm:max-w-[230px] md:max-w-[210px] h-auto rounded-2xl shadow-2xl border border-gray-800 cursor-grab active:cursor-grabbing"></canvas>
                </div>
                <p class="text-[10px] text-gray-500 text-center">Geser & pinch zoom layer aktif!</p>
            </div>

            <!-- KOLOM KANAN: PENGATURAN & TOMBOL AKSI -->
            <div class="w-full md:w-7/12 flex flex-col justify-between space-y-3.5">
                <div class="space-y-2">
                    <!-- KONTROL LAYER ACTIVE SELECTOR MODAL -->
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

                    <!-- DUAL INPUT GALERI & KAMERA -->
                    <input type="file" id="bg-photo-input" accept="image/*" class="hidden" onchange="handleImageUpload(event)">
                    <input type="file" id="camera-photo-input" accept="image/*" capture="environment" class="hidden" onchange="handleImageUpload(event)">
                    
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="document.getElementById('camera-photo-input').click()" 
                            class="py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1 cursor-pointer shadow">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Kamera
                        </button>
                        <button type="button" onclick="document.getElementById('bg-photo-input').click()" 
                            class="py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-300 rounded-xl text-xs font-bold border border-gray-700 transition-all flex items-center justify-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Galeri
                        </button>
                        <button type="button" onclick="resetTransformations()" 
                            class="py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-400 hover:text-white rounded-xl text-xs font-bold border border-gray-700 transition-all cursor-pointer text-center" title="Reset Perbesaran/Posisi">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="space-y-2 pt-1 border-t border-gray-800/80">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Opsi Berbagi</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-2">
                        <button type="button" onclick="shareCardImage()" 
                            class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white font-bold py-3 rounded-xl uppercase tracking-wider text-xs transition-all sport-glow flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            Bagikan
                        </button>

                        <button type="button" onclick="downloadCardImage()" 
                            class="w-full bg-gray-900 hover:bg-gray-800 text-gray-300 font-bold py-3 rounded-xl uppercase tracking-wider text-xs border border-gray-700/80 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh Kartu
                        </button>
                    </div>
                </div>

                <div class="pt-1">
                    <button type="button" onclick="closeShareModal()" 
                        class="w-full py-2 bg-gray-900 hover:bg-gray-800 text-gray-400 hover:text-gray-200 font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-800 transition-colors cursor-pointer text-center block">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let recapMap = null;
    let recapPolyline = null;

    let currentShareActivity = null;
    let shareRoutePoints = [];
    let uploadedImage = null;
    
    let cardLogoImage = new Image();
    cardLogoImage.src = "{{ asset('images/logo.svg') }}";
    cardLogoImage.onload = function() { renderCompositeCard(); };

    let currentMode = 1;
    let activeLayer = 'photo';

    // Layer 1: Background Photo
    let imgScale = 1.0;
    let imgOffsetX = 0;
    let imgOffsetY = 0;

    // Layer 2: GPS Route
    let routeScale = 1.0;
    let routeOffsetX = 0;
    let routeOffsetY = 0;

    // Layer 3: Data Metrics
    let metricsScale = 1.0;
    let metricsOffsetX = 0;
    let metricsOffsetY = 0;

    let isDragging = false;
    let startX = 0, startY = 0;
    let initialPinchDist = 0;
    let initialScale = 1.0;

    document.addEventListener("DOMContentLoaded", function() {
        initCanvasGestures();
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

    function animatePaginationNav(e, targetUrl) {
        e.preventDefault();
        const wrapper = document.getElementById('main-archive-wrapper');

        if (wrapper) {
            wrapper.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                window.location.href = targetUrl;
            }, 250);
        } else {
            window.location.href = targetUrl;
        }
    }

    function filterArchive(sport, btnElement) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.className = "filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 text-gray-400 hover:text-white bg-transparent cursor-pointer";
        });
        btnElement.className = "filter-btn px-4 py-2 rounded-xl text-xs font-black uppercase transition-all duration-300 bg-orange-500 text-black shadow-md cursor-pointer";

        const wrapper = document.getElementById('main-archive-wrapper');
        if (wrapper) {
            wrapper.classList.remove('archive-container-fade');
            void wrapper.offsetWidth;
            wrapper.classList.add('archive-container-fade');
        }

        const cards = document.querySelectorAll('.archive-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardSport = card.getAttribute('data-sport');
            if (sport === 'all' || cardSport === sport) {
                card.classList.remove('hidden-filter');
                visibleCount++;
            } else {
                card.classList.add('hidden-filter');
            }
        });

        const noData = document.getElementById('no-filtered-data');
        if (noData) {
            if (visibleCount === 0) {
                noData.classList.remove('hidden');
            } else {
                noData.classList.add('hidden');
            }
        }
    }

    function openRouteModal(activityId) {
        document.getElementById('route-modal').classList.remove('hidden');

        if (!recapMap) {
            recapMap = L.map('recap-map', { zoomControl: false, attributionControl: false }).setView([-6.200000, 106.816666], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                subdomains: ['a', 'b', 'c']
            }).addTo(recapMap);
        }

        fetch('/api/activity/' + activityId + '/route', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'ngrok-skip-browser-warning': 'true'
            }
        })
        .then(async res => {
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return res.json();
            }
            const text = await res.text();
            throw new Error(text || 'Respon server bukan berupa format JSON.');
        })
        .then(data => {
            if (data.status === 'success' && data.points && data.points.length > 0) {
                const latLngs = data.points.map(pt => [parseFloat(pt.latitude), parseFloat(pt.longitude)]);

                if (recapPolyline) recapMap.removeLayer(recapPolyline);

                recapPolyline = L.polyline(latLngs, { color: '#f97316', weight: 5, opacity: 0.9 }).addTo(recapMap);
                recapMap.fitBounds(recapPolyline.getBounds(), { padding: [20, 20] });
                setTimeout(() => recapMap.invalidateSize(), 250);
            } else {
                alert(data.message || 'Tidak ada data koordinat rute untuk aktivitas ini.');
            }
        })
        .catch(err => {
            console.error('Error fetching route:', err);
            alert('Gagal mengambil data rute: ' + err.message);
        });
    }

    function closeRouteModal() {
        document.getElementById('route-modal').classList.add('hidden');
    }

    function openShareModal(activityId) {
        document.getElementById('share-modal').classList.remove('hidden');

        fetch('/api/activity/' + activityId + '/route', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'ngrok-skip-browser-warning': 'true'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                currentShareActivity = data.activity;
                shareRoutePoints = data.points || [];
                resetTransformations();
                renderCompositeCard();
            } else {
                alert(data.message || 'Gagal memuat detail aktivitas.');
            }
        })
        .catch(err => {
            console.error('Error fetching share data:', err);
            alert('Gagal mengambil data untuk bagikan kartu.');
        });
    }

    function closeShareModal() {
        document.getElementById('share-modal').classList.add('hidden');
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

        const act = currentShareActivity || {
            sport_name: 'LARI',
            sport_type: 'running',
            distance_km: '0,00',
            duration_str: '00:00:00',
            calories: 0,
            avg_speed_kmh: 0,
            pace_min: '00',
            pace_sec: '00',
            user_name: 'Atlet WOYuk!'
        };

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

        // NAMA USER & AKTIVITAS SEJAJAR (KIRI-KANAN)
        ctx.textAlign = "left";
        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 36px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText((act.user_name || 'Atlet WOYuk!').toUpperCase(), 80, 240);

        ctx.textAlign = "right";
        ctx.fillStyle = "#f97316";
        ctx.font = "bold uppercase 24px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(act.sport_name, 1000, 240);
        ctx.textAlign = "left";

        ctx.strokeStyle = "rgba(255, 255, 255, 0.15)";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(80, 280);
        ctx.lineTo(1000, 280);
        ctx.stroke();

        // LAYER 2: ROUTE GPS (JIKA MODE 2)
        if (currentMode === 2) {
            drawRoutePolylineOnCanvas(ctx, shareRoutePoints, 80, 320, 920, 980);
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
            ctx.fillText(act.distance_km + " km", col1X, startY + 50);

            // RITME / AVG SPEED
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.sport_type === 'cycling' ? "AVG SPEED" : "RITME", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            if (act.sport_type === 'cycling') {
                ctx.fillText(act.avg_speed_kmh + " km/h", col2X, startY + 50);
            } else {
                ctx.fillText(act.pace_min + ":" + act.pace_sec + " /km", col2X, startY + 50);
            }

            startY += 150;

            // SUWÉNÉ
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("SUWÉNÉ", col1X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.duration_str, col1X, startY + 50);

            // KOBONG
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("KOBONG", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 44px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.calories + " Cal", col2X, startY + 50);

        } else {
            const startY = 1440;

            // DAWANE
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("DAWANE", col1X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.distance_km + " km", col1X, startY + 45);

            // RITME / AVG SPEED
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.sport_type === 'cycling' ? "AVG SPEED" : "RITME", col2X, startY);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            if (act.sport_type === 'cycling') {
                ctx.fillText(act.avg_speed_kmh + " km/h", col2X, startY + 45);
            } else {
                ctx.fillText(act.pace_min + ":" + act.pace_sec + " /km", col2X, startY + 45);
            }

            const row2Y = startY + 120;

            // SUWÉNÉ
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("SUWÉNÉ", col1X, row2Y);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.duration_str, col1X, row2Y + 45);

            // KOBONG
            ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
            ctx.font = "bold 20px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText("KOBONG", col2X, row2Y);

            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 38px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.fillText(act.calories + " Cal", col2X, row2Y + 45);
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

        const actId = currentShareActivity ? currentShareActivity.id : 'arsip';

        canvas.toBlob(async (blob) => {
            if (!blob) {
                alert("Gagal memproses gambar kartu.");
                return;
            }

            const fileName = 'woyuk-activity-' + actId + '.png';
            const file = new File([blob], fileName, { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: 'Pencapaian Aktivitas WOYuk!',
                        text: 'Yuk intip rekap aktivitasku di WOYuk! 🚀',
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

        const actId = currentShareActivity ? currentShareActivity.id : 'arsip';
        const link = document.createElement('a');
        link.download = 'woyuk-activity-' + actId + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
</script>
@endpush
@endsection