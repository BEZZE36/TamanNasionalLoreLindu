<script setup>
/**
 * Register.vue - Premium Dark Theme Register Page
 * Modern design with GSAP animations, glassmorphism, and responsive layout
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { User, Mail, Phone, Lock, UserPlus, ArrowLeft, Eye, EyeOff, Sparkles, Check, X, Shield, Zap, Clock } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const containerRef = ref(null);
let ctx;

// Password strength calculation
const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return { score: 0, label: '', color: '' };
    
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;
    
    const levels = [
        { score: 0, label: '', color: '' },
        { score: 1, label: 'Lemah', color: 'bg-red-500' },
        { score: 2, label: 'Cukup', color: 'bg-orange-500' },
        { score: 3, label: 'Baik', color: 'bg-yellow-500' },
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
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    ctx = gsap.context(() => {
        // Logo entrance
        gsap.fromTo('.auth-logo',
            { opacity: 0, scale: 0.8 },
            { opacity: 1, scale: 1, duration: 0.6, ease: 'back.out(1.7)' }
        );
        
        // Left panel content
        gsap.fromTo('.left-content',
            { opacity: 0, x: -30 },
            { opacity: 1, x: 0, duration: 0.8, ease: 'power2.out', delay: 0.2 }
        );
        
        // Feature cards stagger
        gsap.fromTo('.feature-card',
            { opacity: 0, y: 20, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.4 }
        );
        
        // Card entrance
        gsap.fromTo('.auth-card',
            { opacity: 0, y: 40, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.7, ease: 'power3.out', delay: 0.3 }
        );
        
        // Form items stagger
        gsap.fromTo('.form-item',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out', delay: 0.5 }
        );
        
        // Floating particles animation
        gsap.to('.particle', {
            y: '+=15',
            x: '+=8',
            duration: 3,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
            stagger: { each: 0.5, from: 'random' }
        });
    }, containerRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});

const features = [
    { icon: Shield, title: 'Aman & Terpercaya', desc: 'Transaksi dijamin aman' },
    { icon: Zap, title: 'Proses Cepat', desc: 'E-ticket instan' },
    { icon: Clock, title: 'Akses 24/7', desc: 'Pesan kapan saja' },
];
</script>

<template>
    <Head title="Daftar" />

    <div ref="containerRef" class="min-h-screen flex bg-[#0a0f1a] overflow-hidden">
        <!-- Background Effects -->
        <div class="fixed inset-0 pointer-events-none">
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-950/40 via-transparent to-teal-950/30"></div>
            
            <!-- Animated orbs -->
            <div class="particle absolute top-[10%] left-[15%] w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="particle absolute bottom-[20%] right-[10%] w-80 h-80 bg-teal-500/8 rounded-full blur-3xl"></div>
            <div class="particle absolute top-[50%] left-[50%] w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
            
            <!-- Grid pattern -->
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>

        <!-- Left Panel - Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative z-10">
            <div class="left-content flex flex-col justify-between p-8 xl:p-12 text-white w-full">
                <!-- Logo -->
                <Link href="/" class="auth-logo inline-flex items-center gap-3 group w-fit">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:shadow-emerald-500/40 transition-shadow">
                        <img src="/assets/logo.png" alt="TNLL" class="w-6 h-6 object-contain">
                    </div>
                    <span class="text-lg font-bold group-hover:text-emerald-300 transition-colors">TNLL Explore</span>
                </Link>
                
                <!-- Main Content -->
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 mb-6">
                        <Sparkles class="w-3.5 h-3.5 text-emerald-400" />
                        <span class="text-[11px] font-semibold text-emerald-300 tracking-wide">MULAI PETUALANGAN</span>
                    </div>
                    
                    <h2 class="text-3xl xl:text-4xl font-black mb-4 leading-tight">
                        Bergabung dengan
                        <span class="bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 bg-clip-text text-transparent"> Ribuan Penjelajah</span>
                    </h2>
                    <p class="text-gray-400 text-sm leading-relaxed mb-8">
                        Daftar sekarang dan nikmati kemudahan memesan tiket wisata ke berbagai destinasi menakjubkan di Taman Nasional Lore Lindu.
                    </p>
                    
                    <!-- Feature Cards -->
                    <div class="grid grid-cols-3 gap-3">
                        <div v-for="(feature, index) in features" :key="index"
                            class="feature-card p-3 rounded-xl bg-white/[0.03] border border-white/10 hover:bg-white/[0.06] hover:border-emerald-500/20 transition-all group">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-2 group-hover:from-emerald-500/30 group-hover:to-teal-500/30 transition-colors">
                                <component :is="feature.icon" class="w-4 h-4 text-emerald-400" />
                            </div>
                            <p class="text-[11px] font-semibold text-white mb-0.5">{{ feature.title }}</p>
                            <p class="text-[10px] text-gray-500">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <p class="text-gray-600 text-[11px]">&copy; {{ new Date().getFullYear() }} TNLL Explore. All rights reserved.</p>
            </div>
        </div>

        <!-- Right Panel - Register Form -->
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10 overflow-y-auto">
            <div class="w-full max-w-md py-4">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-5">
                    <Link href="/" class="auth-logo inline-flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                            <img src="/assets/logo.png" alt="TNLL" class="w-5 h-5 object-contain">
                        </div>
                        <span class="text-base font-bold text-white">TNLL Explore</span>
                    </Link>
                </div>

                <!-- Register Card -->
                <div class="auth-card relative">
                    <!-- Glow effect -->
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-cyan-500/20 rounded-2xl blur-xl opacity-60"></div>
                    
                    <div class="relative bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden">
                        <!-- Top accent line -->
                        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-400/50 to-transparent"></div>
                        
                        <div class="p-5 sm:p-6">
                            <!-- Header -->
                            <div class="form-item text-center lg:text-left mb-5">
                                <h1 class="text-lg sm:text-xl font-black text-white mb-1">Buat Akun Baru 🚀</h1>
                                <p class="text-gray-400 text-xs">Daftar untuk mulai memesan tiket wisata</p>
                            </div>

                            <form @submit.prevent="submit" class="space-y-3.5">
                                <!-- Name -->
                                <div class="form-item">
                                    <label for="name" class="block text-[11px] font-medium text-gray-300 mb-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <User class="w-3 h-3 text-gray-500" />
                                            Nama Lengkap
                                        </span>
                                    </label>
                                    <input type="text" id="name" v-model="form.name" 
                                        class="w-full px-3.5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                        :class="{ 'border-red-500/50': form.errors.name }"
                                        placeholder="Masukkan nama lengkap" required autofocus>
                                    <p v-if="form.errors.name" class="text-red-400 text-[10px] mt-1">{{ form.errors.name }}</p>
                                </div>

                                <!-- Email -->
                                <div class="form-item">
                                    <label for="email" class="block text-[11px] font-medium text-gray-300 mb-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <Mail class="w-3 h-3 text-gray-500" />
                                            Email
                                        </span>
                                    </label>
                                    <input type="email" id="email" v-model="form.email" 
                                        class="w-full px-3.5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                        :class="{ 'border-red-500/50': form.errors.email }"
                                        placeholder="nama@email.com" required>
                                    <p v-if="form.errors.email" class="text-red-400 text-[10px] mt-1">{{ form.errors.email }}</p>
                                </div>

                                <!-- Phone -->
                                <div class="form-item">
                                    <label for="phone" class="block text-[11px] font-medium text-gray-300 mb-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <Phone class="w-3 h-3 text-gray-500" />
                                            Nomor HP
                                        </span>
                                    </label>
                                    <input type="tel" id="phone" v-model="form.phone" 
                                        class="w-full px-3.5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                        :class="{ 'border-red-500/50': form.errors.phone }"
                                        placeholder="08xxxxxxxxxx" required>
                                    <p v-if="form.errors.phone" class="text-red-400 text-[10px] mt-1">{{ form.errors.phone }}</p>
                                </div>

                                <!-- Password Row -->
                                <div class="form-item grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="password" class="block text-[11px] font-medium text-gray-300 mb-1.5">
                                            <span class="flex items-center gap-1.5">
                                                <Lock class="w-3 h-3 text-gray-500" />
                                                Password
                                            </span>
                                        </label>
                                        <div class="relative">
                                            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" 
                                                class="w-full px-3.5 py-2.5 pr-9 bg-white/5 border border-white/10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                                :class="{ 'border-red-500/50': form.errors.password }"
                                                placeholder="Min 8 karakter" required>
                                            <button type="button" @click="showPassword = !showPassword"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                                <component :is="showPassword ? EyeOff : Eye" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="password_confirmation" class="block text-[11px] font-medium text-gray-300 mb-1.5">Konfirmasi</label>
                                        <div class="relative">
                                            <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" v-model="form.password_confirmation" 
                                                class="w-full px-3.5 py-2.5 pr-9 bg-white/5 border border-white/10 rounded-xl text-white text-xs placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                                :class="{ 
                                                    'border-emerald-500/50': passwordsMatch === true,
                                                    'border-red-500/50': passwordsMatch === false 
                                                }"
                                                placeholder="Ulangi password" required>
                                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                                <component :is="showConfirmPassword ? EyeOff : Eye" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Password Strength Indicator -->
                                <div v-if="form.password" class="form-item">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1 bg-white/10 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-300 rounded-full"
                                                :class="passwordStrength.color"
                                                :style="{ width: `${(passwordStrength.score / 5) * 100}%` }">
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-medium" 
                                            :class="{
                                                'text-red-400': passwordStrength.score <= 1,
                                                'text-orange-400': passwordStrength.score === 2,
                                                'text-yellow-400': passwordStrength.score === 3,
                                                'text-emerald-400': passwordStrength.score >= 4
                                            }">
                                            {{ passwordStrength.label }}
                                        </span>
                                    </div>
                                    
                                    <!-- Password Match Indicator -->
                                    <div v-if="form.password_confirmation" class="flex items-center gap-1.5 mt-1.5">
                                        <component :is="passwordsMatch ? Check : X" 
                                            class="w-3 h-3"
                                            :class="passwordsMatch ? 'text-emerald-400' : 'text-red-400'" />
                                        <span class="text-[10px]" 
                                            :class="passwordsMatch ? 'text-emerald-400' : 'text-red-400'">
                                            {{ passwordsMatch ? 'Password cocok' : 'Password tidak cocok' }}
                                        </span>
                                    </div>
                                </div>

                                <p v-if="form.errors.password" class="form-item text-red-400 text-[10px] -mt-2">{{ form.errors.password }}</p>

                                <!-- Terms -->
                                <div class="form-item p-3 bg-white/[0.02] border border-white/5 rounded-xl">
                                    <label class="flex items-start gap-2.5 cursor-pointer group">
                                        <input type="checkbox" v-model="form.terms" required
                                            class="w-4 h-4 mt-0.5 bg-white/5 border-white/20 rounded text-emerald-500 focus:ring-emerald-500/50 focus:ring-offset-0">
                                        <span class="text-[10px] text-gray-400 leading-relaxed">
                                            Dengan mendaftar, saya menyetujui
                                            <Link href="/terms" class="text-emerald-400 hover:text-emerald-300 font-medium transition">Syarat & Ketentuan</Link>
                                            dan
                                            <Link href="/privacy-policy" class="text-emerald-400 hover:text-emerald-300 font-medium transition">Kebijakan Privasi</Link>
                                        </span>
                                    </label>
                                </div>

                                <!-- Submit -->
                                <button type="submit" :disabled="form.processing"
                                    class="form-item w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                                    <UserPlus class="w-4 h-4" />
                                    {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
                                </button>
                            </form>

                            <!-- Divider -->
                            <div class="form-item relative my-4">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-white/10"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span class="px-3 bg-[#0d1829] text-[10px] text-gray-500">atau daftar dengan</span>
                                </div>
                            </div>

                            <!-- Google Register -->
                            <a href="/auth/google"
                                class="form-item w-full flex items-center justify-center gap-2.5 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-xs font-medium text-white hover:bg-white/10 hover:border-white/20 transition-all">
                                <svg class="w-4 h-4" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.12c-.22-.66-.35-1.36-.35-2.12s.13-1.46.35-2.12V7.04H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.96l2.66-2.84z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.04l3.66 2.84c.87-2.6 3.3-4.5 6.16-4.5z" fill="#EA4335"/>
                                </svg>
                                Google
                            </a>

                            <!-- Login Link -->
                            <p class="form-item text-center mt-4 text-[11px] text-gray-400">
                                Sudah punya akun?
                                <Link href="/login" class="text-emerald-400 hover:text-emerald-300 font-medium ml-1 transition">
                                    Masuk ke Akun
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Back to Home -->
                <p class="form-item text-center mt-5">
                    <Link href="/" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-300 text-[11px] font-medium transition">
                        <ArrowLeft class="w-3.5 h-3.5" />
                        Kembali ke Beranda
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom checkbox styling */
input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    cursor: pointer;
    position: relative;
}

input[type="checkbox"]:checked {
    background: linear-gradient(135deg, #10b981, #14b8a6);
    border-color: transparent;
}

input[type="checkbox"]:checked::after {
    content: '';
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Focus states */
input:focus {
    outline: none;
}

/* Smooth will-change for animations */
.auth-logo,
.left-content,
.auth-card,
.form-item,
.feature-card,
.particle {
    will-change: transform, opacity;
}

/* Scrollbar styling */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
