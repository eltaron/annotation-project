<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">نسيت كلمة المرور؟</h2>
        <p class="text-sm text-gray-500 mt-1">لا تقلق! أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('البريد الإلكتروني')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="w-full py-3 bg-gradient-to-r from-cyan-600 to-emerald-600 hover:from-cyan-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition shadow-lg shadow-cyan-600/20 text-sm">
            إرسال رابط إعادة التعيين
        </button>

        <div class="text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="text-cyan-600 hover:text-cyan-700 font-semibold transition">العودة لتسجيل الدخول</a>
        </div>
    </form>
</x-guest-layout>
