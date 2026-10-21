@extends('layouts.app')

@section('title', 'Admin Dashboard — WOYuk!')

@push('styles')
<style>
    /* Glow Accent Effect Khusus Card Statistik Admin */
    .glow-users { box-shadow: 0 0 35px -5px rgba(56, 189, 248, 0.35); }
    .glow-running { box-shadow: 0 0 35px -5px rgba(6, 182, 212, 0.35); }
    .glow-walking { box-shadow: 0 0 35px -5px rgba(16, 185, 129, 0.35); }
    .glow-cycling { box-shadow: 0 0 35px -5px rgba(249, 115, 22, 0.35); }
    .glow-peak { box-shadow: 0 0 35px -5px rgba(168, 85, 247, 0.35); }

    /* Animasi Sliding Indicator Pill Toggle Peak Days/Times */
    .admin-tab-sliding-pill {
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@section('content')
@include('components.navbar')

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8 pb-24">

    <!-- HEADER TITLE & STATS STATUS -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-gray-800/80 pb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full bg-orange-500/10 border border-orange-500/30 text-[10px] font-mono font-bold text-orange-400 uppercase tracking-widest">
                    SYSTEM CONTROL
                </span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black italic uppercase tracking-wider text-white">
                Dashboard <span class="text-orange-500">Administrator</span>
            </h1>
            <p class="text-xs text-gray-400">Ringkasan statistik real-time platform WOYuk!</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <div class="px-4 py-2 rounded-2xl glass-card-dark border border-gray-800 text-xs font-mono text-gray-300 text-center sm:text-right">
                Total Aktivitas Selesai: <strong class="text-orange-400 font-bold text-sm">{{ number_format($totalCompletedActivities ?? 0) }}</strong>
            </div>

            <!-- TOMBOL TAMBAH ADMIN BARU -->
            <button type="button" onclick="openAddAdminModal()" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold uppercase tracking-wider transition-all shadow-lg cursor-pointer flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Admin</span>
            </button>
        </div>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR TAMBAH ADMIN -->
    @if(session('success_admin'))
        <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-300 p-4 rounded-2xl text-xs font-bold">
            {{ session('success_admin') }}
        </div>
    @endif
    @if($errors->has('admin_error'))
        <div class="bg-red-500/20 border border-red-500 text-red-300 p-4 rounded-2xl text-xs font-bold">
            {{ $errors->first('admin_error') }}
        </div>
    @endif

    <!-- GRID METRICS CARDS UTAMA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- CARD 1: TOTAL USERS -->
        <div class="glass-card-dark p-5 sm:p-6 rounded-3xl border border-gray-800/90 space-y-3 relative overflow-hidden group hover:border-sky-500/50 transition-all duration-300 hover:glow-users">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">TOTAL PENGGUNA</span>
                <div class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex items-center justify-center text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-white font-mono tracking-tight">{{ number_format($totalUsers ?? 0) }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Member terdaftar di sistem</p>
            </div>
        </div>

        <!-- CARD 2: AKTIVITAS LARI -->
        <div class="glass-card-dark p-5 sm:p-6 rounded-3xl border border-gray-800/90 space-y-3 relative overflow-hidden group hover:border-cyan-500/50 transition-all duration-300 hover:glow-running">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">SESI LARI</span>
                <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-cyan-400 font-mono tracking-tight">{{ number_format($totalRunning ?? 0) }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Aktivitas Lari Selesai</p>
            </div>
        </div>

        <!-- CARD 3: AKTIVITAS JALAN -->
        <div class="glass-card-dark p-5 sm:p-6 rounded-3xl border border-gray-800/90 space-y-3 relative overflow-hidden group hover:border-emerald-500/50 transition-all duration-300 hover:glow-walking">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">SESI JALAN</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-emerald-400 font-mono tracking-tight">{{ number_format($totalWalking ?? 0) }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Aktivitas Jalan Selesai</p>
            </div>
        </div>

        <!-- CARD 4: AKTIVITAS BERSEPEDA -->
        <div class="glass-card-dark p-5 sm:p-6 rounded-3xl border border-gray-800/90 space-y-3 relative overflow-hidden group hover:border-orange-500/50 transition-all duration-300 hover:glow-cycling">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">SESI BERSEPEDA</span>
                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div>
                <div class="text-3xl font-black text-orange-400 font-mono tracking-tight">{{ number_format($totalCycling ?? 0) }}</div>
                <p class="text-[11px] text-gray-400 mt-1">Aktivitas Sepeda Selesai</p>
            </div>
        </div>

    </div>

    <!-- AREA DETAIL ANALYSIS: DIAGRAM BATANG & TABEL USER TERBARU -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
        
        <!-- BOX DIAGRAM BATANG & WAKTU PUNCAK (PEAK DAYS & PEAK TIMES) -->
        <div class="lg:col-span-5 glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800/90 space-y-6 hover:glow-peak transition-all duration-300 flex flex-col justify-between">
            <div class="space-y-6">
                <!-- HEADER BOX -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="peak-title" class="text-sm font-black italic uppercase text-white tracking-wider">HARI PALING AKTIF</h3>
                            <p id="peak-subtitle" class="text-xs text-gray-400">Grafik rasio kegiatan harian minggu ini</p>
                        </div>
                    </div>
                    <span id="peak-badge" class="text-[10px] font-mono text-purple-400 bg-purple-500/10 px-2.5 py-1 rounded-full border border-purple-500/20">
                        Puncak: {{ $peakDayTranslated ?? '-' }}
                    </span>
                </div>

                <!-- MODE 1: PEAK DAYS (TAMPILAN HARI) -->
                <div id="view-peak-days" class="p-4 rounded-2xl bg-gray-950/60 border border-gray-800 space-y-3">
                    <div class="h-44 flex items-end justify-between gap-2 pt-6 pb-2 px-2 border-b border-gray-800 relative">
                        @foreach(($chartData ?? []) as $day)
                            <div class="flex-1 flex flex-col items-center h-full justify-end group relative z-10">
                                <!-- Tooltip On Hover -->
                                <div class="absolute -top-8 hidden group-hover:flex flex-col items-center z-20">
                                    <span class="bg-gray-900 text-purple-300 text-[10px] font-mono px-2 py-0.5 rounded border border-purple-500/30 whitespace-nowrap shadow-lg">
                                        {{ $day['count'] ?? 0 }} Sesi ({{$day['percentage'] ?? 0 }}%)
                                    </span>
                                </div>

                                <!-- Batang Diagram (Bar) -->
                                <div class="w-full max-w-[28px] bg-gray-900 rounded-t-lg overflow-hidden flex items-end p-0.5 transition-all duration-500 h-full">
                                    <div class="w-full bg-gradient-to-t from-purple-800 to-purple-500 rounded-t-md transition-all duration-700 group-hover:brightness-125"
                                         style="height: {{ max(6, $day['percentage'] ?? 0) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Label Hari (Sen - Min) -->
                    <div class="flex items-center justify-between gap-2 px-2 text-[10px] font-mono text-gray-400 uppercase font-bold text-center">
                        @foreach(($chartData ?? []) as $day)
                            <div class="flex-1 {{ ($day['full_day'] ?? '') === ($peakDayTranslated ?? '') ? 'text-purple-400 font-black' : '' }}">
                                {{ $day['short_day'] ?? '' }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- MODE 2: PEAK TIMES (TAMPILAN WAKTU PAGI, SORE, MALAM) -->
                <div id="view-peak-times" class="p-4 rounded-2xl bg-gray-950/60 border border-gray-800 space-y-3 hidden">
                    <div class="h-44 flex items-end justify-between gap-6 pt-6 pb-2 px-6 border-b border-gray-800 relative">
                        @foreach(($peakTimesData ?? []) as $slotKey =>$slot)
                            <div class="flex-1 flex flex-col items-center h-full justify-end group relative z-10">
                                <!-- Tooltip On Hover -->
                                <div class="absolute -top-8 hidden group-hover:flex flex-col items-center z-20">
                                    <span class="bg-gray-900 text-purple-300 text-[10px] font-mono px-2 py-0.5 rounded border border-purple-500/30 whitespace-nowrap shadow-lg">
                                        {{ $slot['count'] ?? 0 }} Sesi ({{$slot['percentage'] ?? 0 }}%)
                                    </span>
                                </div>

                                <!-- Batang Diagram (Bar) -->
                                <div class="w-full max-w-[42px] bg-gray-900 rounded-t-lg overflow-hidden flex items-end p-0.5 transition-all duration-500 h-full">
                                    <div class="w-full bg-gradient-to-t from-purple-800 to-purple-400 rounded-t-md transition-all duration-700 group-hover:brightness-125"
                                         style="height: {{ max(6, $slot['percentage'] ?? 0) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Label Waktu -->
                    <div class="flex items-center justify-between gap-6 px-4 text-[10px] font-mono text-gray-400 uppercase font-bold text-center">
                        @foreach(($peakTimesData ?? []) as $slotKey =>$slot)
                            <div class="flex-1 text-center">
                                <span class="block text-purple-300 font-bold">{{ $slotKey }}</span>
                                <span class="block text-[9px] text-gray-500 font-normal">{{ $slot['count'] ?? 0 }} Sesi</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- FOOTER DIV AKTIVITAS: COPYRIGHT & NAVBAR TOGGLE PEAK DAYS / PEAK TIMES -->
            <div class="flex items-center justify-between pt-4 mt-6 border-t border-gray-800/60">
                <p class="text-[11px] text-gray-400 leading-relaxed italic">
                    © <span class="text-purple-300 font-mono">Copyright 2026</span>
                </p>

                <!-- NAVBAR TOGGLE DENGAN SLIDING PILL UNGU -->
                <div class="relative inline-flex p-1 bg-gray-900 border border-gray-800 rounded-xl text-[10px] font-mono font-bold">
                    <div id="admin-peak-sliding-pill" class="admin-tab-sliding-pill absolute top-1 left-1 bottom-1 bg-purple-600 rounded-lg shadow-md pointer-events-none"></div>

                    <button type="button" id="btn-peak-days" onclick="switchPeakView('days', this)" class="relative z-10 px-3 py-1 text-white transition-colors cursor-pointer">
                        Peak Days
                    </button>
                    <button type="button" id="btn-peak-times" onclick="switchPeakView('times', this)" class="relative z-10 px-3 py-1 text-gray-400 hover:text-white transition-colors cursor-pointer">
                        Peak Times
                    </button>
                </div>
            </div>
        </div>

        <!-- TABEL LIST PENGGUNA TERBARU -->
        <div class="lg:col-span-7 glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800/90 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-800/80 pb-4">
                <div>
                    <h3 class="text-sm font-black italic uppercase text-white tracking-wider">AKUN TERDAFTAR</h3>
                    <p class="text-xs text-gray-400">DINASTY OF WOYUK</p>
                </div>
                <span class="text-xs font-mono text-orange-400 bg-orange-500/10 px-2.5 py-1 rounded-full border border-orange-500/20">
                    USERS
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-gray-400 font-mono uppercase text-[10px] border-b border-gray-800">
                            <th class="pb-3">Pengguna</th>
                            <th class="pb-3">Public ID</th>
                            <th class="pb-3">Role</th>
                            <th class="pb-3 text-right">Bergabung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60">
                        @forelse(($latestUsers ?? []) as $u)
                            <tr class="hover:bg-gray-900/40 transition-colors">
                                <td class="py-3.5 pr-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-orange-600/20 border border-orange-500/30 flex items-center justify-center font-bold text-orange-400 text-xs shrink-0 overflow-hidden">
                                            @if($u->profile &&$u->profile->avatar)
                                                <img src="{{ asset('storage/' . $u->profile->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($u->profile->name ?? $u->email, 0, 2)) }}
                                            @endif
                                        </div>
                                        <div class="truncate max-w-[120px] sm:max-w-[180px]">
                                            <div class="font-bold text-gray-200 truncate">{{ $u->profile->name ?? 'User' }}</div>
                                            <div class="text-[10px] text-gray-500 truncate">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 font-mono text-orange-400 font-bold">
                                    {{ $u->public_id ?? '-' }}
                                </td>
                                <td class="py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase {{ ($u->role ?? 'user') === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-gray-800 text-gray-400' }}">
                                        {{ $u->role ?? 'user' }}
                                    </span>
                                </td>
                                <td class="py-3.5 text-right font-mono text-gray-400 text-[10px]">
                                    {{ $u->created_at ? $u->created_at->diffForHumans() : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">Belum ada pengguna terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- SEKSI HISTORI PROGRESS ANALYTICS MINGGUAN (CARD MINGGU TERLEWATI INTERAKTIF) -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between border-b border-gray-800/80 pb-3">
            <div>
                <h2 class="text-base font-black italic uppercase text-white tracking-wider">
                    Histori Progress Analytics <span class="text-purple-400">Mingguan</span>
                </h2>
                <p class="text-xs text-gray-400">Klik kartu minggu untuk melihat rincian analytics minggu yang terlewati</p>
            </div>
            <span class="text-xs font-mono text-purple-400 bg-purple-500/10 px-3 py-1 rounded-full border border-purple-500/20">
                {{ count($weeklyHistory ?? []) }} Minggu Terlewati
            </span>
        </div>

        @if(count($weeklyHistory ?? []) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($weeklyHistory as $index =>$history)
                    <div onclick="openAdminWeeklyModal({{ $loop->index }})" 
                         class="glass-card-dark p-5 rounded-3xl border border-gray-800/90 space-y-4 relative overflow-hidden group hover:border-purple-500/50 cursor-pointer transition-all duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg bg-purple-500/10 border border-purple-500/30 text-purple-300 font-mono text-[11px] font-bold group-hover:text-purple-200">
                                Card Minggu {{ $history['week_number'] ?? 1 }}
                            </span>
                            <span class="text-[10px] font-mono text-gray-500">
                                {{ $history['start_date'] ?? '' }} — {{$history['end_date'] ?? '' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="p-3 bg-gray-950/50 rounded-2xl border border-gray-800/60">
                                <span class="block text-[10px] font-mono text-gray-400 uppercase">Total Sesi</span>
                                <span class="text-xl font-black text-white font-mono">{{ number_format($history['total_count'] ?? 0) }}</span>
                            </div>
                            <div class="p-3 bg-gray-950/50 rounded-2xl border border-gray-800/60">
                                <span class="block text-[10px] font-mono text-gray-400 uppercase">Hari Puncak</span>
                                <span class="text-sm font-bold text-purple-400 font-mono truncate block mt-1">{{ $history['peak_day'] ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-800/60 flex items-center justify-between text-[10px] font-mono text-gray-400 group-hover:text-purple-300">
                            <span>Lihat Rincian Analytics</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="glass-card-dark p-8 rounded-3xl border border-gray-800/90 text-center space-y-2">
                <p class="text-xs text-gray-400 font-mono">Belum ada histori minggu yang terlewati.</p>
                <p class="text-[11px] text-gray-600">Data rekap akan dibekukan otomatis setiap memasuki hari Senin jam 00:00.</p>
            </div>
        @endif
    </div>

    <!-- MODAL POPUP DETAIL HISTORI PROGRESS ANALYTICS ADMIN -->
    <div id="admin-weekly-modal" class="fixed inset-0 z-[9995] bg-gray-950/80 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-2xl glass-card-dark border border-gray-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                <div>
                    <h3 class="text-base font-black italic uppercase text-white tracking-wider" id="modal-admin-week-title">CARD MINGGU 1</h3>
                    <p class="text-xs text-purple-400 font-mono mt-0.5" id="modal-admin-week-dates">-</p>
                </div>
                <button type="button" onclick="closeAdminWeeklyModal()" class="text-gray-400 hover:text-white text-xl font-bold cursor-pointer">✕</button>
            </div>

            <!-- Ringkasan Sesi & Hari Puncak -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-950/60 rounded-2xl border border-gray-800">
                    <span class="block text-[10px] font-mono text-gray-400 uppercase">Total Sesi Platform</span>
                    <span class="text-2xl font-black text-white font-mono mt-1 block" id="modal-admin-total-count">0</span>
                </div>
                <div class="p-4 bg-gray-950/60 rounded-2xl border border-gray-800">
                    <span class="block text-[10px] font-mono text-gray-400 uppercase">Hari Puncak</span>
                    <span class="text-lg font-bold text-purple-400 font-mono mt-1 block" id="modal-admin-peak-day">-</span>
                </div>
            </div>

            <!-- Diagram Batang Hari Paling Aktif -->
            <div class="space-y-3">
                <h4 class="text-xs font-black uppercase tracking-wider text-purple-300">Grafik Hari Paling Aktif</h4>
                <div class="p-4 rounded-2xl bg-gray-950/60 border border-gray-800">
                    <div class="h-40 flex items-end justify-between gap-2 pt-6 pb-2 px-2 border-b border-gray-800" id="modal-admin-days-chart">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Breakdown Waktu Paling Aktif -->
            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-black uppercase tracking-wider text-indigo-300">Waktu Paling Aktif</h4>
                <div class="grid grid-cols-3 gap-3 text-center" id="modal-admin-times-chart">
                    <!-- Injected via JavaScript -->
                </div>
            </div>

            <div class="pt-2">
                <button type="button" onclick="closeAdminWeeklyModal()" class="w-full py-3 bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700 transition-colors cursor-pointer">
                    Tutup Rincian
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH ADMIN BARU -->
    <div id="add-admin-modal" class="fixed inset-0 z-[9990] bg-gray-950/80 backdrop-blur-md flex items-center justify-center p-4 hidden">
        <div class="w-full max-w-md glass-card-dark border border-gray-800 rounded-3xl p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                <h3 class="text-sm font-black italic uppercase text-white tracking-wider">Tambah Admin Baru</h3>
                <button type="button" onclick="closeAddAdminModal()" class="text-gray-400 hover:text-white text-xs cursor-pointer">✕</button>
            </div>

            <form action="{{ route('admin.create') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-gray-400 mb-1 font-mono uppercase text-[10px]">Nama Admin</label>
                    <input type="text" name="name" required placeholder="..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1 font-mono uppercase text-[10px]">Email Admin</label>
                    <input type="email" name="email" required placeholder="..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-gray-400 mb-1 font-mono uppercase text-[10px]">Password (Min 8 Karakter)</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3.5 py-2.5 text-white focus:outline-none focus:border-purple-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" onclick="closeAddAdminModal()" class="w-1/2 py-2.5 bg-gray-900 hover:bg-gray-800 text-gray-300 font-bold rounded-xl uppercase cursor-pointer">Batal</button>
                    <button type="submit" class="w-1/2 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl uppercase shadow-lg cursor-pointer">Simpan Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const adminWeeklyHistoryData = @json($weeklyHistory ?? []);

        function openAddAdminModal() {
            document.getElementById('add-admin-modal').classList.remove('hidden');
        }
        function closeAddAdminModal() {
            document.getElementById('add-admin-modal').classList.add('hidden');
        }

        /* FUNCTION SWAP VIEW TOGGLE PEAK DAYS & PEAK TIMES ADMIN DENGAN ANIMASI SLIDING PILL */
        function switchPeakView(mode, btnEl) {
            const daysView = document.getElementById('view-peak-days');
            const timesView = document.getElementById('view-peak-times');
            const btnDays = document.getElementById('btn-peak-days');
            const btnTimes = document.getElementById('btn-peak-times');
            const title = document.getElementById('peak-title');
            const subtitle = document.getElementById('peak-subtitle');
            const pill = document.getElementById('admin-peak-sliding-pill');

            const targetBtn = btnEl || (mode === 'days' ? btnDays : btnTimes);

            if (pill && targetBtn) {
                pill.style.width = `${targetBtn.offsetWidth}px`;
                pill.style.transform = `translateX(${targetBtn.offsetLeft - 4}px)`;
            }

            if (mode === 'days') {
                daysView.classList.remove('hidden');
                timesView.classList.add('hidden');
                
                btnDays.className = 'relative z-10 px-3 py-1 text-white transition-colors cursor-pointer';
                btnTimes.className = 'relative z-10 px-3 py-1 text-gray-400 hover:text-white transition-colors cursor-pointer';

                title.innerText = 'HARI PALING AKTIF';
                subtitle.innerText = 'Grafik rasio kegiatan harian minggu ini';
            } else {
                daysView.classList.add('hidden');
                timesView.classList.remove('hidden');

                btnTimes.className = 'relative z-10 px-3 py-1 text-white transition-colors cursor-pointer';
                btnDays.className = 'relative z-10 px-3 py-1 text-gray-400 hover:text-white transition-colors cursor-pointer';

                title.innerText = 'WAKTU PALING AKTIF';
                subtitle.innerText = 'Grafik rasio kegiatan berdasarkan jam minggu ini';
            }
        }

        /* FUNCTION OPEN & RENDER POPUP MODAL MINGGUAN ADMIN */
        function openAdminWeeklyModal(index) {
            const data = adminWeeklyHistoryData[index];
            if (!data) return;

            document.getElementById('modal-admin-week-title').innerText = `CARD MINGGU ${data.week_number}`;
            document.getElementById('modal-admin-week-dates').innerText = `${data.start_date} — ${data.end_date}`;
            document.getElementById('modal-admin-total-count').innerText = `${data.total_count} Sesi`;
            document.getElementById('modal-admin-peak-day').innerText = data.peak_day;

            // Render Diagram Batang Hari Admin
            const daysContainer = document.getElementById('modal-admin-days-chart');
            let daysHtml = '';
            (data.chart_data || []).forEach(day => {
                daysHtml += `
                    <div class="flex-1 flex flex-col items-center h-full justify-end group relative z-10">
                        <div class="absolute -top-7 hidden group-hover:flex flex-col items-center z-20">
                            <span class="bg-gray-900 text-purple-300 text-[10px] font-mono px-2 py-0.5 rounded border border-purple-500/30 whitespace-nowrap shadow-lg">
                                ${day.count} Sesi (${day.percentage}%)
                            </span>
                        </div>
                        <div class="w-full max-w-[28px] bg-gray-900 rounded-t-lg overflow-hidden flex items-end p-0.5 h-full">
                            <div class="w-full bg-gradient-to-t from-purple-800 to-purple-500 rounded-t-md" style="height: ${Math.max(6, day.percentage)}%;"></div>
                        </div>
                        <span class="text-[9px] font-mono text-gray-400 uppercase mt-1">${day.short_day}</span>
                    </div>
                `;
            });
            daysContainer.innerHTML = daysHtml;

            // Render Breakdown Waktu Admin
            const timesContainer = document.getElementById('modal-admin-times-chart');
            let timesHtml = '';
            if (data.peak_times_data) {
                Object.entries(data.peak_times_data).forEach(([key, slot]) => {
                    timesHtml += `
                        <div class="p-3.5 rounded-2xl bg-gray-950/60 border border-gray-800 space-y-1">
                            <span class="text-[10px] text-purple-300 font-bold block">${key}</span>
                            <span class="text-lg font-black italic font-mono text-white block">${slot.percentage}%</span>
                            <span class="text-[9px] text-gray-500 block">${slot.count} Sesi</span>
                        </div>
                    `;
                });
            }
            timesContainer.innerHTML = timesHtml;

            document.getElementById('admin-weekly-modal').classList.remove('hidden');
        }

        function closeAdminWeeklyModal() {
            document.getElementById('admin-weekly-modal').classList.add('hidden');
        }

        document.addEventListener("DOMContentLoaded", function () {
            const defaultBtn = document.getElementById('btn-peak-days');
            if (defaultBtn) {
                setTimeout(() => switchPeakView('days', defaultBtn), 100);
            }

            window.addEventListener('resize', () => {
                const activeTab = document.getElementById('view-peak-days')?.classList.contains('hidden') ? 'times' : 'days';
                const activeBtn = activeTab === 'days' ? document.getElementById('btn-peak-days') : document.getElementById('btn-peak-times');
                if (activeBtn) switchPeakView(activeTab, activeBtn);
            });
        });
    </script>

</main>
@endsection