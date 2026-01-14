<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Heart, Bookmark, Share2, Eye, Clock, Timer } from 'lucide-vue-next';

const props = defineProps({
    articleId: { type: [Number, String], required: true },
    initialLikesCount: { type: Number, default: 0 },
    initialViewsCount: { type: Number, default: 0 },
});

const isLiked = ref(false);
const isFavorited = ref(false);
const likesCount = ref(props.initialLikesCount);
const viewId = ref(null);
const startTime = ref(null);
const lastSentTime = ref(0);
const updateInterval = ref(null);
const displayInterval = ref(null);
const elapsedSeconds = ref(0);

// Format time for display
const formattedReadingTime = computed(() => {
    const totalSeconds = elapsedSeconds.value;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    if (minutes === 0) {
        return `${seconds} detik`;
    } else if (seconds === 0) {
        return `${minutes} menit`;
    } else {
        return `${minutes} menit ${seconds} detik`;
    }
});

// Short format for compact display
const shortReadingTime = computed(() => {
    const totalSeconds = elapsedSeconds.value;
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    if (minutes === 0) {
        return `${seconds}s`;
    } else {
        return `${minutes}m ${seconds}s`;
    }
});

// Send time spent to server
const sendTimeSpent = async () => {
    if (!viewId.value || !startTime.value) return;
    
    const currentTimeSpent = Math.round((Date.now() - startTime.value) / 1000);
    
    // Only send if time has meaningfully changed (at least 10 seconds more)
    if (currentTimeSpent - lastSentTime.value < 10) return;
    
    try {
        await fetch(`/articles/views/${viewId.value}/time-spent`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ time_spent: currentTimeSpent })
        });
        lastSentTime.value = currentTimeSpent;
    } catch (e) {
        console.log('Time tracking update failed');
    }
};

// Update elapsed time for display
const updateElapsedTime = () => {
    if (startTime.value && !document.hidden) {
        elapsedSeconds.value = Math.floor((Date.now() - startTime.value) / 1000);
    }
};

// Handle page visibility change (user switches tabs)
const handleVisibilityChange = () => {
    if (document.hidden) {
        // User left the page, send current time
        sendTimeSpent();
    }
};

// Handle before unload (user closes browser/tab)
const handleBeforeUnload = () => {
    if (!viewId.value || !startTime.value) return;
    
    const currentTimeSpent = Math.round((Date.now() - startTime.value) / 1000);
    
    // Use sendBeacon for reliability when page is unloading
    const data = JSON.stringify({ time_spent: currentTimeSpent });
    navigator.sendBeacon(
        `/articles/views/${viewId.value}/time-spent`,
        new Blob([data], { type: 'application/json' })
    );
};

// Track view on mount
onMounted(async () => {
    startTime.value = Date.now();
    
    // Track view
    try {
        const response = await fetch(`/articles/${props.articleId}/track-view`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ referer: document.referrer })
        });
        const data = await response.json();
        if (data.success) {
            viewId.value = data.view_id;
        }
    } catch (e) {
        console.log('View tracking failed');
    }

    // Load interaction status
    await loadInteractionStatus();
    
    // Set up periodic time updates every 30 seconds (for server)
    updateInterval.value = setInterval(sendTimeSpent, 30000);
    
    // Set up display update every second
    displayInterval.value = setInterval(updateElapsedTime, 1000);
    
    // Listen for visibility changes
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Listen for page unload
    window.addEventListener('beforeunload', handleBeforeUnload);
});

// Track time spent on unmount
onUnmounted(() => {
    // Clear intervals
    if (updateInterval.value) {
        clearInterval(updateInterval.value);
    }
    if (displayInterval.value) {
        clearInterval(displayInterval.value);
    }
    
    // Remove event listeners
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('beforeunload', handleBeforeUnload);
    
    // Send final time
    sendTimeSpent();
});

const loadInteractionStatus = async () => {
    try {
        const response = await fetch(`/articles/${props.articleId}/interaction-status`);
        const data = await response.json();
        isLiked.value = data.is_liked;
        isFavorited.value = data.is_favorited;
        likesCount.value = data.likes_count;
    } catch (e) {}
};

const toggleLike = async () => {
    try {
        const response = await fetch(`/articles/${props.articleId}/toggle-like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (response.status === 401) {
            alert('Login diperlukan untuk menyukai artikel');
            return;
        }
        if (data.success) {
            isLiked.value = data.is_liked;
            likesCount.value = data.likes_count;
        }
    } catch (e) {}
};

const toggleFavorite = async () => {
    try {
        const response = await fetch(`/articles/${props.articleId}/toggle-favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (response.status === 401) {
            alert('Login diperlukan untuk menyimpan artikel');
            return;
        }
        if (data.success) {
            isFavorited.value = data.is_favorited;
        }
    } catch (e) {}
};

const shareArticle = async () => {
    const url = window.location.href;
    const title = document.title;

    if (navigator.share) {
        try {
            await navigator.share({ title, url });
        } catch (e) {}
    } else {
        // Fallback: copy to clipboard
        await navigator.clipboard.writeText(url);
        alert('Link disalin ke clipboard!');
    }
};
</script>

<template>
    <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
        <!-- Like Button -->
        <button @click="toggleLike" 
            class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-full transition-all text-xs sm:text-sm"
            :class="isLiked ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-500'">
            <Heart :class="['w-3.5 h-3.5 sm:w-5 sm:h-5', isLiked ? 'fill-current' : '']" />
            <span class="font-medium">{{ likesCount }}</span>
        </button>

        <!-- Favorite Button -->
        <button @click="toggleFavorite" 
            class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-full transition-all text-xs sm:text-sm"
            :class="isFavorited ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-yellow-50 hover:text-yellow-600'">
            <Bookmark :class="['w-3.5 h-3.5 sm:w-5 sm:h-5', isFavorited ? 'fill-current' : '']" />
            <span class="font-medium hidden sm:inline">{{ isFavorited ? 'Tersimpan' : 'Simpan' }}</span>
        </button>

        <!-- Views Count -->
        <div class="flex items-center gap-1 text-gray-400 text-[10px] sm:text-xs ml-auto">
            <Eye class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>{{ initialViewsCount }}</span>
        </div>

        <!-- Live Reading Time -->
        <div class="flex items-center gap-1 px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-full bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/50 text-emerald-700 text-[10px] sm:text-xs font-medium">
            <Timer class="w-3 h-3 sm:w-3.5 sm:h-3.5 animate-pulse" />
            <span class="hidden sm:inline">Dibaca:</span>
            <span class="font-semibold">{{ shortReadingTime }}</span>
        </div>
    </div>
</template>
