<script setup>
/**
 * VerifyOtp.vue - OTP Verification Page
 * Modern design with OTP input, countdown timer, and resend functionality
 */
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useForm, Link, Head, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { Mail, ArrowLeft, RefreshCw, CheckCircle, AlertCircle, Clock, Shield } from 'lucide-vue-next';

const props = defineProps({
    email: String,
    type: {
        type: String,
        default: 'register'
    },
    expirySeconds: {
        type: Number,
        default: 60
    },
    maskedEmail: String,
    errors: Object,
});

const otpInputs = ref(['', '', '', '', '', '']);
const containerRef = ref(null);
const countdown = ref(props.expirySeconds);
const resendCountdown = ref(0);
const isVerifying = ref(false);
const isResending = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
let ctx;
let countdownInterval;
let resendInterval;

const otpComplete = computed(() => otpInputs.value.every(digit => digit !== ''));
const otpValue = computed(() => otpInputs.value.join(''));

const form = useForm({
    email: props.email,
    otp: '',
});

const handleInput = (index, event) => {
    const value = event.target.value;
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
        otpInputs.value[index] = '';
        return;
    }

    // Handle paste
    if (value.length > 1) {
        const digits = value.split('').slice(0, 6);
        digits.forEach((digit, i) => {
            if (i < 6) otpInputs.value[i] = digit;
        });
        // Focus last filled or next empty
        const nextIndex = Math.min(digits.length, 5);
        document.getElementById(`otp-${nextIndex}`)?.focus();
        return;
    }

    otpInputs.value[index] = value;

    // Auto-focus next input
    if (value && index < 5) {
        document.getElementById(`otp-${index + 1}`)?.focus();
    }
};

const handleKeydown = (index, event) => {
    // Handle backspace
    if (event.key === 'Backspace' && !otpInputs.value[index] && index > 0) {
        document.getElementById(`otp-${index - 1}`)?.focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    pastedData.split('').forEach((digit, i) => {
        if (i < 6) otpInputs.value[i] = digit;
    });
};

const verifyOtp = async () => {
    if (!otpComplete.value) return;
    
    isVerifying.value = true;
    errorMessage.value = '';
    
    form.otp = otpValue.value;
    
    const route = props.type === 'register' ? '/register/verify-otp' : '/forgot-password/verify-otp';
    
    form.post(route, {
        onError: (errors) => {
            errorMessage.value = errors.otp || 'Verifikasi gagal.';
            isVerifying.value = false;
            // Shake animation
            gsap.to('.otp-container', {
                x: [-10, 10, -10, 10, 0],
                duration: 0.4,
                ease: 'power2.out'
            });
        },
        onFinish: () => {
            isVerifying.value = false;
        }
    });
};

const resendOtp = async () => {
    if (resendCountdown.value > 0 || isResending.value) return;
    
    isResending.value = true;
    errorMessage.value = '';
    
    const route = props.type === 'register' ? '/register/resend-otp' : '/forgot-password/resend-otp';
    
    try {
        // Get fresh CSRF token from cookie
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
            || document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1];
        
        const response = await fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': decodeURIComponent(csrfToken || ''),
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email: props.email }),
        });
        
        // Handle session expired - redirect to start
        if (response.status === 419) {
            errorMessage.value = 'Sesi telah berakhir. Mengalihkan...';
            setTimeout(() => {
                window.location.href = props.type === 'register' ? '/register' : '/forgot-password';
            }, 1500);
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            successMessage.value = 'OTP berhasil dikirim ulang!';
            countdown.value = Math.floor(data.expiry_seconds || props.expirySeconds);
            
            // Use dynamic cooldown from server (progressive cooldown)
            const nextCooldown = Math.floor(data.next_cooldown || 60);
            resendCountdown.value = nextCooldown;
            startResendCountdown();
            startCountdown();
            
            // Show info about next cooldown if it's increased
            if (data.attempt && data.attempt > 1) {
                const cooldownMinutes = Math.floor(nextCooldown / 60);
                if (cooldownMinutes >= 1) {
                    successMessage.value = `OTP dikirim! Cooldown berikutnya: ${cooldownMinutes} menit`;
                }
            }
            
            // Clear inputs
            otpInputs.value = ['', '', '', '', '', ''];
            document.getElementById('otp-0')?.focus();
            
            setTimeout(() => successMessage.value = '', 4000);
        } else {
            errorMessage.value = data.message || 'Gagal mengirim ulang OTP.';
            if (data.wait_seconds) {
                resendCountdown.value = Math.floor(data.wait_seconds);
                startResendCountdown();
            }
        }
    } catch (e) {
        // Show more helpful error and offer redirect
        errorMessage.value = 'Sesi tidak valid. Silakan klik Kembali dan mulai ulang.';
    }
    
    isResending.value = false;
};

const startCountdown = () => {
    countdownInterval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(countdownInterval);
        }
    }, 1000);
};

const startResendCountdown = () => {
    if (resendInterval) clearInterval(resendInterval);
    resendInterval = setInterval(() => {
        if (resendCountdown.value > 0) {
            resendCountdown.value--;
        } else {
            clearInterval(resendInterval);
        }
    }, 1000);
};

// Auto-submit when all digits filled
watch(otpComplete, (complete) => {
    if (complete && countdown.value > 0) {
        verifyOtp();
    }
});

onMounted(() => {
    startCountdown();
    resendCountdown.value = 60; // Initial cooldown
    startResendCountdown();
    
    ctx = gsap.context(() => {
        gsap.fromTo('.otp-card',
            { opacity: 0, y: 30, scale: 0.95 },
            { opacity: 1, y: 0, scale: 1, duration: 0.6, ease: 'power3.out' }
        );
        
        gsap.fromTo('.otp-input',
            { opacity: 0, y: 20 },
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.05, ease: 'power2.out', delay: 0.3 }
        );
    }, containerRef.value);
    
    // Focus first input
    setTimeout(() => document.getElementById('otp-0')?.focus(), 500);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    if (countdownInterval) clearInterval(countdownInterval);
    if (resendInterval) clearInterval(resendInterval);
});
</script>

<template>
    <Head :title="type === 'register' ? 'Verifikasi Email' : 'Verifikasi OTP'" />

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

            <!-- OTP Card -->
            <div class="otp-card relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/20 via-teal-500/10 to-cyan-500/20 rounded-2xl blur-xl opacity-60"></div>
                
                <div class="relative bg-white/[0.03] backdrop-blur-xl rounded-2xl border border-white/10 p-6 sm:p-8">
                    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-400/50 to-transparent"></div>

                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 flex items-center justify-center border border-emerald-500/20">
                            <Shield class="w-8 h-8 text-emerald-400" />
                        </div>
                        <h1 class="text-xl font-bold text-white mb-2">Verifikasi Email</h1>
                        <p class="text-gray-400 text-sm">
                            Masukkan kode OTP yang telah dikirim ke
                        </p>
                        <p class="text-emerald-400 font-medium text-sm mt-1">{{ maskedEmail }}</p>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="flex items-center justify-center gap-2 mb-6" :class="Math.floor(countdown) <= 10 ? 'text-red-400' : 'text-gray-400'">
                        <Clock class="w-4 h-4" />
                        <span class="text-sm font-medium">
                            Kode kedaluwarsa dalam <span class="font-bold">{{ Math.floor(countdown) }}</span> detik
                        </span>
                    </div>

                    <!-- OTP Inputs -->
                    <div class="otp-container flex justify-center gap-2 sm:gap-3 mb-6" @paste="handlePaste">
                        <input
                            v-for="(digit, index) in otpInputs"
                            :key="index"
                            :id="`otp-${index}`"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            class="otp-input w-10 h-12 sm:w-12 sm:h-14 text-center text-xl font-bold bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500/50 transition-all"
                            :class="{ 
                                'border-emerald-500/50 bg-emerald-500/10': digit,
                                'border-red-500/50': errorMessage && countdown <= 0
                            }"
                            :value="digit"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeydown(index, $event)"
                            :disabled="isVerifying || countdown <= 0"
                        />
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="flex items-center gap-2 text-red-400 text-sm mb-4 justify-center">
                        <AlertCircle class="w-4 h-4" />
                        <span>{{ errorMessage }}</span>
                    </div>

                    <!-- Success Message -->
                    <div v-if="successMessage" class="flex items-center gap-2 text-emerald-400 text-sm mb-4 justify-center">
                        <CheckCircle class="w-4 h-4" />
                        <span>{{ successMessage }}</span>
                    </div>

                    <!-- Expired Notice -->
                    <div v-if="countdown <= 0" class="text-center mb-4">
                        <p class="text-red-400 text-sm">Kode OTP telah kedaluwarsa.</p>
                    </div>

                    <!-- Verify Button -->
                    <button
                        @click="verifyOtp"
                        :disabled="!otpComplete || isVerifying || countdown <= 0"
                        class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                    >
                        <CheckCircle class="w-4 h-4" />
                        {{ isVerifying ? 'Memverifikasi...' : 'Verifikasi' }}
                    </button>

                    <!-- Resend OTP -->
                    <div class="text-center mt-6">
                        <p class="text-gray-400 text-sm mb-2">Tidak menerima kode?</p>
                        <button
                            @click="resendOtp"
                            :disabled="resendCountdown > 0 || isResending"
                            class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isResending }" />
                            <span v-if="resendCountdown > 0">Kirim ulang dalam {{ Math.floor(resendCountdown) }}s</span>
                            <span v-else>Kirim Ulang OTP</span>
                        </button>
                    </div>

                    <!-- Back Link -->
                    <div class="text-center mt-6 pt-6 border-t border-white/10">
                        <Link 
                            :href="type === 'register' ? '/register' : '/forgot-password'" 
                            class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-300 text-sm transition-colors"
                        >
                            <ArrowLeft class="w-4 h-4" />
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.otp-input::-webkit-outer-spin-button,
.otp-input::-webkit-inner-spin-button {
    appearance: none;
    -webkit-appearance: none;
    margin: 0;
}
.otp-input[type=number] {
    -moz-appearance: textfield;
}
</style>
