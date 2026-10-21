@push('styles')
<style>
    /* Styling Floating Glassmorphism Capsule Sidebar Desktop */
    #desktop-sidebar {
        transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1), padding 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: width, padding;
    }
    
    #desktop-sidebar.sidebar-expanded {
        width: 16rem; /* 256px */
    }
    
    #desktop-sidebar.sidebar-collapsed {
        width: 5rem; /* 80px */
    }

    /* Layout Header saat Mode Collapsed: Tersusun Vertikal Presisi di Tengah */
    #desktop-sidebar.sidebar-collapsed .sidebar-header-wrapper {
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 0.75rem !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: 100% !important;
    }

    /* Presisi Centering Elemen <a> Pembungkus Logo saat Collapsed */
    #desktop-sidebar.sidebar-collapsed .sidebar-header-wrapper a {
        justify-content: center !important;
        width: 100% !important;
        gap: 0 !important;
    }

    /* Transisi Halus Elemen Teks & Logo tanpa Efek Melompat/Snap */
    .sidebar-text,
    .sidebar-user-details,
    .sidebar-logo-text,
    .sidebar-footer-text,
    .sidebar-logo-full {
        transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), margin 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        overflow: hidden;
        opacity: 1;
        max-width: 200px;
    }

    /* Saat Sidebar Collapsed: Fade Out & Shrink Max-Width secara Mulus */
    #desktop-sidebar.sidebar-collapsed .sidebar-text,
    #desktop-sidebar.sidebar-collapsed .sidebar-user-details,
    #desktop-sidebar.sidebar-collapsed .sidebar-logo-text,
    #desktop-sidebar.sidebar-collapsed .sidebar-footer-text,
    #desktop-sidebar.sidebar-collapsed .sidebar-logo-full {
        opacity: 0;
        max-width: 0;
        margin-left: 0 !important;
        margin-right: 0 !important;
        pointer-events: none;
    }

    /* Transisi Mulus Logo Ringkas Icon (Mode Collapsed) */
    .sidebar-logo-icon-only {
        transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        overflow: hidden;
    }

    #desktop-sidebar:not(.sidebar-collapsed) .sidebar-logo-icon-only {
        opacity: 0;
        max-width: 0;
        pointer-events: none;
    }

    #desktop-sidebar.sidebar-collapsed .sidebar-logo-icon-only {
        opacity: 1;
        max-width: 40px;
        margin: 0 auto !important;
    }

    #desktop-sidebar.sidebar-collapsed #sidebar-toggle-btn {
        margin: 0 auto !important;
    }

    /* Presisi Centering untuk Nav Item & User Card saat Collapsed */
    .sidebar-nav-item,
    .sidebar-user-card {
        transition: padding 0.35s cubic-bezier(0.4, 0, 0.2, 1), gap 0.35s cubic-bezier(0.4, 0, 0.2, 1), justify-content 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #desktop-sidebar.sidebar-collapsed .sidebar-nav-item {
        justify-content: center !important;
        gap: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    #desktop-sidebar.sidebar-collapsed .sidebar-user-card {
        justify-content: center !important;
        gap: 0 !important;
        padding: 0.5rem !important;
    }

    /* Active Curved Pill Accent */
    .sidebar-active-pill {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        box-shadow: 0 0 20px rgba(249, 115, 22, 0.45);
        color: #ffffff !important;
    }

    /* Bottom Nav Bar Mobile Accent */
    .bottom-nav-active {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        box-shadow: 0 0 18px rgba(249, 115, 22, 0.5);
        color: #ffffff !important;
        transform: translateY(-5px);
    }
    
    .bottom-nav-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

<!-- ==========================================
     1. TOP HEADER BRANDING (KHUSUS MOBILE)
     ========================================== -->
<header class="md:hidden sticky top-0 z-40 glass-card-dark border-b border-gray-800/80 backdrop-blur-md px-4 h-16 flex items-center justify-between">
    <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2">
        <img src="{{ asset('images/logo.svg') }}" alt="WOYuk! Logo" class="h-8 w-auto object-contain">
    </a>

    @auth
    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-900 border border-gray-700 text-[10px] font-mono text-gray-300">
        ID: <span class="text-orange-400 font-bold ml-1">{{ Auth::user()->public_id ?? '0000000000' }}</span>
    </div>
    @else
    <div class="flex items-center gap-2 text-xs font-bold uppercase font-mono">
        <a href="{{ route('login') }}" class="px-3 py-1.5 text-gray-300 hover:text-white">Masuk</a>
        <a href="{{ route('register') }}" class="px-3 py-1.5 bg-orange-600 text-white rounded-xl shadow-md">Daftar</a>
    </div>
    @endauth
</header>

<!-- ==========================================
     2. COLLAPSIBLE GLASSMORPHISM SIDEBAR (KHUSUS DESKTOP)
     ========================================== -->
<aside id="desktop-sidebar" 
       class="hidden md:flex fixed top-4 left-4 bottom-4 glass-card-dark border border-slate-800/80 rounded-3xl backdrop-blur-2xl z-50 flex-col justify-between p-4 shadow-2xl sidebar-expanded">
    
    <div class="space-y-6">
        <!-- HEADER SIDEBAR: LOGO BRANDING UTAMA & TOMBOL TOGGLE ARROW -->
        <div class="sidebar-header-wrapper flex items-center justify-between gap-2 px-1 transition-all duration-300">
            <a href="{{ Auth::check() ? route('dashboard') : url('/') }}" class="flex items-center gap-3 overflow-hidden">
                <!-- Logo Utuh SVG (Tampil saat Sidebar Expanded) -->
                <img src="{{ asset('images/logo.svg') }}" alt="WOYuk! Logo" class="sidebar-logo-full h-9 w-auto object-contain shrink-0">
                
                <!-- Logo Ikon Ringkas (Tampil saat Sidebar Collapsed) -->
                <div class="sidebar-logo-icon-only w-10 h-10 rounded-2xl bg-orange-600/20 border border-orange-500/40 flex items-center justify-center shrink-0 p-1.5">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Icon Logo" class="w-full h-full object-contain">
                </div>
            </a>

            <!-- Tombol Toggle Perlebar / Persempit Sidebar -->
            <button type="button" onclick="toggleDesktopSidebar()" id="sidebar-toggle-btn" 
                    title="Perlebar / Persempit Navbar"
                    class="p-2 rounded-xl bg-gray-900/80 hover:bg-gray-800 text-orange-400 border border-gray-700/80 transition-all cursor-pointer shrink-0">
                <svg id="toggle-arrow-icon" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        @auth
        <!-- USER PROFILE STATUS CARD -->
        <div class="sidebar-user-card p-2.5 bg-gray-950/70 rounded-2xl border border-gray-800/90 flex items-center gap-3 transition-all">
            <div class="w-9 h-9 rounded-xl bg-orange-600/20 border border-orange-500/40 flex items-center justify-center text-orange-400 font-black text-xs uppercase shrink-0 overflow-hidden">
                @if(Auth::user()->profile && Auth::user()->profile->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->profile->avatar) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(Auth::user()->profile->name ?? Auth::user()->email, 0, 2)) }}
                @endif
            </div>
            <div class="sidebar-user-details overflow-hidden min-w-0">
                <div class="text-xs font-bold text-white truncate">{{ Auth::user()->profile->name ?? 'User' }}</div>
                <div class="text-[10px] font-mono text-orange-400 truncate">ID: {{ Auth::user()->public_id }}</div>
            </div>
        </div>
        @endauth

        <!-- MENU NAVIGASI UTAMA -->
        <nav class="space-y-2 text-xs font-bold uppercase tracking-wider">
            @auth
                <!-- 1. BERANDA -->
                <a href="{{ route('dashboard') }}" 
                   title="Beranda"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all border {{ request()->routeIs('dashboard') ? 'sidebar-active-pill border-orange-500' : 'text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border-gray-800/80' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="sidebar-text truncate">Beranda</span>
                </a>

                <!-- 2. CARI USER -->
                <button type="button" onclick="openSearchModal()" 
                        title="Cari User"
                        class="sidebar-nav-item w-full flex items-center gap-3.5 px-3.5 py-3 rounded-2xl text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border border-gray-800/80 transition-all cursor-pointer">
                    <svg class="w-5 h-5 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span class="sidebar-text truncate">Cari User</span>
                </button>

                <!-- 3. ARSIP -->
                <a href="{{ route('archive.index') }}" 
                   title="Arsip Aktivitas"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all border {{ request()->routeIs('archive.index') ? 'sidebar-active-pill border-orange-500' : 'text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border-gray-800/80' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('archive.index') ? 'text-white' : 'text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="sidebar-text truncate">Arsip</span>
                </a>

                <!-- 4. PENGATURAN -->
                <a href="{{ route('settings.edit') }}" 
                   title="Pengaturan"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all border {{ request()->routeIs('settings.edit') ? 'sidebar-active-pill border-orange-500' : 'text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border-gray-800/80' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('settings.edit') ? 'text-white' : 'text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-text truncate">Pengaturan</span>
                </a>

                <!-- 5. PROFIL -->
                <a href="{{ route('profile.show') }}" 
                   title="Profil"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all border {{ request()->routeIs('profile.show') ? 'sidebar-active-pill border-orange-500' : 'text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border-gray-800/80' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('profile.show') ? 'text-white' : 'text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="sidebar-text truncate">Profil</span>
                </a>
            @else
                <!-- 1. MASUK / LOGIN (GUEST) -->
                <a href="{{ route('login') }}" 
                   title="Masuk"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all border text-gray-300 hover:text-white bg-gray-900/40 hover:bg-gray-800/80 border-gray-800/80">
                    <svg class="w-5 h-5 shrink-0 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-text truncate">Masuk</span>
                </a>

                <!-- 2. DAFTAR / REGISTER (GUEST) -->
                <a href="{{ route('register') }}" 
                   title="Daftar"
                   class="sidebar-nav-item flex items-center gap-3.5 px-3.5 py-3 rounded-2xl transition-all bg-orange-600 hover:bg-orange-500 text-white sport-glow">
                    <svg class="w-5 h-5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="sidebar-text truncate">Daftar</span>
                </a>
            @endauth
        </nav>
    </div>

    <!-- FOOTER COPYRIGHT SIDEBAR -->
    <div class="sidebar-footer-text text-[10px] font-mono text-gray-500 text-center transition-all">
        © 2026 <span class="text-orange-400 font-bold">WOYuk!</span>
    </div>
</aside>

<!-- ==========================================
     3. BOTTOM NAVIGATION BAR (KHUSUS MOBILE)
     ========================================== -->
@auth
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-slate-950/95 border-t border-slate-800/90 backdrop-blur-2xl px-3 py-2">
    <div class="flex items-center justify-around">

        <!-- 1. BERANDA -->
        <a href="{{ route('dashboard') }}" 
           class="bottom-nav-item flex flex-col items-center justify-center p-2 rounded-2xl w-14 h-14 {{ request()->routeIs('dashboard') ? 'bottom-nav-active' : 'text-gray-400 hover:text-white' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            @if(!request()->routeIs('dashboard'))
                <span class="text-[9px] font-bold font-mono mt-0.5">Teras</span>
            @endif
        </a>

        <!-- 2. CARI USER -->
        <button type="button" onclick="openSearchModal()" 
                class="bottom-nav-item flex flex-col items-center justify-center p-2 rounded-2xl w-14 h-14 text-gray-400 hover:text-white cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-[9px] font-bold font-mono mt-0.5">Golek</span>
        </button>

        <!-- 3. ARSIP -->
        <a href="{{ route('archive.index') }}" 
           class="bottom-nav-item flex flex-col items-center justify-center p-2 rounded-2xl w-14 h-14 {{ request()->routeIs('archive.index') ? 'bottom-nav-active' : 'text-gray-400 hover:text-white' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            @if(!request()->routeIs('archive.index'))
                <span class="text-[9px] font-bold font-mono mt-0.5">Arsip</span>
            @endif
        </a>

        <!-- 4. PENGATURAN -->
        <a href="{{ route('settings.edit') }}" 
           class="bottom-nav-item flex flex-col items-center justify-center p-2 rounded-2xl w-14 h-14 {{ request()->routeIs('settings.edit') ? 'bottom-nav-active' : 'text-gray-400 hover:text-white' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            @if(!request()->routeIs('settings.edit'))
                <span class="text-[9px] font-bold font-mono mt-0.5">Setelan</span>
            @endif
        </a>

        <!-- 5. PROFIL -->
        <a href="{{ route('profile.show') }}" 
           class="bottom-nav-item flex flex-col items-center justify-center p-2 rounded-2xl w-14 h-14 {{ request()->routeIs('profile.show') ? 'bottom-nav-active' : 'text-gray-400 hover:text-white' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            @if(!request()->routeIs('profile.show'))
                <span class="text-[9px] font-bold font-mono mt-0.5">Profil</span>
            @endif
        </a>

    </div>
</nav>
@endauth

<script>
    function applySidebarState(isCollapsed) {
        const sidebar = document.getElementById('desktop-sidebar');
        const arrowIcon = document.getElementById('toggle-arrow-icon');

        if (!sidebar) return;

        const state = isCollapsed ? 'collapsed' : 'expanded';
        document.documentElement.setAttribute('data-sidebar-state', state);

        if (isCollapsed) {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            if (arrowIcon) arrowIcon.style.transform = 'rotate(180deg)';
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            if (arrowIcon) arrowIcon.style.transform = 'rotate(0deg)';
        }
    }

    function toggleDesktopSidebar() {
        const sidebar = document.getElementById('desktop-sidebar');
        if (!sidebar) return;

        const isCollapsed = !sidebar.classList.contains('sidebar-collapsed');
        localStorage.setItem('woyuk_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        applySidebarState(isCollapsed);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedState = localStorage.getItem('woyuk_sidebar_collapsed') === 'true';
        applySidebarState(savedState);
    });
</script>