<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <h1 class="page-title">AI Assistant</h1>
            <p class="page-subtitle">Ask about your projects, images, annotations, and reports</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card animate-fade-in">
                <div id="chatMessages" class="h-[500px] overflow-y-auto p-6 space-y-4 border-b border-white/5">
                    <div class="flex items-start gap-3" id="welcomeMsg">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div class="bg-slate-700/50 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%]">
                            <p class="text-sm text-slate-200 leading-relaxed">Hello! I'm the Geo Annotate assistant. Ask me anything about your data — projects, images, annotations, health reports, and more.</p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-slate-600/50 text-slate-300 hover:bg-slate-600 transition-colors" onclick="quickAsk('Full statistics')">Full statistics</button>
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-slate-600/50 text-slate-300 hover:bg-slate-600 transition-colors" onclick="quickAsk('How many projects do I have?')">Count projects</button>
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-slate-600/50 text-slate-300 hover:bg-slate-600 transition-colors" onclick="quickAsk('Show health reports')">Health reports</button>
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-slate-600/50 text-slate-300 hover:bg-slate-600 transition-colors" onclick="quickAsk('List all my projects')">List projects</button>
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-slate-600/50 text-slate-300 hover:bg-slate-600 transition-colors" onclick="quickAsk('Total classified area')">Total area</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <form id="chatForm" class="flex items-center gap-3" onsubmit="return sendMessage(event)">
                        @csrf
                        <input id="chatInput" type="text" class="input-field flex-1" placeholder="Ask a question..." required autocomplete="off">
                        <button type="submit" id="sendBtn" class="btn-primary flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function quickAsk(msg) {
    document.getElementById('chatInput').value = msg;
    sendMessage(new Event('submit'));
}

function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const msg = input.value.trim();
    if (!msg) return false;

    const container = document.getElementById('chatMessages');
    const csrf = document.querySelector('input[name="_token"]').value;

    // — user bubble —
    const userDiv = document.createElement('div');
    userDiv.className = 'flex items-start gap-3 flex-row-reverse';
    userDiv.innerHTML =
        '<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>' +
        '<div class="bg-cyan-500/10 rounded-2xl rounded-tr-sm px-5 py-3.5 max-w-[85%] border border-cyan-500/20"><p class="text-sm text-slate-200 leading-relaxed">' + escapeHtml(msg) + '</p></div>';
    container.appendChild(userDiv);
    input.value = '';
    input.disabled = true;
    sendBtn.disabled = true;
    container.scrollTop = container.scrollHeight;

    // — thinking bubble —
    const botDiv = document.createElement('div');
    botDiv.className = 'flex items-start gap-3';
    botDiv.id = 'thinkingBubble';
    botDiv.innerHTML =
        '<div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white flex-shrink-0 shadow-sm">' +
        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg></div>' +
        '<div class="bg-slate-700/50 rounded-2xl rounded-tl-sm px-5 py-3.5 max-w-[85%]">' +
        '<div class="flex items-center gap-2 text-sm text-slate-400"><div class="w-4 h-4 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin"></div> Thinking...</div></div>';
    container.appendChild(botDiv);
    container.scrollTop = container.scrollHeight;

    // — fetch —
    fetch('{{ route("assistant.ask") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ message: msg })
    })
    .then(function(r) {
        if (!r.ok) return r.json().then(function(d) { throw new Error(d.answer || 'Server error'); });
        return r.json();
    })
    .then(function(d) {
        var html = '<p class="text-sm text-slate-200 leading-relaxed">' + formatAnswer(d.answer || 'No response.') + '</p>';
        botDiv.querySelector('.bg-slate-700\\/50').innerHTML = html;
    })
    .catch(function(err) {
        var errMsg = err.message || 'Connection error. Please try again.';
        botDiv.querySelector('.bg-slate-700\\/50').innerHTML = '<p class="text-sm text-red-400">' + escapeHtml(errMsg) + '</p>';
    })
    .finally(function() {
        input.disabled = false;
        sendBtn.disabled = false;
        input.focus();
        container.scrollTop = container.scrollHeight;
    });

    return false;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatAnswer(text) {
    return escapeHtml(text).replace(/\*\*(.+?)\*\*/g, '<strong class="text-cyan-300">$1</strong>').replace(/\n/g, '<br>');
}
</script>
