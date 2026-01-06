<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, Ticket, Users, QrCode, Lock, ChevronRight } from 'lucide-vue-next';

const emit = defineEmits(['navigate']);
const page = usePage();

const admin = computed(() => page.props.admin);
const isSuperAdmin = computed(() => admin.value?.role === 'super_admin');
const hasPermission = (permission) => {
    if (isSuperAdmin.value) return true;
    return admin.value?.permissions?.includes(permission) || false;
};

const canAccessBookings = computed(() => hasPermission('access-bookings'));
const canAccessCoupons = computed(() => hasPermission('access-coupons'));
const canAccessUsers = computed(() => hasPermission('access-users'));
const canAccessTickets = computed(() => hasPermission('access-tickets'));

const canOpenBookings = computed(() => hasPermission('read-bookings') || hasPermission('write-bookings'));
const canOpenCoupons = computed(() => hasPermission('read-coupons') || hasPermission('write-coupons'));
const canOpenUsers = computed(() => hasPermission('read-users') || hasPermission('write-users'));
const canOpenTickets = computed(() => hasPermission('read-tickets') || hasPermission('write-tickets') || hasPermission('scan-tickets'));

const showManajemenSection = computed(() => {
    return canAccessBookings.value || canAccessCoupons.value || canAccessUsers.value || canAccessTickets.value;
});

const isActive = (path) => page.url.startsWith(path);
const handleNavigate = () => emit('navigate');
</script>

<template>
    <!-- Section Divider -->
    <div v-if="showManajemenSection" class="section-divider">
        <div class="section-line"></div>
        <span class="section-text">Manajemen</span>
        <div class="section-line"></div>
    </div>

    <!-- Bookings -->
    <div v-if="canAccessBookings && !canOpenBookings" class="menu-locked">
        <div class="icon-wrap icon-locked"><ClipboardList class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Pemesanan</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessBookings" href="/admin/bookings" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/bookings') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/bookings') 
                ? 'bg-gradient-to-br from-sky-500 to-blue-600 shadow-lg shadow-sky-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-sky-500/80 group-hover:to-blue-600/80']">
            <ClipboardList :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/bookings') ? 'text-white' : 'text-sky-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Pemesanan</span>
        <div v-if="isActive('/admin/bookings')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- Coupons -->
    <div v-if="canAccessCoupons && !canOpenCoupons" class="menu-locked">
        <div class="icon-wrap icon-locked"><Ticket class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Kupon</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessCoupons" href="/admin/coupons" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/coupons') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/coupons') 
                ? 'bg-gradient-to-br from-fuchsia-500 to-pink-600 shadow-lg shadow-fuchsia-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-fuchsia-500/80 group-hover:to-pink-600/80']">
            <Ticket :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/coupons') ? 'text-white' : 'text-fuchsia-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Kupon</span>
        <div v-if="isActive('/admin/coupons')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- Users -->
    <div v-if="canAccessUsers && !canOpenUsers" class="menu-locked">
        <div class="icon-wrap icon-locked"><Users class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Pengguna</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessUsers" href="/admin/users" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/users') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/users') 
                ? 'bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-violet-500/80 group-hover:to-purple-600/80']">
            <Users :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/users') ? 'text-white' : 'text-violet-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Pengguna</span>
        <div v-if="isActive('/admin/users')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- Scan Tickets -->
    <div v-if="canAccessTickets && !canOpenTickets" class="menu-locked">
        <div class="icon-wrap icon-locked"><QrCode class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Scan Tiket</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessTickets" href="/admin/tickets" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/tickets') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/tickets') 
                ? 'bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg shadow-purple-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-purple-500/80 group-hover:to-indigo-600/80']">
            <QrCode :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/tickets') ? 'text-white' : 'text-purple-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Scan Tiket</span>
        <div v-if="isActive('/admin/tickets')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>
</template>

<style scoped>
.menu-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 0.625rem;
    color: rgb(203 213 225);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.menu-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
    transform: translateX(-100%);
    transition: transform 0.5s;
}

.menu-item:hover::before { transform: translateX(100%); }
.menu-item:hover { background: rgba(255,255,255,0.05); transform: translateX(2px); }

.menu-active {
    background: linear-gradient(90deg, rgba(139,92,246,0.15), rgba(168,85,247,0.1), transparent);
    border-left: 2px solid rgb(139 92 246);
    color: white;
}

.menu-locked {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 0.625rem;
    color: rgb(71 85 105);
    cursor: not-allowed;
    opacity: 0.6;
}

.icon-wrap {
    width: 2.125rem;
    height: 2.125rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.menu-item:hover .icon-wrap { transform: scale(1.05); }

.icon-locked {
    background: rgba(51,65,85,0.5);
    color: rgb(100 116 139);
}

.lock-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgb(51 65 85);
    border: 1px solid rgb(71 85 105);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgb(148 163 184);
}

.menu-label {
    font-size: 0.8125rem;
    font-weight: 500;
    letter-spacing: 0.01em;
}

.active-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgb(139 92 246), rgb(192 132 252));
    margin-left: auto;
    box-shadow: 0 0 8px rgba(139,92,246,0.6);
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 8px rgba(139,92,246,0.6); }
    50% { box-shadow: 0 0 16px rgba(139,92,246,0.8); }
}

.section-divider {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 0.5rem;
    margin-top: 0.375rem;
}

.section-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(56,189,248,0.3), transparent);
}

.section-text {
    font-size: 0.625rem;
    font-weight: 700;
    color: rgba(56,189,248,0.8);
    text-transform: uppercase;
    letter-spacing: 0.15em;
}
</style>
