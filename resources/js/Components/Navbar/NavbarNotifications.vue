<script setup>
import { ref, onMounted, computed } from 'vue';
import { Bell, BellOff, CheckCheck, Megaphone, CreditCard, Info, AlertTriangle, Clock, ArrowRight, X } from 'lucide-vue-next';
import NotificationSkeleton from '@/Components/Skeleton/NotificationSkeleton.vue';

const props = defineProps({ scrolled: Boolean, user: Object });

const isOpen = ref(false);
const loading = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const isGuest = computed(() => !props.user);

onMounted(() => { checkUnread(); setInterval(checkUnread, 60000); });

const checkUnread = async () => { isGuest.value ? checkGuestNotifications() : checkAuthNotifications(); };

const checkGuestNotifications = async () => {
    try {
        const response = await fetch('/api/announcements/public');
        const data = await response.json();
        const announcements = data.announcements || [];
        const readIds = JSON.parse(localStorage.getItem('read_announcements') || '[]');
        unreadCount.value = announcements.filter(a => !readIds.includes(a.id)).length;
    } catch (e) { console.error('Failed to check guest notifications', e); }
};

const checkAuthNotifications = async () => {
    try {
        const response = await fetch('/notifications/unread-count', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        const data = await response.json();
        unreadCount.value = data.count;
    } catch (e) { console.error('Failed to check notifications', e); }
};

const loadNotifications = async () => {
    loading.value = true;
    isGuest.value ? await loadGuestNotifications() : await loadAuthNotifications();
};

const loadGuestNotifications = async () => {
    try {
        const response = await fetch('/api/announcements/public');
        const data = await response.json();
        const announcements = data.announcements || [];
        const readIds = JSON.parse(localStorage.getItem('read_announcements') || '[]');
        notifications.value = announcements.map(a => ({ ...a, is_read: readIds.includes(a.id), time_ago: a.created_at, link: a.link_url || '#' }));
        unreadCount.value = notifications.value.filter(n => !n.is_read).length;
    } catch (e) { console.error('Failed to load guest notifications', e); }
    finally { loading.value = false; }
};

const loadAuthNotifications = async () => {
    try {
        const response = await fetch('/notifications/recent', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        const data = await response.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch (e) { console.error('Failed to load notifications', e); }
    finally { loading.value = false; }
};

const handleMarkRead = (notification) => { isGuest.value ? markGuestRead(notification) : markAuthRead(notification); };

const markGuestRead = (notification) => {
    let readIds = JSON.parse(localStorage.getItem('read_announcements') || '[]');
    if (!readIds.includes(notification.id)) {
        readIds.push(notification.id);
        localStorage.setItem('read_announcements', JSON.stringify(readIds));
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        notifications.value = notifications.value.map(n => n.id === notification.id ? { ...n, is_read: true } : n);
    }
    if (notification.link && notification.link !== '#') { window.location.href = notification.link; }
};

const markAuthRead = async (notification) => {
    try {
        await fetch(`/notifications/${notification.id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Content-Type': 'application/json' } });
        unreadCount.value = Math.max(0, unreadCount.value - 1);
        notifications.value = notifications.value.map(n => n.id === notification.id ? { ...n, is_read: true } : n);
        if (notification.link) { window.location.href = notification.link; }
    } catch (e) { console.error('Failed to mark as read', e); }
};

const markAllRead = () => {
    if (isGuest.value) {
        const allIds = notifications.value.map(n => n.id);
        const existing = JSON.parse(localStorage.getItem('read_announcements') || '[]');
        localStorage.setItem('read_announcements', JSON.stringify([...new Set([...existing, ...allIds])]));
    } else { fetch('/notifications/mark-all-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } }); }
    unreadCount.value = 0;
    notifications.value = notifications.value.map(n => ({ ...n, is_read: true }));
};

const getIcon = (n) => {
    const t = n.title || '';
    if (t.includes('Berhasil') || n.type === 'payment_success') return CreditCard;
    if (t.includes('Gagal') || n.type === 'payment_failed') return AlertTriangle;
    if (n.type === 'announcement') return Megaphone;
    return Info;
};

const getIconGradient = (n) => {
    const t = n.title || '';
    if (t.includes('Berhasil') || n.type === 'payment_success') return 'from-green-500 to-emerald-600';
    if (t.includes('Gagal') || n.type === 'payment_failed') return 'from-red-500 to-rose-600';
    if (n.type === 'announcement') return 'from-orange-500 to-amber-500';
    return 'from-blue-500 to-cyan-600';
};

const toggleDropdown = () => { 
    isOpen.value = !isOpen.value; 
    if (isOpen.value) loadNotifications(); 
};

const closeDropdown = () => {
    isOpen.value = false;
};
</script>

<template>
    <div class="relative flex items-center">
        <!-- Trigger Button - Responsive -->
        <button 
            @click="toggleDropdown" 
            :class="[
                'group relative w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105',
                scrolled 
                    ? 'text-gray-600 hover:text-emerald-600 hover:bg-gray-100' 
                    : 'text-white hover:bg-white/10'
            ]"
        >
            <!-- Bell Icon with Animation -->
            <Bell :class="['w-4 h-4 sm:w-4.5 sm:h-4.5 lg:w-5 lg:h-5 transition-all duration-300', unreadCount > 0 ? 'animate-wiggle' : 'group-hover:rotate-12']" />
            
            <!-- Badge - Responsive -->
            <span 
                v-if="unreadCount > 0" 
                class="absolute -top-0.5 -right-0.5 min-w-[14px] sm:min-w-[16px] lg:min-w-[18px] h-[14px] sm:h-[16px] lg:h-[18px] px-0.5 sm:px-1 bg-gradient-to-r from-red-500 to-rose-500 rounded-full flex items-center justify-center text-[8px] sm:text-[9px] lg:text-[10px] text-white font-bold shadow-lg shadow-red-500/30 ring-1 sm:ring-2 ring-white animate-bounce-slow"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Backdrop -->
        <div v-if="isOpen" @click="closeDropdown" class="fixed inset-0 z-[90]"></div>

        <!-- Dropdown Menu -->
        <Transition 
            enter-active-class="transition ease-out duration-300" 
            enter-from-class="opacity-0 -translate-y-3 scale-95" 
            enter-to-class="opacity-100 translate-y-0 scale-100" 
            leave-active-class="transition ease-in duration-200" 
            leave-from-class="opacity-100 translate-y-0 scale-100" 
            leave-to-class="opacity-0 -translate-y-3 scale-95"
        >
            <div v-if="isOpen" class="absolute right-0 top-full mt-3 sm:mt-3 md:mt-4 lg:mt-3 w-[250px] sm:w-[280px] md:w-[300px] lg:w-[320px] bg-white/95 backdrop-blur-xl rounded-xl sm:rounded-2xl shadow-2xl shadow-black/10 border border-gray-100/50 z-[100] overflow-hidden">
                
                <!-- Premium Header - Responsive -->
                <div class="relative px-3 sm:px-4 py-3 sm:py-4 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 overflow-hidden">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 w-20 sm:w-24 h-20 sm:h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 w-12 sm:w-16 h-12 sm:h-16 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-2xl"></div>
                    
                    <!-- Close button -->
                    <button 
                        @click.stop="closeDropdown"
                        class="absolute top-2 sm:top-3 right-2 sm:right-3 w-6 h-6 sm:w-7 sm:h-7 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-all duration-200 hover:scale-110 hover:rotate-90 z-10"
                    >
                        <X class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </button>
                    
                    <div class="relative flex items-center justify-between pr-6 sm:pr-8">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                                <Bell class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-sm sm:text-base">Notifikasi</h3>
                                <p v-if="unreadCount > 0" class="text-[9px] sm:text-[10px] text-white/70">{{ unreadCount }} belum dibaca</p>
                                <p v-else class="text-[9px] sm:text-[10px] text-white/70">Semua sudah dibaca</p>
                            </div>
                        </div>
                        
                        <button 
                            v-if="unreadCount > 0" 
                            @click="markAllRead" 
                            class="hidden sm:flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 sm:py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-[9px] sm:text-[10px] font-semibold text-white transition-all duration-200 hover:scale-105"
                        >
                            <CheckCheck class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                            Tandai Dibaca
                        </button>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="max-h-80 overflow-y-auto">
                    <!-- Loading State - Skeleton -->
                    <div v-if="loading">
                        <NotificationSkeleton :count="3" />
                    </div>
                    
                    <!-- Empty State - Responsive -->
                    <div v-else-if="notifications.length === 0" class="py-8 sm:py-10 lg:py-12 text-center px-4 sm:px-6">
                        <div class="relative w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto mb-3 sm:mb-4">
                            <div class="absolute inset-0 bg-gray-200/50 rounded-xl sm:rounded-2xl blur-xl"></div>
                            <div class="relative w-full h-full rounded-xl sm:rounded-2xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center">
                                <BellOff class="w-5 h-5 sm:w-6 sm:h-6 lg:w-7 lg:h-7 text-gray-300" />
                            </div>
                        </div>
                        <p class="font-medium text-gray-600 text-sm sm:text-base">Tidak ada notifikasi</p>
                        <p class="text-[11px] sm:text-xs text-gray-400 mt-1">Kami akan memberitahu Anda saat ada yang baru</p>
                    </div>
                    
                    <!-- Notifications List -->
                    <div v-else class="p-2">
                        <button 
                            v-for="(n, index) in notifications" 
                            :key="n.id" 
                            @click="handleMarkRead(n)" 
                            :class="[
                                'group w-full flex items-start gap-3 p-3 rounded-xl text-left transition-all duration-300 hover:scale-[1.01]',
                                n.is_read 
                                    ? 'bg-white hover:bg-gray-50' 
                                    : 'bg-gradient-to-r from-emerald-50/80 to-teal-50/50 hover:from-emerald-50 hover:to-teal-50 border border-emerald-100/50'
                            ]"
                            :style="{ animationDelay: `${index * 50}ms` }"
                        >
                            <!-- Icon -->
                            <div class="relative flex-shrink-0">
                                <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300', getIconGradient(n)]">
                                    <component :is="getIcon(n)" class="w-5 h-5 text-white" />
                                </div>
                                <!-- Unread dot -->
                                <div v-if="!n.is_read" class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p :class="['text-sm truncate', n.is_read ? 'text-gray-700' : 'text-gray-900 font-semibold']">
                                    {{ n.title }}
                                </p>
                                <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">
                                    {{ n.message || n.content }}
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <Clock class="w-3 h-3 text-gray-300" />
                                    <span class="text-[10px] text-gray-400">{{ n.time_ago }}</span>
                                </div>
                            </div>
                            
                            <!-- Arrow -->
                            <ArrowRight class="w-4 h-4 text-gray-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all duration-200 flex-shrink-0 mt-1" />
                        </button>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    <a 
                        href="/notifications" 
                        class="group flex items-center justify-center gap-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors"
                    >
                        Lihat Semua Notifikasi
                        <ArrowRight class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes wiggle {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-8deg); }
    75% { transform: rotate(8deg); }
}

.animate-wiggle {
    animation: wiggle 0.5s ease-in-out infinite;
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-2px); }
}

.animate-bounce-slow {
    animation: bounce-slow 1s ease-in-out infinite;
}
</style>
