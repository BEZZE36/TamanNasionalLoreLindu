<script setup>
import { ref, onMounted, computed } from 'vue';
import { X, Rocket, ArrowRight, AlertTriangle, AlertCircle } from 'lucide-vue-next';

const announcements = ref([]);
const loading = ref(true);
const dismissedIds = ref([]);
const currentIndex = ref(0);

// Filter banner announcements (not dismissed)
const bannerAnnouncements = computed(() => {
    return announcements.value.filter(a => 
        a.type === 'banner' && 
        !dismissedIds.value.includes(a.id)
    );
});

// Current banner to show
const currentBanner = computed(() => {
    if (bannerAnnouncements.value.length === 0) return null;
    return bannerAnnouncements.value[currentIndex.value];
});

// Has banners to show
const hasBanners = computed(() => bannerAnnouncements.value.length > 0);

// Theme configurations based on notification_type
const themes = {
    info: {
        gradient: 'from-indigo-600 via-[#6366f1] to-purple-600',
        shadow: 'shadow-indigo-500/20 hover:shadow-indigo-500/30',
        badge: 'bg-white text-indigo-700',
        button: 'bg-white text-indigo-600 hover:bg-white shadow-[0_1px_2px_rgba(0,0,0,0.1),0_4px_12px_rgba(255,255,255,0.1)] hover:shadow-[0_10px_20px_-5px_rgba(255,255,255,0.3)]',
        iconBg: 'bg-white/10',
        label: 'INFORMASI'
    },
    danger: {
        gradient: 'from-red-600 via-rose-600 to-pink-600',
        shadow: 'shadow-red-500/20 hover:shadow-red-500/30',
        badge: 'bg-white text-red-700',
        button: 'bg-white text-red-600 hover:bg-white shadow-[0_1px_2px_rgba(0,0,0,0.1),0_4px_12px_rgba(255,255,255,0.1)] hover:shadow-[0_10px_20px_-5px_rgba(255,255,255,0.3)]',
        iconBg: 'bg-white/10',
        label: 'PENTING'
    },
    warning: {
        gradient: 'from-amber-500 via-orange-500 to-yellow-500',
        shadow: 'shadow-amber-500/20 hover:shadow-amber-500/30',
        badge: 'bg-white text-amber-700',
        button: 'bg-white text-amber-600 hover:bg-white shadow-[0_1px_2px_rgba(0,0,0,0.1),0_4px_12px_rgba(255,255,255,0.1)] hover:shadow-[0_10px_20px_-5px_rgba(255,255,255,0.3)]',
        iconBg: 'bg-white/10',
        label: 'PERHATIAN'
    }
};

// Get theme for current banner
const currentTheme = computed(() => {
    if (!currentBanner.value) return themes.info;
    return themes[currentBanner.value.notification_type] || themes.info;
});

// Load announcements from API
const loadAnnouncements = async () => {
    try {
        const response = await fetch('/api/announcements/public');
        const data = await response.json();
        announcements.value = data.announcements || [];
        
        dismissedIds.value = JSON.parse(localStorage.getItem('dismissed_banner_announcements') || '[]');
        
        if (bannerAnnouncements.value.length > 0) {
            trackView(bannerAnnouncements.value[0].id);
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

// Dismiss banner
const dismissBanner = async () => {
    if (!currentBanner.value) return;
    
    const id = currentBanner.value.id;
    dismissedIds.value.push(id);
    localStorage.setItem('dismissed_banner_announcements', JSON.stringify(dismissedIds.value));
    
    try {
        await fetch(`/admin/announcements/${id}/track-dismiss`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        });
    } catch (e) {}
    
    // Move to next banner
    if (currentIndex.value + 1 < bannerAnnouncements.value.length) {
        currentIndex.value++;
        trackView(bannerAnnouncements.value[currentIndex.value].id);
    }
};

onMounted(() => {
    loadAnnouncements();
});
</script>

<template>
    <!-- Premium Top Banner -->
    <div v-if="hasBanners && currentBanner" class="w-full relative z-[100]">
        <div class="w-full px-4 py-3 animate-slide-enter">
            <div 
                :class="[
                    'group relative w-full overflow-hidden rounded-2xl bg-gradient-to-r shadow-2xl ring-1 ring-white/10 transition-all duration-500 hover:scale-[1.002]',
                    currentTheme.gradient,
                    currentTheme.shadow
                ]"
            >
                <!-- Overlay effects -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl transition-transform duration-700 group-hover:-translate-x-10 group-hover:translate-y-10"></div>
                
                <!-- Content -->
                <div class="relative flex flex-col md:flex-row items-center justify-between px-4 md:px-6 py-4 gap-4 z-10">
                    <!-- Left: Icon + Text -->
                    <div class="flex items-center gap-4 text-white flex-1 min-w-0 w-full md:w-auto">
                        <!-- Icon -->
                        <div 
                            :class="[
                                'hidden md:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl backdrop-blur-md border border-white/10 shadow-[inset_0_1px_1px_rgba(255,255,255,0.2)] transition-transform duration-500 group-hover:rotate-6 group-hover:scale-110',
                                currentTheme.iconBg
                            ]"
                        >
                            <Rocket v-if="currentBanner.notification_type === 'info'" class="w-6 h-6" />
                            <AlertCircle v-else-if="currentBanner.notification_type === 'danger'" class="w-6 h-6" />
                            <AlertTriangle v-else class="w-6 h-6" />
                        </div>
                        
                        <!-- Text Content -->
                        <div class="flex flex-col gap-1.5 text-center md:text-left">
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                                <!-- Badge -->
                                <span 
                                    :class="[
                                        'inline-flex items-center rounded-full px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest shadow-sm ring-1 ring-inset ring-black/5',
                                        currentTheme.badge
                                    ]"
                                >
                                    {{ currentTheme.label }}
                                </span>
                                <!-- Title -->
                                <h3 class="text-base md:text-lg font-bold leading-none tracking-tight text-white drop-shadow-sm">
                                    {{ currentBanner.title }}
                                </h3>
                            </div>
                            <!-- Message -->
                            <p class="text-sm font-medium leading-snug text-white/90 max-w-2xl line-clamp-2">
                                {{ currentBanner.message }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Right: Buttons -->
                    <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-center md:justify-end">
                        <!-- CTA Button -->
                        <button 
                            v-if="currentBanner.link_url && currentBanner.link_text"
                            @click="trackClick(currentBanner)"
                            :class="[
                                'group/btn relative overflow-hidden rounded-xl px-5 py-2.5 text-sm font-bold transition-all duration-300 ease-out hover:-translate-y-0.5 active:scale-95 active:translate-y-0 w-full md:w-auto',
                                currentTheme.button
                            ]"
                        >
                            <!-- Shimmer effect -->
                            <div class="absolute inset-0 -translate-x-full group-hover/btn:animate-shimmer bg-gradient-to-r from-transparent via-black/5 to-transparent"></div>
                            <div class="relative flex items-center justify-center gap-2">
                                <span>{{ currentBanner.link_text }}</span>
                                <ArrowRight class="w-4 h-4 transition-transform duration-300 group-hover/btn:translate-x-1" />
                            </div>
                        </button>
                        
                        <!-- Close Button -->
                        <button 
                            v-if="currentBanner.is_dismissible"
                            @click="dismissBanner"
                            aria-label="Tutup"
                            class="group/close flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white/70 transition-all duration-300 ease-out hover:bg-white/20 hover:text-white hover:rotate-90 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-white/40 active:scale-90"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideEnter {
    0% { 
        transform: translateY(-40px) scale(0.95); 
        opacity: 0; 
    }
    100% { 
        transform: translateY(0) scale(1); 
        opacity: 1; 
    }
}

@keyframes shimmer {
    0% { transform: translateX(-150%); }
    100% { transform: translateX(150%); }
}

.animate-slide-enter {
    animation: slideEnter 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

.animate-shimmer {
    animation: shimmer 1.5s infinite;
}
</style>
