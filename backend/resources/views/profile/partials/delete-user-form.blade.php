<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Delete Account</h1>
                <p class="page-subtitle">This action is permanent and cannot be undone</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
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
