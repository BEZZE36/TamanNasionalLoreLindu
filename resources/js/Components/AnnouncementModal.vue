<script setup>
import { ref, onMounted, computed } from 'vue';
import { X, Rocket, ArrowRight, AlertTriangle, AlertCircle } from 'lucide-vue-next';

const announcements = ref([]);
const loading = ref(true);
const dismissedIds = ref([]);
const showModal = ref(false);
const currentIndex = ref(0);
const dontShowAgain = ref(false);

// Filter fullscreen/modal announcements (not dismissed)
const modalAnnouncements = computed(() => {
    return announcements.value.filter(a => 
        a.type === 'fullscreen' && 
        !dismissedIds.value.includes(a.id)
    );
});

// Current modal to show
const currentModal = computed(() => {
    if (modalAnnouncements.value.length === 0) return null;
    return modalAnnouncements.value[currentIndex.value];
});

// Has modals to show
const hasModals = computed(() => modalAnnouncements.value.length > 0);

// Theme configurations based on notification_type
const themes = {
    info: {
        primary: 'bg-gradient-to-r from-blue-500 to-blue-600 hover:to-blue-500',
        primaryShadow: 'shadow-[0_1px_2px_rgba(0,0,0,0.1),inset_0_1px_0_rgba(255,255,255,0.2)] hover:shadow-[0_0_30px_-5px_rgba(59,130,246,0.6)]',
        iconBg: 'from-blue-500/30 to-[#15161e]',
        iconColor: 'text-blue-400 drop-shadow-[0_0_15px_rgba(59,130,246,0.6)]',
        heroGradient: 'from-blue-600/20 to-teal-600/20',
        label: 'INFORMASI'
    },
    danger: {
        primary: 'bg-gradient-to-r from-red-500 to-rose-600 hover:to-rose-500',
        primaryShadow: 'shadow-[0_1px_2px_rgba(0,0,0,0.1),inset_0_1px_0_rgba(255,255,255,0.2)] hover:shadow-[0_0_30px_-5px_rgba(239,68,68,0.6)]',
        iconBg: 'from-red-500/30 to-[#15161e]',
        iconColor: 'text-red-400 drop-shadow-[0_0_15px_rgba(239,68,68,0.6)]',
        heroGradient: 'from-red-600/20 to-rose-600/20',
        label: 'PENTING'
    },
    warning: {
        primary: 'bg-gradient-to-r from-amber-500 to-orange-500 hover:to-orange-400',
        primaryShadow: 'shadow-[0_1px_2px_rgba(0,0,0,0.1),inset_0_1px_0_rgba(255,255,255,0.2)] hover:shadow-[0_0_30px_-5px_rgba(245,158,11,0.6)]',
        iconBg: 'from-amber-500/30 to-[#15161e]',
        iconColor: 'text-amber-400 drop-shadow-[0_0_15px_rgba(245,158,11,0.6)]',
        heroGradient: 'from-amber-600/20 to-orange-600/20',
        label: 'PERHATIAN'
    }
};

// Get theme for current modal
const currentTheme = computed(() => {
    if (!currentModal.value) return themes.info;
    return themes[currentModal.value.notification_type] || themes.info;
});

// Load announcements from API
const loadAnnouncements = async () => {
    try {
        const response = await fetch('/api/announcements/public');
        const data = await response.json();
        announcements.value = data.announcements || [];
        
        dismissedIds.value = JSON.parse(localStorage.getItem('dismissed_fullscreen_announcements') || '[]');
        
        if (modalAnnouncements.value.length > 0) {
            setTimeout(() => {
                showModal.value = true;
                trackView(modalAnnouncements.value[0].id);
            }, 800);
        }
    } catch (e) {
        console.error('Failed to load announcements', e);
    } finally {
        loading.value = false;
    }
};

// Track view
const trackView = async (id) => {
    try {
        await fetch(`/admin/announcements/${id}/track-view`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
    } catch (e) {}
};

// Track click
const trackClick = async (announcement) => {
    try {
        await fetch(`/admin/announcements/${announcement.id}/track-click`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
        
        if (announcement.link_url) {
            window.open(announcement.link_url, '_blank');
        }
    } catch (e) {
        if (announcement.link_url) {
            window.open(announcement.link_url, '_blank');
        }
    }
};

// Dismiss modal
const dismissModal = async (permanent = false) => {
    if (!currentModal.value) return;
    
    const id = currentModal.value.id;
    
    if (permanent || dontShowAgain.value) {
        dismissedIds.value.push(id);
        localStorage.setItem('dismissed_fullscreen_announcements', JSON.stringify(dismissedIds.value));
    }
    
    try {
        await fetch(`/admin/announcements/${id}/track-dismiss`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
    } catch (e) {}
    
    // Show next modal or close
    if (currentIndex.value + 1 < modalAnnouncements.value.length) {
        currentIndex.value++;
        trackView(modalAnnouncements.value[currentIndex.value].id);
    } else {
        showModal.value = false;
    }
    
    dontShowAgain.value = false;
};

const closeLater = () => {
    showModal.value = false;
};

onMounted(() => {
    loadAnnouncements();
});
</script>

<template>
    <Teleport to="body">
        <!-- Fullscreen Modal Overlay -->
        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="showModal && currentModal"
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-[#050505]/80 backdrop-blur-sm"
            >
                <!-- Background glows -->
                <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
                    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
                    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-600/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 2s;"></div>
                </div>
                
                <!-- Modal Card -->
                <Transition
                    enter-active-class="transition-all duration-700 ease-out"
                    enter-from-class="opacity-0 translate-y-8 scale-95 blur-lg"
                    enter-to-class="opacity-100 translate-y-0 scale-100 blur-0"
                    leave-active-class="transition-all duration-300 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div 
                        v-if="showModal"
                        class="group relative w-full max-w-[500px] flex flex-col glass-panel rounded-3xl overflow-hidden transition-all duration-500"
                    >
                        <!-- Hover shine effect -->
                        <div class="absolute top-[-50%] left-[-50%] w-[200%] h-[200%] bg-gradient-to-br from-white/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none rotate-45"></div>
                        
                        <!-- Close button -->
                        <button 
                            @click="dismissModal(true)"
                            class="absolute top-5 right-5 z-30 p-2.5 text-slate-400 hover:text-white bg-black/40 hover:bg-white/10 rounded-full transition-all duration-300 hover:rotate-90 border border-white/5 hover:border-white/20 backdrop-blur-md group/close"
                        >
                            <X class="w-5 h-5 transition-transform group-active/close:scale-90" />
                        </button>
                        
                        <!-- Hero Image Area -->
                        <div class="relative w-full h-56 overflow-hidden group/image">
                            <!-- Gradient background based on theme -->
                            <div 
                                :class="[
                                    'absolute inset-0 bg-gradient-to-br transition-transform duration-[1.5s] ease-[cubic-bezier(0.25,1,0.5,1)] group-hover/image:scale-110',
                                    currentTheme.heroGradient
                                ]"
                            ></div>
                            <!-- Abstract shapes -->
                            <div class="absolute inset-0">
                                <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-gradient-to-br from-white/10 to-transparent rounded-full blur-xl animate-float"></div>
                                <div class="absolute bottom-1/4 right-1/4 w-24 h-24 bg-gradient-to-br from-white/5 to-transparent rounded-full blur-lg animate-float" style="animation-delay: 1s;"></div>
                            </div>
                            <!-- Bottom gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#15161e]/20 to-[#14151a]"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-[#14151a] via-transparent to-transparent opacity-90"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="relative z-10 flex flex-col px-8 pb-8 pt-0 text-center items-center -mt-14">
                            <!-- Floating Icon -->
                            <div class="mb-5 p-2 rounded-full bg-[#15161e]/40 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.4)] animate-float">
                                <div 
                                    :class="[
                                        'relative flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br border border-white/5 shadow-inner overflow-hidden group/icon',
                                        currentTheme.iconBg
                                    ]"
                                >
                                    <!-- Shine animation -->
                                    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent translate-x-[-150%] animate-shine"></div>
                                    <Rocket v-if="currentModal.notification_type === 'info'" :class="['w-7 h-7 group-hover/icon:scale-110 transition-transform duration-300', currentTheme.iconColor]" />
                                    <AlertCircle v-else-if="currentModal.notification_type === 'danger'" :class="['w-7 h-7 group-hover/icon:scale-110 transition-transform duration-300', currentTheme.iconColor]" />
                                    <AlertTriangle v-else :class="['w-7 h-7 group-hover/icon:scale-110 transition-transform duration-300', currentTheme.iconColor]" />
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h2 class="text-white text-2xl font-bold leading-tight tracking-tight mb-3 drop-shadow-md bg-clip-text text-transparent bg-gradient-to-b from-white to-white/70">
                                {{ currentModal.title }}
                            </h2>
                            
                            <!-- Message -->
                            <p class="text-slate-400 text-sm font-medium leading-relaxed mb-6 max-w-[340px] mx-auto tracking-wide">
                                {{ currentModal.message }}
                            </p>
                            
                            <!-- Buttons -->
                            <div class="flex flex-col w-full gap-3 mb-6">
                                <!-- Primary CTA -->
                                <button 
                                    v-if="currentModal.link_url && currentModal.link_text"
                                    @click="trackClick(currentModal)"
                                    :class="[
                                        'group/btn relative flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl h-12 px-6 transition-all duration-300 text-white text-sm font-semibold tracking-wide hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]',
                                        currentTheme.primary,
                                        currentTheme.primaryShadow
                                    ]"
                                >
                                    <span class="relative z-10 flex items-center gap-2">
                                        {{ currentModal.link_text }}
                                        <ArrowRight class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform duration-300" />
                                    </span>
                                    <!-- Shimmer effect -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700 ease-in-out"></div>
                                </button>
                                
                                <!-- Secondary Button (Maybe later) -->
                                <button 
                                    v-if="currentModal.is_dismissible"
                                    @click="closeLater"
                                    class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl h-12 px-6 bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 hover:border-white/10 transition-all duration-300 text-slate-400 hover:text-white text-sm font-medium tracking-wide hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] backdrop-blur-sm"
                                >
                                    <span class="truncate">Nanti saja</span>
                                </button>
                            </div>
                            
                            <!-- Don't show again checkbox -->
                            <div v-if="currentModal.is_dismissible" class="w-full border-t border-white/5 pt-5 flex justify-center">
                                <label class="flex gap-x-3 items-center cursor-pointer group/chk select-none opacity-70 hover:opacity-100 transition-opacity">
                                    <div class="relative flex items-center justify-center">
                                        <input 
                                            v-model="dontShowAgain"
                                            type="checkbox" 
                                            class="h-4 w-4 rounded border-slate-600 bg-white/5 checked:bg-blue-500 checked:border-blue-500 focus:ring-0 focus:ring-offset-0 focus:border-blue-500 transition-all duration-200 cursor-pointer"
                                        />
                                    </div>
                                    <p class="text-slate-400 group-hover/chk:text-slate-200 transition-colors text-xs font-medium tracking-wide">Jangan tampilkan lagi</p>
                                </label>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.glass-panel {
    background: rgba(20, 20, 25, 0.85);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    border-left: 1px solid rgba(255, 255, 255, 0.08);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    box-shadow: 0 40px 80px -12px rgba(0, 0, 0, 0.8), inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@keyframes shine {
    0% { transform: translateX(-150%); }
    100% { transform: translateX(150%); }
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 0.8; }
}

.animate-float {
    animation: float 5s ease-in-out infinite;
}

.animate-shine {
    animation: shine 4s infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
