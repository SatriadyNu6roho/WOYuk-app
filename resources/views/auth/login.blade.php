@extends('layouts.app')

@section('title', 'Login — WOYuk!')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 bg-gray-950 text-white">
    <div class="w-full max-w-md mx-auto glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800 shadow-2xl space-y-6">
        
        <!-- 1. Header Logo & Label Salam -->
        <div class="text-center flex flex-col items-center">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-12 sm:h-14 w-auto object-contain mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold">Sugeng Rawuh Maneh</p>
        </div>

        @if(session('success'))
            <div class="p-3 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-300 text-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 bg-red-500/20 border border-red-500/50 rounded-xl text-red-300 text-xs">
                {{ session('error') }}
            </div>
        @endif

        <!-- 2. Form Textbox & Tombol Masuk Utama -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Textbox Email -->
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="masukkan email..."
                    class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold focus:outline-none focus:border-orange-500 transition-all placeholder-gray-400">
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Textbox Password -->
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold focus:outline-none focus:border-orange-500 transition-all placeholder-gray-400">
                @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Checkbox Elingi Aku -->
            <div class="flex items-center justify-between text-xs text-gray-400 pt-1">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-gray-800 border-gray-700 text-orange-500 focus:ring-0">
                    <span class="ml-2 font-medium">Elingi Aku</span>
                </label>
            </div>

            <!-- 3. Tombol Masuk Utama -->
            <button type="submit" 
                class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl uppercase tracking-wider text-xs sm:text-sm transition-all duration-200 sport-glow cursor-pointer shadow-lg shadow-orange-600/30">
                Masuk / Login
            </button>
        </form>

        <!-- 4. Divider Pembatas -->
        <div class="relative text-center my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-800"></div>
            </div>
            <span class="relative bg-slate-900 px-3 text-[10px] text-gray-400 uppercase font-mono font-bold tracking-widest">Utawa</span>
        </div>

        <!-- 5. Opsi Masuk Pakai Google (BASE64 IMG - 100% ANTI PATAH) -->
        <a href="{{ route('auth.google') }}" 
            class="w-full flex items-center justify-center gap-3 bg-gray-800 hover:bg-gray-700 text-gray-200 font-bold py-3 rounded-xl border border-gray-700 text-xs sm:text-sm transition-all duration-200 cursor-pointer shadow-md">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbD0iI0VBNDMzNSIgZD0iTTI0IDkuNWMzLjU0IDAgNi43MSAxLjIyIDkuMjEgMy42bDYuODUtNi44NUMzNS45IDIuMzggMzAuNDcgMCAyNCAwIDE0LjYyIDAgNi41MSA1LjM4IDIuNTYgMTMuMjJsNy45OCA2LjE5QzEyLjQzIDEzLjcyIDE3Ljc0IDkuNSAyNCA5LjV6Ii8+PHBhdGggZmlsbD0iIzQyODVGNCIgZD0iTTQ2Ljk4IDI0LjU1YzAtMS41Ny0uMTUtMy4wOS0uMzgtNC41NUgyNHY5LjAyaDEyLjk0Yy0uNTggMi45Ni0yLjI2IDUuNDgtNC43OCA3LjE4bDcuNzMgNmM0LjUxLTQuMTggNy4wOS0xMC4zNiA3LjA5LTE3LjY1eiIvPjxwYXRoIGZpbGw9IiNGQkJDMDQiIGQ9Ik0xMC41MyAyOC41OWMtLjQ4LTEuNDUtLjc2LTIuOTktLjc2LTQuNTlzLjI3LTMuMTQuNzYtNC41OWwtNy45OC02LjE5Qy45MiAxNi40NiAwIDIwLjEyIDAgMjRjMCAzLjg4LjkyIDcuNTQgMi41NiAxMC43OGw3Ljk3LTYuMTl6Ii8+PHBhdGggZmlsbD0iIzM0QTg1MyIgZD0iTTI0IDQ4YzYuNDggMCAxMS45My0yLjEzIDE1Ljg5LTUuODFsLTcuNzMtNmMtMi4xNSAxLjQ1LTQuOTIgMi4zLTguMTYgMi4zLTYuMjYgMC0xMS41Ny00LjIyLTEzLjQ3LTkuOTFsLTcuOTggNi4xOUM2LjUxIDQyLjYyIDE0LjYyIDQ4IDI0IDQ4eiIvPjwvc3ZnPg==" 
                alt="Google Logo" class="w-5 h-5 shrink-0 block object-contain">
            <span>Masuk nganggo Google</span>
        </a>

        <!-- 6. Footer Link Registrasi -->
        <div class="pt-2 text-center text-xs text-gray-400 border-t border-gray-800/80">
            Durung duwe akun? 
            <a href="{{ route('register') }}" class="text-orange-400 font-bold hover:underline ml-1">
                Gawe akun
            </a>
        </div>

    </div>
</div>
@endsection