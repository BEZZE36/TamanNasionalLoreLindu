<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import axios from 'axios';
import { 
    Users, ArrowLeft, Save, User, Mail, Lock, Crown, Shield, Power,
    Eye, EyeOff, AtSign, CheckCircle, AlertCircle, Loader2
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ 
    editAdmin: { type: Object, required: true },
    availableRoles: { type: Array, default: () => [] }
});

// Shortcut for template usage
const editAdmin = computed(() => props.editAdmin);

const pageRef = ref(null);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Real-time validation states
const isCheckingEmail = ref(false);
const isCheckingUsername = ref(false);
const emailAvailable = ref(null);
const usernameAvailable = ref(null);

const form = useForm({
    name: props.editAdmin.name || '',
    email: props.editAdmin.email || '',
    username: props.editAdmin.username || '',
    password: '',
    password_confirmation: '',
    role: props.editAdmin.role || 'admin',
    is_active: props.editAdmin.is_active ?? true,
    role_ids: props.editAdmin.role_ids || []
});

const getInitials = (name) => {
    if (!name) return 'A';
    const parts = name.split(' ');
    return parts.length >= 2 ? parts[0].charAt(0) + parts[1].charAt(0) : name.charAt(0);
};

// Check email uniqueness (exclude current admin)
let emailTimeout;
watch(() => form.email, (val) => {
    clearTimeout(emailTimeout);
    emailAvailable.value = null;
    if (!val || !val.includes('@') || val === props.editAdmin.email) {
        emailAvailable.value = val === props.editAdmin.email ? true : null;
        return;
    }
    emailTimeout = setTimeout(async () => {
        isCheckingEmail.value = true;
        try {
            const { data } = await axios.post('/admin/api/check-admin-email', { 
                email: val, 
                exclude_id: props.editAdmin.id 
            });
            emailAvailable.value = data.available;
        } catch (e) {
            emailAvailable.value = null;
        } finally {
            isCheckingEmail.value = false;
        }
    }, 500);
});

// Check username uniqueness (exclude current admin)
let usernameTimeout;
watch(() => form.username, (val) => {
    clearTimeout(usernameTimeout);
    usernameAvailable.value = null;
    if (!val || val.length < 3 || val === props.editAdmin.username) {
        usernameAvailable.value = val === props.editAdmin.username ? true : null;
        return;
    }
    usernameTimeout = setTimeout(async () => {
        isCheckingUsername.value = true;
        try {
            const { data } = await axios.post('/admin/api/check-admin-username', { 
                username: val, 
                exclude_id: props.editAdmin.id 
            });
            usernameAvailable.value = data.available;
        } catch (e) {
            usernameAvailable.value = null;
        } finally {
            isCheckingUsername.value = false;
        }
    }, 500);
});

// Password validation
const passwordStrength = computed(() => {
    const p = form.password;
    if (!p) return { score: 0, items: [] };
    const items = [
        { label: 'Min 8 karakter', valid: p.length >= 8 },
        { label: 'Huruf besar', valid: /[A-Z]/.test(p) },
        { label: 'Huruf kecil', valid: /[a-z]/.test(p) },
        { label: 'Angka', valid: /[0-9]/.test(p) },
    ];
    return { score: items.filter(i => i.valid).length, items };
});

const isPasswordStrong = computed(() => !form.password || passwordStrength.value.score >= 4);
const isPasswordMatch = computed(() => !form.password_confirmation || form.password === form.password_confirmation);

const canSubmit = computed(() => {
    return form.name && form.email && form.username && 
           isPasswordStrong.value && isPasswordMatch.value && 
           emailAvailable.value !== false && usernameAvailable.value !== false;
});

const submit = () => {
    form.put(`/admin/admins/${props.editAdmin.id}`, {
        onSuccess: () => router.visit('/admin/admins')
    });
};

let ctx;
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out' }
        );
    }, pageRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div ref="pageRef" class="max-w-3xl mx-auto space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-start gap-3">
                <Link href="/admin/admins" class="p-2.5 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-bold shadow-lg', editAdmin.role === 'super_admin' ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-white/20 backdrop-blur-sm']">
                            {{ getInitials(editAdmin.name) }}
                        </div>
                        <div v-if="editAdmin.role === 'super_admin'" class="absolute -bottom-1 -right-1 w-5 h-5 rounded bg-amber-400 shadow flex items-center justify-center">
                            <Crown class="w-3 h-3 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-lg font-bold text-white">Edit Admin</h1>
                            <span :class="['px-2 py-0.5 rounded-full text-[9px] font-bold', editAdmin.is_active ? 'bg-emerald-500/20 text-white' : 'bg-red-500/20 text-white']">
                                {{ editAdmin.is_active ? '✓ Aktif' : '✕ Non-Aktif' }}
                            </span>
                        </div>
                        <p class="text-indigo-100/80 text-[11px]">@{{ editAdmin.username }} · {{ editAdmin.email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Account Info Card -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border-b border-indigo-100/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                            <User class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Akun</h3>
                            <p class="text-[10px] text-gray-500">Data identitas administrator</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text" required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all bg-gray-50/50"
                            :class="{ 'border-red-400': form.errors.name }">
                        <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <Mail class="w-3 h-3" /> Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input v-model="form.email" type="email" required
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 transition-all bg-gray-50/50"
                                    :class="[
                                        emailAvailable === true ? 'border-emerald-500' :
                                        emailAvailable === false ? 'border-red-400' :
                                        'border-gray-200 focus:border-indigo-500'
                                    ]">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <Loader2 v-if="isCheckingEmail" class="w-4 h-4 text-gray-400 animate-spin" />
                                    <CheckCircle v-else-if="emailAvailable === true" class="w-4 h-4 text-emerald-500" />
                                    <AlertCircle v-else-if="emailAvailable === false" class="w-4 h-4 text-red-500" />
                                </div>
                            </div>
                            <p v-if="emailAvailable === false" class="text-red-500 text-[10px] mt-1">Email sudah digunakan</p>
                        </div>
                        
                        <!-- Username -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <AtSign class="w-3 h-3" /> Username <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">@</span>
                                <input v-model="form.username" type="text" required
                                    class="w-full pl-7 pr-10 py-2.5 text-xs rounded-xl border-2 transition-all bg-gray-50/50"
                                    :class="[
                                        usernameAvailable === true ? 'border-emerald-500' :
                                        usernameAvailable === false ? 'border-red-400' :
                                        'border-gray-200 focus:border-indigo-500'
                                    ]">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <Loader2 v-if="isCheckingUsername" class="w-4 h-4 text-gray-400 animate-spin" />
                                    <CheckCircle v-else-if="usernameAvailable === true" class="w-4 h-4 text-emerald-500" />
                                    <AlertCircle v-else-if="usernameAvailable === false" class="w-4 h-4 text-red-500" />
                                </div>
                            </div>
                            <p v-if="usernameAvailable === false" class="text-red-500 text-[10px] mt-1">Username sudah digunakan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Card -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-b border-amber-100/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                            <Lock class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Ubah Password</h3>
                            <p class="text-[10px] text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Password Baru</label>
                            <div class="relative">
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" 
                                    placeholder="Kosongkan jika tidak diubah" autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all bg-gray-50/50">
                                <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Konfirmasi Password</label>
                            <div class="relative">
                                <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" 
                                    placeholder="Ulangi password baru"
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 transition-all bg-gray-50/50"
                                    :class="!isPasswordMatch ? 'border-red-400' : 'border-gray-200 focus:border-amber-500'">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <component :is="showConfirmPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="!isPasswordMatch" class="text-red-500 text-[10px] mt-1">Password tidak cocok</p>
                        </div>
                    </div>
                    
                    <!-- Password Strength -->
                    <div v-if="form.password" class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-500 mb-2 uppercase tracking-wider">Kekuatan Password</p>
                        <div class="flex gap-1 mb-2">
                            <div v-for="i in 4" :key="i" :class="['h-1 flex-1 rounded-full transition-all', i <= passwordStrength.score ? 
                                (passwordStrength.score <= 1 ? 'bg-red-400' : passwordStrength.score <= 2 ? 'bg-amber-400' : passwordStrength.score <= 3 ? 'bg-yellow-400' : 'bg-emerald-500') 
                                : 'bg-gray-200']"></div>
                        </div>
                        <ul class="grid grid-cols-2 gap-1.5 text-[9px] text-gray-500">
                            <li v-for="item in passwordStrength.items" :key="item.label" class="flex items-center gap-1">
                                <component :is="item.valid ? CheckCircle : AlertCircle" :class="['w-3 h-3', item.valid ? 'text-emerald-500' : 'text-gray-400']" />
                                {{ item.label }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Role & Status Card -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-600 to-gray-700 flex items-center justify-center shadow-lg">
                            <Shield class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Role & Status</h3>
                            <p class="text-[10px] text-gray-500">Hak akses dan status akun</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Admin Type -->
                    <div>
                        <p class="text-[10px] font-bold text-gray-600 mb-2">Tipe Admin</p>
                        <div class="grid grid-cols-2 gap-3">
                            <label :class="['relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3', form.role === 'admin' ? 'border-blue-500 bg-blue-50/50 shadow-lg shadow-blue-500/10' : 'border-gray-200 hover:border-gray-300']">
                                <input type="radio" v-model="form.role" value="admin" class="sr-only">
                                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center transition-all', form.role === 'admin' ? 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg' : 'bg-gray-200']">
                                    <Shield :class="['w-5 h-5', form.role === 'admin' ? 'text-white' : 'text-gray-400']" />
                                </div>
                                <div>
                                    <p :class="['text-xs font-bold', form.role === 'admin' ? 'text-blue-700' : 'text-gray-700']">Admin</p>
                                    <p class="text-[9px] text-gray-500">Akses berdasarkan role</p>
                                </div>
                            </label>
                            <label :class="['relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3', form.role === 'super_admin' ? 'border-amber-500 bg-amber-50/50 shadow-lg shadow-amber-500/10' : 'border-gray-200 hover:border-gray-300']">
                                <input type="radio" v-model="form.role" value="super_admin" class="sr-only">
                                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center transition-all', form.role === 'super_admin' ? 'bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg' : 'bg-gray-200']">
                                    <Crown :class="['w-5 h-5', form.role === 'super_admin' ? 'text-white' : 'text-gray-400']" />
                                </div>
                                <div>
                                    <p :class="['text-xs font-bold', form.role === 'super_admin' ? 'text-amber-700' : 'text-gray-700']">Super Admin</p>
                                    <p class="text-[9px] text-gray-500">Akses penuh</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Assigned Roles -->
                    <div v-if="availableRoles.length > 0">
                        <p class="text-[10px] font-bold text-gray-600 mb-2">Role & Hak Akses</p>
                        <p class="text-[9px] text-gray-500 mb-3">Pilih satu atau lebih role untuk menentukan hak akses admin</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <label v-for="role in availableRoles" :key="role.id"
                                :class="['relative p-3 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-2',
                                    form.role_ids.includes(role.id) ? 'border-indigo-500 bg-indigo-50/50' : 'border-gray-200 hover:border-gray-300']">
                                <input type="checkbox" :value="role.id" v-model="form.role_ids" class="sr-only">
                                <div :class="['w-5 h-5 rounded flex items-center justify-center transition-all text-xs font-bold',
                                    form.role_ids.includes(role.id) ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-400']">
                                    <span v-if="form.role_ids.includes(role.id)">✓</span>
                                </div>
                                <span :class="['text-xs font-medium', form.role_ids.includes(role.id) ? 'text-indigo-700' : 'text-gray-600']">
                                    {{ role.name }}
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Active Status -->
                    <label :class="['relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3', form.is_active ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 hover:border-gray-300']">
                        <input type="checkbox" v-model="form.is_active" class="sr-only">
                        <div :class="['w-10 h-10 rounded-lg flex items-center justify-center transition-all', form.is_active ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg' : 'bg-gray-200']">
                            <Power :class="['w-5 h-5', form.is_active ? 'text-white' : 'text-gray-400']" />
                        </div>
                        <div>
                            <p :class="['text-xs font-bold', form.is_active ? 'text-emerald-700' : 'text-gray-700']">Akun Aktif</p>
                            <p class="text-[9px] text-gray-500">Admin dapat login ke sistem</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing || !canSubmit"
                    class="flex-1 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Perbarui Admin' }}
                </button>
                <Link href="/admin/admins" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </Link>
            </div>
        </form>
    </div>
</template>
