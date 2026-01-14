<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { MessageCircle, X, Send, Bot, User, Zap } from 'lucide-vue-next';

const props = defineProps({ visible: { type: Boolean, default: true } });
const emit = defineEmits(['toggle']);

const isOpen = ref(false);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(false);
const tooltipVisible = ref(true);
const isHovered = ref(false);

// Quick action suggestions
const quickActions = [
    { text: 'Destinasi populer', icon: '🏞️' },
    { text: 'Jam operasional', icon: '⏰' },
    { text: 'Harga tiket', icon: '🎫' },
];

onMounted(() => {
    messages.value.push({ 
        text: 'Halo! 👋 Saya asisten virtual TNLL. Ada yang bisa saya bantu hari ini?', 
        isUser: false,
        timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    });
});

const toggleChat = () => {
    tooltipVisible.value = false;
    isOpen.value = !isOpen.value;
    emit('toggle', isOpen.value);
};

const closeChat = () => {
    isOpen.value = false;
    emit('toggle', false);
};

const scrollToBottom = () => {
    nextTick(() => {
        const container = document.getElementById('vue-chat-messages');
        if (container) container.scrollTop = container.scrollHeight;
    });
};

const sendMessage = async (text = null) => {
    const prompt = text || newMessage.value;
    if (!prompt.trim() || loading.value) return;
    
    messages.value.push({ 
        text: prompt, 
        isUser: true,
        timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    });
    newMessage.value = '';
    loading.value = true;
    scrollToBottom();
    
    try {
        const response = await fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ message: prompt })
        });
        const data = await response.json();
        messages.value.push({ 
            text: data.reply, 
            isUser: false,
            timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        });
    } catch (error) {
        messages.value.push({ 
            text: 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.', 
            isUser: false,
            timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        });
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

const sendQuickAction = (action) => {
    sendMessage(action.text);
};
</script>

<template>
    <div v-if="visible" class="fixed right-3 sm:right-4 lg:right-5 bottom-3 sm:bottom-4 lg:bottom-5 z-[9990] flex flex-col items-end gap-3 sm:gap-4">
        <!-- Chat Window with Premium Glassmorphism Design - Responsive -->
        <Transition
            enter-active-class="transition-all duration-400 ease-out"
            enter-from-class="opacity-0 translate-y-6 scale-90"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-6 scale-90"
        >
            <div v-if="isOpen" class="chat-window relative w-[290px] sm:w-[320px] lg:w-[340px] h-[420px] sm:h-[450px] lg:h-[480px] rounded-xl sm:rounded-2xl overflow-hidden flex flex-col">
                <!-- Glassmorphism Background -->
                <div class="absolute inset-0 bg-white/80 backdrop-blur-2xl"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 via-white/30 to-teal-50/50"></div>
                
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-400/20 to-teal-400/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-20 left-0 w-24 h-24 bg-gradient-to-br from-cyan-400/15 to-emerald-400/10 rounded-full blur-2xl -translate-x-1/2"></div>
                
                <!-- Header - Responsive -->
                <div class="relative z-10 p-3 sm:p-4 flex items-center justify-between border-b border-gray-100/50">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Animated Bot Avatar -->
                        <div class="relative animate-float">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <Bot class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                            </div>
                            <!-- Dot on head (top-right, moves with icon) -->
                            <span class="absolute top-0 right-0 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-green-500 rounded-full border border-white sm:border-[1.5px] animate-pulse"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm">
                                TNLL Assistant
                            </h3>
                            <p class="text-[10px] sm:text-xs text-emerald-600 font-medium flex items-center gap-1">
                                <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                Aktif sekarang
                            </p>
                        </div>
                    </div>
                    <button 
                        @click="closeChat" 
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gray-100/80 hover:bg-red-50 text-gray-400 hover:text-red-500 flex items-center justify-center transition-all duration-300 hover:rotate-90"
                        aria-label="Tutup chat"
                    >
                        <X class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </button>
                </div>

                <!-- Messages Container - Responsive -->
                <div id="vue-chat-messages" class="relative z-10 flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 sm:space-y-4 scrollbar-thin">
                    <template v-for="(msg, i) in messages" :key="i">
                        <div :class="['flex gap-2', msg.isUser ? 'justify-end' : 'justify-start']" class="animate-message-in">
                            <!-- Bot Avatar - Responsive -->
                            <div v-if="!msg.isUser" class="flex-shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-md sm:rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                <Bot class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white" />
                            </div>
                            
                            <!-- Message Bubble - Responsive -->
                            <div class="max-w-[75%] space-y-0.5 sm:space-y-1">
                                <div :class="[
                                    'px-3 py-2 sm:px-3.5 sm:py-2.5 text-xs sm:text-sm leading-relaxed',
                                    msg.isUser 
                                        ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-xl sm:rounded-2xl rounded-tr-sm sm:rounded-tr-md shadow-lg shadow-emerald-500/15' 
                                        : 'bg-white text-gray-700 rounded-xl sm:rounded-2xl rounded-tl-sm sm:rounded-tl-md shadow-md border border-gray-100/50'
                                ]">
                                    {{ msg.text }}
                                </div>
                                <p :class="['text-[9px] sm:text-[10px]', msg.isUser ? 'text-right text-gray-400' : 'text-gray-400']">
                                    {{ msg.timestamp }}
                                </p>
                            </div>
                            
                            <!-- User Avatar - Responsive -->
                            <div v-if="msg.isUser" class="flex-shrink-0 w-6 h-6 sm:w-7 sm:h-7 rounded-md sm:rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <User class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white" />
                            </div>
                        </div>
                    </template>
                    
                    <!-- Skeleton Loading Indicator -->
                    <div v-if="loading" class="flex gap-2 justify-start animate-message-in">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0">
                            <Bot class="w-3.5 h-3.5 text-white" />
                        </div>
                        <div class="max-w-[75%] space-y-2">
                            <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-md shadow-md border border-gray-100/50">
                                <div class="space-y-2">
                                    <div class="h-3 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded-full w-48 animate-skeleton"></div>
                                    <div class="h-3 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded-full w-36 animate-skeleton" style="animation-delay: 0.15s"></div>
                                    <div class="h-3 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded-full w-24 animate-skeleton" style="animation-delay: 0.3s"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions (show when no user messages yet) -->
                    <div v-if="messages.length === 1 && !loading" class="pt-2 space-y-2">
                        <p class="text-xs text-gray-500 flex items-center gap-1.5">
                            <Zap class="w-3 h-3 text-amber-500" />
                            Pilih pertanyaan cepat:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="action in quickActions" 
                                :key="action.text"
                                @click="sendQuickAction(action)"
                                class="px-3 py-1.5 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 rounded-full text-xs text-gray-600 hover:text-emerald-700 transition-all duration-300 flex items-center gap-1.5 shadow-sm hover:shadow-md"
                            >
                                <span>{{ action.icon }}</span>
                                {{ action.text }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Input Area - Responsive -->
                <div class="relative z-10 p-3 sm:p-4 border-t border-gray-100/50 bg-white/50">
                    <form @submit.prevent="sendMessage()" class="relative flex items-center gap-1.5 sm:gap-2">
                        <input 
                            v-model="newMessage" 
                            type="text" 
                            placeholder="Ketik pesan Anda..." 
                            class="flex-1 px-3 sm:px-4 py-2.5 sm:py-3 bg-white border border-gray-200 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 text-xs sm:text-sm text-gray-700 placeholder:text-gray-400 shadow-sm transition-all duration-300"
                        >
                        <button 
                            type="submit" 
                            :disabled="!newMessage.trim() || loading"
                            class="w-10 h-10 sm:w-11 sm:h-11 bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-lg sm:rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 transition-all duration-300"
                            aria-label="Kirim pesan"
                        >
                            <Send class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Floating Chat Button - Icon Only with Animations -->
        <div class="relative">
            <!-- Tooltip Bubble - Responsive -->
            <Transition
                enter-active-class="transition-all duration-400 ease-out"
                enter-from-class="opacity-0 translate-x-3 scale-90"
                enter-to-class="opacity-100 translate-x-0 scale-100"
                leave-active-class="transition-all duration-300 ease-in"
                leave-from-class="opacity-100 translate-x-0 scale-100"
                leave-to-class="opacity-0 translate-x-3 scale-90"
            >
                <div 
                    v-if="isHovered && !isOpen" 
                    class="absolute right-full mr-2 sm:mr-3 top-1/2 -translate-y-[110%] sm:-translate-y-1/2 whitespace-nowrap"
                >
                    <div class="relative px-3 py-2 sm:px-4 sm:py-2.5 bg-white rounded-lg sm:rounded-xl shadow-xl border border-gray-100 flex items-center gap-1.5 sm:gap-2 animate-tooltip-bounce">
                        <div class="flex items-center gap-1 sm:gap-1.5">
                            <span class="text-base sm:text-lg animate-wave">👋</span>
                            <span class="text-xs sm:text-sm text-gray-700">
                                Butuh bantuan?
                            </span>
                        </div>
                        <!-- Tooltip Arrow -->
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-full">
                            <div class="border-[6px] sm:border-8 border-transparent border-l-white drop-shadow-sm"></div>
                        </div>
                    </div>
                </div>
            </Transition>
            
            <!-- Main Chat Button - Icon Only - Responsive -->
            <button 
                @click="toggleChat"
                @mouseenter="isHovered = true"
                @mouseleave="isHovered = false"
                class="chat-button group relative flex items-center justify-center"
                aria-label="Buka chat"
            >
                <!-- Ripple Effect -->
                <span class="absolute inset-0 rounded-full animate-ripple"></span>
                <span class="absolute inset-0 rounded-full animate-ripple" style="animation-delay: 0.5s"></span>
                
                <!-- Icon Container - Clean Design - Responsive -->
                <div class="relative z-10 w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 flex items-center justify-center">
                    <!-- X Icon when open - with morph animation -->
                    <div v-if="isOpen" class="animate-morph-in">
                        <X class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 text-emerald-500" />
                    </div>
                    <!-- Chatbot Icon with dot - both float together -->
                    <div v-else class="relative animate-morph-in">
                        <div class="animate-float-gentle">
                            <img 
                                src="/assets/chatbot.png" 
                                alt="Chat" 
                                class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 object-contain"
                            />
                            <!-- Online Status Dot - moves with icon - Responsive -->
                            <span class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 lg:top-3.5 lg:right-4 w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 bg-green-500 rounded-full border-2 border-white shadow-sm animate-pulse"></span>
                        </div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>

<style scoped>
.chat-window {
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.15),
        0 0 40px rgba(16, 185, 129, 0.1);
}

/* Float Animation */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

/* Sparkle Animation */
@keyframes sparkle {
    0%, 100% { opacity: 1; transform: scale(1) rotate(0deg); }
    50% { opacity: 0.6; transform: scale(1.2) rotate(15deg); }
}

.animate-sparkle {
    animation: sparkle 2s ease-in-out infinite;
}

/* Message In Animation */
@keyframes message-in {
    from { 
        opacity: 0; 
        transform: translateY(10px) scale(0.95);
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

.animate-message-in {
    animation: message-in 0.3s ease-out forwards;
}

/* Typing Dots Animation */
@keyframes typing-dot {
    0%, 100% { 
        opacity: 0.4;
        transform: scale(0.8) translateY(0);
    }
    50% { 
        opacity: 1;
        transform: scale(1) translateY(-4px);
    }
}

.animate-typing-dot {
    animation: typing-dot 0.6s ease-in-out infinite;
}

/* Tooltip Bounce */
@keyframes tooltip-bounce {
    0%, 100% { transform: translateX(0) translateY(-50%); }
    50% { transform: translateX(-3px) translateY(-50%); }
}

.animate-tooltip-bounce {
    animation: tooltip-bounce 2s ease-in-out infinite;
}

/* Wave Animation */
@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(20deg); }
    75% { transform: rotate(-10deg); }
}

.animate-wave {
    animation: wave 1.5s ease-in-out infinite;
    display: inline-block;
    transform-origin: 70% 70%;
}

/* Ripple Effect */
@keyframes ripple {
    0% { 
        transform: scale(1);
        opacity: 0.4;
    }
    100% { 
        transform: scale(1.8);
        opacity: 0;
    }
}

.animate-ripple {
    animation: ripple 2s ease-out infinite;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, transparent 70%);
}

/* Glow Animation */
@keyframes glow {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.1); }
}

.animate-glow {
    animation: glow 3s ease-in-out infinite;
}

/* Skeleton Loading Animation */
@keyframes skeleton {
    0% { 
        background-position: 200% 0;
    }
    100% { 
        background-position: -200% 0;
    }
}

.animate-skeleton {
    background-size: 200% 100%;
    animation: skeleton 1.5s ease-in-out infinite;
}

/* Spin In Animation for X icon */
@keyframes spin-in {
    from { 
        transform: rotate(-180deg) scale(0);
        opacity: 0;
    }
    to { 
        transform: rotate(0deg) scale(1);
        opacity: 1;
    }
}

.animate-spin-in {
    animation: spin-in 0.2s ease-out forwards;
}

/* Morph In Animation - smooth elastic entrance */
@keyframes morph-in {
    0% { 
        transform: scale(0) rotate(-45deg);
        opacity: 0;
    }
    50% { 
        transform: scale(1.15) rotate(5deg);
        opacity: 1;
    }
    75% { 
        transform: scale(0.95) rotate(-2deg);
    }
    100% { 
        transform: scale(1) rotate(0deg);
        opacity: 1;
    }
}

.animate-morph-in {
    animation: morph-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Float Gentle - smooth floating for icon with dot */
@keyframes float-gentle {
    0%, 100% { 
        transform: translateY(0) rotate(0deg);
    }
    25% { 
        transform: translateY(-4px) rotate(2deg);
    }
    75% { 
        transform: translateY(2px) rotate(-1deg);
    }
}

.animate-float-gentle {
    animation: float-gentle 3s ease-in-out infinite;
}

/* Scrollbar Styling */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.2);
    border-radius: 20px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.4);
}
</style>
