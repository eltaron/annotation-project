<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Update Password</h1>
                <p class="page-subtitle">Make sure your account is using a strong password</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-8 animate-fade-in">
                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label class="input-label" for="current_password">Current Password</label>
                        <input id="current_password" class="input-field" type="password" name="current_password" required autocomplete="current-password" placeholder="••••••••">
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>
                    <div>
                        <label class="input-label" for="password">New Password</label>
                        <input id="password" class="input-field" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <label class="input-label" for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" class="input-field" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
