<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Users, ArrowLeft, Check, X, Save, Crown, Shield, 
    CheckCircle, Mail, Loader2, Sparkles
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    admins: Array,
    roles: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const pageRef = ref(null);
const editingAdmin = ref(null);
const selectedRoles = ref([]);
const isSubmitting = ref(false);

onMounted(() => {
    gsap.context(() => {
        gsap.fromTo('.admin-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out' }
        );
    }, pageRef.value);
});

const startEdit = (admin) => {
    editingAdmin.value = admin.id;
    selectedRoles.value = admin.roles?.map(r => r.id) || [];
};

const cancelEdit = () => {
    editingAdmin.value = null;
    selectedRoles.value = [];
};

const toggleRole = (roleId) => {
    const index = selectedRoles.value.indexOf(roleId);
    if (index > -1) selectedRoles.value.splice(index, 1);
    else selectedRoles.value.push(roleId);
};

const saveRoles = (admin) => {
    isSubmitting.value = true;
    router.post(`/admin/roles-admins/${admin.id}`, {
        roles: selectedRoles.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editingAdmin.value = null;
            selectedRoles.value = [];
        },
        onFinish: () => { isSubmitting.value = false; }
    });
};

const getInitials = (name) => {
    if (!name) return 'A';
    const parts = name.split(' ');
    return parts.length >= 2 ? parts[0].charAt(0) + parts[1].charAt(0) : name.charAt(0);
};
</script>

<template>
    <div ref="pageRef" class="min-h-screen space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/5 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-start gap-3">
                <Link href="/admin/roles" class="p-2.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-xl shadow-purple-500/30">
                            <Users class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-lg font-bold text-white">Assign Role Admin</h1>
                            <Sparkles class="w-4 h-4 text-amber-400" />
                        </div>
                        <p class="text-slate-300/80 text-[11px]">Tetapkan role untuk setiap administrator</p>
                    </div>
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

        <!-- Admins List -->
        <div class="rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-slate-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Daftar Administrator</h3>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-full">
                        {{ admins?.length || 0 }} Admin
                    </span>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                <div v-for="admin in admins" :key="admin.id" 
                    class="admin-card p-4 hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <!-- Avatar -->
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-lg flex-shrink-0', admin.role === 'super_admin' ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-gradient-to-br from-indigo-500 to-purple-500']">
                            {{ getInitials(admin.name) }}
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ admin.name }}</h4>
                                <span v-if="admin.role === 'super_admin'" class="px-2 py-0.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white text-[9px] font-bold rounded-full flex items-center gap-0.5">
                                    <Crown class="w-2.5 h-2.5" /> Master
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-gray-500">
                                <Mail class="w-3 h-3" />
                                <span class="truncate">{{ admin.email }}</span>
                            </div>
                        </div>
                        
                        <!-- Roles -->
                        <div class="flex-1">
                            <template v-if="editingAdmin === admin.id">
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="role in roles" :key="role.id" 
                                        @click="toggleRole(role.id)" type="button"
                                        :class="['px-3 py-1.5 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1', 
                                            selectedRoles.includes(role.id) 
                                                ? (role.slug === 'super-admin' ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-md' : 'bg-gradient-to-r from-slate-600 to-gray-700 text-white shadow-md') 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                                        <component :is="role.slug === 'super-admin' ? Crown : Shield" class="w-3 h-3" />
                                        {{ role.name }}
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="role in admin.roles" :key="role.id"
                                        :class="['px-2.5 py-1 rounded-full text-[10px] font-bold flex items-center gap-1', 
                                            role.slug === 'super-admin' 
                                                ? 'bg-amber-100 text-amber-700' 
                                                : 'bg-slate-100 text-slate-700']">
                                        <component :is="role.slug === 'super-admin' ? Crown : Shield" class="w-2.5 h-2.5" />
                                        {{ role.name }}
                                    </span>
                                    <span v-if="!admin.roles?.length" class="text-gray-400 text-[10px]">
                                        Tidak ada role
                                    </span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-1.5 flex-shrink-0">
                            <template v-if="editingAdmin === admin.id">
                                <button @click="saveRoles(admin)" :disabled="isSubmitting"
                                    class="p-2.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:shadow-lg transition-all disabled:opacity-50">
                                    <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                                    <Check v-else class="w-4 h-4" />
                                </button>
                                <button @click="cancelEdit" 
                                    class="p-2.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">
                                    <X class="w-4 h-4" />
                                </button>
                            </template>
                            <template v-else>
                                <button @click="startEdit(admin)" 
                                    class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-bold hover:bg-slate-200 transition-colors">
                                    Ubah Role
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="!admins?.length" class="py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-4">
                        <Users class="w-8 h-8 text-gray-400" />
                    </div>
                    <p class="text-gray-600 font-medium text-sm">Belum ada admin</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-16px); }
</style>
