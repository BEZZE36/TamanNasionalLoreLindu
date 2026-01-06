<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Users, Plus, Search, Eye, Edit, Trash2, ChevronLeft, ChevronRight, 
    UserCheck, UserX, Shield, ShieldOff, Mail, Phone, Calendar, 
    Download, Filter, MoreVertical, CheckCircle, XCircle, TrendingUp,
    UserPlus, AlertTriangle, Sparkles, Globe, Star, Zap
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: Object,
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) }
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const tableRef = ref(null);
const pageRef = ref(null);

let searchTimeout;
watch(search, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => applyFilters(), 300); });

const applyFilters = () => {
    router.get('/admin/users', {
        search: search.value || undefined,
        status: status.value || undefined
    }, { preserveState: true });
};

const confirmDelete = (item) => { deleteTarget.value = item; showDeleteModal.value = true; };
const deleteUser = () => {
    if (deleteTarget.value) {
        router.delete(`/admin/users/${deleteTarget.value.id}`, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => { showDeleteModal.value = false; deleteTarget.value = null; }
        });
    }
};

const toggleBlock = (user) => {
    const action = user.status === 'active' ? 'block' : 'unblock';
    router.post(`/admin/users/${user.id}/${action}`, {}, {
        preserveScroll: true,
        preserveState: false,
    });
};

const getStatusClasses = (s) => ({
    'active': 'from-emerald-500 to-teal-600 text-white',
    'blocked': 'from-red-500 to-rose-600 text-white'
}[s] || 'from-gray-500 to-gray-600 text-white');

const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

const formatPhone = (phone) => {
    if (!phone) return '-';
    let digits = phone.replace(/\D/g, '');
    if (digits.startsWith('0')) digits = '62' + digits.slice(1);
    if (!digits.startsWith('62')) digits = '62' + digits;
    
    const countryCode = '+62';
    const rest = digits.slice(2);
    if (rest.length === 0) return countryCode;
    
    let formatted = countryCode;
    if (rest.length > 0) formatted += ' ' + rest.slice(0, 3);
    if (rest.length > 3) formatted += ' ' + rest.slice(3, 7);
    if (rest.length > 7) formatted += ' ' + rest.slice(7, 11);
    return formatted;
};

// Stats
const totalUsers = computed(() => props.stats?.total || props.users?.total || 0);
const activeUsers = computed(() => props.stats?.active || 0);
const blockedUsers = computed(() => props.stats?.blocked || 0);
const newThisMonth = computed(() => props.stats?.newThisMonth || 0);

let ctx;
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.user-row', 
            { opacity: 0, x: -20 }, 
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.05, delay: 0.3, ease: 'power2.out' }
        );
    }, pageRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <div ref="pageRef" class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-yellow-400/20 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/30 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-yellow-300/50 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/30 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-xl ring-2 ring-white/30 shadow-xl">
                            <Users class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Manajemen User</h1>
                            <span class="px-2 py-0.5 bg-white/20 rounded-full text-[10px] font-bold text-white/90 backdrop-blur-sm">
                                {{ totalUsers }} Total
                            </span>
                        </div>
                        <p class="text-orange-100/80 text-xs">Kelola semua akun pengguna terdaftar</p>
                    </div>
                </div>
                
                <Link href="/admin/users/create"
                    class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-orange-600 text-xs font-bold hover:bg-orange-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg">
                    <UserPlus class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    <span>Tambah User</span>
                    <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                </Link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <Users class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ totalUsers }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total User</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <UserCheck class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ activeUsers }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-100 to-rose-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                        <ShieldOff class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ blockedUsers }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Diblokir</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform">
                        <TrendingUp class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">+{{ newThisMonth }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Baru Bulan Ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-md group">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
                    <input v-model="search" type="text" placeholder="Cari nama, email, username, telepon..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 focus:bg-white transition-all duration-300">
                </div>
                <select v-model="status" @change="applyFilters"
                    class="px-4 py-2.5 text-xs rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 focus:bg-white transition-all cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="blocked">Diblokir</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div ref="tableRef" class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 via-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-600 uppercase tracking-wider">Kontak</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Bookings</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Bergabung</th>
                            <th class="px-4 py-3.5 text-center text-[10px] font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in users.data" :key="user.id" 
                            class="user-row group hover:bg-gradient-to-r hover:from-orange-50/50 hover:to-amber-50/30 transition-all duration-200">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center text-white text-xs font-bold shadow-md overflow-hidden">
                                            <img v-if="user.avatar_url && !user.avatar_url.includes('ui-avatars')" 
                                                :src="user.avatar_url" 
                                                :alt="user.name"
                                                class="w-full h-full object-cover">
                                            <span v-else>{{ user.name?.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div :class="['absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white', user.status === 'active' ? 'bg-emerald-500' : 'bg-red-500']"></div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <p class="text-xs font-bold text-gray-900 group-hover:text-orange-600 transition-colors">{{ user.name }}</p>
                                            <div v-if="user.google_id" class="flex items-center gap-0.5 px-1 py-0.5 bg-blue-50 rounded border border-blue-200/50" title="Akun Google">
                                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-500 font-medium">@{{ user.username || 'no-username' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-0.5">
                                    <p class="text-[11px] text-gray-700 flex items-center gap-1">
                                        <Mail class="w-3 h-3 text-gray-400" />
                                        {{ user.email }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                        <Phone class="w-3 h-3 text-gray-400" />
                                        {{ formatPhone(user.phone) }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-gradient-to-r shadow-sm', getStatusClasses(user.status)]">
                                    <component :is="user.status === 'active' ? CheckCircle : XCircle" class="w-3 h-3" />
                                    {{ user.status === 'active' ? 'Aktif' : 'Diblokir' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                                    {{ user.bookings_count || 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1 text-[10px] text-gray-500">
                                    <Calendar class="w-3 h-3" />
                                    {{ formatDate(user.created_at) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="`/admin/users/${user.id}`" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 hover:shadow-sm transition-all"
                                        title="Lihat Detail">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/users/${user.id}/edit`" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-amber-600 hover:bg-amber-50 hover:shadow-sm transition-all"
                                        title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="toggleBlock(user)"
                                        :class="['p-2 rounded-lg transition-all', user.status === 'active' ? 'text-gray-400 hover:text-red-600 hover:bg-red-50' : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50']"
                                        :title="user.status === 'active' ? 'Blokir' : 'Aktifkan'">
                                        <component :is="user.status === 'active' ? ShieldOff : Shield" class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(user)" 
                                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 hover:shadow-sm transition-all"
                                        title="Hapus">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <tr v-if="!users.data?.length">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-4">
                                        <Users class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-600 mb-1">Tidak ada user ditemukan</p>
                                    <p class="text-xs text-gray-400">Coba ubah filter pencarian Anda</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <strong>{{ users.from }}-{{ users.to }}</strong> dari <strong>{{ users.total }}</strong> user
                </p>
                <div class="flex gap-1">
                    <Link v-if="users.prev_page_url" :href="users.prev_page_url" 
                        class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 transition-all">
                        <ChevronLeft class="w-4 h-4" />
                    </Link>
                    <span class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-gradient-to-r from-orange-500 to-amber-600 text-white shadow-md">
                        {{ users.current_page }} / {{ users.last_page }}
                    </span>
                    <Link v-if="users.next_page_url" :href="users.next_page_url" 
                        class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 transition-all">
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus User</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Yakin ingin menghapus user <strong class="text-orange-600">{{ deleteTarget?.name }}</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button @click="showDeleteModal = false" 
                                class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button @click="deleteUser" 
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all flex items-center gap-2">
                                <Trash2 class="w-4 h-4" />
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.95) translateY(10px); }
</style>
