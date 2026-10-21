@extends('layouts.app')

@section('title', 'Daftar Akun — WOYuk!')

@section('content')
<div class="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 bg-gray-950 text-white">
    <div class="w-full max-w-md mx-auto glass-card-dark p-6 sm:p-8 rounded-3xl border border-gray-800 shadow-2xl space-y-6">
        <!-- Logo Header -->
        <div class="text-center flex flex-col items-center">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-12 sm:h-14 w-auto object-contain mb-2">
            <p class="text-xs text-gray-400 uppercase tracking-widest font-semibold">Gawe Akun Anyar</p>
        </div>

        @if($errors->has('error'))
            <div class="p-3 bg-red-500/20 border border-red-500/50 rounded-xl text-red-300 text-xs">
                {{ $errors->first('error') }}
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
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

            <!-- Textbox Konfirmasi Password -->
            <div>
                <label class="block text-xs font-bold text-gray-300 uppercase mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••"
                    class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 font-bold focus:outline-none focus:border-orange-500 transition-all placeholder-gray-400">
            </div>

            <button type="submit" 
                class="w-full bg-orange-600 hover:bg-orange-500 text-white font-bold py-3.5 rounded-xl uppercase tracking-wider text-xs sm:text-sm transition-all duration-200 sport-glow mt-2 cursor-pointer shadow-lg shadow-orange-600/30">
                Daftar Akun
            </button>
        </form>

        <div class="pt-2 text-center text-xs text-gray-400 border-t border-gray-800/80">
            Wis duwe akun? 
            <a href="{{ route('login') }}" class="text-orange-400 font-bold hover:underline ml-1">
                Mbuet / Login
            </a>
        </div>
    </div>
</div>
@endsection