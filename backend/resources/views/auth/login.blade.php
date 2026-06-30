<x-guest-layout>
    {{-- Header --}}
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">تسجيل الدخول</h2>
        <p class="text-sm text-gray-500 mt-1">مرحباً بعودتك! سجّل دخولك للمتابعة</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('كلمة المرور')" />
            <x-text-input id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-cyan-600 shadow-sm focus:ring-cyan-500" name="remember">
                <span class="text-sm text-gray-600">تذكرني</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-cyan-600 hover:text-cyan-700 font-medium transition" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full py-3 bg-gradient-to-r from-cyan-600 to-emerald-600 hover:from-cyan-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition shadow-lg shadow-cyan-600/20 text-sm">
            تسجيل الدخول
        </button>

        {{-- Register link --}}
        <div class="text-center text-sm text-gray-500">
            ليس لديك حساب؟
            <a href="{{ route('register') }}" class="text-cyan-600 hover:text-cyan-700 font-semibold transition">إنشاء حساب جديد</a>
        </div>
    </form>
</x-guest-layout>
