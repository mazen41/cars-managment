@extends('seller.layouts.app')

@section('panel_content')
<style>
    .chat-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }

    .message {
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }

    .chat-header {
        background: #fff;
        padding: 15px;
        border-radius: 15px 15px 0 0;
        border-bottom: 1px solid #eee;
    }

    .chat-messages {
        height: 60vh;
        overflow-y: auto;
        padding: 20px;
        background: #f5f5f5;
    }

    .message {
        display: flex;
        margin-bottom: 20px;
    }

    /* RTL Support for messages */
    [dir="rtl"] .message.sent {
        justify-content: flex-end;
        flex-direction: row-reverse;
    }

    [dir="rtl"] .message.received {
        justify-content: flex-start;
        flex-direction: row-reverse;
    }

  .message.sent {
        justify-content: flex-end;
        flex-direction: row-reverse;
    }

   .message.received {
        justify-content: flex-start;
        flex-direction: row-reverse;
    }

    .message-content {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 20px;
        position: relative;
        margin: 0 10px;
    }

    /* RTL Support for message bubbles */
    [dir="rtl"] .received .message-content {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-top-left-radius: 5px;
        border-top-right-radius: 20px;
    }

    [dir="rtl"] .sent .message-content {
        background: #007bff;
        color: white;
        border-top-right-radius: 5px;
        border-top-left-radius: 20px;
    }

   .received .message-content {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-top-right-radius: 5px;
        border-top-left-radius: 20px;
    }

    .sent .message-content {
        background: #007bff;
        color: white;
        border-top-left-radius: 5px;
        border-top-right-radius: 20px;
    }

    .message-meta {
        font-size: 0.75rem;
        margin-top: 5px;
        opacity: 0.7;
    }

    /* RTL Support for message meta */
    [dir="rtl"] .message-meta {
        text-align: left;
    }

     .message-meta {
        text-align: right;
    }

    .avatar-container {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .chat-input {
        background: #fff;
        padding: 20px;
        border-radius: 0 0 15px 15px;
        border-top: 1px solid #eee;
    }

    .chat-input textarea {
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 10px 20px;
        resize: none;
    }

    .send-button {
        border-radius: 20px;
        padding: 8px 25px;
        min-width: 100px;
    }

    .button-loader {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* RTL Support for input area */
    [dir="rtl"] .chat-input textarea {
        text-align: right;
    }

    /* RTL Support for message text */
    [dir="rtl"] .message-text {
        text-align: right;
    }

    [dir="rtl"] .font-weight-bold {
        text-align: right;
    }
</style>
<audio id="notification-sound" src="{{ static_asset('assets/audio/notification.mp3') }}" preload="auto" ></audio>
<div class="chat-container">
    <div class="card shadow-sm">
        <div class="chat-header">
            <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0">#{{ $conversation->title }}</h5>
            @if($conversation->conversable && $conversation->conversable_type === 'App\Models\Product')
                <a href="{{ route('product', $conversation->conversable->slug) }}"
                   class="btn btn-soft-primary btn-sm" target="_blank">
                    {{ translate('View Product') }}
                </a>
            @endif
            </div>
            <small class="text-muted">
                {{translate('Between')}}
                @if($conversation->sender != null) {{ $conversation->sender->name }} @endif
                {{translate('and')}}
                @if($conversation->receiver != null) {{ $conversation->receiver->name }} @endif
            </small>
            @if($conversation->conversable && $conversation->conversable_type === 'App\Models\Product')
             <div class="text-muted">  {{ translate('Product') }}: {{ $conversation->conversable->name }}</div>
            @endif
        </div>

        <div class="chat-messages" id="chat-messages">
            @foreach($conversation->messages as $message)
            <div class="message {{ $message->user_id == auth()->id() ? 'sent' : 'received' }}"
                data-message-id="{{ $message->id }}">
                @if($message->user_id != auth()->id())
                <div class="avatar-container">
                    <img src="{{ $message->user != null ? uploaded_asset($message->user->avatar_original) : static_asset('assets/img/avatar-place.png') }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                </div>
                @endif

                <div class="message-content">
                    @if($message->user_id != auth()->id())
                    <div class="font-weight-bold">
                        {{ $message->user != null ? $message->user->name : '' }}
                    </div>
                    @endif
                    <div class="message-text">{{ $message->message }}</div>
                    <div class="message-meta text-right">
                        {{ $message->created_at->format('h:i A') }}
                    </div>
                </div>

                @if($message->user_id == auth()->id())
                <div class="avatar-container">
                    <img src="{{ uploaded_asset(auth()->user()->avatar_original) }}"
                        onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @if ($conversation->receiver != null && $conversation->receiver->id == \Auth::user()->id )
        <div class="chat-input">
            <form id="message-form">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                <div class="row align-items-center">
                    <div class="col">
                        <textarea class="form-control" id="message-input" rows="2" name="message"
                            placeholder="{{ translate('Type your message...') }}" required></textarea>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary send-button">
                            <span class="button-text">{{translate('Send')}}</span>
                            <span class="button-loader d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection
@section('script')
@vite('resources/js/app.js')
<script>
    document.addEventListener('DOMContentLoaded', function() {

    const messagesContainer = document.querySelector('.chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const conversationId = "{{ $conversation->id }}";
    const sendButton = messageForm.querySelector('.send-button');
    const buttonText = sendButton.querySelector('.button-text');
    const buttonLoader = sendButton.querySelector('.button-loader');
    const messageTexts = document.querySelectorAll(".message-text");
    //Scroll to the top for the last message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    // identifies links in messages and converts them in to <a> tags
    messageTexts.forEach((m) => {
        m.innerHTML = m.innerHTML.replace(
            /(https?:\/\/[^\s]+)/g,
            '<a href="$1" target="_blank">$1</a>'
        );
    });

    function toggleButtonLoading(isLoading) {
        if (isLoading) {
            buttonText.classList.add('d-none');
            buttonLoader.classList.remove('d-none');
            sendButton.disabled = true;
        } else {
            buttonText.classList.remove('d-none');
            buttonLoader.classList.add('d-none');
            sendButton.disabled = false;
        }
    }

    // Function to send message
    async function sendMessage(message) {
        try {
            toggleButtonLoading(true);

            const response = await fetch('{{ route('messages.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    message: message,
                    isAjax: true
                })
            });

            const data = await response.json();

            if (data.success) {
                messageInput.value = '';
                appendMessage({
                    id: data.data.id,
                    message: data.data.message,
                    user_id: "{{ auth()->id() }}",
                    user_name: "{{ auth()->user()->name }}",
                    avatar_url: "{{ uploaded_asset(auth()->user()->avatar_original) }}",
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    date: new Date().toLocaleDateString()
                });
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
            }
        } catch (error) {
            AIZ.plugins.notify('danger', error);
        } finally {
            toggleButtonLoading(false);
        }
    }

    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (message) {
            sendMessage(message);
        }
    });

    //Handle Enter key to send message
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const message = this.value.trim();
            if (message) {
                sendMessage(message);
            }
        }
    });
        // Function to get the last message ID
        function getLastMessageId() {
        const messages = messagesContainer.querySelectorAll('[data-message-id]');
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            return lastMessage.getAttribute('data-message-id');
        }
        return 0;
        }

        // Function to add new message to the UI
        function appendMessage(message) {
    // Check if message already exists
    if (document.querySelector(`[data-message-id="${message.id}"]`)) {
        return; // Skip if message already exists
    }

    // Determine if the message is sent by the current user
    const isCurrentUser = message.user_id == "{{ auth()->id() }}";

    const messageHtml = `
        <div class="message ${isCurrentUser ? 'sent' : 'received'}" data-message-id="${message.id}">
            ${!isCurrentUser ? `
                <div class="avatar-container">
                    <img src="{{ $message->user != null ? uploaded_asset($message->user->avatar_original) : static_asset('assets/img/avatar-place.png') }}"
                         onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                </div>
            ` : ''}

            <div class="message-content">
                ${!isCurrentUser ? `
                    <div class="font-weight-bold">
                        ${message.user_name || ''}
                    </div>
                ` : ''}
                <div class="message-text">${message.message}</div>
                <div class="message-meta text-right">
                    ${message.time}
                </div>
            </div>

            ${isCurrentUser ? `
                <div class="avatar-container">
                    <img src="{{ uploaded_asset(auth()->user()->avatar_original) }}"
                         onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                </div>
            ` : ''}
        </div>
    `;

    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);

    // Scroll to the bottom after adding new message
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

 //websocket listener
        Echo.private('user.{{ auth()->id() }}')
        .subscribed(() => {
            console.log('Successfully subscribed to private channel!');
        })
        .error((error) => {
            console.error('Error subscribing to private channel:', error);
        })
        .listen(".MessageSent", (e) => {
           console.log('MessageSent event received:', e);
              if(e.conversation_id == conversationId){
                    // Play notification sound
                    const notificationSound = document.getElementById('notification-sound');
                    if (notificationSound) {
                        notificationSound.play().catch(error => {
                            console.error('Error playing notification sound:', error);
                        });
                    }
                appendMessage(e);
              }
        });

});
</script>
@endsection
