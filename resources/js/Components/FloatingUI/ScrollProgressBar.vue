<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// State
const scrollProgress = ref(0);

// Handle scroll for progress bar
const handleScroll = () => {
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (scrollHeight > 0) {
        scrollProgress.value = Math.min(100, (window.scrollY / scrollHeight) * 100);
    } else {
        scrollProgress.value = 0;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <!-- Scroll Progress Bar (Top of page) -->
    <div class="fixed top-0 left-0 right-0 h-1 z-[9999] pointer-events-none bg-gray-200/30">
        <div 
            class="h-full bg-gradient-to-r from-violet-500 via-purple-500 to-violet-600 shadow-lg shadow-violet-500/30 transition-all duration-100 ease-out origin-left"
            :style="{ transform: `scaleX(${scrollProgress / 100})` }"
        ></div>
    </div>
</template>
