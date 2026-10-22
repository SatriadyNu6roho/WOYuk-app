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

    /* === ANIMASI MODAL MILESTONE STREAK === */
    @keyframes modalPop {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-modal-pop {
        animation: modalPop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    @keyframes iconBounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-20px); }
        60% { transform: translateY(-10px); }
    }
    .animate-icon-bounce {
        animation: iconBounce 1.5s infinite;
    }

    /* === ANIMASI ZZZ UNTUK STREAK TIDUR === */
    @keyframes floatZzz {
        0% { transform: translateY(0) scale(1); opacity: 0; }
        20% { opacity: 1; }
        100% { transform: translateY(-30px) scale(1.5); opacity: 0; }
    }
    .animate-float-zzz {
        animation: floatZzz 2.5s ease-in-out infinite;
    }
    .zzz-delay-1 { animation-delay: 0s; }
    .zzz-delay-2 { animation-delay: 0.8s; }
    .zzz-delay-3 { animation-delay: 1.6s; }
</style>
@endpush

@section('content')
@include('components.navbar')

<!-- ALERT FLOATING MINIMALIS -->
@if(session('warning') || session('errors') || session('success'))
    @php
        $alertType = session('success') ? 'success' : (session('warning') ? 'warning' : 'error');
        $alertMessage = session('success') ?? (session('warning') ?? session('errors')->first());
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

@php
    $currentStreak = $displayStreak ?? ($user->streak->current_streak ?? 0);
    $longestStreak = $user->streak->longest_streak ?? 0;
    
    // Default Warna (1-9): Biru / Cyan
    $colorBg = 'bg-gradient-to-br from-cyan-500 to-blue-600';
    $colorText = 'text-cyan-400';
    $colorBar = 'bg-gradient-to-r from-cyan-600 to-blue-400';
    $glowClass = 'shadow-[0_0_20px_rgba(6,182,212,0.4)] border-cyan-500/30';

    if ($streakStatus === 'sleeping') {
        // TIDUR / RECOVERY: Abu-abu
        $colorBg = 'bg-gradient-to-br from-gray-600 to-gray-800';
        $colorText = 'text-gray-400';
        $colorBar = 'bg-gradient-to-r from-gray-600 to-gray-500';
        $glowClass = 'shadow-[0_0_20px_rgba(107,114,128,0.3)] border-gray-600/30';
    } elseif ($streakStatus === 'broken') {
        // HANGUS: Merah
        $colorBg = 'bg-gradient-to-br from-red-600 to-rose-800';
        $colorText = 'text-red-500';
        $colorBar = 'bg-gradient-to-r from-red-600 to-rose-500';
        $glowClass = 'shadow-[0_0_20px_rgba(239,68,68,0.3)] border-red-600/30';
    } elseif ($currentStreak >= 100) {
        // 100+ Ungu / Purple
        $colorBg = 'bg-gradient-to-br from-purple-500 to-fuchsia-600';
        $colorText = 'text-purple-400';
        $colorBar = 'bg-gradient-to-r from-purple-600 to-fuchsia-400';
        $glowClass = 'shadow-[0_0_20px_rgba(168,85,247,0.4)] border-purple-500/30';
    } elseif ($currentStreak >= 50) {
        // 50-99 Orange
        $colorBg = 'bg-gradient-to-br from-orange-500 to-amber-600';
        $colorText = 'text-orange-400';
        $colorBar = 'bg-gradient-to-r from-orange-600 to-amber-400';
        $glowClass = 'shadow-[0_0_20px_rgba(249,115,22,0.4)] border-orange-500/30';
    } elseif ($currentStreak >= 10) {
        // 10-49 Hijau / Emerald
        $colorBg = 'bg-gradient-to-br from-emerald-500 to-teal-600';
        $colorText = 'text-emerald-400';
        $colorBar = 'bg-gradient-to-r from-emerald-600 to-teal-400';
        $glowClass = 'shadow-[0_0_20px_rgba(16,185,129,0.4)] border-emerald-500/30';
    }

    // Kalkulasi persentase bar (Maksimal 100%)
    $progressPercent = min(100, $currentStreak);
@endphp

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
            <!-- UI STREAK BARU DENGAN DYNAMIC COLOR & BATTERY STATUS -->
            <div class="glass-card-dark p-5 sm:p-6 rounded-3xl border {{ $glowClass }} flex flex-col justify-center shadow-xl h-full transition-all duration-500 relative overflow-visible">
                
                <div class="flex items-center justify-between relative">
                    <div class="flex items-center gap-3 sm:gap-4 relative">
                        <!-- Ikon Dinamis -->
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl {{ $colorBg }} flex items-center justify-center text-white shadow-lg transition-all duration-500 flex-shrink-0 relative">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            
                            <!-- ANIMASI ZZZ JIKA TIDUR -->
                            @if($streakStatus === 'sleeping')
                                <div class="absolute -top-3 -right-4 pointer-events-none">
                                    <span class="absolute text-[10px] font-black text-gray-400 animate-float-zzz zzz-delay-1">Z</span>
                                    <span class="absolute left-2 -top-2 text-[12px] font-black text-gray-300 animate-float-zzz zzz-delay-2">Z</span>
                                    <span class="absolute left-4 -top-4 text-[14px] font-black text-gray-200 animate-float-zzz zzz-delay-3">Z</span>
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <div class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                @if($streakStatus === 'sleeping')
                                    <span class="text-gray-400">STREAK RECOVERY</span>
                                    <span class="bg-gray-800 text-gray-300 px-1.5 py-0.5 rounded text-[8px] animate-pulse">TIDUR</span>
                                @elseif($streakStatus === 'broken')
                                    <span class="text-red-400">STREAK TERPUTUS</span>
                                @else
                                    STREAK AKTIF
                                @endif
                            </div>
                            <div class="text-xl sm:text-2xl font-black text-white italic tracking-wider font-mono">
                                {{ $currentStreak }} <span class="text-[10px] sm:text-xs font-normal {{ $colorText }} uppercase">HARI</span>
                            </div>
                            
                            <!-- Peringatan Kecil -->
                            @if($streakStatus === 'sleeping')
                                <div class="text-[9px] text-gray-400 mt-0.5 italic">Ayo olahraga hari ini biar bangun!</div>
                            @elseif($streakStatus === 'broken')
                                <div class="text-[9px] text-red-400 mt-0.5 italic">Mulai dari awal lagi ya.</div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-right border-l border-gray-800/80 pl-4 sm:pl-6">
                        <div class="text-[8px] sm:text-[9px] text-gray-500 uppercase tracking-wider">REKOR TERTINGGI</div>
                        <div class="text-xs sm:text-sm font-bold text-gray-200 font-mono">{{ $longestStreak }} HARI</div>
                    </div>
                </div>

                <!-- BATTERY STATUS BAR & MILESTONE TOOLTIP -->
                <div class="mt-8 relative px-1">
                    <!-- Tooltip Penanda Milestone -->
                    <div class="absolute -top-5 left-[10%] -translate-x-1/2 flex flex-col items-center">
                        <span class="text-[10px] font-black {{ $currentStreak >= 10 ? 'text-emerald-400' : 'text-gray-500' }}">10</span>
                        <div class="w-1.5 h-1.5 rounded-full {{ $currentStreak >= 10 ? 'bg-emerald-500' : 'bg-gray-700' }} mt-0.5"></div>
                    </div>
                    <div class="absolute -top-5 left-[50%] -translate-x-1/2 flex flex-col items-center">
                        <span class="text-[10px] font-black {{ $currentStreak >= 50 ? 'text-orange-400' : 'text-gray-500' }}">50</span>
                        <div class="w-1.5 h-1.5 rounded-full {{ $currentStreak >= 50 ? 'bg-orange-500' : 'bg-gray-700' }} mt-0.5"></div>
                    </div>
                    <div class="absolute -top-5 left-[100%] -translate-x-1/2 flex flex-col items-center">
                        <span class="text-[10px] font-black {{ $currentStreak >= 100 ? 'text-purple-400' : 'text-gray-500' }}">100+</span>
                        <div class="w-1.5 h-1.5 rounded-full {{ $currentStreak >= 100 ? 'bg-purple-500' : 'bg-gray-700' }} mt-0.5"></div>
                    </div>

                    <!-- Garis Progress (Battery) -->
                    <div class="w-full h-3 bg-gray-900 rounded-full overflow-hidden border border-gray-800 relative z-10 shadow-inner">
                        <div class="h-full {{ $colorBar }} transition-all duration-1000 ease-out relative" style="width: {{ $progressPercent }}%;">
                            <!-- Efek kilau di ujung bar -->
                            <div class="absolute top-0 right-0 bottom-0 w-4 bg-white/20 blur-sm"></div>
                        </div>
                    </div>
                    
                    <!-- Garis Pembatas Vertikal (Divider) -->
                    <div class="absolute top-0 bottom-0 left-[10%] w-px bg-white/20 z-20 pointer-events-none"></div>
                    <div class="absolute top-0 bottom-0 left-[50%] w-px bg-white/20 z-20 pointer-events-none"></div>
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

<!-- ==================== MODAL MILESTONE STREAK BARU ==================== -->
<div id="streak-milestone-modal" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden transition-all duration-300">
    <div class="bg-gradient-to-b from-gray-900 to-gray-950 border border-gray-700 max-w-sm w-full rounded-[2rem] p-8 text-center space-y-6 shadow-2xl relative overflow-hidden transform scale-90 opacity-0 transition-all duration-500" id="streak-milestone-content">
        
        <!-- Glow Effect di belakang ikon -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-48 h-48 bg-orange-500/20 blur-3xl rounded-full pointer-events-none"></div>

        <!-- Ikon Badge -->
        <div class="relative mx-auto w-24 h-24 rounded-full bg-gradient-to-tr from-gray-800 to-gray-900 border-4 border-gray-700 flex items-center justify-center shadow-lg animate-icon-bounce" id="streak-milestone-icon-wrapper">
            <svg id="streak-milestone-icon" class="w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>

        <!-- Teks -->
        <div class="space-y-2 relative z-10">
            <div class="text-[10px] font-black uppercase tracking-widest text-orange-400 mb-1">PENCAPAIAN MILESTONE</div>
            <h2 class="text-3xl font-black italic text-white uppercase tracking-wider">
                STREAK <span id="milestone-number" class="text-orange-400"></span> HARI!
            </h2>
            <p class="text-sm text-gray-300 leading-relaxed font-medium">
                Luar biasa! Sampeyan berhasil konsisten olahraga tanpa putus. Terus jaga semangat e!
            </p>
        </div>

        <!-- Tombol Aksi -->
        <div class="space-y-3 pt-2 relative z-10">
            <button type="button" onclick="shareStreakMilestone()" class="w-full py-3 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-500 hover:to-amber-400 text-white font-black text-sm uppercase tracking-widest rounded-xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                BAGIKAN
            </button>
            <button type="button" onclick="closeMilestoneModal()" class="w-full py-2.5 bg-transparent hover:bg-gray-800 text-gray-400 hover:text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeSport = 'running';
    const targetAchievedType = @json($targetAchievedType);
    // Milestone streak (berisi angka 10/50/100 atau null)
    const streakMilestone = @json($streakMilestone ?? null);

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

    // === LOGIKA MODAL MILESTONE STREAK ===
    
    function openMilestoneModal(milestone) {
        const modal = document.getElementById('streak-milestone-modal');
        const content = document.getElementById('streak-milestone-content');
        const numberEl = document.getElementById('milestone-number');
        const iconWrapper = document.getElementById('streak-milestone-icon-wrapper');
        const iconSvg = document.getElementById('streak-milestone-icon');

        // Set Angka
        numberEl.innerText = milestone;

        // Sesuaikan Warna Icon & Glow Berdasarkan Milestone
        if (milestone >= 100) {
            numberEl.className = 'text-purple-400';
            iconWrapper.className = 'relative mx-auto w-24 h-24 rounded-full bg-gradient-to-tr from-gray-800 to-gray-900 border-4 border-purple-500/50 flex items-center justify-center shadow-[0_0_30px_rgba(168,85,247,0.5)] animate-icon-bounce';
            iconSvg.className = 'w-12 h-12 text-purple-400';
        } else if (milestone >= 50) {
            numberEl.className = 'text-orange-400';
            iconWrapper.className = 'relative mx-auto w-24 h-24 rounded-full bg-gradient-to-tr from-gray-800 to-gray-900 border-4 border-orange-500/50 flex items-center justify-center shadow-[0_0_30px_rgba(249,115,22,0.5)] animate-icon-bounce';
            iconSvg.className = 'w-12 h-12 text-orange-400';
        } else {
            numberEl.className = 'text-emerald-400';
            iconWrapper.className = 'relative mx-auto w-24 h-24 rounded-full bg-gradient-to-tr from-gray-800 to-gray-900 border-4 border-emerald-500/50 flex items-center justify-center shadow-[0_0_30px_rgba(16,185,129,0.5)] animate-icon-bounce';
            iconSvg.className = 'w-12 h-12 text-emerald-400';
        }

        modal.classList.remove('hidden');
        
        // Trigger animasi pop-up
        setTimeout(() => {
            content.classList.remove('scale-90', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 50);
    }

    function closeMilestoneModal() {
        const modal = document.getElementById('streak-milestone-modal');
        const content = document.getElementById('streak-milestone-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-90', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function shareStreakMilestone() {
        const text = `Pencapaian Baru! Aku berhasil menjaga konsistensi olahraga selama ${streakMilestone} hari berturut-turut di WOYuk! 🏃‍♂️🔥 Ayo obah bareng!`;
        const url = window.location.origin;

        if (navigator.share) {
            navigator.share({
                title: 'Pencapaian Streak WOYuk!',
                text: text,
                url: url
            }).then(() => {
                console.log('Berhasil dibagikan');
            }).catch((error) => {
                console.error('Gagal membagikan', error);
            });
        } else {
            // Fallback ke WA jika browser tidak mendukung (PC/Laptop)
            const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(text + ' ' + url)}`;
            window.open(waUrl, '_blank');
        }
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

        // PICULAH MODAL STREAK JIKA MILESTONE TERCAPAI HARI INI
        if (streakMilestone) {
            setTimeout(() => {
                // Jalankan confetti
                triggerCelebrationAnimation('both'); 
                // Munculkan modal
                openMilestoneModal(streakMilestone);
            }, 800);
        }
    });
</script>
@endpush
@endsection