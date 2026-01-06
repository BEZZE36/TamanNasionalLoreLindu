<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Shield, Plus, Edit, Trash2, Users, Key, Crown, 
    CheckCircle, AlertTriangle, Loader2, Sparkles, Lock, Settings
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    roles: { type: Array, default: () => [] },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const localRoles = ref(JSON.parse(JSON.stringify(props.roles)));
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const isDeleting = ref(false);
let ctx;

watch(() => props.roles, (newVal) => {
    localRoles.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.stat-card', 
            { opacity: 0, y: 20, scale: 0.95 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.7)' }
        );
        gsap.fromTo('.role-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, delay: 0.2, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

const stats = computed(() => ({
    total: localRoles.value.length,
    protected: localRoles.value.filter(r => r.slug === 'super-admin').length,
    custom: localRoles.value.filter(r => r.slug !== 'super-admin').length,
}));

const openDelete = (role) => { 
    deleteTarget.value = role; 
    showDeleteModal.value = true; 
};

const closeDelete = () => { 
    deleteTarget.value = null; 
    showDeleteModal.value = false; 
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    isDeleting.value = true;
    
    router.delete(`/admin/roles/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDelete(),
        onFinish: () => { isDeleting.value = false; }
    });
};
</script>

<template>
    <div class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute top-10 right-40 w-32 h-32 bg-slate-400/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="absolute top-6 left-20 w-2 h-2 bg-white/20 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="absolute top-12 right-32 w-1.5 h-1.5 bg-slate-300/30 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            
            <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/20 rounded-xl blur-lg"></div>
                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 backdrop-blur-xl ring-2 ring-white/20 shadow-xl">
                            <Shield class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-white tracking-tight drop-shadow-lg">Role & Akses</h1>
                            <span class="px-2 py-0.5 bg-white/10 rounded-full text-[10px] font-bold text-white/80 backdrop-blur-sm">
                                {{ stats.total }} Role
                            </span>
                        </div>
                        <p class="text-slate-300/80 text-xs">Kelola role dan hak akses administrator</p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <Link 
                        href="/admin/roles-admins"
                        class="group inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white text-xs font-bold hover:bg-white/20 transition-all"
                    >
                        <Users class="w-4 h-4" />
                        <span>Assign Role</span>
                    </Link>
                    <Link 
                        href="/admin/roles/create"
                        class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 shadow-lg"
                    >
                        <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                        <span>Buat Role</span>
                        <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <p class="text-xs font-semibold text-emerald-800">{{ flash.success }}</p>
            </div>
        </Transition>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-3">
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-slate-100 to-gray-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-600 to-gray-700 shadow-lg shadow-slate-500/30 group-hover:scale-110 transition-transform">
                        <Shield class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.total }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Total Role</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <Lock class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.protected }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Protected</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card group relative overflow-hidden rounded-xl bg-white p-4 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                        <Settings class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ stats.custom }}</p>
                        <p class="text-[10px] text-gray-500 font-medium">Custom Role</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="role in localRoles" :key="role.id"
                class="role-card group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div :class="['p-5', role.slug === 'super-admin' ? 'bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50' : 'bg-gradient-to-r from-slate-50 via-gray-50 to-slate-50']">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shadow-lg', role.slug === 'super-admin' ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-gradient-to-br from-slate-500 to-gray-600']">
                                <component :is="role.slug === 'super-admin' ? Crown : Shield" class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">{{ role.name }}</h3>
                                <p class="text-[10px] text-gray-500 font-mono">{{ role.slug }}</p>
                            </div>
                        </div>
                        <span v-if="role.slug === 'super-admin'" class="px-2 py-1 bg-amber-100 text-amber-700 text-[9px] font-bold rounded-full">⭐ Master</span>
                    </div>
                </div>
                <div class="p-5">
                    <p v-if="role.description" class="text-[11px] text-gray-600 mb-4 line-clamp-2">{{ role.description }}</p>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
                            <Users class="w-3.5 h-3.5" />
                            <span class="font-medium">{{ role.member_count || 0 }} {{ role.member_label }}</span>
                        </div>
                        <div v-if="role.slug === 'super-admin'" class="flex items-center gap-1.5 text-[10px] text-amber-600">
                            <span class="text-amber-500">✨</span>
                            <span class="font-bold">Akses Penuh</span>
                        </div>
                        <div v-else class="flex items-center gap-1.5 text-[10px] text-gray-500">
                            <Key class="w-3.5 h-3.5" />
                            <span class="font-medium">{{ role.permissions_count || 0 }} Izin</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="`/admin/roles/${role.id}/edit`" 
                            class="flex-1 py-2.5 text-center rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors flex items-center justify-center gap-1.5">
                            <Edit class="w-3.5 h-3.5" /> Edit
                        </Link>
                        <button v-if="role.slug !== 'super-admin'" @click="openDelete(role)" 
                            class="px-4 py-2.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="!localRoles.length" class="col-span-full bg-white rounded-2xl shadow-lg border border-gray-100 py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-100 to-gray-200 flex items-center justify-center mx-auto mb-4">
                    <Shield class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-600 font-medium mb-4 text-sm">Belum ada role</p>
                <Link href="/admin/roles/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-slate-600 to-gray-700 text-white text-xs font-bold rounded-xl shadow-lg">
                    <Plus class="w-4 h-4" /> Buat Role Pertama
                </Link>
            </div>
        </div>

        <!-- Delete Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDelete"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-red-100 to-rose-100">
                                <AlertTriangle class="w-6 h-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Hapus Role</h3>
                                <p class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Apakah Anda yakin ingin menghapus role <strong class="text-gray-900">"{{ deleteTarget?.name }}"</strong>?
                        </p>
                        
                        <div class="flex gap-3 justify-end">
                            <button @click="closeDelete" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button @click="confirmDelete" :disabled="isDeleting"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold hover:shadow-lg transition-all disabled:opacity-50 flex items-center gap-2">
                                <Loader2 v-if="isDeleting" class="w-4 h-4 animate-spin" />
                                <Trash2 v-else class="w-4 h-4" />
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
.slide-enter-active, .slide-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-16px); }
.modal-enter-active, .modal-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
