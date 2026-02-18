@extends('layouts.admin')

@section('content')
<header class="mb-10 ">
    <div class="flex items-baseline gap-4">
        <span class="text-dorado-400 text-sm font-serif italic">|</span>
        <h1 class="text-white text-7xl md:text-9xl font-extralight tracking-[-0.02em] leading-none">
            Directorio de usuarios<span class="font-light text-dorado"></span><span
                class="text-dorado-400 animate-pulse">_</span>
        </h1>
    </div>
</header>

<div class="bg-white rounded-2xl shadow-xl border border-carbon-200 overflow-hidden relative z-10">

    <div class="bg-carbon-900 px-6 py-4 border-b-2 border-dorado-400 flex justify-between items-center">
        <h2 class="text-dorado-400 text-lg font-bold uppercase tracking-widest flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
            </svg>
            Directorio de Chat
        </h2>
    </div>

    <div class="p-6">
        <form method="GET" action="{{ route('admin.users.chat-directory') }}" class="mb-8">
            <div class="flex flex-col md:flex-row gap-4 items-end">

                <div class="flex-grow w-full">
                    <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Buscar Usuario</label>
                    <div class="relative">
                        <input type="text" name="search" id="userSearchInput"
                            placeholder="Buscar por nombre o correo..." value="{{ $search ?? '' }}"
                            class="block w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-carbon-900 focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 w-full md:w-48">
                    <label class="block text-xs font-bold text-carbon-900 uppercase mb-1">Ordenar</label>
                    <select name="sort"
                        class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-carbon-900 bg-white focus:outline-none focus:border-dorado-400 focus:ring-1 focus:ring-dorado-400 transition-colors"
                        onchange="this.form.submit()">
                        <option value="asc" @if($sort=='asc' ) selected @endif>A-Z</option>
                        <option value="desc" @if($sort=='desc' ) selected @endif>Z-A</option>
                        <option value="recent" @if($sort=='recent' ) selected @endif>Recientes</option>
                    </select>
                </div>

                <div class="flex-shrink-0 w-full md:w-auto">
                    <button type="submit"
                        class="w-full bg-carbon-900 text-dorado-400 font-bold uppercase tracking-wider px-6 py-2 rounded-lg hover:bg-carbon-900/90 transition shadow-sm border border-dorado/30">
                        Buscar
                    </button>
                </div>
            </div>
        </form>

        @if ($users->isEmpty())
        <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
            <p class="text-carbon-900 font-medium italic">No se encontraron usuarios con los criterios actuales.</p>
        </div>
        @else
        <ul id="userList" class="divide-y divide-carbon-200 border-t border-carbon">
            @foreach ($users as $user)
            <li
                class="user-item py-4 flex items-center justify-between hover:bg-gray-50 transition px-2 rounded-lg -mx-2">
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-full bg-carbon-900 text-dorado-400 flex items-center justify-center font-bold text-lg shadow-sm">
                        {{ substr($user->name, 0, 1) }}
                    </div>

                    <div>
                        <p class="text-sm font-bold text-carbon-900 uppercase tracking-wide">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <button type="button" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                    class="start-chat-btn inline-flex items-center gap-2 bg-dorado-400 hover:bg-dorado/80 text-white px-4 py-2 rounded-lg transition duration-300 shadow-sm font-bold text-xs uppercase tracking-wider">
                    <span>Chat</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </button>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
<div id="adminChatModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-50 transition-opacity duration-300">

    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col h-[600px] border border-carbon-200 overflow-hidden transform scale-100 transition-transform duration-300">

        <div class="bg-carbon-900 px-5 py-4 border-b-2 border-dorado-400 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <span
                    class="w-2.5 h-2.5 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)] animate-pulse"></span>
                <h3 class="text-dorado-400 font-bold uppercase tracking-widest text-sm truncate max-w-[200px]"
                    id="chattingWithName">
                    Chat
                </h3>
            </div>

            <button id="closeAdminChatBtn"
                class="text-gray-400 hover:text-white transition focus:outline-none transform hover:rotate-90 duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="adminChatMessages" class="flex-grow p-4 bg-gray-50 overflow-y-auto custom-scroll space-y-3">
            <div class="flex justify-center h-full items-center text-gray-400 italic text-sm opacity-50">
                <span>Historial de conversación</span>
            </div>
        </div>

        <div class="p-3 border-t border-carbon-200 bg-white shrink-0">
            <div
                class="flex items-center gap-2 bg-gray-100 rounded-full px-2 py-2 border border-transparent focus-within:border-dorado-400 focus-within:bg-white focus-within:ring-1 focus-within:ring-dorado-400 transition-all duration-300">

                <input type="text" id="adminChatInput" placeholder="Escribe tu mensaje..."
                    class="flex-1 bg-transparent border-none text-carbon-900 text-sm px-3 focus:ring-0 placeholder-gray-400"
                    disabled>

                <button id="sendAdminChatBtn"
                    class="bg-dorado-400 text-white p-2 rounded-full hover:bg-dorado/80 transition-transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                    disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-5 h-5 pl-0.5">
                        <path
                            d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const adminChatModal = document.getElementById('adminChatModal');
    const closeAdminChatBtn = document.getElementById('closeAdminChatBtn');
    const adminChatMessagesDiv = document.getElementById('adminChatMessages');
    const adminChatInput = document.getElementById('adminChatInput');
    const sendAdminChatBtn = document.getElementById('sendAdminChatBtn');
    const chattingWithName = document.getElementById('chattingWithName');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (adminChatModal) {
        document.body.appendChild(adminChatModal);
    }

    let currentChatUserId = null;
    let messagePollingInterval = null;

    
    document.querySelectorAll('.start-chat-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentChatUserId = this.dataset.userId;
            const userName = this.dataset.userName;

            chattingWithName.textContent = `Chat con: ${userName}`;

            
            adminChatModal.classList.remove('hidden');
            adminChatModal.classList.add('flex');

            adminChatInput.disabled = false;
            sendAdminChatBtn.disabled = false;

            fetchAdminMessages(currentChatUserId);

            if (messagePollingInterval) {
                clearInterval(messagePollingInterval);
            }
            messagePollingInterval = setInterval(() => fetchAdminMessages(currentChatUserId),
                5000);
        });
    });

    
    if (closeAdminChatBtn) {
        closeAdminChatBtn.addEventListener('click', function() {
            adminChatModal.classList.add('hidden');
            adminChatModal.classList.remove('flex');

            if (messagePollingInterval) {
                clearInterval(messagePollingInterval);
            }
            messagePollingInterval = null;
            currentChatUserId = null;
            adminChatMessagesDiv.innerHTML = '';
            adminChatInput.disabled = true;
            sendAdminChatBtn.disabled = true;
        });
    }

    
    adminChatModal.addEventListener('click', function(e) {
        if (e.target === adminChatModal) {
            closeAdminChatBtn.click();
        }
    });

    function scrollAdminChatToBottom() {
        if (adminChatMessagesDiv) {
            adminChatMessagesDiv.scrollTop = adminChatMessagesDiv.scrollHeight;
        }
    }

    
    function displayAdminMessages(messages) {
        if (!adminChatMessagesDiv) return;

        adminChatMessagesDiv.innerHTML = '';
        if (messages.length === 0) {
            adminChatMessagesDiv.innerHTML =
                '<div class="text-center text-gray-400 italic py-4 opacity-70">Inicia la conversación.</div>';
            return;
        }

        messages.forEach(message => {
            if (!message.sender) return;

            const messageWrapper = document.createElement('div');
            const messageBubble = document.createElement('div');

            const isUserSender = message.sender.role === 'usuario';

            
            messageWrapper.classList.add('flex', 'w-full', 'mb-2');

            
            messageBubble.classList.add('px-4', 'py-2', 'max-w-[75%]', 'break-words', 'shadow-sm',
                'text-sm');

            if (isUserSender) {
                
                messageWrapper.classList.add('justify-start');
                messageBubble.classList.add('bg-white', 'text-carbon-900', 'rounded-2xl',
                    'rounded-bl-none', 'border', 'border-gray-200');
            } else {
                
                messageWrapper.classList.add('justify-end');
                messageBubble.classList.add('bg-carbon-900', 'text-dorado', 'rounded-2xl',
                    'rounded-br-none', 'font-medium');
            }

            messageBubble.textContent = message.message;
            messageWrapper.appendChild(messageBubble);
            adminChatMessagesDiv.appendChild(messageWrapper);
        });
        scrollAdminChatToBottom();
    }

    
    async function fetchAdminMessages(userId) {
        if (!userId) return;
        try {
            const response = await fetch(`/admin/chat/messages/${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            if (response.status === 401 || response.status === 419) {
                window.location.reload();
                return;
            }

            if (!response.ok) throw new Error('Failed to fetch admin messages');

            const messages = await response.json();
            displayAdminMessages(messages);
        } catch (error) {
            console.error('Error fetching admin messages:', error);
        }
    }

    async function sendAdminMessage() {
        if (!adminChatInput || !currentChatUserId) return;

        const messageText = adminChatInput.value.trim();
        if (messageText === '') return;

        try {
            const response = await fetch(`/admin/chat/messages/${currentChatUserId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message: messageText
                })
            });

            if (response.status === 401 || response.status === 419) {
                window.location.reload();
                return;
            }

            if (!response.ok) throw new Error('Failed to send admin message');

            adminChatInput.value = '';
            fetchAdminMessages(currentChatUserId);
        } catch (error) {
            console.error('Error sending admin message:', error);
        }
    }

    if (sendAdminChatBtn) {
        sendAdminChatBtn.addEventListener('click', sendAdminMessage);
    }
    if (adminChatInput) {
        adminChatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendAdminMessage();
            }
        });
    }
});
</script>
@endpush