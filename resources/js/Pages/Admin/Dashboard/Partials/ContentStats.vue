<script setup>
defineProps({
    stats: { type: Array, required: true }
});
</script>

<template>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div 
            v-for="(stat, index) in stats"
            :key="index"
            :class="['group rounded-xl p-3.5 border hover:shadow-lg transition-all bg-gradient-to-br', stat.bgColor, stat.borderColor]"
        >
            <div class="flex items-center gap-3">
                <div :class="['w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform bg-gradient-to-br', stat.iconBg]">
                    <component :is="stat.icon" :class="['w-4 h-4', stat.iconColor]" />
                </div>
                <div>
                    <p :class="['text-lg font-bold', stat.textColor]">{{ stat.value }}</p>
                    <p :class="['text-[11px] font-medium opacity-80', stat.textColor]">{{ stat.title }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
