<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    text: { type: String, required: true },
    position: { type: String, default: 'top' }, // top, bottom, left, right
    delay: { type: Number, default: 300 },
});

const showTooltip = ref(false);
let timeout = null;

const positionClasses = computed(() => {
    const positions = {
        top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
        left: 'right-full top-1/2 -translate-y-1/2 mr-2',
        right: 'left-full top-1/2 -translate-y-1/2 ml-2',
    };
    return positions[props.position] || positions.top;
});

const arrowClasses = computed(() => {
    const arrows = {
        top: 'top-full left-1/2 -translate-x-1/2 border-t-gray-900',
        bottom: 'bottom-full left-1/2 -translate-x-1/2 border-b-gray-900',
        left: 'left-full top-1/2 -translate-y-1/2 border-l-gray-900',
        right: 'right-full top-1/2 -translate-y-1/2 border-r-gray-900',
    };
    return arrows[props.position] || arrows.top;
});

const showTip = () => {
    timeout = setTimeout(() => {
        showTooltip.value = true;
    }, props.delay);
};

const hideTip = () => {
    clearTimeout(timeout);
    showTooltip.value = false;
};
</script>

<template>
    <div class="relative inline-block" @mouseenter="showTip" @mouseleave="hideTip">
        <slot />
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div 
                v-if="showTooltip" 
                :class="['absolute z-50 px-3 py-1.5 text-xs font-medium text-white bg-gray-900 rounded-lg whitespace-nowrap', positionClasses]"
            >
                {{ text }}
                <div :class="['absolute w-0 h-0 border-4 border-transparent', arrowClasses]"></div>
            </div>
        </Transition>
    </div>
</template>
