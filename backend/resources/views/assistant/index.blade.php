<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">المساعد الذكي</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div id="assistant-container" class="bg-white overflow-hidden shadow-sm rounded-2xl flex flex-col" style="height: 70vh;">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-cyan-600 to-emerald-600 text-white px-6 py-5 rounded-t-2xl flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-lg">🤖</div>
                    <div>
                        <div class="font-bold text-lg">GeoLens Assistant</div>
                        <div class="text-sm text-white/70">اسأل عن مشاريعك، صورك، تصنيفاتك وتقاريرك الصحية</div>
                    </div>
                </div>

                {{-- Messages --}}
                <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white text-sm shrink-0">🤖</div>
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-5 py-3 max-w-[85%] shadow-sm">
                            <p class="text-gray-700 leading-relaxed">مرحباً! 👋 أنا مساعد GeoLens الذكي. اسألني أي سؤال عن بياناتك:</p>
                            <ul class="mt-2 text-sm text-gray-500 space-y-1">
                                <li>🔹 «عدد المشاريع كام»</li>
                                <li>🔹 «عدد الصور المرفوعة»</li>
                                <li>🔹 «عدد التصنيفات»</li>
                                <li>🔹 «إجمالي المساحة المُصنَّفة»</li>
                                <li>🔹 «إحصائيات كاملة»</li>
                                <li>🔹 «تقرير صحة المحاصيل»</li>
                                <li>🔹 «أكبر مشروع»</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Input --}}
                <div class="border-t border-gray-200 p-4 bg-white rounded-b-2xl">
                    <form id="ask-form" class="flex gap-3">
                        @csrf
                        <input type="text" id="question-input" autocomplete="off" placeholder="اكتب سؤالك هنا..."
                            class="flex-1 border border-gray-300 rounded-xl px-5 py-3 text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none transition">
                        <button type="submit" id="ask-btn"
                            class="bg-gradient-to-r from-cyan-600 to-emerald-600 text-white px-6 py-3 rounded-xl text-sm font-medium hover:from-cyan-700 hover:to-emerald-700 transition disabled:opacity-50 flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            إرسال
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('ask-form');
            const input = document.getElementById('question-input');
            const btn = document.getElementById('ask-btn');
            const messages = document.getElementById('chat-messages');

            function addMessage(text, sender) {
                const div = document.createElement('div');
                div.className = sender === 'user'
                    ? 'flex items-start gap-3 justify-end'
                    : 'flex items-start gap-3';

                const isUser = sender === 'user';
                const icon = isUser ? '🧑' : '🤖';
                const bg = isUser ? 'bg-cyan-600 text-white' : 'bg-white border border-gray-200 text-gray-700';
                const rounded = isUser ? 'rounded-2xl rounded-br-sm' : 'rounded-2xl rounded-tl-sm';

                div.innerHTML = `
                    ${!isUser ? `<div class="w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white text-sm shrink-0">${icon}</div>` : ''}
                    <div class="${bg} ${rounded} px-5 py-3 max-w-[85%] shadow-sm">
                        <p class="leading-relaxed whitespace-pre-line">${text}</p>
                    </div>
                    ${isUser ? `<div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm shrink-0">${icon}</div>` : ''}
                `;

                messages.appendChild(div);
                messages.scrollTop = messages.scrollHeight;
            }

            function showTyping() {
                const div = document.createElement('div');
                div.id = 'typing-indicator';
                div.className = 'flex items-start gap-3';
                div.innerHTML = `
                    <div class="w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white text-sm shrink-0">🤖</div>
                    <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-5 py-3 shadow-sm">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                `;
                messages.appendChild(div);
                messages.scrollTop = messages.scrollHeight;
            }

            function removeTyping() {
                const el = document.getElementById('typing-indicator');
                if (el) el.remove();
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const question = input.value.trim();
                if (!question) return;

                addMessage(question, 'user');
                input.value = '';
                btn.disabled = true;
                showTyping();

                try {
                    const res = await fetch('{{ route("assistant.ask") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ question })
                    });

                    removeTyping();
                    const data = await res.json();
                    addMessage(data.answer || 'عذراً، لم أتمكن من الإجابة.', 'bot');
                } catch (err) {
                    removeTyping();
                    addMessage('عذراً، حدث خطأ في الاتصال.', 'bot');
                } finally {
                    btn.disabled = false;
                }
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        #chat-messages::-webkit-scrollbar { width: 6px; }
        #chat-messages::-webkit-scrollbar-track { background: transparent; }
        #chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        #chat-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        .animate-bounce { animation: bounce 1s infinite; }
    </style>
    @endpush
</x-app-layout>
