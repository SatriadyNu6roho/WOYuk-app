@extends('layouts.app')

@section('title', 'Pengaturan Profil — WOYuk!')

@section('content')
@include('components.navbar')

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 pb-24">

    <!-- HEADER SECTION -->
    <div class="flex items-center justify-between border-b border-gray-800 pb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black italic uppercase text-white tracking-wider flex items-center gap-3">
                <span class="w-3 h-8 bg-orange-600 rounded-full inline-block"></span>
                Pengaturan Akun
            </h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1"></p>
        </div>
    </div>

    <!-- NOTIFIKASI SUKSES -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/40 rounded-2xl text-emerald-300 text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- NOTIFIKASI ERROR VALIDASI -->
    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/40 rounded-2xl text-red-300 text-xs font-bold space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        
        <!-- FORM 1: DATA PROFIL, FOTO, USERNAME & BIO -->
        <div class="md:col-span-7 glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800 space-y-6">
            <div class="flex items-center gap-3 border-b border-gray-800/80 pb-4">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-black italic uppercase text-white tracking-wider">Informasi Profil</h2>
                    <p class="text-[10px] text-gray-400"></p>
                </div>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- FOTO PROFIL AVATAR -->
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl bg-gray-900 border-2 border-orange-500 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-lg">
                        @if($profile->avatar)
                            <img src="{{ asset('storage/' . $profile->avatar) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl font-black text-orange-500 uppercase font-mono">{{ substr($profile->name ?? 'U', 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-200 uppercase">Ganti Foto Profil</label>
                        <input type="file" name="avatar" accept="image/*" class="text-xs text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-800 file:text-orange-400 hover:file:bg-gray-700 cursor-pointer">
                        <p class="text-[10px] text-gray-500">(Maksimal 2MB)</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $profile->name ?? '') }}" required
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white font-bold focus:outline-none focus:border-orange-500 transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-sm text-gray-500 font-mono">@</span>
                        <input type="text" name="username" value="{{ old('username', $profile->username ?? '') }}" placeholder="pelari_handal"
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl pl-8 pr-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-orange-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Bio</label>
                    <textarea name="bio" rows="2" placeholder="Tuliskan kata-kata semangat atau deskripsi singkat..."
                        class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-orange-500 transition-colors">{{ old('bio', $profile->bio ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Berat (KG)</label>
                        <input type="number" step="0.1" name="weight_kg" value="{{ old('weight_kg', $profile->weight_kg ?? '') }}" placeholder="65"
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-orange-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Tinggi (CM)</label>
                        <input type="number" step="0.1" name="height_cm" value="{{ old('height_cm', $profile->height_cm ?? '') }}" placeholder="170"
                            class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white font-mono focus:outline-none focus:border-orange-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Jenis Kelamin</label>
                    <select name="gender" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white font-bold focus:outline-none focus:border-orange-500 transition-colors">
                        <option value="">-- Pilih --</option>
                        <option value="male" {{ (old('gender', $profile->gender ?? '') === 'male') ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="female" {{ (old('gender', $profile->gender ?? '') === 'female') ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl uppercase tracking-wider text-xs transition-all sport-glow cursor-pointer">
                    Simpan Profil
                </button>
            </form>
        </div>

        <!-- FORM 2: PREFERENSI SETTINGS ZONA WAKTU -->
        <div class="md:col-span-5 glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800 space-y-6 h-fit">
            <div class="flex items-center gap-3 border-b border-gray-800/80 pb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-black italic uppercase text-white tracking-wider">Pengaturan Sistem</h2>
                    <p class="text-[10px] text-gray-400">ShareLoc-Mu</p>
                </div>
            </div>

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-200 uppercase mb-2">Zona Waktu (Timezone)</label>
                    <select name="timezone" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white font-bold focus:outline-none focus:border-amber-500 transition-colors">
                        <option value="Asia/Jakarta" {{ (old('timezone', $setting->timezone ?? '') === 'Asia/Jakarta') ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                        <option value="Asia/Makassar" {{ (old('timezone', $setting->timezone ?? '') === 'Asia/Makassar') ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                        <option value="Asia/Jayapura" {{ (old('timezone', $setting->timezone ?? '') === 'Asia/Jayapura') ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white font-bold py-3.5 rounded-xl uppercase tracking-wider text-xs transition-all sport-glow cursor-pointer">
                    Simpan Pengaturan
                </button>
            </form>
        </div>

    </div>

</main>
@endsection