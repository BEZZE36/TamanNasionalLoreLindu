<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Shield, ArrowLeft, Save, Key, Loader2, Crown, ChevronDown, ChevronUp, Lock,
    Eye, EyeOff, Edit3, Check, X, LayoutDashboard, MapPin, Sparkles, Bird, Image,
    Megaphone, FileText, Newspaper, Mail, Settings, ClipboardList, Ticket, Users,
    QrCode, Clock, BarChart3, UserCircle, Database
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    role: Object,
    permissions: Object
});

const pageRef = ref(null);
const expandedGroups = ref({}); // Semua tertutup awalnya

// Group config sesuai urutan sidebar (19 menu)
const groupConfig = {
    // KONTEN
    'dashboard': { label: 'Dashboard', icon: LayoutDashboard, color: 'blue', order: 1 },
    'destinations': { label: 'Destinasi', icon: MapPin, color: 'emerald', order: 2 },
    'flora': { label: 'Flora', icon: Sparkles, color: 'lime', order: 3 },
    'fauna': { label: 'Fauna', icon: Bird, color: 'amber', order: 4 },
    'gallery': { label: 'Gallery', icon: Image, color: 'pink', order: 5 },
    'announcements': { label: 'Pengumuman', icon: Megaphone, color: 'orange', order: 6 },
    'articles': { label: 'Artikel', icon: FileText, color: 'indigo', order: 7 },
    'news': { label: 'Berita', icon: Newspaper, color: 'red', order: 8 },
    'newsletter': { label: 'Newsletter', icon: Mail, color: 'teal', order: 9 },
    'site-info': { label: 'Edit Info', icon: Settings, color: 'cyan', order: 10 },
    // MANAJEMEN
    'bookings': { label: 'Pemesanan', icon: ClipboardList, color: 'sky', order: 11 },
    'coupons': { label: 'Kupon', icon: Ticket, color: 'fuchsia', order: 12 },
    'users': { label: 'Pengguna', icon: Users, color: 'violet', order: 13 },
    'tickets': { label: 'Scan Tiket', icon: QrCode, color: 'purple', order: 14 },
    // LAPORAN
    'activity-logs': { label: 'Activity Log', icon: Clock, color: 'gray', order: 15 },
    'analytics': { label: 'Analytics', icon: BarChart3, color: 'green', order: 16 },
    // PENGATURAN
    'admins': { label: 'Kelola Admin', icon: UserCircle, color: 'slate', order: 17 },
    'roles': { label: 'Role & Akses', icon: Shield, color: 'rose', order: 18 },
    'database': { label: 'Database', icon: Database, color: 'blue', order: 19 },
};

// Permission levels
const permissionLevels = [
    { key: 'none', label: 'Tidak Ada', icon: X, color: 'gray', desc: 'Menu tidak terlihat' },
    { key: 'access', label: 'Tampilkan', icon: Eye, color: 'blue', desc: 'Menu terlihat di sidebar' },
    { key: 'read', label: 'Read Only', icon: EyeOff, color: 'amber', desc: 'Bisa lihat, tidak bisa edit' },
    { key: 'write', label: 'Full Akses', icon: Edit3, color: 'emerald', desc: 'Akses penuh CRUD' },
];

const form = useForm({
    name: props.role?.name || '',
    description: props.role?.description || '',
    permissions: props.role?.permissions?.map(p => p.id) || []
});

const isProtected = props.role?.slug === 'super-admin';

// Filter only admin permissions (exclude public & user-actions) dan urutkan
const adminPermissions = computed(() => {
    if (!props.permissions) return {};
    const filtered = {};
    
    // Get groups and sort by order
    const sortedGroups = Object.entries(props.permissions)
        .filter(([group]) => !['public', 'user-actions'].includes(group) && groupConfig[group])
        .sort((a, b) => (groupConfig[a[0]]?.order || 99) - (groupConfig[b[0]]?.order || 99));
    
    sortedGroups.forEach(([group, perms]) => {
        filtered[group] = perms;
    });
    
    return filtered;
});

// Get current permission level for a group
const getGroupLevel = (group) => {
    const groupPerms = adminPermissions.value[group] || [];
    const permIds = groupPerms.map(p => p.id);
    const selected = permIds.filter(id => form.permissions.includes(id));
    
    if (selected.length === 0) return 'none';
    
    // Find which permissions are selected
    const hasAccess = groupPerms.some(p => p.slug.startsWith('access-') && form.permissions.includes(p.id));
    const hasRead = groupPerms.some(p => p.slug.startsWith('read-') && form.permissions.includes(p.id));
    const hasWrite = groupPerms.some(p => (p.slug.startsWith('write-') || p.slug.startsWith('scan-')) && form.permissions.includes(p.id));
    
    if (hasWrite) return 'write';
    if (hasRead) return 'read';
    if (hasAccess) return 'access';
    return 'none';
};

// Set permission level for a group
const setGroupLevel = (group, level) => {
    if (isProtected) return;
    
    const groupPerms = adminPermissions.value[group] || [];
    const permIds = groupPerms.map(p => p.id);
    
    // Remove all group permissions first
    form.permissions = form.permissions.filter(id => !permIds.includes(id));
    
    if (level === 'none') return;
    
    // Add permissions based on level
    groupPerms.forEach(p => {
        if (level === 'access' && p.slug.startsWith('access-')) {
            form.permissions.push(p.id);
        } else if (level === 'read' && (p.slug.startsWith('access-') || p.slug.startsWith('read-'))) {
            form.permissions.push(p.id);
        } else if (level === 'write') {
            // Full access = all permissions in group
            form.permissions.push(p.id);
        }
    });
};

const toggleGroup = (group) => {
    expandedGroups.value[group] = !expandedGroups.value[group];
};

const submit = () => {
    console.log('Submitting permissions:', form.permissions);
    console.log('Form data:', form.data());
    form.put(`/admin/roles/${props.role.id}`);
};

// Stats
const stats = computed(() => {
    let access = 0, read = 0, write = 0;
    Object.keys(adminPermissions.value).forEach(group => {
        const level = getGroupLevel(group);
        if (level === 'access') access++;
        else if (level === 'read') read++;
        else if (level === 'write') write++;
    });
    return { access, read, write, total: Object.keys(adminPermissions.value).length };
});

// Get available permission levels for a group
// Dashboard only allows 'read' and 'write' (no 'none' or 'access')
const getPermissionLevelsForGroup = (group) => {
    if (group === 'dashboard') {
        return permissionLevels.filter(l => l.key === 'read' || l.key === 'write');
    }
    return permissionLevels;
};

// Ensure Dashboard has at least read permission on mount
const ensureDashboardPermission = () => {
    const dashboardLevel = getGroupLevel('dashboard');
    if (dashboardLevel === 'none' || dashboardLevel === 'access') {
        setGroupLevel('dashboard', 'read');
    }
};

onMounted(() => {
    // Ensure Dashboard always has at least read permission
    ensureDashboardPermission();
    
    gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out' }
        );
    }, pageRef.value);
});
</script>

<template>
    <div ref="pageRef" class="max-w-5xl mx-auto space-y-5">
        <!-- Premium Header -->
        <div :class="['relative overflow-hidden rounded-2xl p-5 shadow-2xl', isProtected ? 'bg-gradient-to-r from-amber-600 via-yellow-600 to-amber-600' : 'bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900']">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-start gap-3">
                <Link href="/admin/roles" class="p-2.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3 flex-1">
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shadow-xl', isProtected ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-white/10 backdrop-blur-sm']">
                        <component :is="isProtected ? Crown : Shield" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-lg font-bold text-white">Edit Role</h1>
                            <span v-if="isProtected" class="px-2 py-0.5 bg-white/20 rounded-full text-[9px] font-bold text-white flex items-center gap-1">
                                <Lock class="w-2.5 h-2.5" /> Protected
                            </span>
                        </div>
                        <p class="text-white/70 text-[11px]">{{ role?.name }} · {{ role?.slug }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Protected Warning -->
        <div v-if="isProtected" class="flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-5 py-4">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center shadow-lg">
                <Lock class="w-5 h-5 text-white" />
            </div>
            <div>
                <p class="text-sm font-bold text-amber-800">Role Dilindungi</p>
                <p class="text-xs text-amber-600">Super Admin memiliki akses penuh dan tidak dapat diubah</p>
            </div>
        </div>

        <!-- Permission Stats -->
        <div v-if="!isProtected" class="grid grid-cols-4 gap-3">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center">
                    <Shield class="w-4 h-4 text-white" />
                </div>
                <div>
                    <p class="text-lg font-black text-slate-700">{{ stats.total }}</p>
                    <p class="text-[10px] text-slate-500">Total Menu</p>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <Eye class="w-4 h-4 text-white" />
                </div>
                <div>
                    <p class="text-lg font-black text-blue-700">{{ stats.access }}</p>
                    <p class="text-[10px] text-blue-600">Tampilkan</p>
                </div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                    <EyeOff class="w-4 h-4 text-white" />
                </div>
                <div>
                    <p class="text-lg font-black text-amber-700">{{ stats.read }}</p>
                    <p class="text-[10px] text-amber-600">Read Only</p>
                </div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                    <Edit3 class="w-4 h-4 text-white" />
                </div>
                <div>
                    <p class="text-lg font-black text-emerald-700">{{ stats.write }}</p>
                    <p class="text-[10px] text-emerald-600">Full Akses</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Basic Info -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-600 to-gray-700 flex items-center justify-center shadow-lg">
                            <Shield class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Role</h3>
                            <p class="text-[10px] text-gray-500">Detail dasar role</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Nama Role <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required :disabled="isProtected"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Slug</label>
                            <input :value="role?.slug" type="text" disabled
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed font-mono">
                            <p class="text-gray-400 text-[9px] mt-1">Slug tidak dapat diubah</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Deskripsi</label>
                        <textarea v-model="form.description" rows="2" :disabled="isProtected"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 resize-none disabled:opacity-50 disabled:cursor-not-allowed"></textarea>
                    </div>
                </div>
            </div>

            <!-- Permissions with 3-Level System -->
            <div v-if="!isProtected" class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-purple-50 via-indigo-50 to-purple-50 border-b border-purple-100/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                                <Key class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Hak Akses Menu ({{ stats.total }} Menu)</h3>
                                <p class="text-[10px] text-gray-500">Atur level izin untuk setiap menu sidebar</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 space-y-2">
                    <div v-for="(perms, group) in adminPermissions" :key="group" 
                        class="border border-gray-200 rounded-xl overflow-hidden hover:border-gray-300 transition-colors">
                        
                        <!-- Group Header with Level Selector -->
                        <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100">
                                    <component :is="groupConfig[group]?.icon || Shield" class="w-4 h-4 text-gray-600" />
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ groupConfig[group]?.label || group }}</p>
                                </div>
                            </div>
                            
                            <!-- Level Selector Buttons -->
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="level in getPermissionLevelsForGroup(group)"
                                    :key="level.key"
                                    type="button"
                                    @click="setGroupLevel(group, level.key)"
                                    :class="[
                                        'px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1',
                                        getGroupLevel(group) === level.key
                                            ? level.color === 'gray' ? 'bg-gray-600 text-white shadow-md' :
                                              level.color === 'blue' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' :
                                              level.color === 'amber' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/30' :
                                              'bg-emerald-600 text-white shadow-md shadow-emerald-500/30'
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                    ]"
                                >
                                    <component :is="level.icon" class="w-3 h-3" />
                                    <span class="hidden sm:inline">{{ level.label }}</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Expandable Detail (tersembunyi awalnya) -->
                        <button type="button" @click="toggleGroup(group)" 
                            class="w-full px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-500 hover:bg-gray-100 transition-colors">
                            <span>{{ expandedGroups[group] ? 'Sembunyikan' : 'Lihat' }} detail permission ({{ perms.length }})</span>
                            <component :is="expandedGroups[group] ? ChevronUp : ChevronDown" class="w-3.5 h-3.5" />
                        </button>
                        
                        <Transition name="expand">
                            <div v-if="expandedGroups[group]" class="px-4 py-3 bg-gray-50/50 border-t border-gray-100">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <div v-for="perm in perms" :key="perm.id" 
                                        :class="['flex items-center gap-2 px-3 py-2 rounded-lg text-[11px]',
                                            form.permissions.includes(perm.id) 
                                                ? 'bg-purple-50 text-purple-700 border border-purple-200' 
                                                : 'bg-white text-gray-500 border border-gray-200']">
                                        <Check v-if="form.permissions.includes(perm.id)" class="w-3.5 h-3.5 text-purple-600" />
                                        <X v-else class="w-3.5 h-3.5 text-gray-400" />
                                        <span>{{ perm.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- Super Admin full access info -->
            <div v-else class="form-card rounded-xl bg-gradient-to-r from-amber-50 to-yellow-50 shadow-lg border border-amber-200 overflow-hidden">
                <div class="px-5 py-6 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-amber-400 to-yellow-500 flex items-center justify-center shadow-xl shadow-amber-500/30 mb-4">
                        <Crown class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-lg font-bold text-amber-800 mb-1">Akses Penuh</h3>
                    <p class="text-xs text-amber-600 max-w-sm mx-auto">
                        Super Admin memiliki akses ke <strong>semua 19 menu</strong> tanpa batasan. 
                        Permissions tidak perlu diatur secara manual.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing || isProtected"
                    class="flex-1 py-3 bg-gradient-to-r from-slate-600 to-gray-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-slate-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Perbarui Role' }}
                </button>
                <Link href="/admin/roles" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </Link>
            </div>
        </form>
    </div>
</template>

<style scoped>
.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { max-height: 500px; }
</style>
