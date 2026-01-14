<script setup>
/**
 * ResetPassword.vue - Reset Password Page
 * Final step after OTP verification
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Lock, Eye, EyeOff, ArrowLeft, CheckCircle, Shield } from 'lucide-vue-next';

const props = defineProps({
    email: String,
    errors: Object,
});

const containerRef = ref(null);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
let ctx;

const form = useForm({
    email: props.email,
    password: '',
    password_confirmation: '',
});

// Password strength calculation
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return { score: 0, label: '', color: '' };
    
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    const levels = [
        { score: 0, label: '', color: '' },
        { score: 1, label: 'Lemah', color: 'bg-red-500' },
        { score: 2, label: 'Kurang', color: 'bg-orange-500' },
        { score: 3, label: 'Cukup', color: 'bg-yellow-500' },
        { score: 4, label: 'Kuat', color: 'bg-emerald-500' },
        { score: 5, label: 'Sangat Kuat', color: 'bg-emerald-400' },
    ];
    
    return levels[Math.min(score, 5)];
});

// Password match check
const passwordsMatch = computed(() => {
    if (!form.password_confirmation) return null;
    return form.password === form.password_confirmation;
});

const submit = () => {
    form.post('/forgot-password/reset', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.reset-card',
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.6, ease: 'power3.out' }
        );
        
        gsap.fromTo('.form-item',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out', delay: 0.3 }
        );
    }, containerRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <Head title="Reset Password" />

    <div ref="containerRef" class="min-h-screen flex items-center justify-center bg-[#0a0f1a] p-4">
        <!-- Background Effects -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-950/40 via-transparent to-teal-950/30"></div>
            <div class="absolute top-[20%] left-[20%] w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-[20%] right-[20%] w-80 h-80 bg-teal-500/8 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-md relative z-10">
            <!-- Logo -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                        <img src="/assets/logo.png" alt="TNLL" class="w-6 h-6 object-contain">
                    </div>
                    <span class="text-lg font-bold text-white group-hover:text-emerald-300 transition-colors">TNLL Explore</span>
                </Link>
            </div>

            <!-- Reset Password Card -->
            <div class="reset-card relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-cyan-500/20 rounded-2xl blur-xl opacity-60"></div>
                
                <div class="relative bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/10 p-6 sm:p-8">
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-400/50 to-transparent"></div>

                    <!-- Header -->
                    <div class="form-item text-center mb-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 flex items-center justify-center border border-emerald-500/20">
                            <Shield class="w-8 h-8 text-emerald-400" />
                        </div>
                        <h1 class="text-xl font-bold text-white mb-2">Buat Password Baru</h1>
                        <p class="text-gray-400 text-sm">
                            Password baru harus berbeda dari password sebelumnya
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- New Password -->
                        <div class="form-item">
                            <label for="password" class="block text-xs font-medium text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <Lock class="w-3.5 h-3.5 text-gray-500" />
                                    Password Baru
                                </span>
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    id="password" 
                                    v-model="form.password"
                                    class="w-full px-4 py-3 pr-11 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                    :class="{ 'border-red-500/50': form.errors.password }"
                                    placeholder="Minimal 8 karakter"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                                >
                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            
                            <!-- Password Strength -->
                            <div v-if="form.password" class="mt-2">
                                <div class="flex gap-1 mb-1">
                                    <div v-for="i in 5" :key="i" 
                                        class="h-1 flex-1 rounded-full transition-all duration-300"
                                        :class="i <= passwordStrength.score ? passwordStrength.color : 'bg-white/10'"
                                    ></div>
                                </div>
                                <p class="text-[10px]" :class="{
                                    'text-red-400': passwordStrength.score <= 2,
                                    'text-yellow-400': passwordStrength.score === 3,
                                    'text-emerald-400': passwordStrength.score >= 4
                                }">{{ passwordStrength.label }}</p>
                            </div>
                            
                            <p v-if="form.errors.password" class="text-red-400 text-[11px] mt-1.5">{{ form.errors.password }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-item">
                            <label for="password_confirmation" class="block text-xs font-medium text-gray-300 mb-2">
                                <span class="flex items-center gap-2">
                                    <Lock class="w-3.5 h-3.5 text-gray-500" />
                                    Konfirmasi Password
                                </span>
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showConfirmPassword ? 'text' : 'password'" 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation"
                                    class="w-full px-4 py-3 pr-11 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                    :class="{ 
                                        'border-red-500/50': passwordsMatch === false,
                                        'border-emerald-500/50': passwordsMatch === true
                                    }"
                                    placeholder="Ulangi password baru"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                                >
                                    <component :is="showConfirmPassword ? EyeOff : Eye" class="w-4 h-4" />
                                </button>
                            </div>
                            
                            <!-- Match indicator -->
                            <div v-if="form.password_confirmation" class="flex items-center gap-1 mt-1.5">
                                <CheckCircle v-if="passwordsMatch" class="w-3 h-3 text-emerald-400" />
                                <span class="text-[10px]" :class="passwordsMatch ? 'text-emerald-400' : 'text-red-400'">
                                    {{ passwordsMatch ? 'Password cocok' : 'Password tidak cocok' }}
                                </span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing || !passwordsMatch"
                            class="form-item w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                        >
                            <CheckCircle class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                        </button>
                    </form>

                    <!-- Back to Login -->
                    <div class="form-item text-center mt-6 pt-6 border-t border-white/10">
                        <Link href="/login" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-300 text-sm transition-colors">
                            <ArrowLeft class="w-4 h-4" />
                            Kembali ke Login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
