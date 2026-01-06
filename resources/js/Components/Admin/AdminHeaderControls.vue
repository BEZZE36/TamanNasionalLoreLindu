<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bell, CheckCheck, Megaphone, CreditCard, Info, AlertTriangle, LogOut, User, Settings } from 'lucide-vue-next';

defineProps({
    admin: Object,
});

const notificationsOpen = ref(false);
const userMenuOpen = ref(false);
const loading = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);

// Check unread count on mount
onMounted(() => {
    checkUnread();
    setInterval(checkUnread, 60000);
});

const checkUnread = async () => {
    try {
        const response = await fetch('/admin/notifications/api');
        if (response.ok) {
            const data = await response.json();
            unreadCount.value = data.unread_count || 0;
        }
    } catch (e) {
        console.error('Failed to check notifications', e);
    }
};

const loadNotifications = async () => {
    loading.value = true;
    try {
        const response = await fetch('/admin/notifications/api');
        if (response.ok) {
            const data = await response.json();
            notifications.value = data.notifications || [];
            unreadCount.value = data.unread_count || 0;
        }
    } catch (e) {
        console.error('Failed to load notifications', e);
    } finally {
        loading.value = false;
    }
};

const markAllRead = async () => {
    try {
        await fetch('/admin/notifications/api/mark-all-read', { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        unreadCount.value = 0;
        notifications.value = notifications.value.map(n => ({ ...n, is_read: true }));
    } catch (e) {
        console.error('Failed to mark all as read', e);
    }
};

const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value;
    userMenuOpen.value = false;
    if (notificationsOpen.value) {
        loadNotifications();
    }
};

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
    notificationsOpen.value = false;
};

const logout = () => {
    router.post('/admin/logout');
};

const getIcon = (notification) => {
    const title = notification.title || '';
    if (title.includes('Booking') || notification.type === 'booking') return CreditCard;
    if (notification.type === 'announcement') return Megaphone;
    if (notification.type === 'error') return AlertTriangle;
    return Info;
};

const getIconColor = (notification) => {
    const title = notification.title || '';
    if (title.includes('Berhasil') || notification.type === 'success') return 'from-green-500 to-emerald-500';
    if (title.includes('Gagal') || notification.type === 'error') return 'from-red-500 to-rose-500';
    if (notification.type === 'booking') return 'from-cyan-500 to-blue-500';
    return 'from-violet-500 to-purple-500';
};
</script>

<template>
    <div class="flex items-center gap-3">
        <!-- Notifications -->
        <div class="relative" @mouseleave="notificationsOpen = false">
            <button 
                @click="toggleNotifications"
                class="relative p-2 rounded-lg text-gray-500 hover:text-violet-600 hover:bg-violet-50 transition-all duration-300"
            >
                <Bell class="w-4 h-4" />
                <span 
                    v-if="unreadCount > 0"
                    class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white animate-pulse"
                ></span>
            </button>

            <!-- Dropdown -->
            <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
            >
                <div 
                    v-if="notificationsOpen"
                    class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl shadow-2xl shadow-black/15 border border-gray-100 z-[100] overflow-hidden"
                >
                    <div class="flex items-center justify-between px-3 py-2.5 border-b border-gray-100 bg-gradient-to-r from-violet-50 to-purple-50">
                        <h3 class="text-sm font-bold text-gray-900">Notifikasi</h3>
                        <button 
                            v-if="unreadCount > 0"
                            @click="markAllRead"
                            class="text-xs text-violet-600 hover:text-violet-700 font-medium flex items-center gap-1"
                        >
                            <CheckCheck class="w-3.5 h-3.5" />
                            Tandai Dibaca
                        </button>
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        <div v-if="loading" class="flex items-center justify-center py-8">
                            <div class="w-6 h-6 border-2 border-violet-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        <div v-else-if="notifications.length === 0" class="py-6 text-center">
                            <Bell class="w-10 h-10 mx-auto text-gray-300 mb-2" />
                            <p class="text-xs text-gray-500">Tidak ada notifikasi</p>
                        </div>

                        <div v-else class="divide-y divide-gray-50">
                            <div 
                                v-for="notification in notifications"
                                :key="notification.id"
                                :class="[
                                    'flex items-start gap-3 px-4 py-3 transition-colors duration-200',
                                    notification.is_read ? 'bg-white' : 'bg-violet-50/50'
                                ]"
                            >
                                <div :class="['w-8 h-8 rounded-lg bg-gradient-to-br flex items-center justify-center flex-shrink-0', getIconColor(notification)]">
                                    <component :is="getIcon(notification)" class="w-4 h-4 text-white" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p :class="['text-xs truncate', notification.is_read ? 'text-gray-700' : 'text-gray-900 font-semibold']">
                                        {{ notification.title }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 line-clamp-2 mt-0.5">{{ notification.message }}</p>
                                    <p class="text-[9px] text-gray-400 mt-0.5">{{ notification.time_ago }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- User Menu -->
        <div class="flex items-center gap-2 pl-2 border-l border-gray-200" @mouseleave="userMenuOpen = false">
            <div class="hidden sm:block text-right">
                <p class="text-xs font-semibold text-gray-700">{{ admin?.name || 'Admin' }}</p>
                <p class="text-[10px] text-gray-400">{{ admin?.role?.replace('_', ' ')?.replace(/\b\w/g, l => l.toUpperCase()) || 'Admin' }}</p>
            </div>
            <div class="relative">
                <button @click="toggleUserMenu" class="group">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg blur-md opacity-0 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-violet-500/30 ring-2 ring-white">
                        {{ admin?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                </button>

                <!-- Dropdown -->
                <Transition
                    enter-active-class="transition ease-out duration-100"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div 
                        v-if="userMenuOpen"
                        class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-2xl shadow-black/10 border border-gray-100 z-[100] overflow-hidden p-1.5"
                    >
                        <a href="/admin/profile" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs text-gray-700 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                            <User class="w-3.5 h-3.5" />
                            Profil Saya
                        </a>
                        <a href="/admin/settings" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs text-gray-700 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                            <Settings class="w-3.5 h-3.5" />
                            Pengaturan
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button 
                            @click="logout"
                            class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <LogOut class="w-3.5 h-3.5" />
                            Keluar
                        </button>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>
