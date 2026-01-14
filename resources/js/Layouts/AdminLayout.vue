<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, X, Search, Clock, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { gsap } from 'gsap';

// Import admin partials
import SidebarNavKonten from '@/Components/Admin/SidebarNavKonten.vue';
import SidebarNavManajemen from '@/Components/Admin/SidebarNavManajemen.vue';
import SidebarNavLaporan from '@/Components/Admin/SidebarNavLaporan.vue';
import SidebarNavPengaturan from '@/Components/Admin/SidebarNavPengaturan.vue';
import AdminHeaderControls from '@/Components/Admin/AdminHeaderControls.vue';
import PageLoadingOverlay from '@/Components/Skeleton/PageLoadingOverlay.vue';
import GlobalSearchModal from '@/Components/Admin/GlobalSearchModal.vue';
import AIToast from '@/Components/Admin/AIToast.vue';

// Import auto-logout composable
import { useIdleTimeout } from '@/composables/useIdleTimeout';

const props = defineProps({
    admin: Object,
    notifications: Array,
});

// Initialize auto-logout (admin always authenticated, isAdmin = true for correct redirect)
useIdleTimeout(true, true);

const page = usePage();

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const isMobile = ref(false);
const sidebarRef = ref(null);
const logoRef = ref(null);
const navRef = ref(null);

// Global Search
const showGlobalSearch = ref(false);

const openGlobalSearch = () => {
    showGlobalSearch.value = true;
};

const closeGlobalSearch = () => {
    showGlobalSearch.value = false;
};

// Keyboard shortcut for global search
const handleKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        openGlobalSearch();
    }
};

// Check if mobile on mount
const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) {
        sidebarOpen.value = false;
    } else {
        sidebarOpen.value = true;
    }
};

// Animate sidebar entrance
const animateSidebar = () => {
    if (sidebarRef.value && !isMobile.value) {
        gsap.fromTo(logoRef.value, 
            { opacity: 0, x: -20, scale: 0.9 },
            { opacity: 1, x: 0, scale: 1, duration: 0.6, ease: 'back.out(1.7)' }
        );
        
        gsap.fromTo('.nav-section', 
            { opacity: 0, x: -15 },
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out', delay: 0.3 }
        );
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    window.addEventListener('keydown', handleKeydown);
    setTimeout(animateSidebar, 100);
    updateTime();
    setInterval(updateTime, 60000);
    
    // Start checking admin status every 10 seconds for realtime blocking
    checkAdminStatus();
    statusCheckInterval = setInterval(checkAdminStatus, 10000);
});

// Check if admin is still active (realtime blocking)
let statusCheckInterval = null;
const checkAdminStatus = async () => {
    try {
        const response = await fetch('/admin/api/check-status', {
            method: 'GET',
            headers: { 
                'Accept': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest' 
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            if (!data.is_active) {
                // Admin has been deactivated, redirect to blocked page
                window.location.href = '/admin/account-blocked';
            }
        }
    } catch (e) {
        // Silently fail - don't disrupt normal usage
        console.warn('Failed to check admin status:', e);
    }
};

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    window.removeEventListener('keydown', handleKeydown);
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
});

const toggleSidebar = () => {
    if (isMobile.value) {
        sidebarOpen.value = !sidebarOpen.value;
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value;
    }
};

const closeSidebar = () => {
    if (isMobile.value) {
        sidebarOpen.value = false;
    }
};

// Current time for footer
const currentTime = ref('');
const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

// Computed sidebar width
const sidebarWidth = computed(() => {
    if (isMobile.value) return 'w-[280px]';
    return sidebarCollapsed.value ? 'w-[70px]' : 'w-[260px]';
});

const mainMargin = computed(() => {
    if (isMobile.value) return '';
    return sidebarCollapsed.value ? 'lg:ml-[70px]' : 'lg:ml-[260px]';
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100">
        <!-- Page Loading Overlay -->
        <PageLoadingOverlay />
        
        <!-- AI Rate Limit Toast -->
        <AIToast />
        
        <!-- Global Search Modal -->
        <GlobalSearchModal :show="showGlobalSearch" @close="closeGlobalSearch" />
        
        <!-- Mobile Sidebar Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="sidebarOpen && isMobile" 
                @click="closeSidebar"
                class="fixed inset-0 z-40 bg-black/70 backdrop-blur-md lg:hidden"
            ></div>
        </Transition>

        <div class="flex min-h-screen w-full">
            <!-- Ultra Premium Sidebar -->
            <aside 
                ref="sidebarRef"
                :class="[
                    'fixed inset-y-0 left-0 z-50 flex flex-col',
                    sidebarWidth,
                    'transition-all duration-300 ease-out',
                    isMobile ? (sidebarOpen ? 'translate-x-0' : '-translate-x-full') : 'translate-x-0',
                ]"
            >
                <!-- Multi-layer Background -->
                <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a] via-[#1e293b] to-[#0f172a]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,0.08),transparent_50%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(139,92,246,0.06),transparent_50%)]"></div>
                
                <!-- Animated Mesh Gradient -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-32 -right-32 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl animate-float"></div>
                    <div class="absolute top-1/3 -left-20 w-48 h-48 bg-violet-500/8 rounded-full blur-3xl animate-float-delayed"></div>
                    <div class="absolute -bottom-24 right-0 w-56 h-56 bg-cyan-500/6 rounded-full blur-3xl animate-float-slow"></div>
                </div>
                
                <!-- Glass Border Effect -->
                <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>
                
                <!-- Content Container -->
                <div class="relative flex flex-col h-full">
                    <!-- Premium Logo Header -->
                    <div ref="logoRef" class="relative h-16 px-3 flex items-center justify-between border-b border-white/5">
                        <Link v-if="!sidebarCollapsed || isMobile" href="/admin/dashboard" class="flex items-center gap-2.5 group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 via-teal-400 to-cyan-400 rounded-xl blur-xl opacity-40 group-hover:opacity-70 transition-all duration-500 group-hover:scale-110"></div>
                                <div class="absolute inset-0.5 bg-gradient-to-br from-emerald-500/50 to-teal-600/50 rounded-lg blur-sm"></div>
                                <div class="relative w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-0.5 group-hover:scale-105 transition-transform duration-300">
                                    <img src="/assets/logo.png" alt="TNLL" class="w-full h-full rounded-lg object-cover">
                                </div>
                            </div>
                            <div class="overflow-hidden">
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-bold bg-gradient-to-r from-white via-emerald-100 to-white bg-clip-text text-transparent">TNLL</span>
                                    <span class="text-sm font-bold text-white/90">Admin</span>
                                </div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <div class="w-1 h-1 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-[8px] text-emerald-400/80 uppercase tracking-[0.15em] font-medium">Control Panel</span>
                                </div>
                            </div>
                        </Link>
                        
                        <!-- Collapsed Logo -->
                        <Link v-else href="/admin/dashboard" class="w-full flex justify-center group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-lg blur-lg opacity-50 group-hover:opacity-80 transition-opacity"></div>
                                <img src="/assets/logo.png" alt="TNLL" class="relative w-9 h-9 rounded-lg ring-2 ring-white/20">
                            </div>
                        </Link>
                        
                        <!-- Mobile Close Button -->
                        <button 
                            v-if="isMobile"
                            @click="closeSidebar" 
                            class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200 hover:rotate-90"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Navigation Sections -->
                    <nav ref="navRef" class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto sidebar-scroll">
                        <div class="nav-section">
                            <SidebarNavKonten :collapsed="sidebarCollapsed && !isMobile" @navigate="closeSidebar" />
                        </div>
                        <div class="nav-section">
                            <SidebarNavManajemen :collapsed="sidebarCollapsed && !isMobile" @navigate="closeSidebar" />
                        </div>
                        <div class="nav-section">
                            <SidebarNavLaporan :collapsed="sidebarCollapsed && !isMobile" @navigate="closeSidebar" />
                        </div>
                        <div class="nav-section">
                            <SidebarNavPengaturan :collapsed="sidebarCollapsed && !isMobile" :admin="admin" @navigate="closeSidebar" />
                        </div>
                    </nav>
                    
                    <!-- Collapse Button (Desktop Only) -->
                    <div v-if="!isMobile" class="px-2 py-2 border-t border-white/5">
                        <button 
                            @click="sidebarCollapsed = !sidebarCollapsed"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200"
                        >
                            <component :is="sidebarCollapsed ? ChevronRight : ChevronLeft" class="w-4 h-4" />
                            <span v-if="!sidebarCollapsed" class="text-xs">Collapse</span>
                        </button>
                    </div>
                    
                    <!-- Premium Footer -->
                    <div v-if="!sidebarCollapsed || isMobile" class="relative px-3 py-2.5 border-t border-white/5">
                        <div class="absolute top-0 left-4 right-4 h-px bg-gradient-to-r from-transparent via-emerald-500/30 to-transparent"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <div class="absolute inset-0 w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
                                </div>
                                <span class="text-[10px] text-slate-500">System Online</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-500">
                                <Clock class="w-3 h-3 text-slate-400" />
                                <span>{{ currentTime }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main 
                :class="[
                    'flex-1 transition-all duration-300 min-h-screen overflow-x-hidden',
                    mainMargin
                ]"
            >
                <!-- Premium Header -->
                <header class="sticky top-0 z-30 backdrop-blur-xl bg-white/80 border-b border-gray-200/50 shadow-sm">
                    <div class="flex items-center justify-between h-12 sm:h-14 px-3 sm:px-4 lg:px-6">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <!-- Menu Toggle -->
                            <button 
                                @click="toggleSidebar" 
                                class="p-2 rounded-xl text-gray-500 hover:text-violet-600 hover:bg-violet-50 transition-all duration-300 hover:scale-105"
                            >
                                <Menu class="w-5 h-5" />
                            </button>
                            
                            <!-- Search Button -->
                            <button @click="openGlobalSearch" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-100/80 hover:bg-gray-100 border border-gray-200/50 transition-all duration-300 hover:shadow-md">
                                <Search class="w-4 h-4 text-gray-400" />
                                <span class="hidden sm:inline text-xs text-gray-500">Cari...</span>
                                <span class="hidden md:flex text-[10px] text-gray-400 bg-gray-200 px-1.5 py-0.5 rounded ml-2 sm:ml-6">⌘K</span>
                            </button>
                        </div>

                        <!-- Header Controls -->
                        <AdminHeaderControls :admin="admin" />
                    </div>
                </header>

                <!-- Page Content -->
                <div class="p-3 sm:p-4 lg:p-6 overflow-x-hidden max-w-full">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
.sidebar-scroll::-webkit-scrollbar {
    width: 3px;
}
.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 10px;
}
.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Floating animations */
@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(3deg); }
}
@keyframes float-delayed {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-8px) rotate(-2deg); }
}
@keyframes float-slow {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-6px) rotate(2deg); }
}

.animate-float {
    animation: float 8s ease-in-out infinite;
}
.animate-float-delayed {
    animation: float-delayed 10s ease-in-out infinite;
    animation-delay: 2s;
}
.animate-float-slow {
    animation: float-slow 12s ease-in-out infinite;
    animation-delay: 4s;
}
</style>
