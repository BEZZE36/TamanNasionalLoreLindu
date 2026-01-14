<script setup>
/**
 * Login.vue - Premium Dark Theme Login Page
 * Modern design with GSAP animations, glassmorphism, and responsive layout
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, Head, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Mail, Lock, LogIn, ArrowLeft, Eye, EyeOff, Sparkles, AlertCircle } from 'lucide-vue-next';

const page = usePage();
const pageErrors = computed(() => page.props.errors || {});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const containerRef = ref(null);
let ctx;

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
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
        
        // Card entrance
        gsap.fromTo('.auth-card',
            { opacity: 0, y: 40, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.7, ease: 'power3.out', delay: 0.3 }
        );
        
        // Form items stagger
        gsap.fromTo('.form-item',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.08, ease: 'power2.out', delay: 0.5 }
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
        
        // Stats counter animation
        gsap.fromTo('.stat-item',
            { opacity: 0, scale: 0.8 },
            { opacity: 1, scale: 1, duration: 0.5, stagger: 0.1, ease: 'back.out(1.5)', delay: 0.6 }
        );
    }, containerRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
});
</script>

<template>
    <Head title="Masuk" />

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
                        <span class="text-[11px] font-semibold text-emerald-300 tracking-wide">PLATFORM RESMI</span>
                    </div>
                    
                    <h2 class="text-3xl xl:text-4xl font-black mb-4 leading-tight">
                        Jelajahi Keindahan
                        <span class="bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 bg-clip-text text-transparent"> Alam Sulawesi Tengah</span>
                    </h2>
                    <p class="text-gray-400 text-sm leading-relaxed mb-8">
                        Platform e-ticketing resmi untuk Taman Nasional Lore Lindu. Pesan tiket dengan mudah, aman, dan praktis untuk pengalaman wisata terbaik.
                    </p>
                    
                    <!-- Stats -->
                    <div class="flex items-center gap-6">
                        <div class="stat-item flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 border-2 border-[#0a0f1a] flex items-center justify-center text-[10px] font-bold">A</div>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-cyan-600 border-2 border-[#0a0f1a] flex items-center justify-center text-[10px] font-bold">B</div>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 border-2 border-[#0a0f1a] flex items-center justify-center text-[10px] font-bold">C</div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">10,000+</p>
                                <p class="text-[10px] text-gray-500">Pengunjung</p>
                            </div>
                        </div>
                        
                        <div class="stat-item h-8 w-px bg-white/10"></div>
                        
                        <div class="stat-item">
                            <p class="text-sm font-bold text-white">4.9 ⭐</p>
                            <p class="text-[10px] text-gray-500">Rating</p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <p class="text-gray-600 text-[11px]">&copy; {{ new Date().getFullYear() }} TNLL Explore. All rights reserved.</p>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-6">
                    <Link href="/" class="auth-logo inline-flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                            <img src="/assets/logo.png" alt="TNLL" class="w-6 h-6 object-contain">
                        </div>
                        <span class="text-lg font-bold text-white">TNLL Explore</span>
                    </Link>
                </div>

                <!-- Login Card -->
                <div class="auth-card relative">
                    <!-- Glow effect -->
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-cyan-500/20 rounded-2xl blur-xl opacity-60"></div>
                    
                    <div class="relative bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden">
                        <!-- Top accent line -->
                        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-400/50 to-transparent"></div>
                        
                        <div class="p-6 sm:p-8">
                            <!-- Header -->
                            <div class="form-item text-center lg:text-left mb-6">
                                <h1 class="text-xl sm:text-2xl font-black text-white mb-1">Selamat Datang! 👋</h1>
                                <p class="text-gray-400 text-xs sm:text-sm">Masuk ke akun Anda untuk melanjutkan</p>
                            </div>

                            <!-- Error Banner (from redirects like Google OAuth) -->
                            <div v-if="pageErrors.email" class="form-item mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20">
                                <div class="flex items-start gap-2">
                                    <AlertCircle class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" />
                                    <p class="text-red-400 text-xs leading-relaxed">{{ pageErrors.email }}</p>
                                </div>
                            </div>

                            <form @submit.prevent="submit" class="space-y-4">
                                <!-- Email -->
                                <div class="form-item">
                                    <label for="email" class="block text-xs font-medium text-gray-300 mb-2">
                                        <span class="flex items-center gap-2">
                                            <Mail class="w-3.5 h-3.5 text-gray-500" />
                                            Email
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <input type="email" id="email" v-model="form.email" 
                                            class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                            :class="{ 'border-red-500/50 focus:ring-red-500/50': form.errors.email }"
                                            placeholder="nama@email.com" required autofocus>
                                    </div>
                                    <p v-if="form.errors.email" class="text-red-400 text-[11px] mt-1.5">{{ form.errors.email }}</p>
                                </div>

                                <!-- Password -->
                                <div class="form-item">
                                    <label for="password" class="block text-xs font-medium text-gray-300 mb-2">
                                        <span class="flex items-center gap-2">
                                            <Lock class="w-3.5 h-3.5 text-gray-500" />
                                            Password
                                        </span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" 
                                            class="w-full px-4 py-3 pr-11 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                            :class="{ 'border-red-500/50 focus:ring-red-500/50': form.errors.password }"
                                            placeholder="••••••••" required>
                                        <button type="button" @click="showPassword = !showPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                                            <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password" class="text-red-400 text-[11px] mt-1.5">{{ form.errors.password }}</p>
                                </div>

                                <!-- Remember & Forgot -->
                                <div class="form-item flex items-center justify-between">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" v-model="form.remember"
                                            class="w-4 h-4 bg-white/5 border-white/20 rounded text-emerald-500 focus:ring-emerald-500/50 focus:ring-offset-0 transition">
                                        <span class="ml-2 text-[11px] text-gray-400 group-hover:text-gray-300 transition">Ingat saya</span>
                                    </label>
                                    <Link href="/forgot-password" class="text-[11px] text-emerald-400 hover:text-emerald-300 font-medium transition">
                                        Lupa password?
                                    </Link>
                                </div>

                                <!-- Submit -->
                                <button type="submit" :disabled="form.processing"
                                    class="form-item w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                                    <LogIn class="w-4 h-4" />
                                    {{ form.processing ? 'Memproses...' : 'Masuk' }}
                                </button>
                            </form>

                            <!-- Divider -->
                            <div class="form-item relative my-5">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-white/10"></div>
                                </div>
                                <div class="relative flex justify-center">
                                    <span class="px-3 bg-[#0d1829] text-[10px] text-gray-500">atau masuk dengan</span>
                                </div>
                            </div>

                            <!-- Google Login -->
                            <a href="/auth/google"
                                class="form-item w-full flex items-center justify-center gap-3 px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium text-white hover:bg-white/10 hover:border-white/20 transition-all">
                                <svg class="w-4 h-4" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.12c-.22-.66-.35-1.36-.35-2.12s.13-1.46.35-2.12V7.04H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.96l2.66-2.84z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.04l3.66 2.84c.87-2.6 3.3-4.5 6.16-4.5z" fill="#EA4335"/>
                                </svg>
                                Google
                            </a>

                            <!-- Register Link -->
                            <p class="form-item text-center mt-5 text-[11px] text-gray-400">
                                Belum punya akun?
                                <Link href="/register" class="text-emerald-400 hover:text-emerald-300 font-medium ml-1 transition">
                                    Buat Akun Baru
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Back to Home -->
                <p class="form-item text-center mt-6">
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
.stat-item,
.particle {
    will-change: transform, opacity;
}
</style>
