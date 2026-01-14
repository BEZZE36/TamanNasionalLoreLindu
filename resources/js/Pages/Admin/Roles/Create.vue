<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
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
    permissions: Object
});

const pageRef = ref(null);

// Group config sesuai urutan sidebar (20 menu)
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
    'testimonial': { label: 'Testimonial', icon: Users, color: 'rose', order: 10 },
    'site-info': { label: 'Edit Info', icon: Settings, color: 'cyan', order: 11 },
    // MANAJEMEN
    'bookings': { label: 'Pemesanan', icon: ClipboardList, color: 'sky', order: 12 },
    'coupons': { label: 'Kupon', icon: Ticket, color: 'fuchsia', order: 13 },
    'users': { label: 'Pengguna', icon: Users, color: 'violet', order: 14 },
    'tickets': { label: 'Scan Tiket', icon: QrCode, color: 'purple', order: 15 },
    // LAPORAN
    'activity-logs': { label: 'Activity Log', icon: Clock, color: 'gray', order: 16 },
    'analytics': { label: 'Analytics', icon: BarChart3, color: 'green', order: 17 },
    // PENGATURAN
    'admins': { label: 'Kelola Admin', icon: UserCircle, color: 'slate', order: 18 },
    'roles': { label: 'Role & Akses', icon: Shield, color: 'rose', order: 19 },
    'database': { label: 'Database', icon: Database, color: 'blue', order: 20 },
};

// Permission levels
const permissionLevels = [
    { key: 'none', label: 'Tidak Ada', icon: X, color: 'gray', desc: 'Menu tidak terlihat' },
    { key: 'access', label: 'Tampilkan', icon: Eye, color: 'blue', desc: 'Menu terlihat di sidebar' },
    { key: 'read', label: 'Read Only', icon: EyeOff, color: 'amber', desc: 'Bisa lihat, tidak bisa edit' },
    { key: 'write', label: 'Full Akses', icon: Edit3, color: 'emerald', desc: 'Akses penuh CRUD' },
];

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: []
});

const generateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
};

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

const submit = () => {
    console.log('Submitting permissions:', form.permissions);
    form.post('/admin/roles');
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
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-start gap-3">
                <Link href="/admin/roles" class="p-2.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-xl shadow-purple-500/30">
                        <Shield class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white mb-0.5">Buat Role Baru</h1>
                        <p class="text-slate-300/80 text-[11px]">Tentukan akses dan izin untuk role admin baru</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="relative mt-4 grid grid-cols-4 gap-3">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                    <div class="text-xl font-bold text-white">{{ stats.total }}</div>
                    <div class="text-[10px] text-slate-300">Total Menu</div>
                </div>
                <div class="bg-blue-500/20 backdrop-blur-sm rounded-xl p-3 text-center border border-blue-400/30">
                    <div class="text-xl font-bold text-blue-300">{{ stats.access }}</div>
                    <div class="text-[10px] text-blue-200">Tampilkan</div>
                </div>
                <div class="bg-amber-500/20 backdrop-blur-sm rounded-xl p-3 text-center border border-amber-400/30">
                    <div class="text-xl font-bold text-amber-300">{{ stats.read }}</div>
                    <div class="text-[10px] text-amber-200">Read Only</div>
                </div>
                <div class="bg-emerald-500/20 backdrop-blur-sm rounded-xl p-3 text-center border border-emerald-400/30">
                    <div class="text-xl font-bold text-emerald-300">{{ stats.write }}</div>
                    <div class="text-[10px] text-emerald-200">Full Akses</div>
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
                            <input v-model="form.name" @blur="generateSlug" type="text" required placeholder="Contoh: Editor"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50">
                            <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Slug <span class="text-red-500">*</span></label>
                            <input v-model="form.slug" type="text" required placeholder="editor"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 font-mono">
                            <p v-if="form.errors.slug" class="text-red-500 text-[10px] mt-1">{{ form.errors.slug }}</p>
                            <p v-else class="text-gray-400 text-[9px] mt-1">Auto-generate dari nama role</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Deskripsi</label>
                        <textarea v-model="form.description" rows="2" placeholder="Deskripsi singkat tentang role ini..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Menu Access -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-b border-emerald-100/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                <Key class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Hak Akses Menu ({{ stats.total }} Menu)</h3>
                                <p class="text-[10px] text-gray-500">Atur level akses untuk setiap menu sidebar</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100">
                    <div v-for="(perms, group) in adminPermissions" :key="group" 
                        class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition-colors">
                        <!-- Menu Info -->
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center',
                                `bg-${groupConfig[group]?.color || 'gray'}-100`
                            ]">
                                <component :is="groupConfig[group]?.icon || Shield" 
                                    :class="['w-4 h-4', `text-${groupConfig[group]?.color || 'gray'}-600`]" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-900">{{ groupConfig[group]?.label || group }}</h4>
                                <p class="text-[9px] text-gray-500">{{ perms.length }} permission tersedia</p>
                            </div>
                        </div>
                        
                        <!-- Permission Level Buttons -->
                        <div class="flex items-center gap-1.5">
                            <button v-for="level in getPermissionLevelsForGroup(group)" :key="level.key"
                                type="button"
                                @click="setGroupLevel(group, level.key)"
                                :class="[
                                    'px-2.5 py-1.5 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1',
                                    getGroupLevel(group) === level.key
                                        ? level.key === 'none' ? 'bg-gray-200 text-gray-700 ring-2 ring-gray-400'
                                        : level.key === 'access' ? 'bg-blue-500 text-white ring-2 ring-blue-300'
                                        : level.key === 'read' ? 'bg-amber-500 text-white ring-2 ring-amber-300'
                                        : 'bg-emerald-500 text-white ring-2 ring-emerald-300'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                ]"
                                :title="level.desc">
                                <component :is="level.icon" class="w-3 h-3" />
                                {{ level.label }}
                            </button>
                        </div>
                    </div>
                    
                    <p v-if="!Object.keys(adminPermissions).length" class="text-center text-gray-500 py-8 text-xs">
                        Tidak ada permission tersedia
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing"
                    class="flex-1 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Role' }}
                </button>
                <Link href="/admin/roles" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" />
                    Kembali
                </Link>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Dynamic color classes for Tailwind */
.bg-blue-100 { background-color: rgb(219 234 254); }
.bg-emerald-100 { background-color: rgb(209 250 229); }
.bg-lime-100 { background-color: rgb(236 252 203); }
.bg-amber-100 { background-color: rgb(254 243 199); }
.bg-pink-100 { background-color: rgb(252 231 243); }
.bg-orange-100 { background-color: rgb(255 237 213); }
.bg-indigo-100 { background-color: rgb(224 231 255); }
.bg-red-100 { background-color: rgb(254 226 226); }
.bg-teal-100 { background-color: rgb(204 251 241); }
.bg-rose-100 { background-color: rgb(255 228 230); }
.bg-cyan-100 { background-color: rgb(207 250 254); }
.bg-sky-100 { background-color: rgb(224 242 254); }
.bg-fuchsia-100 { background-color: rgb(250 232 255); }
.bg-violet-100 { background-color: rgb(237 233 254); }
.bg-purple-100 { background-color: rgb(243 232 255); }
.bg-gray-100 { background-color: rgb(243 244 246); }
.bg-green-100 { background-color: rgb(220 252 231); }
.bg-slate-100 { background-color: rgb(241 245 249); }

.text-blue-600 { color: rgb(37 99 235); }
.text-emerald-600 { color: rgb(5 150 105); }
.text-lime-600 { color: rgb(101 163 13); }
.text-amber-600 { color: rgb(217 119 6); }
.text-pink-600 { color: rgb(219 39 119); }
.text-orange-600 { color: rgb(234 88 12); }
.text-indigo-600 { color: rgb(79 70 229); }
.text-red-600 { color: rgb(220 38 38); }
.text-teal-600 { color: rgb(13 148 136); }
.text-rose-600 { color: rgb(225 29 72); }
.text-cyan-600 { color: rgb(8 145 178); }
.text-sky-600 { color: rgb(2 132 199); }
.text-fuchsia-600 { color: rgb(192 38 211); }
.text-violet-600 { color: rgb(124 58 237); }
.text-purple-600 { color: rgb(147 51 234); }
.text-gray-600 { color: rgb(75 85 99); }
.text-green-600 { color: rgb(22 163 74); }
.text-slate-600 { color: rgb(71 85 105); }
</style>
