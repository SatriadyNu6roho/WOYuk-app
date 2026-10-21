@extends('layouts.app')

@section('title', 'Dashboard — WOYuk!')

@push('styles')
<style>
    @keyframes spin-ring {
        0% { transform: rotate(0deg) scale(0.95); }
        50% { transform: rotate(180deg) scale(1.05); }
        100% { transform: rotate(360deg) scale(1); }
    }
    .animate-spin-ring {
        animation: spin-ring 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes alertShake {
        0% { transform: scale(0.9) translateX(0); opacity: 0; }
        20% { transform: scale(1) translateX(-8px); opacity: 1; }
        40% { transform: scale(1) translateX(8px); }
        60% { transform: scale(1) translateX(-5px); }
        80% { transform: scale(1) translateX(5px); }
        100% { transform: scale(1) translateX(0); }
    }
    .animate-alert-shake {
        animation: alertShake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    }

    /* Glow Elements & Sport Accents */
    .glow-running { box-shadow: 0 0 35px -5px rgba(6, 182, 212, 0.4); }
    .glow-walking { box-shadow: 0 0 35px -5px rgba(16, 185, 129, 0.4); }
    .glow-cycling { box-shadow: 0 0 35px -5px rgba(249, 115, 22, 0.4); }

    .glow-gold-target {
        box-shadow: 0 0 30px 2px rgba(245, 158, 11, 0.5);
        border-color: rgba(245, 158, 11, 0.8) !important;
    }

    .accordion-content {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out, margin 0.3s ease;
    }
    .accordion-content.open {
        max-height: 600px;
        opacity: 1;
        margin-top: 1rem;
    }

    /* Glitter particle effect animation */
    @keyframes glitterSparkle {
        0%, 100% { opacity: 0; transform: scale(0.3) rotate(0deg); }
        50% { opacity: 1; transform: scale(1.2) rotate(180deg); }
    }
    .glitter-sparkle {
        position: absolute;
        pointer-events: none;
        animation: glitterSparkle 1.8s infinite ease-in-out;
    }
</style>
@endpush

@section('content')
@include('components.navbar')

<!-- ALERT FLOATING MINIMALIS -->
@if(session('warning') || session('errors') || session('success'))
    @php
        $alertType = session('success') ? 'success' : (session('warning') ? 'warning' : 'error');$alertMessage = session('success') ?? (session('warning') ?? session('errors')->first());
    @endphp

    <div id="flash-alert" class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] w-full max-w-md px-4 transition-all duration-500 ease-in-out">
        <div class="p-4 rounded-2xl border backdrop-blur-md shadow-2xl flex items-center justify-between gap-3 animate-alert-shake
            {{ $alertType === 'success' ? 'bg-emerald-950/90 border-emerald-500/50 text-emerald-300' : '' }}
            {{ $alertType === 'warning' ? 'bg-amber-950/90 border-amber-500/50 text-amber-300' : '' }} {{$alertType === 'error' ? 'bg-red-950/90 border-red-500/50 text-red-300' : '' }}">
            
            <div class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $alertMessage }}</span>
            </div>

            <button type="button" onclick="closeFlashAlert()" class="text-gray-400 hover:text-white transition-colors cursor-pointer text-base font-bold">
                &times;
            </button>
        </div>
    </div>
@endif

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8 sm:space-y-10 pb-44 relative">

    <!-- CANVAS GLITTER UNTUK CELEBRATION EFFECT -->
    <canvas id="glitter-canvas" class="fixed inset-0 pointer-events-none z-[9995]"></canvas>

    <div class="flex items-center justify-center sm:justify-start gap-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse shadow-md"></span>
        <span class="text-xs font-black uppercase tracking-widest text-emerald-400 font-mono">Sistem Siap Tracking</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <div class="lg:col-span-6 flex flex-col justify-center text-center sm:text-left space-y-2 sm:space-y-3">
            <h1 class="text-xl sm:text-3xl font-black italic uppercase tracking-wider text-white leading-tight">
                Sugeng Rawuh, <span id="user-display-name" class="text-cyan-400 transition-colors duration-300 inline">{{ $user->profile->name ?? 'Atlet' }}</span>!
            </h1>
            <p class="text-xs sm:text-sm font-semibold text-gray-400 leading-relaxed">
                PILIH AKTIVITAS, URUPNO GPS, LAN MULAI OBAH DINA IKI.
            </p>
        </div>

        <div class="lg:col-span-6">
            <div class="glass-card-dark p-4 sm:p-5 rounded-3xl border border-gray-800/90 flex items-center justify-between shadow-xl h-full">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div id="streak-icon-bg" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg transition-all duration-500 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">STREAK AKTIF</div>
                        <div class="text-xl sm:text-2xl font-black text-white italic tracking-wider font-mono">
                            {{ $user->streak->current_streak ?? 0 }} <span class="text-[10px] sm:text-xs font-normal text-cyan-400 uppercase">HARI</span>
                        </div>
                    </div>
                </div>
                <div class="text-right border-l border-gray-800/80 pl-4 sm:pl-6">
                    <div class="text-[8px] sm:text-[9px] text-gray-500 uppercase tracking-wider">REKOR TERTINGGI</div>
                    <div class="text-xs sm:text-sm font-bold text-gray-200 font-mono">{{ $user->streak->longest_streak ?? 0 }} HARI</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
        
        <!-- HERO CARD TRACKING -->
        <div class="lg:col-span-6 glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800/90 text-center relative overflow-hidden transition-all duration-500" id="hero-card">
            <div class="space-y-5 sm:space-y-6 relative z-10">
                <div>
                    <span id="selected-sport-label" class="inline-block px-4 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-xs font-bold uppercase tracking-widest transition-all duration-300">
                        MODE: LARI
                    </span>
                </div>

                <form action="{{ route('activity.start') }}" method="POST" id="main-tracking-form">
                    @csrf
                    <input type="hidden" name="sport_type" id="main-sport-type" value="running">

                    <div class="relative w-40 h-40 sm:w-48 sm:h-48 mx-auto flex items-center justify-center">
                        <div id="hero-button-ring" class="absolute inset-0 rounded-full border-2 border-cyan-400/30 transition-all duration-500 pointer-events-none"></div>
                        
                        <button type="submit" id="hero-start-btn"
                            class="w-36 h-36 sm:w-44 sm:h-44 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-500 hover:from-cyan-500 hover:to-blue-400 text-white font-black italic text-xl sm:text-2xl uppercase tracking-widest shadow-2xl glow-running border-4 border-cyan-300/30 transition-all duration-500 active:scale-95 hover:scale-105 flex items-center justify-center cursor-pointer">
                            MULAI
                        </button>
                    </div>
                </form>

                <p class="text-xs text-gray-300 max-w-xs mx-auto">
                    LORO ATI?<strong class="text-white"> TOMBONE YO WO!</strong>
                </p>
            </div>
        </div>

        <!-- PROGRESS METRICS CARD DENGAN DYNAMIC TARGET CELEBRATION -->
        <div class="lg:col-span-6 glass-card-dark p-5 sm:p-6 rounded-3xl border border-gray-800/90 space-y-4 relative overflow-hidden {{ $targetAchievedType ? 'glow-gold-target' : '' }}" id="target-metrics-card">
            
            <!-- BADGE TARGET TERCAPAI JIKA MEMENUHI SYARAT -->
            @if($targetAchievedType)
                <div class="bg-gradient-to-r from-amber-500/20 via-yellow-500/30 to-amber-500/20 border border-amber-500/60 p-3 rounded-2xl flex items-center justify-between text-amber-300 shadow-lg animate-pulse">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">🏆</span>
                        <div>
                            <div class="text-xs font-black italic uppercase tracking-wider text-white">TARGET TERCAPAI!</div>
                            <div class="text-[10px] text-amber-200 font-medium">
                                @if($targetAchievedType === 'both')
                                    Target Jarak & Kalori Sampeyan Dino Iki Rampung Penuh!
                                @elseif($targetAchievedType === 'distance')
                                    Target Jarak Sampeyan Dino Iki Berhasil Dilampaui!
                                @else
                                    Target Kalori Sampeyan Dino Iki Berhasil Dilampaui!
                                @endif
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="triggerCelebrationAnimation('{{ $targetAchievedType }}')" 
                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-black font-black text-[10px] uppercase rounded-xl shadow-md transition-all cursor-pointer">
                        SELEBRASI
                    </button>
                </div>
            @endif

            <div class="flex items-center justify-between p-2 rounded-2xl bg-gray-900/30">
                <button type="button" onclick="toggleMetricsAccordion()" class="flex items-center gap-3 text-left focus:outline-none group">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gray-900 border border-gray-800 flex items-center justify-center text-cyan-400 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-black italic uppercase text-white tracking-wider">TARGET HARI INI</div>
                        <div class="text-[10px] text-gray-400">DETAIL PENCAPAIAN</div>
                    </div>
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openTargetModal()" title="Atur Target Harian" class="p-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-cyan-400 border border-gray-700 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>

                    <div id="accordion-arrow" onclick="toggleMetricsAccordion()" class="w-8 h-8 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-300 hover:text-white transition-transform duration-300 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Preview Ringkas -->
            <div id="metrics-preview" class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-800/60 text-xs font-mono">
                <div class="flex flex-col">
                    <span class="text-[10px] text-gray-400 uppercase">JARAK</span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="font-bold text-cyan-400">{{ $todayDistanceKm }} / {{$targetDistanceKm }} KM</span>
                        @if($isDistanceAchieved)
                            <span class="text-xs text-amber-400" title="Target Jarak Tercapai">✓</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-gray-400 uppercase">KALORI</span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="font-bold text-amber-400">{{ $todayCalories }} / {{$targetCalories }} KCAL</span>
                        @if($isCaloriesAchieved)
                            <span class="text-xs text-amber-400" title="Target Kalori Tercapai">✓</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Accordion Content -->
            <div id="metrics-accordion" class="accordion-content space-y-6">
                <!-- Progress Jarak -->
                <div class="space-y-2 bg-gray-950/60 p-4 rounded-2xl border {{ $isDistanceAchieved ? 'border-amber-500/60 bg-amber-950/10' : 'border-gray-800/80' }}">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                        <span class="text-gray-200 flex items-center gap-1.5">
                            DISTANCE TODAY
                            @if($isDistanceAchieved)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40">TERCAPAI</span>
                            @endif
                        </span>
                        <span id="progress-distance-text" class="text-cyan-400 font-mono text-sm">{{ $todayDistanceKm }} / {{$targetDistanceKm }} KM</span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-800">
                        <div id="progress-distance-bar" class="bg-gradient-to-r {{ $isDistanceAchieved ? 'from-amber-500 to-yellow-400' : 'from-cyan-600 to-blue-400' }} h-full rounded-full transition-all duration-500"
                            style="width: {{ $distanceProgressPercent }}%;"></div>
                    </div>
                    <div class="text-right text-[10px] text-gray-300 font-mono">{{ $distanceProgressPercent }}% Target Terpenuhi</div>
                </div>

                <!-- Progress Kalori -->
                <div class="space-y-2 bg-gray-950/60 p-4 rounded-2xl border {{ $isCaloriesAchieved ? 'border-amber-500/60 bg-amber-950/10' : 'border-gray-800/80' }}">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                        <span class="text-gray-200 flex items-center gap-1.5">
                            CALORIES TODAY
                            @if($isCaloriesAchieved)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40">TERCAPAI</span>
                            @endif
                        </span>
                        <span id="progress-calories-text" class="text-amber-400 font-mono text-sm">{{ $todayCalories }} / {{$targetCalories }} KCAL</span>
                    </div>
                    <div class="w-full bg-gray-900 h-3 rounded-full overflow-hidden p-0.5 border border-gray-800">
                        <div id="progress-calories-bar" class="bg-gradient-to-r from-amber-500 to-yellow-400 h-full rounded-full transition-all duration-500"
                            style="width: {{ $caloriesProgressPercent }}%;"></div>
                    </div>
                    <div class="text-right text-[10px] text-gray-300 font-mono">{{ $caloriesProgressPercent }}% Target Terpenuhi</div>
                </div>
            </div>

        </div>

    </div>

</main>

<!-- FLOATING SPORT SELECTOR -->
<div class="fixed bottom-20 md:bottom-6 inset-x-0 mx-auto z-40 w-max max-w-[95vw]">
    <div class="bg-gray-950/90 backdrop-blur-xl p-1.5 rounded-2xl border border-gray-800/90 shadow-2xl">
        <div class="flex items-center justify-center gap-1.5 text-xs font-bold uppercase tracking-wider" id="sport-selector">

            <button type="button" onclick="selectSport('running')" id="tab-running"
                class="sport-tab py-2.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-white font-black bg-cyan-600 shadow-md cursor-pointer">
                <svg id="icon-running" class="w-4 h-4 flex-shrink-0 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="tab-label inline text-xs">Lari</span>
            </button>

            <button type="button" onclick="selectSport('walking')" id="tab-walking"
                class="sport-tab py-2.5 px-3 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-white font-black bg-transparent hover:bg-gray-800/50 cursor-pointer">
                <svg id="icon-walking" class="w-4 h-4 flex-shrink-0 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span class="tab-label hidden text-xs">Jalan</span>
            </button>

            <button type="button" onclick="selectSport('cycling')" id="tab-cycling"
                class="sport-tab py-2.5 px-3 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-white font-black bg-transparent hover:bg-gray-800/50 cursor-pointer">
                <svg id="icon-cycling" class="w-4 h-4 flex-shrink-0 text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="tab-label hidden text-xs">Bersepeda</span>
            </button>

        </div>
    </div>
</div>

<!-- MODAL UPDATE TARGET -->
<div id="target-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden">
    <div class="glass-card-dark max-w-md w-full p-6 rounded-3xl border border-gray-800 space-y-5">
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 class="text-base font-black italic uppercase text-white tracking-wider">Atur Target Harian</h3>
            <button type="button" onclick="closeTargetModal()" class="text-gray-400 hover:text-white text-xl font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('target.update') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase mb-1">Target Jarak (KM)</label>
                <input type="number" step="0.1" min="0.1" max="500" name="daily_distance_km" value="{{ $targetDistanceKm }}" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase mb-1">Target Kalori (KCAL)</label>
                <input type="number" min="10" max="10000" name="daily_calories" value="{{ $targetCalories }}" required
                    class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white font-mono focus:outline-none focus:border-amber-500">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeTargetModal()" class="px-4 py-2 bg-gray-800 text-gray-300 rounded-xl text-xs font-bold uppercase cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold uppercase cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let activeSport = 'running';
    const targetAchievedType = @json($targetAchievedType);

    const sportThemes = {
        running: {
            label: 'MODE: LARI',
            btnBg: 'bg-gradient-to-tr from-cyan-600 to-blue-500 hover:from-cyan-500 hover:to-blue-400',
            glowClass: 'glow-running',
            borderColor: 'border-cyan-300/30',
            badgeClass: 'bg-cyan-500/10 border-cyan-500/30 text-cyan-400',
            activeTabBg: 'bg-cyan-600 shadow-md',
            nameColor: 'text-cyan-400'
        },
        walking: {
            label: 'MODE: JALAN',
            btnBg: 'bg-gradient-to-tr from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400',
            glowClass: 'glow-walking',
            borderColor: 'border-emerald-300/30',
            badgeClass: 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
            activeTabBg: 'bg-emerald-600 shadow-md',
            nameColor: 'text-emerald-400'
        },
        cycling: {
            label: 'MODE: BERSEPEDA',
            btnBg: 'bg-gradient-to-tr from-orange-600 to-amber-500 hover:from-orange-500 hover:to-amber-400',
            glowClass: 'glow-cycling',
            borderColor: 'border-orange-300/30',
            badgeClass: 'bg-orange-500/10 border-orange-500/30 text-orange-400',
            activeTabBg: 'bg-orange-600 shadow-md',
            nameColor: 'text-orange-400'
        }
    };

    function selectSport(sport) {
        if (activeSport === sport) return;
        activeSport = sport;

        const theme = sportThemes[sport];

        document.querySelectorAll('.sport-tab').forEach(tab => {
            const label = tab.querySelector('.tab-label');
            
            if (tab.id === `tab-${sport}`) {
                tab.className = `sport-tab py-2.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-white font-black ${theme.activeTabBg} cursor-pointer`;
                if(label) label.classList.remove('hidden');
            } else {
                tab.className = 'sport-tab py-2.5 px-3 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 text-white font-black bg-transparent hover:bg-gray-800/50 cursor-pointer';
                if(label) label.classList.add('hidden');
            }
        });

        const ring = document.getElementById('hero-button-ring');
        if (ring) {
            ring.classList.remove('animate-spin-ring');
            void ring.offsetWidth;
            ring.classList.add('animate-spin-ring');
        }

        const startBtn = document.getElementById('hero-start-btn');
        if (startBtn) {
            startBtn.className = `w-36 h-36 sm:w-44 sm:h-44 rounded-full ${theme.btnBg} text-white font-black italic text-xl sm:text-2xl uppercase tracking-widest shadow-2xl ${theme.glowClass} border-4 ${theme.borderColor} transition-all duration-500 active:scale-95 hover:scale-105 flex items-center justify-center cursor-pointer`;
        }

        const labelEl = document.getElementById('selected-sport-label');
        if (labelEl) {
            labelEl.innerText = theme.label;
            labelEl.className = `inline-block px-4 py-1.5 rounded-full ${theme.badgeClass} text-xs font-bold uppercase tracking-widest transition-all duration-300`;
        }

        const nameEl = document.getElementById('user-display-name');
        if (nameEl) {
            nameEl.className = `${theme.nameColor} transition-colors duration-300 inline`;
        }

        const inputEl = document.getElementById('main-sport-type');
        if (inputEl) {
            inputEl.value = sport;
        }
    }

    function toggleMetricsAccordion() {
        const accordion = document.getElementById('metrics-accordion');
        const arrow = document.getElementById('accordion-arrow');
        const preview = document.getElementById('metrics-preview');

        if (accordion.classList.contains('open')) {
            accordion.classList.remove('open');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            if (preview) preview.classList.remove('hidden');
        } else {
            accordion.classList.add('open');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
            if (preview) preview.classList.add('hidden');
        }
    }

    function openTargetModal() {
        const modal = document.getElementById('target-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeTargetModal() {
        const modal = document.getElementById('target-modal');
        if (modal) modal.classList.add('hidden');
    }

    function closeFlashAlert() {
        const alertEl = document.getElementById('flash-alert');
        if (alertEl) {
            alertEl.style.opacity = '0';
            alertEl.style.transform = 'translate(-50%, -20px)';
            setTimeout(() => alertEl.remove(), 500);
        }
    }

    // Trigger Animasi Glitter Sporty
    function triggerCelebrationAnimation(type) {
        const canvas = document.getElementById('glitter-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const colors = ['#f59e0b', '#fbbf24', '#f97316', '#38bdf8', '#10b981', '#ffffff'];

        for (let i = 0; i < 120; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height * 0.5,
                size: Math.random() * 6 + 2,
                color: colors[Math.floor(Math.random() * colors.length)],
                speedX: (Math.random() - 0.5) * 6,
                speedY: Math.random() * 4 + 2,
                rotation: Math.random() * 360,
                spin: (Math.random() - 0.5) * 10,
                opacity: 1
            });
        }

        let animationFrame;
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let activeCount = 0;

            particles.forEach(p => {
                if (p.opacity > 0) {
                    activeCount++;
                    p.x += p.speedX;
                    p.y += p.speedY;
                    p.rotation += p.spin;
                    p.opacity -= 0.008;

                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.globalAlpha = Math.max(0, p.opacity);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 1.5);
                    ctx.restore();
                }
            });

            if (activeCount > 0) {
                animationFrame = requestAnimationFrame(animate);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                cancelAnimationFrame(animationFrame);
            }
        }

        animate();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const alertEl = document.getElementById('flash-alert');
        if (alertEl) {
            setTimeout(() => {
                closeFlashAlert();
            }, 5000);
        }

        // Picu otomatis selebrasi jika target hari ini baru saja tercapai
        if (targetAchievedType) {
            setTimeout(() => {
                triggerCelebrationAnimation(targetAchievedType);
            }, 600);
        }
    });
</script>
@endpush
@endsection