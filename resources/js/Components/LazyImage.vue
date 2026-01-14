<script setup>
/**
 * LazyImage.vue
 * Performance-optimized lazy loading image component with WebP support
 * Uses native loading="lazy" with IntersectionObserver fallback
 */
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    alt: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: null,
    },
    class: {
        type: String,
        default: '',
    },
    eager: {
        type: Boolean,
        default: false,
    },
    // Responsive image widths for srcset
    widths: {
        type: Array,
        default: () => [],
    },
    // Image quality (affects blur placeholder)
    quality: {
        type: String,
        default: 'high', // low, medium, high
    },
});

const imageRef = ref(null);
const isLoaded = ref(false);
const isInView = ref(false);
const hasError = ref(false);
const currentSrc = ref('');

let observer = null;

// Generate WebP src if available
const webpSrc = computed(() => {
    if (!props.src) return '';
    return props.src.replace(/\.(jpg|jpeg|png|gif)$/i, '.webp');
});

// Generate srcset for responsive images
const srcset = computed(() => {
    if (!props.widths.length || !props.src) return '';
    return props.widths.map(w => `${props.src} ${w}w`).join(', ');
});

const onLoad = () => {
    isLoaded.value = true;
};

const onError = () => {
    // If WebP fails, fallback to original
    if (currentSrc.value === webpSrc.value && webpSrc.value !== props.src) {
        currentSrc.value = props.src;
        hasError.value = false;
    } else {
        hasError.value = true;
        isLoaded.value = true;
    }
};

onMounted(() => {
    currentSrc.value = webpSrc.value || props.src;
    
    // Use IntersectionObserver for lazy loading
    if (!props.eager && 'IntersectionObserver' in window) {
        observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    isInView.value = true;
                    observer?.disconnect();
                }
            });
        }, {
            rootMargin: '100px 0px', // Start loading 100px before entering viewport
            threshold: 0.01,
        });

        if (imageRef.value) {
            observer.observe(imageRef.value);
        }
    } else {
        // If eager or no IntersectionObserver support, load immediately
        isInView.value = true;
    }
});

onUnmounted(() => {
    observer?.disconnect();
});
</script>

<template>
    <div ref="imageRef" class="lazy-image-container relative overflow-hidden" :class="props.class">
        <!-- Shimmer placeholder while loading -->
        <div 
            v-if="!isLoaded" 
            class="absolute inset-0 bg-gradient-to-r from-gray-100 via-gray-200 to-gray-100 animate-shimmer"
            :class="{
                'blur-sm': quality === 'low',
                'blur-[2px]': quality === 'medium',
            }"
        ></div>
        
        <!-- Low quality image placeholder (LQIP) -->
        <img
            v-if="placeholder && !isLoaded"
            :src="placeholder"
            :alt="alt"
            class="absolute inset-0 w-full h-full object-cover blur-lg scale-110"
        />
        
        <!-- Actual image with WebP support -->
        <picture v-if="isInView && !hasError">
            <!-- WebP version (if browser supports) -->
            <source 
                v-if="webpSrc && webpSrc !== src"
                :srcset="webpSrc" 
                type="image/webp"
            />
            <!-- Fallback to original format -->
            <img
                :src="currentSrc"
                :srcset="srcset"
                :alt="alt"
                :loading="eager ? 'eager' : 'lazy'"
                :decoding="eager ? 'sync' : 'async'"
                @load="onLoad"
                @error="onError"
                :class="[
                    'w-full h-full object-cover transition-opacity duration-300',
                    isLoaded ? 'opacity-100' : 'opacity-0'
                ]"
            />
        </picture>
        
        <!-- Error fallback -->
        <div 
            v-if="hasError" 
            class="absolute inset-0 flex items-center justify-center bg-gray-100 text-gray-400"
        >
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.animate-shimmer {
    background-size: 200% 100%;
    animation: shimmer 1.5s ease-in-out infinite;
}
</style>
