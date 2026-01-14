<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { AlertTriangle, X, Clock } from 'lucide-vue-next';

const toasts = ref([]);
let toastId = 0;

const addToast = (message, type = 'error', resetTime = null, duration = 8000) => {
    const id = ++toastId;
    toasts.value.push({ id, message, type, resetTime });
    setTimeout(() => removeToast(id), duration);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

// Global event listener for AI rate limit errors
const handleRateLimitEvent = (event) => {
    const { message, resetTime } = event.detail;
    addToast(message || 'AI Rate Limit Tercapai', 'rate_limit', resetTime);
};

onMounted(() => {
    window.addEventListener('ai-rate-limit', handleRateLimitEvent);
});

onUnmounted(() => {
    window.removeEventListener('ai-rate-limit', handleRateLimitEvent);
});

// Expose addToast globally
if (typeof window !== 'undefined') {
    window.showAIToast = addToast;
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-4 right-4 z-[9999] space-y-3 pointer-events-none">
            <TransitionGroup name="toast">
                <div v-for="toast in toasts" :key="toast.id"
                    class="max-w-sm bg-white border border-red-200 rounded-xl shadow-2xl p-4 pointer-events-auto animate-slide-in">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <AlertTriangle class="w-5 h-5 text-red-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm">AI Rate Limit</p>
                            <p class="text-gray-600 text-xs mt-0.5">{{ toast.message }}</p>
                            <div v-if="toast.resetTime" class="flex items-center gap-1 mt-2 text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-lg w-fit">
                                <Clock class="w-3 h-3" />
                                <span>Reset pada: <strong>{{ toast.resetTime }}</strong></span>
                            </div>
                        </div>
                        <button @click="removeToast(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active {
    animation: slide-in 0.3s ease-out;
}
.toast-leave-active {
    animation: slide-out 0.3s ease-in;
}

@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>
