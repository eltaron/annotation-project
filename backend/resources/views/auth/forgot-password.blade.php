<x-guest-layout>
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 mb-5 ring-1 ring-white/10">
            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white">Forgot Password?</h2>
        <p class="text-sm text-slate-400 mt-2">No worries! Enter your email and we'll send you a reset link</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div class="space-y-1.5">
            <label class="input-label" for="email">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </span>
                <input id="email" class="input-field !pl-10" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="w-full py-3 bg-gradient-to-l from-cyan-600 to-emerald-600 text-white font-bold rounded-xl hover:from-cyan-500 hover:to-emerald-500 transition-all duration-300 shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 text-sm">
            Send Reset Link
        </button>

        <div class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-semibold transition">Back to sign in</a>
        </div>
    </form>
</x-guest-layout>
