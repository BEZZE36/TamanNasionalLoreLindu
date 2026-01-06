<script setup>
/**
 * Sidebar.vue - Premium Dashboard Sidebar
 * Modern design with welcome card, quick links, and promo card
 */
import { computed, ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { User, Ticket, UserCircle, LogOut, MapPin, Menu, Home, Edit, Settings, ChevronRight, Sparkles, Calendar, Award, TrendingUp } from 'lucide-vue-next';

const { props } = usePage();
const user = computed(() => props.auth?.user);

const sidebarRef = ref(null);

onMounted(() => {
    gsap.fromTo('.sidebar-card', 
        { opacity: 0, x: 20 }, 
        { opacity: 1, x: 0, duration: 0.4, stagger: 0.15, ease: 'power2.out', delay: 0.3 }
    );
});

const logout = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.content;
    form.appendChild(csrfInput);
    document.body.appendChild(form);
    form.submit();
};

// Menu items
const menuItems = [
    { href: '/dashboard', icon: Home, label: 'Dashboard', active: true },
    { href: '/my-bookings', icon: Ticket, label: 'Tiket Saya', active: false },
    { href: '/profile', icon: UserCircle, label: 'Profil Saya', active: false },
    { href: '/settings', icon: Settings, label: 'Pengaturan', active: false },
];
</script>

<template>
    <div ref="sidebarRef" class="space-y-4 sm:space-y-5">
        <!-- Welcome Card - New Design -->
        <div class="sidebar-card relative bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 rounded-xl sm:rounded-2xl shadow-xl overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-500/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl"></div>
            </div>
            
            <div class="relative z-10 p-4 sm:p-5">
                <!-- User Info Row -->
                <div class="flex items-center gap-3 mb-4">
                    <!-- Avatar -->
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-lg sm:text-xl font-black shadow-lg shadow-emerald-500/30 flex-shrink-0">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-white text-sm sm:text-base truncate">{{ user?.name }}</h3>
                        <p class="text-[10px] sm:text-xs text-white/60 truncate">{{ user?.email }}</p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-2.5 sm:p-3 text-center">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            <Calendar class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-400" />
                            <span class="text-[8px] sm:text-[9px] text-white/50 uppercase tracking-wider">Member Sejak</span>
                        </div>
                        <p class="text-[10px] sm:text-xs font-semibold text-white">{{ user?.member_since || '-' }}</p>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-2.5 sm:p-3 text-center">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            <Award class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-400" />
                            <span class="text-[8px] sm:text-[9px] text-white/50 uppercase tracking-wider">Status</span>
                        </div>
                        <p class="text-[10px] sm:text-xs font-semibold text-emerald-400">Aktif</p>
                    </div>
                </div>

                <!-- Edit Profile Button -->
                <Link href="/profile" 
                      class="w-full flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 hover:border-white/30 rounded-lg sm:rounded-xl text-white font-semibold text-xs sm:text-sm transition-all duration-300 group">
                    <Edit class="w-3.5 h-3.5 sm:w-4 sm:h-4 group-hover:scale-110 transition-transform" />
                    <span>Edit Profil</span>
                </Link>
            </div>
        </div>

        <!-- Quick Links Card -->
        <div class="sidebar-card bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-5">
            <h4 class="text-sm sm:text-base font-bold text-gray-900 mb-3 sm:mb-4 flex items-center gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                    <Menu class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-600" />
                </div>
                <span>Menu Cepat</span>
            </h4>
            
            <nav class="space-y-1.5 sm:space-y-2">
                <Link v-for="item in menuItems" :key="item.href" :href="item.href"
                    :class="[
                        'flex items-center gap-2.5 sm:gap-3 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl transition-all duration-200 group',
                        item.active 
                            ? 'bg-gradient-to-r from-emerald-50 to-teal-50 text-emerald-700 border border-emerald-100' 
                            : 'text-gray-700 hover:bg-gray-50 hover:text-emerald-600'
                    ]">
                    <div :class="[
                        'w-7 h-7 sm:w-8 sm:h-8 rounded-md sm:rounded-lg flex items-center justify-center transition-all duration-200',
                        item.active 
                            ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow' 
                            : 'bg-gray-100 group-hover:bg-emerald-100 text-gray-500 group-hover:text-emerald-600'
                    ]">
                        <component :is="item.icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </div>
                    <span class="text-xs sm:text-sm font-medium flex-1">{{ item.label }}</span>
                    <ChevronRight v-if="!item.active" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 group-hover:text-emerald-500 opacity-0 group-hover:opacity-100 transition-all" />
                </Link>
                
                <!-- Logout Button -->
                <div class="pt-2 sm:pt-3 mt-2 sm:mt-3 border-t border-gray-100">
                    <button @click="logout"
                        class="w-full flex items-center gap-2.5 sm:gap-3 px-3 sm:px-4 py-2 sm:py-2.5 text-red-600 hover:bg-red-50 rounded-lg sm:rounded-xl transition-all duration-200 group">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-md sm:rounded-lg bg-red-50 group-hover:bg-red-100 flex items-center justify-center transition-colors">
                            <LogOut class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        </div>
                        <span class="text-xs sm:text-sm font-medium">Keluar</span>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Promo Card -->
        <div class="sidebar-card relative overflow-hidden rounded-xl sm:rounded-2xl shadow-lg">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-500 via-teal-500 to-emerald-600"></div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-24 h-24 sm:w-28 sm:h-28 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 sm:w-24 sm:h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <div class="absolute top-1/2 right-4 w-12 h-12 bg-white/5 rounded-lg rotate-12"></div>
            
            <!-- Content -->
            <div class="relative z-10 p-4 sm:p-5 text-white">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center mb-3">
                    <MapPin class="w-4 h-4 sm:w-5 sm:h-5" />
                </div>
                <h4 class="text-sm sm:text-base font-bold mb-1">Destinasi Populer</h4>
                <p class="text-white/80 text-[10px] sm:text-xs mb-3 sm:mb-4 leading-relaxed">Temukan tempat wisata terbaik di Lore Lindu!</p>
                
                <Link href="/destinations"
                    class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-md sm:rounded-lg bg-white text-cyan-600 font-semibold text-[10px] sm:text-xs hover:bg-white/90 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                    <Sparkles class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                    <span>Jelajahi</span>
                    <ChevronRight class="w-3 h-3 sm:w-3.5 sm:h-3.5 group-hover:translate-x-0.5 transition-transform" />
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.sidebar-card {
    will-change: transform, opacity;
}
</style>
