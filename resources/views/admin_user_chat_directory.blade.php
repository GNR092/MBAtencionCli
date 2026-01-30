@extends('layouts.admin')

@section('content')
    <h1 class="text-3xl font-bold text-white mb-6">Directorio de Usuarios para Chat</h1>

    <div class="bg-gray-800 shadow-md rounded-lg p-6">
        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('admin.users.chat-directory') }}" class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search Bar -->
                <div class="flex-grow">
                    <input type="text" name="search" id="userSearchInput" placeholder="Buscar por nombre o correo..." value="{{ $search ?? '' }}" class="w-full p-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <!-- Sort Dropdown -->
                <div class="flex-shrink-0">
                    <select name="sort" class="w-full md:w-auto p-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="asc" @if($sort == 'asc') selected @endif>A-Z</option>
                        <option value="desc" @if($sort == 'desc') selected @endif>Z-A</option>
                        <option value="recent" @if($sort == 'recent') selected @endif>Recientes</option>
                    </select>
                </div>
                <!-- Search Button -->
                <div class="flex-shrink-0">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md border border-blue-400">Buscar</button>
                </div>
            </div>
        </form>

        @if ($users->isEmpty())
            <p class="text-gray-400">No se encontraron usuarios con los criterios actuales.</p>
        @else
            <ul id="userList" class="divide-y divide-gray-700">
                @foreach ($users as $user)
                    <li class="user-item py-4 flex items-center justify-between">
                        <div>
                            <p class="text-lg font-semibold text-white">{{ $user->name }}</p>
                            <p class="text-sm text-gray-400">{{ $user->email }}</p>
                        </div>
                        <button type="button" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" class="start-chat-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300 border border-blue-400">
                            Chat
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Admin Chat Modal -->
    <div id="adminChatModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md flex flex-col h-[500px]">
            <div class="bg-gray-800 text-white p-3 rounded-t-lg flex justify-between items-center">
                <h3 class="font-bold" id="chattingWithName">Chat con: </h3>
                <button id="closeAdminChatBtn" class="text-white hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="adminChatMessages" class="flex-grow p-3 bg-gray-100" style="height: 380px; overflow-y: auto;">
                <!-- Messages will be loaded here -->
            </div>
            <div class="p-3 border-t border-gray-200 bg-white">
                <input type="text" id="adminChatInput" placeholder="Escribe tu mensaje..." class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" style="color: black !important;" disabled>
                <button id="sendAdminChatBtn" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-md" disabled>Enviar</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adminChatModal = document.getElementById('adminChatModal');
        const closeAdminChatBtn = document.getElementById('closeAdminChatBtn');
        const adminChatMessagesDiv = document.getElementById('adminChatMessages');
        const adminChatInput = document.getElementById('adminChatInput');
        const sendAdminChatBtn = document.getElementById('sendAdminChatBtn');
        const chattingWithName = document.getElementById('chattingWithName');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let currentChatUserId = null;
        let messagePollingInterval = null;

        document.querySelectorAll('.start-chat-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentChatUserId = this.dataset.userId;
                const userName = this.dataset.userName;
                
                chattingWithName.textContent = `Chat con: ${userName}`;
                adminChatModal.style.display = 'flex';
                adminChatInput.disabled = false;
                sendAdminChatBtn.disabled = false;
                
                fetchAdminMessages(currentChatUserId);

                if (messagePollingInterval) {
                    clearInterval(messagePollingInterval);
                }
                messagePollingInterval = setInterval(() => fetchAdminMessages(currentChatUserId), 5000);
            });
        });

        if (closeAdminChatBtn) {
            closeAdminChatBtn.addEventListener('click', function() {
                adminChatModal.style.display = 'none';
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

        function scrollAdminChatToBottom() {
            if (adminChatMessagesDiv) {
                adminChatMessagesDiv.scrollTop = adminChatMessagesDiv.scrollHeight;
            }
        }

        function displayAdminMessages(messages) {
            if (!adminChatMessagesDiv) return;

            adminChatMessagesDiv.innerHTML = '';
            if (messages.length === 0) {
                adminChatMessagesDiv.innerHTML = '<div class="text-center text-gray-500 py-4">Inicia la conversación.</div>';
                return;
            }

            messages.forEach(message => {
                if (!message.sender) return; 

                const messageWrapper = document.createElement('div');
                const messageBubble = document.createElement('div');
                
                const isUserSender = message.sender.rol === 'usuario';

                // Wrapper for alignment
                messageWrapper.classList.add('flex', 'w-full', 'mb-2');

                // Bubble for styling
                messageBubble.classList.add('p-2', 'rounded-lg', 'max-w-[70%]', 'break-words');
                
                if (isUserSender) {
                    messageWrapper.classList.add('justify-start');
                    messageBubble.classList.add('bg-gray-200', 'text-black');
                } else {
                    messageWrapper.classList.add('justify-end');
                    messageBubble.classList.add('bg-blue-600', 'text-white');
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
                if (!response.ok) throw new Error('Failed to fetch admin messages');
                
                const messages = await response.json();
                displayAdminMessages(messages);
            } catch (error) {
                console.error('Error fetching admin messages:', error);
                if (adminChatMessagesDiv) {
                    adminChatMessagesDiv.innerHTML = '<div class="text-center text-red-500 py-4">Error al cargar mensajes.</div>';
                }
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
                    body: JSON.stringify({ message: messageText })
                });

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
