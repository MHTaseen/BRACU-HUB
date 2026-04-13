@extends('layouts.modern')

@section('title', 'AI Academic Assistant')

@section('extra_css')
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 60vh;
        max-height: 600px;
    }
    .chat-box {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .chat-message {
        max-width: 80%;
        padding: 12px 18px;
        border-radius: 18px;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .message-user {
        align-self: flex-end;
        background-color: var(--primary-neon);
        color: var(--bg-deep);
        border-bottom-right-radius: 4px;
        font-weight: 500;
    }
    .message-bot {
        align-self: flex-start;
        background-color: var(--bg-accent);
        color: var(--text-main);
        border: 1px solid var(--glass-border);
        border-bottom-left-radius: 4px;
    }
    .chat-input-wrapper {
        display: flex;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid var(--glass-border);
        background: rgba(15, 23, 42, 0.5);
    }
    .chat-input {
        flex: 1;
        background: var(--bg-accent);
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        border-radius: 24px;
        padding: 10px 20px;
        outline: none;
        transition: border-color 0.3s ease;
    }
    .chat-input:focus {
        border-color: var(--primary-neon);
    }
    .btn-send {
        background: var(--primary-neon);
        color: var(--bg-deep);
        border: none;
        border-radius: 24px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-send:hover {
        box-shadow: 0 0 15px var(--primary-neon);
    }
    .quick-actions {
        display: flex;
        gap: 10px;
        padding: 0 20px 10px;
        flex-wrap: wrap;
    }
    .btn-quick {
        background: transparent;
        border: 1px solid var(--primary-neon);
        color: var(--primary-neon);
        border-radius: 12px;
        padding: 5px 12px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-quick:hover {
        background: var(--primary-neon);
        color: var(--bg-deep);
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">AI Academic Assistant</h1>
    <p class="page-subtitle">Your personalized study planner and workload manager.</p>
</div>

<div class="glass-panel chat-container" x-data="assistantChat()">
    <div class="chat-box" id="chatBox">
        <div class="chat-message message-bot">
            Hello {{ auth()->user()->name }}! I am your AI Academic Assistant. How can I help you manage your studies today? You can ask me for a workload summary or your next priority.
        </div>
        
        <template x-for="msg in messages" :key="msg.id">
            <div :class="['chat-message', msg.isUser ? 'message-user' : 'message-bot']" x-html="msg.text"></div>
        </template>
        
        <div x-show="loading" class="chat-message message-bot" style="font-style: italic; opacity: 0.7;">
            Thinking...
        </div>
    </div>
    
    <div class="quick-actions">
        <button class="btn-quick" @click="sendQuery('What is my study priority?')">Study Priority</button>
        <button class="btn-quick" @click="sendQuery('Show my workload summary')">Workload Summary</button>
    </div>

    <div class="chat-input-wrapper">
        <input type="text" x-model="query" @keydown.enter="submitQuery" class="chat-input" placeholder="Type your question here...">
        <button class="btn-send" @click="submitQuery">Send</button>
    </div>
</div>
@endsection

@section('extra_js')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assistantChat', () => ({
            query: '',
            loading: false,
            messages: [],
            msgId: 0,
            
            submitQuery() {
                if (this.query.trim() === '') return;
                this.sendQuery(this.query);
            },
            
            sendQuery(text) {
                const currentText = text;
                this.query = '';
                
                this.messages.push({
                    id: this.msgId++,
                    text: currentText,
                    isUser: true
                });
                
                this.scrollToBottom();
                this.loading = true;
                
                fetch("{{ route('assistant.ask') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: currentText })
                })
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    
                    let formattedResponse = data.response.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    
                    this.messages.push({
                        id: this.msgId++,
                        text: formattedResponse,
                        isUser: false
                    });
                    
                    this.scrollToBottom();
                })
                .catch(error => {
                    this.loading = false;
                    this.messages.push({
                        id: this.msgId++,
                        text: 'An error occurred while reaching the assistant.',
                        isUser: false
                    });
                });
            },
            
            scrollToBottom() {
                setTimeout(() => {
                    const box = document.getElementById('chatBox');
                    box.scrollTop = box.scrollHeight;
                }, 100);
            }
        }));
    });
</script>
@endsection
