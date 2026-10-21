<nav class="sticky top-0 z-50 glass-card-dark border-b border-gray-800/80 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- KIRI: Logo & Public User ID -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-xl bg-orange-600/20 border border-orange-500/40 flex items-center justify-center text-orange-500 group-hover:scale-105 transition-transform sport-glow">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-black italic tracking-widest text-orange-500 uppercase">WOYuk!</span>
                </a>

                <!-- Badge Public User ID 10-Digit -->
                <div class="hidden sm:inline-flex items-center px-3 py-1 rounded-full bg-gray-800 border border-gray-700 text-[11px] font-mono text-gray-400">
                    ID: <span class="text-orange-400 font-bold ml-1">{{ Auth::user()->public_id ?? '0000000000' }}</span>
                </div>
            </div>

            <!-- KANAN: Search Trigger & Menu Navigasi Utama -->
            <div class="flex items-center space-x-1 sm:space-x-4 text-xs font-bold uppercase tracking-wider">
                
                <!-- Search Button Trigger (Terlihat di Mobile & Desktop) -->
                <button type="button" onclick="openSearchModal()" class="px-3 py-2 rounded-xl text-gray-200 hover:text-orange-400 hover:bg-gray-800/80 transition-all flex items-center gap-2 border border-gray-700/60 bg-gray-900/50 shadow-sm">
                    <svg class="w-4 h-4 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="text-xs text-gray-300 font-medium">Cari<span class="hidden md:inline"> User</span>...</span>
                </button>

                <!-- Menu Settings -->
                <a href="#settings" class="px-3.5 py-2 rounded-xl text-gray-300 hover:text-orange-400 hover:bg-gray-800/60 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="hidden md:inline">Settings</span>
                </a>

                <!-- Menu Arsip -->
                <a href="#archive" class="px-3.5 py-2 rounded-xl text-gray-300 hover:text-orange-400 hover:bg-gray-800/60 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="hidden md:inline">Arsip</span>
                </a>

                <!-- Menu Profile -->
                <a href="#profile" class="px-3.5 py-2 rounded-xl text-gray-300 hover:text-orange-400 hover:bg-gray-800/60 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="hidden md:inline">Profile</span>
                </a>

                <!-- Form Logout -->
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>

            </div>

        </div>
    </div>
</nav>