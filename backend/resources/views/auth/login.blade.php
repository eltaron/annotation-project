<x-guest-layout>
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-emerald-500/20 mb-5 ring-1 ring-white/10">
            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
        <p class="text-sm text-slate-400 mt-2">Sign in to your account to continue</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div class="space-y-1.5">
            <label class="input-label" for="email">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input id="email" class="input-field !pl-10" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="input-label !mb-0" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-cyan-400/70 hover:text-cyan-300 transition font-medium" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </span>
                <input id="password" class="input-field !pl-10" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer group">
                <div class="relative">
                    <input id="remember_me" type="checkbox" class="peer sr-only" name="remember">
                    <div class="w-5 h-5 rounded-md border border-white/20 bg-slate-700/50 peer-checked:bg-cyan-500 peer-checked:border-cyan-500 transition-all duration-200 ring-1 ring-transparent peer-checked:ring-cyan-500/30 group-hover:border-white/30"></div>
                    <svg class="absolute inset-0 w-5 h-5 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-sm text-slate-300 group-hover:text-white transition">Remember me</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3 bg-gradient-to-l from-cyan-600 to-emerald-600 text-white font-bold rounded-xl hover:from-cyan-500 hover:to-emerald-500 transition-all duration-300 shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 text-sm">
            Sign In
        </button>

        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/5"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-slate-900 px-3 text-xs text-slate-500">Or</span>
            </div>
        </div>

        <div class="text-center text-sm">
            <span class="text-slate-400">Don't have an account?</span>
            <a href="{{ route('register') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold transition ml-1">Create one</a>
        </div>
    </form>
</x-guest-layout>
