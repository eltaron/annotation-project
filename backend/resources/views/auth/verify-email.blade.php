<x-guest-layout>
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 mb-4">
            <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-white">Verify Your Email</h2>
        <p class="text-sm text-slate-400 mt-1">Thanks for signing up! Please check your email to activate your account</p>
    </div>

    <div class="bg-cyan-500/10 border border-cyan-500/20 rounded-2xl p-5 text-sm text-cyan-300 mb-6 leading-relaxed">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5 text-sm text-emerald-300 mb-6">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-3 bg-gradient-to-l from-cyan-600 to-emerald-600 text-white font-bold rounded-xl hover:from-cyan-500 hover:to-emerald-500 transition-all duration-300 shadow-sm hover:shadow-md text-sm">
                Resend Verification Link
            </button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="py-3 px-5 text-sm text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all font-medium">
                Log out
            </button>
        </form>
    </div>
</x-guest-layout>
