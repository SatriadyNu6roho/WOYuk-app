@extends('layouts.app')

@section('title', 'WOYuk! — Obah Bareng, Konsisten Bendino')

@section('content')
<!-- Universal Navbar -->
@include('components.navbar')

<!-- HERO SECTION -->
<section class="relative min-h-[calc(100vh-80px)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 overflow-hidden bg-gray-950">
    
    <!-- Background Glow Subtle Accent -->
    <div class="absolute top-1/4 left-10 w-96 h-96 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative z-10">
        
        <!-- SISI KIRI: Jargon Jawa & Image Preview -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- Badge Heritage Sport -->
            <div data-aos="fade-right" data-aos-delay="100" class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-card-dark border border-orange-500/40 text-orange-400 text-xs font-bold uppercase tracking-widest shadow-md">
                <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                WO, yuk olahraga!
            </div>

            <!-- Jargon Utama Bahasa Jawa (Teks Putih Kontras) -->
            <div class="space-y-4">
                <h1 data-aos="fade-right" data-aos-delay="200" class="text-4xl sm:text-6xl font-black italic tracking-tight text-gray-400 uppercase leading-none">
                    OBAH BARENG, <br>
                    <span class="text-orange-500">
                        KONSISTEN BENDINO.
                    </span>
                </h1>
                <p data-aos="fade-right" data-aos-delay="300" class="text-gray-300 text-base sm:text-lg font-normal max-w-xl leading-relaxed">
                    Lacak mlayu, mlaku, lan pit-pitanmu presisi tanpa batas.
                </p>
            </div>

            <!-- HERO IMAGE SECTION -->
            <div data-aos="fade-up" data-aos-delay="400" class="relative rounded-3xl overflow-hidden glass-card-dark border border-gray-800/90 p-2 max-w-xl shadow-2xl">
                <img src="{{ asset('assets/images/woyuk.jpg') }}" 
                     alt="WOYuk! Sports Tracking" 
                     class="w-full h-64 sm:h-72 object-cover rounded-2xl transition-all hover:scale-105 duration-500"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-64 sm:h-72 bg-gradient-to-br from-gray-900 to-gray-950 rounded-2xl flex flex-col items-center justify-center text-gray-400 border border-gray-800 p-6 text-center\'><svg class=\'w-12 h-12 mb-3 text-orange-500/80\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M13 10V3L4 14h7v7l9-11h-7z\'/></svg><span class=\'text-xs font-bold uppercase tracking-wider text-gray-200\'>WOYuk! Sports Activity Image Placeholder</span></div>';">
            </div>

            <!-- TICKER RUNNING TEXT REAL-TIME AKTIVITAS (CENTERED) -->
            <div data-aos="fade-up" data-aos-delay="500" class="w-full max-w-xl flex justify-center pt-2">
                <div class="glass-card-dark border border-gray-800/90 rounded-full px-6 py-3 shadow-2xl relative overflow-hidden w-full max-w-md h-12 flex items-center justify-center">
                    <div id="sport-ticker-container" class="relative w-full h-full flex items-center justify-center">
                        
                        <!-- Item 1: Lari -->
                        <div class="ticker-item absolute inset-0 flex items-center justify-center gap-2.5 transition-all duration-500 opacity-100 transform translate-y-0 text-xs font-bold uppercase tracking-wider text-gray-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 shadow-sm shadow-cyan-500 animate-pulse"></span>
                            <span class="text-gray-300">Lari:</span>
                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="font-mono text-cyan-400 font-black text-sm">{{ number_format($runningCount ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-400 font-normal lowercase">aktivitas</span>
                        </div>

                        <!-- Item 2: Jalan -->
                        <div class="ticker-item absolute inset-0 flex items-center justify-center gap-2.5 transition-all duration-500 opacity-0 transform translate-y-4 text-xs font-bold uppercase tracking-wider text-gray-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500 animate-pulse"></span>
                            <span class="text-gray-300">Jalan:</span>
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span class="font-mono text-emerald-400 font-black text-sm">{{ number_format($walkingCount ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-400 font-normal lowercase">aktivitas</span>
                        </div>

                        <!-- Item 3: Bersepeda -->
                        <div class="ticker-item absolute inset-0 flex items-center justify-center gap-2.5 transition-all duration-500 opacity-0 transform translate-y-4 text-xs font-bold uppercase tracking-wider text-gray-200">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-500 shadow-sm shadow-orange-500 animate-pulse"></span>
                            <span class="text-gray-300">Bersepeda:</span>
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono text-orange-400 font-black text-sm">{{ number_format($cyclingCount ?? 0, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-gray-400 font-normal lowercase">aktivitas</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- SISI KANAN: Glassmorphism Login Card -->
        <div class="lg:col-span-5" data-aos="fade-left" data-aos-delay="300">
            <div class="glass-card-dark p-8 sm:p-10 rounded-3xl border border-gray-800/90 shadow-2xl relative space-y-6">
                
                <div class="border-b border-gray-800 pb-4">
                    <h2 class="text-xl font-black italic uppercase text-white tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-orange-600 rounded-full inline-block"></span>
                        Mlebu Akun
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">Mulai catet aktivitas olahragamu dina iki.</p>
                </div>

                @if($errors->any())
                    <div class="p-4 bg-red-500/10 border border-red-500/40 rounded-2xl text-red-300 text-xs font-bold">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Email</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            placeholder="YOUR@GMAIL.COM"
                            class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold focus:outline-none focus:border-orange-500 transition-colors placeholder-gray-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-300 uppercase mb-2">Password</label>
                        <input type="password" name="password" required
                            placeholder="••••••••"
                            class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold focus:outline-none focus:border-orange-500 transition-colors placeholder-gray-400">
                    </div>

                    <button type="submit" 
                        class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl uppercase tracking-wider text-xs transition-all duration-200 sport-glow cursor-pointer">
                        Masuk / Login
                    </button>
                </form>

                <!-- Divider Pembatas -->
                <div class="relative text-center my-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-800"></div>
                    </div>
                    <span class="relative bg-slate-900 px-3 text-[10px] text-gray-400 uppercase font-mono font-bold tracking-widest">Utawa</span>
                </div>

                <!-- Opsi Masuk Pakai Google (SUDAH DIGANTI PAKAI BASE64 DARI LOGIN) -->
                <a href="{{ route('auth.google') }}" 
                    class="w-full flex items-center justify-center gap-3 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold py-3 rounded-xl border border-gray-700 text-xs sm:text-sm transition-all duration-200 cursor-pointer shadow-md">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbD0iI0VBNDMzNSIgZD0iTTI0IDkuNWMzLjU0IDAgNi43MSAxLjIyIDkuMjEgMy42bDYuODUtNi44NUMzNS45IDIuMzggMzAuNDcgMCAyNCAwIDE0LjYyIDAgNi41MSA1LjM4IDIuNTYgMTMuMjJsNy45OCA2LjE5QzEyLjQzIDEzLjcyIDE3Ljc0IDkuNSAyNCA5LjV6Ii8+PHBhdGggZmlsbD0iIzQyODVGNCIgZD0iTTQ2Ljk4IDI0LjU1YzAtMS41Ny0uMTUtMy4wOS0uMzgtNC41NUgyNHY5LjAyaDEyLjk0Yy0uNTggMi45Ni0yLjI2IDUuNDgtNC43OCA3LjE4bDcuNzMgNmM0LjUxLTQuMTggNy4wOS0xMC4zNiA3LjA5LTE3LjY1eiIvPjxwYXRoIGZpbGw9IiNGQkJDMDQiIGQ9Ik0xMC41MyAyOC41OWMtLjQ4LTEuNDUtLjc2LTIuOTktLjc2LTQuNTlzLjI3LTMuMTQuNzYtNC41OWwtNy45OC02LjE5Qy45MiAxNi40NiAwIDIwLjEyIDAgMjRjMCAzLjg4LjkyIDcuNTQgMi41NiAxMC43OGw3Ljk3LTYuMTl6Ii8+PHBhdGggZmlsbD0iIzM0QTg1MyIgZD0iTTI0IDQ4YzYuNDggMCAxMS45My0yLjEzIDE1Ljg5LTUuODFsLTcuNzMtNmMtMi4xNSAxLjQ1LTQuOTIgMi4zLTguMTYgMi4zLTYuMjYgMC0xMS41Ny00LjIyLTEzLjQ3LTkuOTFsLTcuOTggNi4xOUM2LjUxIDQyLjYyIDE0LjYyIDQ4IDI0IDQ4eiIvPjwvc3ZnPg==" 
                    alt="Google Logo" class="w-4 h-4 shrink-0 block object-contain">
                    <span>Masuk nganggo Google</span>
                </a>

                <!-- Footer Card Link -->
                <div class="pt-4 border-t border-gray-800 text-center text-xs text-gray-400">
                    Durung duwe akun? 
                    <a href="{{ route('register') }}" class="text-orange-400 font-bold hover:underline ml-1">
                        Gawe akun anyar
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- SECTION FITUR-FITUR UTAMA PROJECT WOYUK! -->
<section id="fitur-section" class="py-16 sm:py-24 bg-gray-950 border-t border-gray-900 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-12">
        
        <div data-aos="fade-up" class="text-center space-y-3 max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-4xl font-black italic uppercase text-white tracking-wider">
                FITUR UNGGULAN <span class="text-orange-500">WOYUK!</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-400">
                Aplikasi pencatat aktivitas olahraga modern kanthi tampilan presisi lan responsif.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Fitur 1: Tracking GPS -->
            <div data-aos="fade-up" data-aos-delay="100" class="glass-card-dark p-6 rounded-3xl border border-gray-800 space-y-4 hover:border-orange-500/50 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-lg font-black italic uppercase text-white">Tracking GPS Realtime</h3>
                <p class="text-xs text-gray-400 leading-relaxed">Pencatatan rute jalan, lari, lan bersepeda presisi kanthi peta interaktif Leaflet JS.</p>
            </div>

            <!-- Fitur 2: Daily Streak System -->
            <div data-aos="fade-up" data-aos-delay="200" class="glass-card-dark p-6 rounded-3xl border border-gray-800 space-y-4 hover:border-cyan-500/50 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg font-black italic uppercase text-white">Streak Active System</h3>
                <p class="text-xs text-gray-400 leading-relaxed">Jaga konsistensi olahraga harian lan cetak rekor streak paling dhuwur saben dina.</p>
            </div>

            <!-- Fitur 3: Target Metrik -->
            <div data-aos="fade-up" data-aos-delay="300" class="glass-card-dark p-6 rounded-3xl border border-gray-800 space-y-4 hover:border-emerald-500/50 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-black italic uppercase text-white">Target Jarak & Kalori</h3>
                <p class="text-xs text-gray-400 leading-relaxed">Pantau akumulasi jarak (KM) lan estimasi kalori (KCAL) terkuantifikasi harian.</p>
            </div>

            <!-- Fitur 4: Arsip & Visualisasi Rute -->
            <div data-aos="fade-up" data-aos-delay="400" class="glass-card-dark p-6 rounded-3xl border border-gray-800 space-y-4 hover:border-amber-500/50 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-black italic uppercase text-white">Arsip & Rekap Rute</h3>
                <p class="text-xs text-gray-400 leading-relaxed">Riwayat aktivitas tersimpan rapi lengkap kanthi filter olahraga lan peta rekap rute GPS.</p>
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const items = document.querySelectorAll('#sport-ticker-container .ticker-item');
        if (items.length > 0) {
            let currentIndex = 0;
            setInterval(() => {
                const current = items[currentIndex];
                current.classList.remove('opacity-100', 'translate-y-0');
                current.classList.add('opacity-0', '-translate-y-4');

                currentIndex = (currentIndex + 1) % items.length;
                const next = items[currentIndex];
                next.classList.remove('opacity-0', 'translate-y-4');
                next.classList.add('opacity-100', 'translate-y-0');

                setTimeout(() => {
                    current.classList.remove('-translate-y-4');
                    current.classList.add('translate-y-4');
                }, 500);
            }, 3000);
        }
    });
</script>
@endpush
@endsection