<x-guest-layout>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">تحقق من بريدك الإلكتروني</h2>
        <p class="text-sm text-gray-500 mt-1">شكراً لتسجيلك! يرجى التحقق من بريدك الإلكتروني لتفعيل الحساب</p>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700 mb-6">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-700 mb-6">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-cyan-600 to-emerald-600 hover:from-cyan-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition shadow-lg shadow-cyan-600/20 text-sm">
                إعادة إرسال رابط التفعيل
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="py-3 px-4 text-sm text-gray-500 hover:text-gray-700 font-medium transition">
                تسجيل خروج
            </button>
        </form>
    </div>
</x-guest-layout>
