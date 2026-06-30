<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('حذف الحساب') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('عند حذف حسابك، سيتم حذف جميع بياناته وموارده بشكل نهائي. قبل الحذف، يرجى تنزيل أي معلومات تريد الاحتفاظ بها.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium text-sm shadow-sm"
    >{{ __('حذف الحساب') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('هل أنت متأكد من حذف حسابك؟') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('عند حذف حسابك، سيتم حذف جميع بياناته بشكل نهائي. يرجى إدخال كلمة المرور للتأكيد.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('كلمة المرور') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('كلمة المرور') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-start gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    {{ __('إلغاء') }}
                </button>
                <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm shadow-sm">
                    {{ __('حذف الحساب') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
