<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronDown, LayoutDashboard, Heart, Star, User, LogOut, Ticket, ShieldCheck, Crown, ArrowRight } from 'lucide-vue-next';

const props = defineProps({ scrolled: Boolean, user: Object });

const isOpen = ref(false);
const dropdownRef = ref(null);
const triggerRef = ref(null);
let closeTimeout = null;

const menuItems = [
    { 
        name: 'Dashboard', 
        href: '/dashboard', 
        icon: LayoutDashboard, 
        description: 'Beranda akun Anda',
        gradient: 'from-indigo-500 to-purple-600',
        hoverBg: 'hover:bg-indigo-50'
    },
    { 
        name: 'Tiket Saya', 
        href: '/my-bookings', 
        icon: Ticket, 
        description: 'Kelola tiket kunjungan',
        gradient: 'from-blue-500 to-cyan-600',
        hoverBg: 'hover:bg-blue-50'
    },
    { 
        name: 'Wishlist', 
        href: '/wishlist', 
        icon: Heart, 
        description: 'Konten favorit Anda',
        gradient: 'from-rose-500 to-pink-600',
        hoverBg: 'hover:bg-rose-50'
    },
    { 
        name: 'Ulasan Saya', 
        href: '/my-reviews', 
        icon: Star, 
        description: 'Review yang Anda buat',
        gradient: 'from-amber-500 to-orange-600',
        hoverBg: 'hover:bg-amber-50'
    },
    { 
        name: 'Edit Profile', 
        href: '/profile', 
        icon: User, 
        description: 'Kelola data profil Anda',
        gradient: 'from-emerald-500 to-teal-600',
        hoverBg: 'hover:bg-emerald-50'
    },
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
    }, 150);
};

const closeDropdown = () => {
    if (closeTimeout) clearTimeout(closeTimeout);
    isOpen.value = false;
};

const logout = () => { 
    closeDropdown();
    router.post('/logout'); 
};

const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return parts[0].charAt(0).toUpperCase() + parts[1].charAt(0).toUpperCase();
    }
    return name.charAt(0).toUpperCase();
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
        <!-- Trigger Button - Responsive Avatar Style -->
        <button 
            ref="triggerRef"
            :class="[
                'group flex items-center gap-1 sm:gap-2 p-0.5 sm:p-1 pr-1.5 sm:pr-2 lg:pr-3 rounded-full transition-all duration-300 hover:scale-105',
                scrolled 
                    ? 'bg-slate-100/80 hover:bg-slate-100 text-slate-700' 
                    : 'bg-white/10 hover:bg-white/20 text-white'
            ]"
            aria-haspopup="true"
            :aria-expanded="isOpen"
        >
            <!-- Avatar Container with Ring Animation -->
            <div class="relative">
                <!-- Animated ring on hover -->
                <div :class="[
                    'absolute -inset-0.5 rounded-full bg-gradient-to-r from-violet-500 via-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 blur-sm transition-all duration-300',
                ]"></div>
                
                <!-- Avatar - Responsive sizes -->
                <div class="relative">
                    <img 
                        v-if="user?.avatar" 
                        :src="user.avatar" 
                        :alt="user?.name" 
                        class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 rounded-full object-cover ring-1 sm:ring-2 ring-white/30 group-hover:ring-white/50 transition-all duration-300" 
                        @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'U')}&background=6366f1&color=fff`"
                    >
                    <div 
                        v-else 
                        class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[10px] sm:text-xs font-bold ring-1 sm:ring-2 ring-white/30 group-hover:ring-white/50 transition-all duration-300"
                    >
                        {{ getInitials(user?.name) }}
                    </div>
                    
                    <!-- Online indicator - Responsive -->
                    <span class="absolute -top-0.5 -right-0.5 w-2 h-2 sm:w-2.5 sm:h-2.5 lg:w-3 lg:h-3 bg-green-500 rounded-full border-1 sm:border-2 border-white shadow-lg">
                        <span class="absolute inset-0 rounded-full bg-green-400 animate-ping opacity-75"></span>
                    </span>
                </div>
            </div>
            
            <!-- Name - Hidden on mobile -->
            <span class="hidden lg:block max-w-16 lg:max-w-20 truncate text-[10px] sm:text-xs font-semibold">
                {{ user?.name?.split(' ')[0] || 'User' }}
            </span>
            
            <!-- Chevron - Smaller on mobile -->
            <ChevronDown :class="['w-2.5 h-2.5 sm:w-3 sm:h-3 lg:w-3.5 lg:h-3.5 transition-transform duration-300', isOpen ? 'rotate-180' : '']" />
        </button>

        <!-- Backdrop for mobile -->
        <div v-if="isOpen" @click="closeDropdown" class="fixed inset-0 z-[90] lg:hidden"></div>

        <!-- Premium Dropdown Menu -->
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
                class="absolute right-0 top-full mt-3 sm:mt-3 md:mt-4 lg:mt-3 w-[230px] sm:w-[260px] md:w-[280px] lg:w-[288px] bg-white/98 backdrop-blur-xl rounded-xl sm:rounded-2xl shadow-2xl shadow-black/15 border border-slate-200/50 z-[100] overflow-hidden"
                @mouseenter="handleMouseEnter"
                @mouseleave="handleMouseLeave"
            >
                
                <!-- Premium Header - Responsive -->
                <div class="relative px-3 sm:px-4 lg:px-5 py-3 sm:py-4 lg:py-5 bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 overflow-hidden">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-24 sm:w-28 lg:w-32 h-24 sm:h-28 lg:h-32 bg-violet-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 w-16 sm:w-20 lg:w-24 h-16 sm:h-20 lg:h-24 bg-indigo-500/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl"></div>
                    
                    <div class="relative flex items-center gap-2.5 sm:gap-3 lg:gap-4">
                        <!-- Large Avatar - Responsive -->
                        <div class="relative">
                            <div class="absolute -inset-0.5 sm:-inset-1 bg-white/20 rounded-xl sm:rounded-2xl blur"></div>
                            <img 
                                v-if="user?.avatar" 
                                :src="user.avatar" 
                                :alt="user?.name" 
                                class="relative w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 rounded-lg sm:rounded-xl object-cover ring-1 sm:ring-2 ring-white/30 shadow-xl" 
                                @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'U')}&background=ffffff&color=6366f1&size=56`"
                            >
                            <div 
                                v-else 
                                class="relative w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 rounded-lg sm:rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-sm sm:text-base lg:text-lg font-bold ring-1 sm:ring-2 ring-white/30 shadow-xl"
                            >
                                {{ getInitials(user?.name) }}
                            </div>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <p class="text-sm sm:text-base font-bold text-white truncate">{{ user?.name || 'User' }}</p>
                            <p class="text-[10px] sm:text-[11px] text-white/70 truncate">{{ user?.email || '' }}</p>
                            <!-- Member Badge -->
                            <div class="inline-flex items-center gap-0.5 sm:gap-1 mt-1 sm:mt-1.5 px-1.5 sm:px-2 py-0.5 bg-violet-500/30 rounded-full">
                                <Crown class="w-2 h-2 sm:w-2.5 sm:h-2.5 text-amber-300" />
                                <span class="text-[8px] sm:text-[9px] font-semibold text-white/90">Member</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Menu -->
                <div class="p-2">
                    <Link 
                        v-for="item in menuItems" 
                        :key="item.name" 
                        :href="item.href" 
                        @click="closeDropdown" 
                        :class="[
                            'group flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-300 hover:scale-[1.02]',
                            item.hoverBg
                        ]"
                    >
                        <!-- Icon -->
                        <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-md group-hover:shadow-lg group-hover:scale-110 transition-all duration-300', item.gradient]">
                            <component :is="item.icon" class="w-5 h-5 text-white" />
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-900">{{ item.name }}</p>
                            <p class="text-[10px] text-slate-400">{{ item.description }}</p>
                        </div>
                        
                        <!-- Arrow -->
                        <ArrowRight class="w-4 h-4 text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all opacity-0 group-hover:opacity-100" />
                    </Link>
                </div>
                

                
                <!-- Logout Button -->
                <div class="p-2 pt-0">
                    <div class="border-t border-slate-100 pt-2">
                        <button 
                            @click="logout" 
                            class="group w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-red-50 to-rose-50 hover:from-red-100 hover:to-rose-100 border border-red-100/50 text-red-600 transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-md group-hover:shadow-red-500/30 group-hover:scale-110 transition-all">
                                <LogOut class="w-4 h-4 text-white" />
                            </div>
                            <span class="font-semibold text-sm">Keluar dari Akun</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
