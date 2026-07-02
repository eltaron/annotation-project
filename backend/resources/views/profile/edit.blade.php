<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Profile</h1>
                <p class="page-subtitle">Update your personal account information</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="card p-8 animate-fade-in">
                <div class="flex items-center gap-5 mb-8 pb-6 border-b border-white/5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white text-2xl font-bold shadow-md">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ Auth::user()->name }}</h3>
                        <p class="text-sm text-slate-400">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf @method('PATCH')
                    <div>
                        <label class="input-label" for="name">Name</label>
                        <input id="name" class="input-field" type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label class="input-label" for="email">Email</label>
                        <input id="email" class="input-field" type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Update Password --}}
            <div class="card p-8 animate-fade-in">
                <div class="flex items-center gap-5 mb-8 pb-6 border-b border-white/5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Update Password</h3>
                        <p class="text-sm text-slate-400">Ensure your account is using a strong password</p>
                    </div>
                </div>

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

            {{-- Delete Account --}}
            <div class="card p-8 border-red-500/30 animate-fade-in">
                <div class="flex items-center gap-5 mb-6 pb-6 border-b border-red-500/10">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-500/20 to-rose-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-300">Delete Account</h3>
                        <p class="text-sm text-red-400">Once your account is deleted, all of its data will be permanently deleted. Please download any information you wish to keep before proceeding.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-5">
                    @csrf @method('DELETE')
                    <div>
                        <label class="input-label" for="password">Enter your password to confirm deletion</label>
                        <input id="password" class="input-field border-red-500/30 focus:border-red-500" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-l from-red-600 to-rose-600 text-white font-bold rounded-xl hover:from-red-700 hover:to-rose-700 transition-all duration-200 shadow-sm hover:shadow-md text-sm">
                        Permanently Delete My Account
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
