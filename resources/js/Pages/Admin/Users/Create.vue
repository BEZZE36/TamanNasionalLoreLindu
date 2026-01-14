<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { 
    Users, ArrowLeft, Save, User, Mail, Phone, Lock, Shield, ShieldOff,
    Eye, EyeOff, AtSign, CheckCircle, AlertCircle, Sparkles, UserPlus
} from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const pageRef = ref(null);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Phone formatting
const formatPhoneNumber = (phone) => {
    if (!phone) return '';
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

const parsePhoneToDigits = (formatted) => formatted.replace(/\D/g, '');
const phoneDisplay = ref('');

const form = useForm({
    name: '', username: '', email: '', phone: '', 
    password: '', password_confirmation: '', status: 'active'
});

watch(phoneDisplay, (newVal) => { form.phone = parsePhoneToDigits(newVal); });
const handlePhoneInput = (e) => { phoneDisplay.value = formatPhoneNumber(e.target.value); };

const submit = () => form.post('/admin/users', { onSuccess: () => router.visit('/admin/users') });

const isPasswordConfirmationMismatch = computed(() => {
    return form.password && form.password_confirmation && form.password !== form.password_confirmation;
});

const isPasswordStrong = computed(() => {
    const p = form.password;
    if (!p) return false;
    return p.length >= 8 && /[A-Z]/.test(p) && /[a-z]/.test(p) && /[0-9]/.test(p) && /[@#?!$%^&*_\-+=]/.test(p);
});

const isPasswordValid = computed(() => {
    return form.password && form.password_confirmation && 
           isPasswordStrong.value && form.password === form.password_confirmation;
});

const canSubmitForm = computed(() => form.name && form.username && form.email && isPasswordValid.value);

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
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 via-amber-600 to-yellow-600 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-center gap-3">
                <Link href="/admin/users" class="p-2.5 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                        <UserPlus class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Tambah User Baru</h1>
                        <p class="text-orange-100/80 text-[11px]">Buat akun pengguna baru</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- User Info Card -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border-b border-orange-100/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center shadow-lg shadow-orange-500/25">
                            <User class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Akun</h3>
                            <p class="text-[10px] text-gray-500">Detail profil pengguna</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                            <User class="w-3 h-3" /> Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text" required placeholder="Masukkan nama lengkap"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all bg-gray-50/50"
                            :class="{ 'border-red-400': form.errors.name }">
                        <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                            <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <AtSign class="w-3 h-3" /> Username <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">@</span>
                                <input v-model="form.username" type="text" required placeholder="username"
                                    class="w-full pl-7 pr-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all bg-gray-50/50"
                                    :class="{ 'border-red-400': form.errors.username }">
                            </div>
                            <p v-if="form.errors.username" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.username }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                                <Mail class="w-3 h-3" /> Email <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.email" type="email" required placeholder="email@example.com"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all bg-gray-50/50"
                                :class="{ 'border-red-400': form.errors.email }">
                            <p v-if="form.errors.email" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.email }}
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5 flex items-center gap-1">
                            <Phone class="w-3 h-3" /> Telepon
                        </label>
                        <input v-model="phoneDisplay" @input="handlePhoneInput" type="text" placeholder="+62 877 5169 0646"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all bg-gray-50/50"
                            :class="{ 'border-red-400': form.errors.phone }">
                        <p v-if="form.errors.phone" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                            <AlertCircle class="w-3 h-3" /> {{ form.errors.phone }}
                        </p>
                        <p v-else class="text-gray-400 text-[9px] mt-1">Format: +62 XXX XXXX XXXX</p>
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
                            <h3 class="text-sm font-bold text-gray-900">Password</h3>
                            <p class="text-[10px] text-gray-500">Buat password yang kuat</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all bg-gray-50/50"
                                    :class="{ 'border-red-400': form.errors.password }">
                                <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> {{ form.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required
                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-xl border-2 border-gray-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all bg-gray-50/50"
                                    :class="{ 'border-red-400': isPasswordConfirmationMismatch }">
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <component :is="showConfirmPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="isPasswordConfirmationMismatch" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" /> Konfirmasi password tidak cocok
                            </p>
                        </div>
                    </div>
                    
                    <!-- Password Strength -->
                    <div v-if="form.password" class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-500 mb-2 uppercase tracking-wider">Kekuatan Password</p>
                        <div class="flex gap-1 mb-2">
                            <div :class="['h-1 flex-1 rounded-full transition-all', form.password.length >= 1 ? 'bg-red-400' : 'bg-gray-200']"></div>
                            <div :class="['h-1 flex-1 rounded-full transition-all', form.password.length >= 4 ? 'bg-amber-400' : 'bg-gray-200']"></div>
                            <div :class="['h-1 flex-1 rounded-full transition-all', form.password.length >= 8 ? 'bg-emerald-400' : 'bg-gray-200']"></div>
                            <div :class="['h-1 flex-1 rounded-full transition-all', isPasswordStrong ? 'bg-emerald-500' : 'bg-gray-200']"></div>
                        </div>
                        <ul class="grid grid-cols-2 gap-1.5 text-[9px] text-gray-500">
                            <li class="flex items-center gap-1">
                                <component :is="form.password.length >= 8 ? CheckCircle : AlertCircle" :class="['w-3 h-3', form.password.length >= 8 ? 'text-emerald-500' : 'text-gray-400']" />
                                Min 8 karakter
                            </li>
                            <li class="flex items-center gap-1">
                                <component :is="/[A-Z]/.test(form.password) ? CheckCircle : AlertCircle" :class="['w-3 h-3', /[A-Z]/.test(form.password) ? 'text-emerald-500' : 'text-gray-400']" />
                                Huruf besar
                            </li>
                            <li class="flex items-center gap-1">
                                <component :is="/[a-z]/.test(form.password) ? CheckCircle : AlertCircle" :class="['w-3 h-3', /[a-z]/.test(form.password) ? 'text-emerald-500' : 'text-gray-400']" />
                                Huruf kecil
                            </li>
                            <li class="flex items-center gap-1">
                                <component :is="/[0-9]/.test(form.password) ? CheckCircle : AlertCircle" :class="['w-3 h-3', /[0-9]/.test(form.password) ? 'text-emerald-500' : 'text-gray-400']" />
                                Angka
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-gray-50 via-white to-gray-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center shadow-lg">
                            <Shield class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Status Awal</h3>
                            <p class="text-[10px] text-gray-500">Atur status akses pengguna</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="['relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3', form.status === 'active' ? 'border-emerald-500 bg-emerald-50/50 shadow-lg shadow-emerald-500/10' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50']">
                            <input type="radio" v-model="form.status" value="active" class="sr-only">
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center transition-all', form.status === 'active' ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg' : 'bg-gray-200']">
                                <CheckCircle :class="['w-5 h-5', form.status === 'active' ? 'text-white' : 'text-gray-400']" />
                            </div>
                            <div>
                                <p :class="['text-xs font-bold', form.status === 'active' ? 'text-emerald-700' : 'text-gray-700']">Aktif</p>
                                <p class="text-[9px] text-gray-500">User dapat login</p>
                            </div>
                        </label>
                        <label :class="['relative p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center gap-3', form.status === 'blocked' ? 'border-red-500 bg-red-50/50 shadow-lg shadow-red-500/10' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50']">
                            <input type="radio" v-model="form.status" value="blocked" class="sr-only">
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center transition-all', form.status === 'blocked' ? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-lg' : 'bg-gray-200']">
                                <ShieldOff :class="['w-5 h-5', form.status === 'blocked' ? 'text-white' : 'text-gray-400']" />
                            </div>
                            <div>
                                <p :class="['text-xs font-bold', form.status === 'blocked' ? 'text-red-700' : 'text-gray-700']">Diblokir</p>
                                <p class="text-[9px] text-gray-500">User tidak dapat login</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing || !canSubmitForm"
                    class="flex-1 py-3 bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <Save class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Buat User' }}
                    <Sparkles class="w-3.5 h-3.5" />
                </button>
                <Link href="/admin/users" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </Link>
            </div>
        </form>
    </div>
</template>
