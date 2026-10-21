@extends('layouts.app')

@section('title', 'Profil Saya — WOYuk!')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<style>
    .sport-glow-box {
        box-shadow: 0 0 25px rgba(249, 115, 22, 0.35);
    }
</style>
@endpush

@section('content')
@include('components.navbar')

<main class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 pb-32 sm:pb-36">

    <!-- HERO HEADER STRAVA STYLE -->
    <div class="glass-card-dark border border-gray-800/90 rounded-3xl overflow-hidden shadow-2xl">
        <div class="h-36 bg-gradient-to-r from-cyan-600/30 via-orange-500/20 to-emerald-600/30 border-b border-gray-800 relative"></div>
        
        <div class="px-6 pb-4 relative flex flex-col sm:flex-row items-center sm:items-end justify-between -mt-14 gap-4">
            <div class="flex flex-col sm:flex-row items-center sm:items-end gap-5 text-center sm:text-left">
                
                <!-- AVATAR DENGAN OVERLAY IKON PENSIL -->
                <div class="relative group">
                    <div class="w-28 h-28 rounded-3xl bg-gray-900 border-2 border-orange-500 flex items-center justify-center overflow-hidden shadow-2xl sport-glow">
                        @if($user->profile &&$user->profile->avatar)
                            <img src="{{ asset('storage/' . $user->profile->avatar) }}" class="w-full h-full object-cover" id="user-avatar-img">
                        @else
                            <span class="text-3xl font-black text-orange-500 uppercase font-mono" id="user-avatar-initials">
                                {{ substr($user->profile->name ?? $user->email, 0, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- IKON PENSIL DI POJOK KANAN ATAS FOTO PROFIL -->
                    <a href="{{ route('settings.edit') }}" 
                       class="absolute -top-1 -right-1 w-8 h-8 rounded-full bg-orange-600 hover:bg-orange-500 border-2 border-gray-950 flex items-center justify-center text-white shadow-lg transition-transform hover:scale-110 cursor-pointer"
                       title="Ganti Foto Profil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </a>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-center sm:justify-start gap-2">
                        <h1 class="text-2xl sm:text-3xl font-black text-white italic tracking-wide uppercase font-sans" id="profile-display-name">
                            {{ $user->profile->name ?? 'Atlet WOYuk!' }}
                        </h1>
                        @if(($user->role ?? '') === 'admin' || (method_exists($user, 'isAdmin') &&$user->isAdmin()))
                            <span class="px-2.5 py-0.5 rounded-full bg-purple-500/20 border border-purple-500/40 text-[10px] font-mono font-bold text-purple-300 uppercase">
                                ADMIN
                            </span>
                        @endif
                    </div>
                    <p class="text-xs font-mono text-cyan-400">@ <span id="profile-display-username">{{ $user->profile->username ?? 'user' }}</span></p>
                </div>
            </div>

            <!-- PUBLIC ID & TOMBOL AKSI IKONIK (ADMIN & LOGOUT) -->
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5">
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-700 text-xs font-mono text-gray-300">
                    PUBLIC ID: <span class="text-orange-400 font-bold ml-1">{{ $user->public_id }}</span>
                </div>

                <a href="{{ route('settings.edit') }}" 
                   class="p-2.5 bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white border border-gray-700 rounded-xl transition-all shadow-md cursor-pointer"
                   title="Edit Profil">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>

                @if(($user->role ?? '') === 'admin' || (method_exists($user, 'isAdmin') &&$user->isAdmin()))
                    <a href="{{ route('admin.dashboard') }}" 
                       class="p-2.5 bg-purple-950/80 hover:bg-purple-900 border border-purple-700/80 text-purple-300 hover:text-purple-100 rounded-xl transition-all shadow-md cursor-pointer"
                       title="Dashboard Admin">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="p-2.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 hover:text-red-300 border border-red-500/30 rounded-xl transition-all shadow-md cursor-pointer"
                            title="Keluar / Logout Akun">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- BARIS BIO & TOMBOL BAGIKAN SEJAJAR DI KANAN UJUNG -->
        <div class="border-t border-gray-800/60 pt-3 px-6 pb-5 flex items-center justify-between gap-4">
            <p class="text-xs sm:text-sm text-gray-300 italic">
                {{ $user->profile->bio ?? 'Member aktif WOYuk!' }}
            </p>

            <button type="button" onclick="openProfileShareModal()" 
                    class="p-2.5 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white border border-orange-500/50 rounded-xl transition-all shadow-md shrink-0 cursor-pointer flex items-center gap-1.5 text-xs font-bold sport-glow"
                    title="Bagikan Profil">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                <span class="hidden sm:inline">Bagikan</span>
            </button>
        </div>
    </div>

    <!-- STATISTIK AKTIVITAS RIIL SINKRON DENGAN ARSIP -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 space-y-1">
            <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">LARI (ARSIP)</span>
            <div class="text-2xl font-black text-cyan-400 font-mono"><span id="stat-run">{{ $stats['running'] }}</span> <span class="text-xs font-normal text-gray-400">SESI</span></div>
        </div>

        <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 space-y-1">
            <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">JALAN (ARSIP)</span>
            <div class="text-2xl font-black text-emerald-400 font-mono"><span id="stat-walk">{{ $stats['walking'] }}</span> <span class="text-xs font-normal text-gray-400">SESI</span></div>
        </div>

        <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 space-y-1">
            <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">BERSEPEDA (ARSIP)</span>
            <div class="text-2xl font-black text-orange-400 font-mono"><span id="stat-cycle">{{ $stats['cycling'] }}</span> <span class="text-xs font-normal text-gray-400">SESI</span></div>
        </div>

        <div class="glass-card-dark p-5 rounded-2xl border border-gray-800 space-y-1">
            <span class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">TOTAL JARAK</span>
            <div class="text-2xl font-black text-purple-400 font-mono"><span id="stat-dist">{{ $stats['total_distance'] }}</span> <span class="text-xs font-normal text-gray-400">KM</span></div>
        </div>
    </div>

    <!-- ANALITIK AKTIVITAS SMOOTH AREA LINE CHART WITH TIME FILTERS -->
    <div class="glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800/90 space-y-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800/80 pb-4">
            <div>
                <h2 class="text-lg font-black italic uppercase text-white tracking-wider">Progress Analytics</h2>
                <p class="text-xs text-gray-400">Akumulasi tren jarak tempuh (KM)</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center bg-gray-950 p-1 rounded-2xl border border-gray-800 gap-1 font-mono text-xs font-bold">
                    <button type="button" onclick="updateChartPeriod('1d', this)"
                            class="period-btn px-3 py-1.5 rounded-xl transition-all cursor-pointer text-gray-400 hover:text-white">
                        1H
                    </button>
                    <button type="button" onclick="updateChartPeriod('7d', this)"
                            class="period-btn px-3 py-1.5 rounded-xl transition-all cursor-pointer bg-orange-500 text-black shadow-md font-black">
                        7H
                    </button>
                    <button type="button" onclick="updateChartPeriod('30d', this)"
                            class="period-btn px-3 py-1.5 rounded-xl transition-all cursor-pointer text-gray-400 hover:text-white">
                        30H
                    </button>
                </div>

                <div class="hidden sm:flex items-center gap-3 text-xs font-bold font-mono">
                    <span class="flex items-center gap-1.5 text-cyan-400"><span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Lari</span>
                    <span class="flex items-center gap-1.5 text-emerald-400"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Jalan</span>
                    <span class="flex items-center gap-1.5 text-orange-400"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Bersepeda</span>
                </div>
            </div>
        </div>

        <div class="h-72 sm:h-80 relative w-full">
            <canvas id="smoothProgressChart"></canvas>
        </div>
    </div>

</main>

<!-- MODAL BAGIKAN PROFIL MULTI-PLATFORM (SISTEM NATIVE, INSTAGRAM, WHATSAPP, TELEGRAM, DLL) -->
<div id="profile-share-modal" class="fixed inset-0 z-[9990] flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-md hidden overflow-y-auto">
    <div class="glass-card-dark max-w-md w-full p-5 sm:p-6 rounded-3xl border border-gray-800 space-y-4 shadow-2xl relative my-auto">
        
        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-orange-400 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                Bagikan Profil
            </h3>
            <button type="button" onclick="closeProfileShareModal()" class="text-gray-400 hover:text-white text-xl font-bold px-1 cursor-pointer">&times;</button>
        </div>

        <div class="flex items-center bg-gray-950 p-1 rounded-2xl border border-gray-800 text-xs font-bold font-mono">
            <button type="button" onclick="switchShareTab('card')" id="tab-btn-card"
                class="flex-1 py-2 rounded-xl transition-all bg-orange-500 text-black shadow-md font-bold">
                Kartu Visual (IG/Story/Aplikasi)
            </button>
            <button type="button" onclick="switchShareTab('link')" id="tab-btn-link"
                class="flex-1 py-2 rounded-xl transition-all text-gray-400 hover:text-white font-bold">
                WhatsApp & Tautan
            </button>
        </div>

        <div id="share-content-card" class="space-y-4 flex flex-col items-center">
            <div class="relative w-full flex justify-center">
                <canvas id="profile-card-canvas" width="1080" height="1920" class="w-full max-w-[220px] h-auto rounded-2xl shadow-2xl border-2 border-orange-500/80 sport-glow-box"></canvas>
            </div>
            <p class="text-[10px] text-gray-400 text-center font-mono">Kartu sporty dilengkapi QR Code yang mengarah langsung ke profil saat dipindai!</p>

            <div class="grid grid-cols-2 gap-2 w-full">
                <button type="button" onclick="shareNativeCard()" 
                    class="py-3 bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-500 hover:opacity-90 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Bagikan
                </button>

                <button type="button" onclick="downloadProfileCard()" 
                    class="py-3 bg-gray-900 hover:bg-gray-800 text-gray-200 hover:text-white font-bold rounded-xl text-xs uppercase tracking-wider border border-gray-700 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh
                </button>
            </div>
        </div>

        <div id="share-content-link" class="space-y-4 hidden">
            <div class="p-3 bg-gray-900/90 rounded-2xl border border-gray-800 space-y-2">
                <span class="text-[10px] font-bold uppercase text-gray-400 block">Link Profil Publik</span>
                <div class="flex items-center gap-2 bg-gray-950 p-2 rounded-xl border border-gray-800">
                    <input type="text" id="share-url-input" readonly 
                        value="{{ route('user.profile.show', ['public_id' => $user->public_id]) }}" 
                        class="w-full bg-transparent text-xs font-mono text-gray-200 outline-none px-2 select-all">
                    <button type="button" onclick="copyProfileUrl()" 
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-orange-400 rounded-lg text-xs font-bold shrink-0 transition-colors cursor-pointer">
                        Salin
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <button type="button" onclick="shareToWhatsapp()" 
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.572-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    Kirim ke WhatsApp (Chat/Status)
                </button>
            </div>

            <div id="hidden-qrcode" class="hidden"></div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    let myChart = null;

    document.addEventListener("DOMContentLoaded", function () {
        const canvas = document.getElementById('smoothProgressChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const gradientCyan = ctx.createLinearGradient(0, 0, 0, 300);
        gradientCyan.addColorStop(0, 'rgba(6, 182, 212, 0.45)');
        gradientCyan.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        const gradientEmerald = ctx.createLinearGradient(0, 0, 0, 300);
        gradientEmerald.addColorStop(0, 'rgba(16, 185, 129, 0.45)');
        gradientEmerald.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const gradientOrange = ctx.createLinearGradient(0, 0, 0, 300);
        gradientOrange.addColorStop(0, 'rgba(249, 115, 22, 0.45)');
        gradientOrange.addColorStop(1, 'rgba(249, 115, 22, 0.0)');

        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Lari (KM)',
                        data: @json($runningData),
                        borderColor: '#06b6d4',
                        backgroundColor: gradientCyan,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#06b6d4'
                    },
                    {
                        label: 'Jalan (KM)',
                        data: @json($walkingData),
                        borderColor: '#10b981',
                        backgroundColor: gradientEmerald,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10b981'
                    },
                    {
                        label: 'Bersepeda (KM)',
                        data: @json($cyclingData),
                        borderColor: '#f97316',
                        backgroundColor: gradientOrange,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#f97316'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: { ticks: { color: '#9ca3af', font: { weight: 'bold', size: 11 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: '#9ca3af', font: { size: 11 } }, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                }
            }
        });
    });

    function updateChartPeriod(period, btn) {
        document.querySelectorAll('.period-btn').forEach(b => {
            b.className = "period-btn px-3 py-1.5 rounded-xl transition-all cursor-pointer text-gray-400 hover:text-white";
        });
        btn.className = "period-btn px-3 py-1.5 rounded-xl transition-all cursor-pointer bg-orange-500 text-black shadow-md font-black";

        fetch('/profile?period=' + period, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && myChart) {
                myChart.data.labels = data.chartLabels;
                myChart.data.datasets[0].data = data.runningData;
                myChart.data.datasets[1].data = data.walkingData;
                myChart.data.datasets[2].data = data.cyclingData;
                myChart.update();
            }
        })
        .catch(err => console.error('Gagal memperbarui grafik:', err));
    }

    function openProfileShareModal() {
        document.getElementById('profile-share-modal').classList.remove('hidden');
        const hiddenContainer = document.getElementById("hidden-qrcode");
        hiddenContainer.innerHTML = '';
        const profileUrl = document.getElementById('share-url-input').value;

        new QRCode(hiddenContainer, {
            text: profileUrl,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        setTimeout(() => { renderProfileCardCanvas(); }, 200);
    }

    function closeProfileShareModal() {
        document.getElementById('profile-share-modal').classList.add('hidden');
    }

    function switchShareTab(tab) {
        const btnCard = document.getElementById('tab-btn-card');
        const btnLink = document.getElementById('tab-btn-link');
        const contentCard = document.getElementById('share-content-card');
        const contentLink = document.getElementById('share-content-link');

        const activeClass = "flex-1 py-2 rounded-xl transition-all bg-orange-500 text-black shadow-md font-bold";
        const inactiveClass = "flex-1 py-2 rounded-xl transition-all text-gray-400 hover:text-white font-bold";

        if (tab === 'card') {
            btnCard.className = activeClass;
            btnLink.className = inactiveClass;
            contentCard.classList.remove('hidden');
            contentLink.classList.add('hidden');
            renderProfileCardCanvas();
        } else {
            btnLink.className = activeClass;
            btnCard.className = inactiveClass;
            contentLink.classList.remove('hidden');
            contentCard.classList.add('hidden');
        }
    }

    function renderProfileCardCanvas() {
        const canvas = document.getElementById('profile-card-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const name = document.getElementById('profile-display-name').innerText.trim();
        const username = document.getElementById('profile-display-username').innerText.trim();
        const run = document.getElementById('stat-run').innerText.trim();
        const walk = document.getElementById('stat-walk').innerText.trim();
        const cycle = document.getElementById('stat-cycle').innerText.trim();
        const dist = document.getElementById('stat-dist').innerText.trim();

        const bgGrad = ctx.createLinearGradient(0, 0, 0, canvas.height);
        bgGrad.addColorStop(0, '#030712');
        bgGrad.addColorStop(0.5, '#0b0f19');
        bgGrad.addColorStop(1, '#030712');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.shadowColor = '#f97316';
        ctx.shadowBlur = 40;
        ctx.strokeStyle = '#f97316';
        ctx.lineWidth = 12;

        const margin = 60;
        const boxW = canvas.width - (margin * 2);
        const boxH = canvas.height - (margin * 2);
        
        ctx.beginPath();
        ctx.roundRect(margin, margin, boxW, boxH, 40);
        ctx.stroke();

        ctx.shadowBlur = 0;

        ctx.fillStyle = "#f97316";
        ctx.font = "bold italic 60px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("WOYuk!", 120, 180);

        ctx.fillStyle = "rgba(255, 255, 255, 0.6)";
        ctx.font = "bold 24px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("OBAH BARENG, KONSISTEN BENDINO", 120, 225);

        ctx.strokeStyle = "rgba(255, 255, 255, 0.15)";
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(120, 260);
        ctx.lineTo(960, 260);
        ctx.stroke();

        const avatarImg = document.getElementById('user-avatar-img');
        const avatarX = 120, avatarY = 320, avatarSize = 220;

        ctx.save();
        ctx.shadowColor = 'rgba(249, 115, 22, 0.5)';
        ctx.shadowBlur = 20;
        ctx.fillStyle = '#111827';
        ctx.strokeStyle = '#f97316';
        ctx.lineWidth = 6;
        
        ctx.beginPath();
        ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 35);
        ctx.fill();
        ctx.stroke();
        ctx.restore();

        if (avatarImg && avatarImg.complete && avatarImg.naturalWidth !== 0) {
            ctx.save();
            ctx.beginPath();
            ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 35);
            ctx.clip();
            ctx.drawImage(avatarImg, avatarX, avatarY, avatarSize, avatarSize);
            ctx.restore();
        } else {
            const initials = document.getElementById('user-avatar-initials') ? document.getElementById('user-avatar-initials').innerText.trim() : 'U';
            ctx.fillStyle = '#f97316';
            ctx.font = "bold 80px 'Helvetica Neue', Helvetica, Arial, sans-serif";
            ctx.textAlign = 'center';
            ctx.fillText(initials, avatarX + (avatarSize / 2), avatarY + 140);
            ctx.textAlign = 'left';
        }

        ctx.fillStyle = "#ffffff";
        ctx.font = "bold 52px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(name.toUpperCase(), 380, 410);

        ctx.fillStyle = "#06b6d4";
        ctx.font = "bold 32px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("@" + username, 380, 465);

        const gridY = 620;

        ctx.fillStyle = "rgba(17, 24, 39, 0.8)";
        ctx.strokeStyle = "rgba(168, 85, 247, 0.4)";
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.roundRect(120, gridY, 400, 180, 25);
        ctx.fill(); ctx.stroke();

        ctx.fillStyle = "#9ca3af";
        ctx.font = "bold 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("TOTAL JARAK", 150, gridY + 50);
        ctx.fillStyle = "#c084fc";
        ctx.font = "bold 50px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(dist + " KM", 150, gridY + 125);

        ctx.fillStyle = "rgba(17, 24, 39, 0.8)";
        ctx.strokeStyle = "rgba(6, 182, 212, 0.4)";
        ctx.beginPath();
        ctx.roundRect(560, gridY, 400, 180, 25);
        ctx.fill(); ctx.stroke();

        ctx.fillStyle = "#9ca3af";
        ctx.font = "bold 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("LARI", 590, gridY + 50);
        ctx.fillStyle = "#22d3ee";
        ctx.font = "bold 50px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(run + " SESI", 590, gridY + 125);

        ctx.fillStyle = "rgba(17, 24, 39, 0.8)";
        ctx.strokeStyle = "rgba(16, 185, 129, 0.4)";
        ctx.beginPath();
        ctx.roundRect(120, gridY + 220, 400, 180, 25);
        ctx.fill(); ctx.stroke();

        ctx.fillStyle = "#9ca3af";
        ctx.font = "bold 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("JALAN", 150, gridY + 270);
        ctx.fillStyle = "#34d399";
        ctx.font = "bold 50px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(walk + " SESI", 150, gridY + 345);

        ctx.fillStyle = "rgba(17, 24, 39, 0.8)";
        ctx.strokeStyle = "rgba(249, 115, 22, 0.4)";
        ctx.beginPath();
        ctx.roundRect(560, gridY + 220, 400, 180, 25);
        ctx.fill(); ctx.stroke();

        ctx.fillStyle = "#9ca3af";
        ctx.font = "bold 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("BERSEPEDA", 590, gridY + 270);
        ctx.fillStyle = "#fb923c";
        ctx.font = "bold 50px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText(cycle + " SESI", 590, gridY + 345);

        const qrImg = document.querySelector('#hidden-qrcode img');
        const qrCanvas = document.querySelector('#hidden-qrcode canvas');
        const qrY = 1180, qrSize = 320;

        ctx.fillStyle = "#ffffff";
        ctx.beginPath();
        ctx.roundRect(380, qrY, qrSize, qrSize, 30);
        ctx.fill();

        if (qrImg && qrImg.src) {
            ctx.drawImage(qrImg, 400, qrY + 20, 280, 280);
        } else if (qrCanvas) {
            ctx.drawImage(qrCanvas, 400, qrY + 20, 280, 280);
        }

        ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
        ctx.font = "bold 26px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.textAlign = 'center';
        ctx.fillText("PINDAI QR UNTUK INTIP PROFIL", 540, qrY + 380);
        ctx.textAlign = 'left';

        ctx.fillStyle = "rgba(255, 255, 255, 0.4)";
        ctx.font = "bold 22px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("woyuk.app", 120, 1810);

        ctx.fillStyle = "#f97316";
        ctx.font = "bold italic 26px 'Helvetica Neue', Helvetica, Arial, sans-serif";
        ctx.fillText("#OBAHBARENG", 760, 1810);
    }

    function shareToWhatsapp() {
        const url = document.getElementById('share-url-input').value;
        const name = document.getElementById('profile-display-name').innerText.trim();
        const text = encodeURIComponent(`Yuk intip profil olahraga ${name} di WOYuk! 🚀\n${url}`);
        window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
    }

    async function shareNativeCard() {
        const canvas = document.getElementById('profile-card-canvas');
        if (!canvas) return;

        const url = document.getElementById('share-url-input').value;
        const name = document.getElementById('profile-display-name').innerText.trim();

        canvas.toBlob(async (blob) => {
            if (!blob) {
                alert("Gagal memproses gambar kartu profil.");
                return;
            }

            const file = new File([blob], 'profil-woyuk.png', { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        title: 'Profil Atlet WOYuk!',
                        text: `Yuk intip profil olahraga ${name} di WOYuk! 🚀\n${url}`,
                        files: [file]
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') downloadProfileCard();
                }
            } else if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Profil Atlet WOYuk!',
                        text: `Yuk intip profil olahraga ${name} di WOYuk! 🚀`,
                        url: url
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') downloadProfileCard();
                }
            } else {
                downloadProfileCard();
                alert('Gambar kartu berhasil diunduh!');
            }
        }, 'image/png');
    }

    function downloadProfileCard() {
        const canvas = document.getElementById('profile-card-canvas');
        if (!canvas) return;

        const link = document.createElement('a');
        link.download = 'profil-woyuk.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function copyProfileUrl() {
        const input = document.getElementById('share-url-input');
        input.select();
        navigator.clipboard.writeText(input.value);
        alert('Link profil berhasil disalin!');
    }
</script>
@endpush
@endsection