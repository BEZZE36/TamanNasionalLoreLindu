<script setup>
import { ImageOff } from 'lucide-vue-next';

const props = defineProps({
    src: { type: String, default: '' },
    alt: { type: String, default: 'Image' },
    fallback: { type: String, default: '/images/placeholder-no-image.svg' },
    class: { type: String, default: '' }
});

const handleError = (e) => {
    e.target.onerror = null;
    e.target.src = props.fallback || '/images/placeholder-no-image.svg';
};
</script>

<template>
    <img 
        :src="src || fallback" 
        :alt="alt" 
        :class="$props.class"
        @error="handleError"
        loading="lazy"
    >
</template>
