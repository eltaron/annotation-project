<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <h1 class="page-title">AI Assistant</h1>
            <p class="page-subtitle">Ask about your projects, images, and reports</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card animate-fade-in">
                {{-- Messages --}}
                <div id="chatMessages" class="h-96 overflow-y-auto p-6 space-y-4 border-b border-white/5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div class="bg-slate-700/50 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%]">
                            <p class="text-sm text-slate-200 leading-relaxed">Hello! I'm the AI assistant. I can answer your questions about your projects and analyses. Try:</p>
                            <ul class="text-xs text-slate-400 mt-2 space-y-1">
                                <li>• How many projects do I have?</li>
                                <li>• Give me full statistics</li>
                                <li>• What are my health reports?</li>
                                <li>• Show my recent projects</li>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Input --}}
                <div class="p-4">
                    <form id="chatForm" class="flex items-center gap-3" onsubmit="return sendMessage(event)">
                        @csrf
                        <input id="chatInput" type="text" class="input-field flex-1" placeholder="Type your question here..." required autocomplete="off">
                        <button type="submit" class="btn-primary flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if(!msg) return false;
    const container = document.getElementById('chatMessages');

    // User message
    const userDiv = document.createElement('div');
    userDiv.className = 'flex items-start gap-3 flex-row-reverse';
    userDiv.innerHTML = `
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm">${'{{ substr(Auth::user()->name, 0, 1) }}' || 'U'}</div>
        <div class="bg-cyan-500/10 rounded-2xl rounded-tr-sm px-5 py-3.5 max-w-[85%] border border-cyan-500/20">
            <p class="text-sm text-slate-200 leading-relaxed">${msg}</p>
        </div>`;
    container.appendChild(userDiv);
    input.value = '';
    container.scrollTop = container.scrollHeight;

    // Bot thinking
    const botDiv = document.createElement('div');
    botDiv.className = 'flex items-start gap-3';
    botDiv.innerHTML = `
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <div class="bg-slate-700/50 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%]">
            <div class="flex items-center gap-2 text-sm text-slate-400"><div class="w-4 h-4 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin"></div> Thinking...</div>
        </div>`;
    container.appendChild(botDiv);
    container.scrollTop = container.scrollHeight;

    fetch('{{ route("assistant.ask") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ message: msg })
    }).then(r => r.json()).then(d => {
        botDiv.querySelector('.bg-slate-700\\/50').innerHTML = `<p class="text-sm text-slate-200 leading-relaxed">${d.answer || d.error || 'Sorry, I could not process your question.'}</p>`;
        container.scrollTop = container.scrollHeight;
    }).catch(() => {
        botDiv.querySelector('.bg-slate-700\\/50').innerHTML = `<p class="text-sm text-red-400">Connection error. Please try again.</p>`;
    });
    return false;
}
</script>
@endpush
