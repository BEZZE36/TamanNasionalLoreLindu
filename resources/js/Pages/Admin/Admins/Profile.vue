<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    User, Mail, Lock, Crown, Shield, Save, Eye, EyeOff, AtSign,
    CheckCircle, AlertCircle, Loader2, Settings
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({ admin: { type: Object, required: true } });

const page = usePage();
const flash = computed(() => page.props.flash || {});
const pageRef = ref(null);
const activeTab = ref('profile');
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const profileForm = useForm({
    name: props.admin.name || '',
    email: props.admin.email || '',
    username: props.admin.username || ''
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: ''
});

const getInitials = (name) => {
    if (!name) return 'A';
    const parts = name.split(' ');
    return parts.length >= 2 ? parts[0].charAt(0) + parts[1].charAt(0) : name.charAt(0);
};

// Password validation
const passwordStrength = computed(() => {
    const p = passwordForm.password;
    if (!p) return { score: 0, items: [] };
    const items = [
        { label: 'Min 8 karakter', valid: p.length >= 8 },
        { label: 'Huruf besar', valid: /[A-Z]/.test(p) },
        { label: 'Huruf kecil', valid: /[a-z]/.test(p) },
        { label: 'Angka', valid: /[0-9]/.test(p) },
    ];
    return { score: items.filter(i => i.valid).length, items };
});

const isPasswordStrong = computed(() => passwordStrength.value.score >= 4);
const isPasswordMatch = computed(() => !passwordForm.password_confirmation || passwordForm.password === passwordForm.password_confirmation);

const submitProfile = () => {
    profileForm.put('/admin/profile', { preserveScroll: true });
};

const submitPassword = () => {
    passwordForm.put('/admin/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        }
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
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 p-6 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-center gap-4">
                <div class="relative">
                    <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-xl', admin.role === 'super_admin' ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-white/20 backdrop-blur-sm']">
                        {{ getInitials(admin.name) }}
                    </div>
                    <div v-if="admin.role === 'super_admin'" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-amber-400 shadow flex items-center justify-center">
                        <Crown class="w-3.5 h-3.5 text-white" />
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-white">Profil Saya</h1>
                        <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold', admin.role === 'super_admin' ? 'bg-amber-500/30 text-white' : 'bg-blue-500/30 text-white']">
                            <component :is="admin.role === 'super_admin' ? Crown : Shield" class="w-3 h-3 inline mr-1" />
                            {{ admin.role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                        </span>
                    </div>
                    <p class="text-indigo-100/80 text-xs">@{{ admin.username }} · {{ admin.email }}</p>
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

        <!-- Tabs -->
        <div class="flex gap-2 bg-gray-100 p-1 rounded-xl">
            <button @click="activeTab = 'profile'" 
                :class="['flex-1 py-2.5 px-4 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2', activeTab === 'profile' ? 'bg-white text-indigo-600 shadow-lg' : 'text-gray-600 hover:bg-gray-200']">
                <User class="w-4 h-4" /> Informasi Profil
            </button>
            <button @click="activeTab = 'password'" 
                :class="['flex-1 py-2.5 px-4 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2', activeTab === 'password' ? 'bg-white text-indigo-600 shadow-lg' : 'text-gray-600 hover:bg-gray-200']">
                <Lock class="w-4 h-4" /> Keamanan
            </button>
        </div>

        <!-- Profile Form -->
        <form v-if="activeTab === 'profile'" @submit.prevent="submitProfile" class="space-y-4">
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border-b border-indigo-100/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                            <User class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Profil</h3>
                            <p class="text-[10px] text-gray-500">Perbarui data profil Anda</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input v-model="profileForm.name" type="text" required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all bg-gray-50/50">
                        <p v-if="profileForm.errors.name" class="text-red-500 text-[10px] mt-1">{{ profileForm.errors.name }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <Mail class="w-3 h-3" /> Email <span class="text-red-500">*</span>
                            </label>
                            <input v-model="profileForm.email" type="email" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all bg-gray-50/50">
                            <p v-if="profileForm.errors.email" class="text-red-500 text-[10px] mt-1">{{ profileForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <AtSign class="w-3 h-3" /> Username <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">@</span>
                                <input v-model="profileForm.username" type="text" required
                                    class="w-full pl-7 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all bg-gray-50/50">
                            </div>
                            <p v-if="profileForm.errors.username" class="text-red-500 text-[10px] mt-1">{{ profileForm.errors.username }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" :disabled="profileForm.processing"
                class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                <Loader2 v-if="profileForm.processing" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
            </button>
        </form>

        <!-- Password Form -->
        <form v-if="activeTab === 'password'" @submit.prevent="submitPassword" class="space-y-4">
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-b border-amber-100/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                            <Lock class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Ubah Password</h3>
                            <p class="text-[10px] text-gray-500">Pastikan password Anda aman</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Password Saat Ini <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input v-model="passwordForm.current_password" :type="showCurrentPassword ? 'text' : 'password'" required
                                class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all bg-gray-50/50"
                                :class="{ 'border-red-400': passwordForm.errors.current_password }">
                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <component :is="showCurrentPassword ? EyeOff : Eye" class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="text-red-500 text-[10px] mt-1">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input v-model="passwordForm.password" :type="showNewPassword ? 'text' : 'password'" required
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all bg-gray-50/50">
                                <button type="button" @click="showNewPassword = !showNewPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <component :is="showNewPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" class="text-red-500 text-[10px] mt-1">{{ passwordForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input v-model="passwordForm.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required
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
                    
                    <div v-if="passwordForm.password" class="p-3 bg-gray-50 rounded-xl border border-gray-100">
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
            
            <button type="submit" :disabled="passwordForm.processing || !isPasswordStrong || !isPasswordMatch"
                class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                <Loader2 v-if="passwordForm.processing" class="w-4 h-4 animate-spin" />
                <Lock v-else class="w-4 h-4" />
                {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
            </button>
        </form>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}
</style>
