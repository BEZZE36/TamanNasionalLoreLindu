<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { UserCircle, Shield, Database, Lock, ChevronRight } from 'lucide-vue-next';

const emit = defineEmits(['navigate']);
defineProps({ admin: Object });
const page = usePage();

const adminUser = computed(() => page.props.admin);
const isSuperAdmin = computed(() => adminUser.value?.role === 'super_admin');
const hasPermission = (permission) => {
    if (isSuperAdmin.value) return true;
    return adminUser.value?.permissions?.includes(permission) || false;
};

const canAccessAdmins = computed(() => hasPermission('access-admins'));
const canAccessRoles = computed(() => hasPermission('access-roles'));
const canAccessDatabase = computed(() => hasPermission('access-database'));

const canOpenAdmins = computed(() => hasPermission('read-admins') || hasPermission('write-admins'));
const canOpenRoles = computed(() => hasPermission('read-roles') || hasPermission('write-roles'));
const canOpenDatabase = computed(() => hasPermission('read-database') || hasPermission('write-database'));

const showPengaturanSection = computed(() => {
    return canAccessAdmins.value || canAccessRoles.value || canAccessDatabase.value;
});

const isActive = (path) => page.url.startsWith(path);
const handleNavigate = () => emit('navigate');
</script>

<template>
    <!-- Section Divider -->
    <div v-if="showPengaturanSection" class="section-divider">
        <div class="section-line"></div>
        <span class="section-text">Pengaturan</span>
        <div class="section-line"></div>
    </div>

    <!-- Kelola Admin -->
    <div v-if="canAccessAdmins && !canOpenAdmins" class="menu-locked">
        <div class="icon-wrap icon-locked"><UserCircle class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Kelola Admin</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessAdmins" href="/admin/admins" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/admins') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/admins') 
                ? 'bg-gradient-to-br from-slate-500 to-gray-600 shadow-lg shadow-slate-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-slate-500/80 group-hover:to-gray-600/80']">
            <UserCircle :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/admins') ? 'text-white' : 'text-slate-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Kelola Admin</span>
        <div v-if="isActive('/admin/admins')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- Role & Akses -->
    <div v-if="canAccessRoles && !canOpenRoles" class="menu-locked">
        <div class="icon-wrap icon-locked"><Shield class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Role & Akses</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessRoles" href="/admin/roles" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/roles') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/roles') 
                ? 'bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg shadow-rose-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-rose-500/80 group-hover:to-pink-600/80']">
            <Shield :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/roles') ? 'text-white' : 'text-rose-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Role & Akses</span>
        <div v-if="isActive('/admin/roles')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- Database -->
    <div v-if="canAccessDatabase && !canOpenDatabase" class="menu-locked">
        <div class="icon-wrap icon-locked"><Database class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Database</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessDatabase" href="/admin/database" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/database') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/database') 
                ? 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-blue-500/80 group-hover:to-indigo-600/80']">
            <Database :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/database') ? 'text-white' : 'text-blue-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Database</span>
        <div v-if="isActive('/admin/database')" class="active-indicator"></div>
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
    background: linear-gradient(90deg, transparent, rgba(251,113,133,0.3), transparent);
}

.section-text {
    font-size: 0.625rem;
    font-weight: 700;
    color: rgba(251,113,133,0.8);
    text-transform: uppercase;
    letter-spacing: 0.15em;
}
</style>
