<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronDown, Check, Globe } from 'lucide-vue-next';

defineProps({ scrolled: Boolean });

const isOpen = ref(false);
const currentLang = ref('ID');
const dropdownRef = ref(null);
const triggerRef = ref(null);
let closeTimeout = null;

const languages = [
    { code: 'ID', name: 'Indonesia', subtitle: 'Bahasa Indonesia', flagImg: '/images/flags/id.svg' },
    { code: 'EN', name: 'English', subtitle: 'English (US)', flagImg: '/images/flags/us.svg' },
];

// Hover logic with 1-second delay
const handleMouseEnter = () => {
    if (closeTimeout) {
        clearTimeout(closeTimeout);
        closeTimeout = null;
    }
    isOpen.value = true;
};

const handleMouseLeave = () => {
    closeTimeout = setTimeout(() => {
        isOpen.value = false;
    }, 150); // 1 second delay
};

const closeDropdown = () => {
    if (closeTimeout) clearTimeout(closeTimeout);
    isOpen.value = false;
};

const switchLanguage = (lang) => { 
    currentLang.value = lang.code; 
    closeDropdown(); 
};

const currentLanguage = () => languages.find(l => l.code === currentLang.value);

// Click outside to close
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target) && 
        triggerRef.value && !triggerRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (closeTimeout) clearTimeout(closeTimeout);
});
</script>

<template>
    <div 
        class="relative"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <!-- Trigger Button - Responsive Design -->
        <button 
            ref="triggerRef"
            :class="[
                'group flex items-center gap-1 sm:gap-2 px-1.5 sm:px-2 lg:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-xs font-semibold transition-all duration-300 hover:scale-105 border',
                scrolled 
                    ? 'text-slate-600 hover:bg-slate-50 hover:text-slate-800 border-transparent hover:border-slate-200' 
                    : 'text-white/90 hover:bg-white/10 border-transparent hover:border-white/20'
            ]"
            aria-haspopup="true"
            :aria-expanded="isOpen"
        >
            <!-- Current Flag Image - Responsive sizes -->
            <img 
                :src="currentLanguage()?.flagImg" 
                :alt="currentLanguage()?.name" 
                class="w-4 h-3 sm:w-5 sm:h-3.5 lg:w-6 lg:h-4 rounded-sm object-cover shadow-sm ring-1 ring-black/10"
                style="aspect-ratio: 3/2;"
            >
            
            <!-- Chevron -->
            <ChevronDown :class="['w-3 h-3 sm:w-3.5 sm:h-3.5 transition-transform duration-300', isOpen ? 'rotate-180' : '']" />
        </button>

        <!-- Backdrop for mobile -->
        <div v-if="isOpen" @click="closeDropdown" class="fixed inset-0 z-[90] lg:hidden"></div>

        <!-- Dropdown Menu -->
        <Transition 
            enter-active-class="transition ease-out duration-300" 
            enter-from-class="opacity-0 -translate-y-3 scale-95" 
            enter-to-class="opacity-100 translate-y-0 scale-100" 
            leave-active-class="transition ease-in duration-200" 
            leave-from-class="opacity-100 translate-y-0 scale-100" 
            leave-to-class="opacity-0 -translate-y-3 scale-95"
        >
            <div 
                v-if="isOpen" 
                ref="dropdownRef"
                class="absolute right-0 top-full mt-3 sm:mt-3 md:mt-4 lg:mt-3 w-40 sm:w-48 md:w-52 lg:w-56 bg-white/98 backdrop-blur-xl rounded-xl sm:rounded-2xl shadow-2xl shadow-black/10 border border-slate-200/50 z-[100] overflow-hidden"
                @mouseenter="handleMouseEnter"
                @mouseleave="handleMouseLeave"
            >
                <!-- Premium Header - Responsive -->
                <div class="px-3 sm:px-4 py-2.5 sm:py-3 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-100">
                    <div class="flex items-center gap-2 sm:gap-2.5">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 rounded-lg sm:rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/20">
                            <Globe class="w-3 h-3 sm:w-3.5 sm:h-3.5 lg:w-4 lg:h-4 text-white" />
                        </div>
                        <div>
                            <p class="text-[11px] sm:text-xs font-bold text-slate-800">Pilih Bahasa</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-500">Select Language</p>
                        </div>
                    </div>
                </div>

                <!-- Language Options - Responsive -->
                <div class="p-1.5 sm:p-2">
                    <button 
                        v-for="lang in languages" 
                        :key="lang.code" 
                        @click="switchLanguage(lang)" 
                        :class="[
                            'group w-full flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 sm:py-3 rounded-lg sm:rounded-xl text-xs sm:text-sm transition-all duration-300 hover:scale-[1.02]', 
                            currentLang === lang.code 
                                ? 'bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-200/50 shadow-sm' 
                                : 'hover:bg-slate-50 border border-transparent'
                        ]"
                    >
                        <!-- Flag Image - Responsive -->
                        <img 
                            :src="lang.flagImg" 
                            :alt="lang.name" 
                            class="w-7 h-5 sm:w-8 sm:h-5 lg:w-9 lg:h-6 rounded-md object-cover shadow-sm ring-1 ring-black/10 group-hover:scale-110 transition-transform duration-200"
                            style="aspect-ratio: 3/2;"
                        >
                        
                        <!-- Language Info -->
                        <div class="flex-1 text-left">
                            <p :class="['font-semibold text-xs sm:text-sm', currentLang === lang.code ? 'text-violet-700' : 'text-slate-700']">
                                {{ lang.name }}
                            </p>
                            <p :class="['text-[9px] sm:text-[10px]', currentLang === lang.code ? 'text-violet-500' : 'text-slate-400']">
                                {{ lang.subtitle }}
                            </p>
                        </div>
                        
                        <!-- Check Icon - Responsive -->
                        <div v-if="currentLang === lang.code" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center shadow-sm">
                            <Check class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white" />
                        </div>
                        
                        <!-- Radio Style for unselected -->
                        <div v-else class="w-5 h-5 rounded-full border-2 border-slate-200 group-hover:border-violet-300 transition-colors"></div>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
