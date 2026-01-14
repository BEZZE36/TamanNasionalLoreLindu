<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, Lock, LogIn, Eye, EyeOff, ArrowLeft, Shield, ChevronRight } from 'lucide-vue-next';
import { gsap } from 'gsap';

const showPassword = ref(false);
const containerRef = ref(null);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};

onMounted(() => {
    gsap.context(() => {
        // Animate logo
        gsap.fromTo('.logo-container', 
            { opacity: 0, scale: 0.8, rotate: -10 },
            { opacity: 1, scale: 1, rotate: 0, duration: 0.6, ease: 'back.out(1.5)' }
        );
        
        // Animate branding text
        gsap.fromTo('.brand-text > *', 
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.5, stagger: 0.1, delay: 0.2, ease: 'power2.out' }
        );
        
        // Animate form card
        gsap.fromTo('.login-card', 
            { opacity: 0, y: 30, scale: 0.98 },
            { opacity: 1, y: 0, scale: 1, duration: 0.5, delay: 0.3, ease: 'power2.out' }
        );
        
        // Animate form fields
        gsap.fromTo('.form-field', 
            { opacity: 0, x: -20 },
            { opacity: 1, x: 0, duration: 0.4, stagger: 0.08, delay: 0.5, ease: 'power2.out' }
        );
        
        // Animate features
        gsap.fromTo('.feature-item', 
            { opacity: 0, y: 15 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, delay: 0.6, ease: 'power2.out' }
        );
    }, containerRef.value);
});
</script>

<template>
    <Head title="Admin Login - TNLL Explore" />

    <div ref="containerRef" class="min-h-screen flex bg-slate-950 relative overflow-hidden">
        <!-- Animated Background Orbs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -right-32 w-64 h-64 bg-emerald-500/8 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-32 -left-32 w-48 h-48 bg-teal-500/8 rounded-full blur-3xl animate-pulse" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/4 w-32 h-32 bg-cyan-500/5 rounded-full blur-2xl animate-pulse" style="animation-delay: 3s;"></div>
            
            <!-- Grid -->
            <div class="absolute inset-0 bg-[linear-gradient(rgba(16,185,129,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.015)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        </div>

        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center p-8">
            <div class="max-w-sm text-center">
                <!-- Logo -->
                <div class="logo-container relative inline-block mb-6">
                    <div class="absolute inset-0 bg-emerald-500/15 rounded-full blur-2xl scale-125"></div>
                    <img src="/assets/logo.png" alt="TNLL" class="h-24 relative drop-shadow-xl">
                </div>
                
                <!-- Brand Text -->
                <div class="brand-text">
                    <h1 class="text-2xl font-black text-white mb-1">
                        Taman Nasional
                    </h1>
                    <h2 class="text-xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-400 mb-3">
                        Lore Lindu
                    </h2>
                    <p class="text-[11px] text-slate-500 mb-6 leading-relaxed">
                        Sistem Administrasi Pengelolaan<br/>Wisata & Konservasi Alam
                    </p>
                </div>
                
                <!-- Features -->
                <div class="flex justify-center gap-3">
                    <div class="feature-item bg-white/5 backdrop-blur-sm rounded-xl px-3 py-2.5 border border-white/5 hover:border-emerald-500/30 hover:bg-white/8 transition-all group">
                        <div class="w-7 h-7 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center mx-auto mb-1.5 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                            <Shield class="w-3.5 h-3.5 text-white" />
                        </div>
                        <p class="text-[9px] text-slate-500 font-medium">Secure</p>
                    </div>
                    <div class="feature-item bg-white/5 backdrop-blur-sm rounded-xl px-3 py-2.5 border border-white/5 hover:border-teal-500/30 hover:bg-white/8 transition-all group">
                        <div class="w-7 h-7 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg flex items-center justify-center mx-auto mb-1.5 shadow-lg shadow-teal-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <p class="text-[9px] text-slate-500 font-medium">Dashboard</p>
                    </div>
                    <div class="feature-item bg-white/5 backdrop-blur-sm rounded-xl px-3 py-2.5 border border-white/5 hover:border-green-500/30 hover:bg-white/8 transition-all group">
                        <div class="w-7 h-7 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mx-auto mb-1.5 shadow-lg shadow-green-500/20 group-hover:scale-110 transition-transform">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="text-[9px] text-slate-500 font-medium">Analytics</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6">
            <div class="w-full max-w-xs">
                
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-6">
                    <img src="/assets/logo.png" alt="TNLL" class="h-14 mx-auto mb-3 drop-shadow-xl">
                    <h1 class="text-sm font-bold text-white">Admin Panel</h1>
                </div>

                <!-- Login Card -->
                <div class="login-card bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-white/5 shadow-2xl shadow-black/30 overflow-hidden">
                    <!-- Header -->
                    <div class="px-5 pt-6 pb-3 text-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-500/25 rotate-2 hover:rotate-0 transition-transform">
                            <Shield class="w-5 h-5 text-white" />
                        </div>
                        <h2 class="text-sm font-bold text-white mb-0.5">Selamat Datang</h2>
                        <p class="text-[10px] text-slate-500">Masuk ke dashboard admin</p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="px-5 pb-5 pt-3 space-y-3.5">
                        <!-- Email -->
                        <div class="form-field">
                            <label class="block text-[10px] font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Email</label>
                            <div class="relative group">
                                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 group-focus-within:text-emerald-400 transition-colors" />
                                <input 
                                    type="email" 
                                    v-model="form.email"
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-800/60 border border-slate-700/50 rounded-lg text-xs text-white placeholder-slate-600 focus:border-emerald-500/50 focus:bg-slate-800 focus:ring-0 transition-all"
                                    :class="{ 'border-red-500/50': form.errors.email }"
                                    placeholder="admin@tnll.com" 
                                    required 
                                    autofocus
                                >
                            </div>
                            <p v-if="form.errors.email" class="text-red-400 text-[10px] mt-1">{{ form.errors.email }}</p>
                        </div>

                        <!-- Password -->
                        <div class="form-field">
                            <label class="block text-[10px] font-semibold text-slate-400 mb-1.5 uppercase tracking-wide">Password</label>
                            <div class="relative group">
                                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-600 group-focus-within:text-emerald-400 transition-colors" />
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    v-model="form.password"
                                    class="w-full pl-9 pr-9 py-2.5 bg-slate-800/60 border border-slate-700/50 rounded-lg text-xs text-white placeholder-slate-600 focus:border-emerald-500/50 focus:bg-slate-800 focus:ring-0 transition-all"
                                    :class="{ 'border-red-500/50': form.errors.password }"
                                    placeholder="••••••••" 
                                    required
                                >
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-600 hover:text-emerald-400 transition-colors"
                                >
                                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-red-400 text-[10px] mt-1">{{ form.errors.password }}</p>
                        </div>

                        <!-- Remember -->
                        <div class="form-field flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                v-model="form.remember" 
                                id="remember"
                                class="w-3.5 h-3.5 rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-0 focus:ring-offset-0"
                            >
                            <label for="remember" class="text-[11px] text-slate-500 cursor-pointer hover:text-slate-400 transition-colors">Ingat saya</label>
                        </div>

                        <!-- Submit -->
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="form-field w-full flex items-center justify-center gap-2 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white text-xs font-semibold rounded-lg hover:from-emerald-500 hover:to-green-500 hover:shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                        >
                            <div v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            <LogIn v-else class="w-4 h-4" />
                            <span>{{ form.processing ? 'Memproses...' : 'Masuk' }}</span>
                            <ChevronRight v-if="!form.processing" class="w-3.5 h-3.5 -mr-1" />
                        </button>
                    </form>
                </div>

                <!-- Back Link -->
                <a href="/" class="flex items-center justify-center gap-1.5 mt-4 text-[11px] text-slate-600 hover:text-emerald-400 transition-colors group">
                    <ArrowLeft class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
                    Kembali ke Website
                </a>

                <!-- Footer -->
                <p class="text-center mt-6 text-slate-700 text-[9px]">
                    © {{ new Date().getFullYear() }} TNLL Explore
                </p>
            </div>
        </div>
    </div>
</template>
