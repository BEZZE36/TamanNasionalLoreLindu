<script setup>
/**
 * ForgotPassword.vue - Forgot Password Page with Account Search
 * Step 1: Search by email/username/phone
 * Step 2: Show masked account info, send OTP
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, Link, Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Search, Mail, User, Phone, ArrowLeft, ArrowRight, Send, Shield, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    step: {
        type: Number,
        default: 1
    },
    account: {
        type: Object,
        default: null
    },
    errors: Object,
});

const containerRef = ref(null);
let ctx;

// Step 1: Search form
const searchForm = useForm({
    search: '',
});

// Submit search
const submitSearch = () => {
    searchForm.post('/forgot-password/find', {
        preserveScroll: true,
    });
};

// Send OTP
const sendOtp = () => {
    router.post('/forgot-password/send-otp');
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.forgot-card',
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
    <Head title="Lupa Password" />

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

            <!-- Forgot Password Card -->
            <div class="forgot-card relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-cyan-500/20 rounded-2xl blur-xl opacity-60"></div>
                
                <div class="relative bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/10 p-6 sm:p-8">
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-400/50 to-transparent"></div>

                    <!-- Step 1: Search Account -->
                    <div v-if="step === 1">
                        <!-- Header -->
                        <div class="form-item text-center mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 flex items-center justify-center border border-emerald-500/20">
                                <Search class="w-8 h-8 text-emerald-400" />
                            </div>
                            <h1 class="text-xl font-bold text-white mb-2">Cari Akun Anda</h1>
                            <p class="text-gray-400 text-sm">
                                Masukkan email, username, atau nomor HP yang terdaftar
                            </p>
                        </div>

                        <form @submit.prevent="submitSearch" class="space-y-4">
                            <!-- Search Input -->
                            <div class="form-item">
                                <label for="search" class="block text-xs font-medium text-gray-300 mb-2">
                                    <span class="flex items-center gap-2">
                                        <Search class="w-3.5 h-3.5 text-gray-500" />
                                        Email / Username / No. HP
                                    </span>
                                </label>
                                <input 
                                    type="text" 
                                    id="search" 
                                    v-model="searchForm.search"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                                    :class="{ 'border-red-500/50': searchForm.errors.search }"
                                    placeholder="contoh@email.com atau username"
                                    required
                                    autofocus
                                />
                                <p v-if="searchForm.errors.search" class="text-red-400 text-[11px] mt-1.5 flex items-center gap-1">
                                    <AlertCircle class="w-3 h-3" />
                                    {{ searchForm.errors.search }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="searchForm.processing || !searchForm.search"
                                class="form-item w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                            >
                                <Search class="w-4 h-4" />
                                {{ searchForm.processing ? 'Mencari...' : 'Cari Akun' }}
                            </button>
                        </form>
                    </div>

                    <!-- Step 2: Account Found -->
                    <div v-else-if="step === 2 && account">
                        <!-- Header -->
                        <div class="form-item text-center mb-6">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full overflow-hidden border-2 border-emerald-500/30">
                                <img :src="account.avatar" alt="Avatar" class="w-full h-full object-cover" />
                            </div>
                            <h1 class="text-xl font-bold text-white mb-2">Akun Ditemukan</h1>
                            <p class="text-gray-400 text-sm">
                                Konfirmasi ini adalah akun Anda
                            </p>
                        </div>

                        <!-- Account Info -->
                        <div class="form-item space-y-3 mb-6">
                            <!-- Username -->
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                    <User class="w-4 h-4 text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Username</p>
                                    <p class="text-white font-medium text-sm">{{ account.username }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                                <div class="w-9 h-9 rounded-lg bg-teal-500/10 flex items-center justify-center">
                                    <Mail class="w-4 h-4 text-teal-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Email</p>
                                    <p class="text-white font-medium text-sm">{{ account.email }}</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div v-if="account.phone" class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                                <div class="w-9 h-9 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                                    <Phone class="w-4 h-4 text-cyan-400" />
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">No. Handphone</p>
                                    <p class="text-white font-medium text-sm">{{ account.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Notice -->
                        <div class="form-item flex items-start gap-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 mb-6">
                            <Shield class="w-5 h-5 text-emerald-400 flex-shrink-0 mt-0.5" />
                            <p class="text-emerald-300 text-xs leading-relaxed">
                                Kami akan mengirim kode OTP ke email Anda untuk memverifikasi identitas.
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="form-item flex gap-3">
                            <Link
                                href="/forgot-password"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-white/5 border border-white/10 text-gray-300 text-sm font-medium rounded-xl hover:bg-white/10 transition-all"
                            >
                                <ArrowLeft class="w-4 h-4" />
                                Bukan Saya
                            </Link>
                            <button
                                @click="sendOtp"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all"
                            >
                                Kirim OTP
                                <Send class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

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
