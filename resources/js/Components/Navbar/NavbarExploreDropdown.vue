<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Compass, ChevronDown, Map, Leaf, Bird, Image, Star } from 'lucide-vue-next';

defineProps({ scrolled: Boolean });

const page = usePage();
const isOpen = ref(false);
const dropdownRef = ref(null);
const triggerRef = ref(null);
let closeTimeout = null;

const exploreItems = [
    { name: 'Explore Map', href: '/explore/map', icon: Map, description: 'Interactive explore', gradient: 'from-blue-500 to-cyan-500', hoverBg: 'hover:bg-blue-50', hoverText: 'hover:text-blue-700' },
    { name: 'Flora', href: '/flora', icon: Leaf, description: 'Plant diversity', gradient: 'from-emerald-500 to-green-500', hoverBg: 'hover:bg-emerald-50', hoverText: 'hover:text-emerald-700' },
    { name: 'Fauna', href: '/fauna', icon: Bird, description: 'Endemic wildlife', gradient: 'from-amber-500 to-orange-500', hoverBg: 'hover:bg-amber-50', hoverText: 'hover:text-amber-700' },
    { name: 'Galeri', href: '/gallery', icon: Image, description: 'Photos & videos', gradient: 'from-purple-500 to-pink-500', hoverBg: 'hover:bg-purple-50', hoverText: 'hover:text-purple-700' },
    { name: 'Testimoni', href: '/testimonials', icon: Star, description: 'Visitor reviews', gradient: 'from-yellow-500 to-amber-500', hoverBg: 'hover:bg-yellow-50', hoverText: 'hover:text-yellow-700' },
];

const isExploreActive = () => exploreItems.some(item => page.url.startsWith(item.href));

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
    }, 150);
};

const closeDropdown = () => {
    if (closeTimeout) clearTimeout(closeTimeout);
    isOpen.value = false;
};

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
        <!-- Trigger Button -->
        <button 
            ref="triggerRef"
            :class="[
                'nav-link group px-3 py-1.5 text-xs font-semibold transition-all duration-300 flex items-center gap-1.5 rounded-lg hover:scale-105 relative',
                isExploreActive() 
                    ? (scrolled ? 'text-slate-800 bg-slate-100/80' : 'text-white bg-white/15') 
                    : (scrolled ? 'text-slate-600 hover:text-slate-800 hover:bg-slate-50' : 'text-white/80 hover:text-white hover:bg-white/10')
            ]"
            aria-haspopup="true"
            :aria-expanded="isOpen"
        >
            <Compass class="w-3.5 h-3.5 transition-all duration-500 group-hover:rotate-180" />
            <span>Jelajahi</span>
            <ChevronDown :class="['w-2.5 h-2.5 transition-transform duration-300', isOpen ? 'rotate-180' : '']" />
            <!-- Active underline -->
            <span v-if="isExploreActive()" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full"></span>
            <!-- Hover underline - purple gradient (hidden when active) -->
            <span v-if="!isExploreActive()" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-purple-500 rounded-full scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-center"></span>
        </button>

        <!-- Backdrop for mobile -->
        <div v-if="isOpen" @click="closeDropdown" class="fixed inset-0 z-[90] lg:hidden"></div>

        <!-- Dropdown Menu -->
        <Transition 
            enter-active-class="transition ease-out duration-200" 
            enter-from-class="opacity-0 -translate-y-2 scale-95" 
            enter-to-class="opacity-100 translate-y-0 scale-100" 
            leave-active-class="transition ease-in duration-150" 
            leave-from-class="opacity-100 translate-y-0 scale-100" 
            leave-to-class="opacity-0 -translate-y-2 scale-95"
        >
            <div 
                v-if="isOpen" 
                ref="dropdownRef"
                class="absolute left-1/2 -translate-x-1/2 top-full mt-4 w-60 bg-white/98 backdrop-blur-xl rounded-2xl shadow-2xl shadow-black/10 border border-slate-200/50 z-[100] overflow-hidden p-2"
                @mouseenter="handleMouseEnter"
                @mouseleave="handleMouseLeave"
            >
                <!-- Arrow -->
                <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-white border-l border-t border-slate-200/50 rotate-45 rounded-tl"></div>
                
                <div class="relative space-y-1">
                    <Link 
                        v-for="item in exploreItems" 
                        :key="item.name" 
                        :href="item.href" 
                        @click="closeDropdown" 
                        :class="[
                            'flex items-center gap-3 px-3 py-3 rounded-xl text-xs text-slate-700 group/item transition-all duration-200 hover:scale-[1.02]',
                            item.hoverBg, item.hoverText
                        ]"
                    >
                        <div :class="['w-9 h-9 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-md group-hover/item:scale-110 group-hover/item:shadow-lg transition-all duration-300', item.gradient]">
                            <component :is="item.icon" class="w-4 h-4 text-white" />
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold block text-[11px]">{{ item.name }}</span>
                            <span class="text-[9px] text-slate-400 group-hover/item:text-slate-500 transition-colors">{{ item.description }}</span>
                        </div>
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>
