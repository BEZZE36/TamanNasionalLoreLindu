/**
 * Floating UI Components - Alpine.js functions for Chatbot
 * Simplified: Removed scroll-to-top functionality
 */

/**
 * Floating UI Controller
 * Manages chatbot state only
 */
function floatingUI() {
    return {
        chatOpen: false,

        init() {
            // Watch chatOpen and dispatch event to chat window
            this.$watch('chatOpen', v => {
                window.dispatchEvent(new CustomEvent('toggle-chatbot-state', { detail: { open: v } }));
            });

            // Listen for close from chat window
            window.addEventListener('chatbot-closed', () => {
                this.chatOpen = false;
            });
        }
    }
}

/**
 * Initialize Chatbot Alpine Component
 */
function initChatbot() {
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatbot', () => ({
            open: false,
            messages: [],
            newMessage: '',
            loading: false,

            init() {
                this.messages.push({
                    text: 'Halo! 👋 Ada yang bisa saya bantu tentang TNLL?',
                    isUser: false
                });

                window.addEventListener('toggle-chatbot-state', e => {
                    this.open = e.detail.open;
                });
            },

            sendMessage() {
                if (!this.newMessage.trim()) return;

                const prompt = this.newMessage;
                this.messages.push({ text: prompt, isUser: true });
                this.newMessage = '';
                this.loading = true;
                this.scrollToBottom();

                fetch(window.chatRoute || '/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken || ''
                    },
                    body: JSON.stringify({ message: prompt })
                })
                    .then(r => r.json())
                    .then(data => {
                        this.loading = false;
                        this.messages.push({ text: data.reply, isUser: false });
                        this.scrollToBottom();
                    })
                    .catch(() => {
                        this.loading = false;
                        this.messages.push({ text: 'Maaf, terjadi kesalahan.', isUser: false });
                    });
            },

            scrollToBottom() {
                setTimeout(() => {
                    const c = document.getElementById('chat-messages');
                    if (c) c.scrollTop = c.scrollHeight;
                }, 50);
            }
        }))
    });
}

// Initialize chatbot on load
if (typeof window !== 'undefined') {
    initChatbot();
}

// Export for ES modules
if (typeof module !== 'undefined') {
    module.exports = { floatingUI, initChatbot };
}
