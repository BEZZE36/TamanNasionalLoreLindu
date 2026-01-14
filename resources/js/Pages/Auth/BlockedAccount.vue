<script setup>
import { onMounted, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ShieldOff, Mail, UserPlus, LogOut, RefreshCw, Lock, HelpCircle, Copy, Check, X } from 'lucide-vue-next';

const isLoggingOut = ref(false);
const isCheckingStatus = ref(false);
const showEmailModal = ref(false);
const emailCopied = ref(false);

// Get CSRF token
const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
};

// Contact info
const contactEmail = 'admin@tnll-explore.com';
const contactPhone = '6287751690646';
const emailSubject = 'Permintaan Buka Blokir Akun';
const emailBody = 'Halo Admin,\n\nSaya ingin meminta agar akun saya dibuka blokirnya.\n\nNama: \nEmail: \nAlasan: \n\nTerima kasih.';

// Gmail compose URL (works without email client)
const gmailComposeUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${contactEmail}&su=${encodeURIComponent(emailSubject)}&body=${encodeURIComponent(emailBody)}`;

// Outlook web compose URL
const outlookComposeUrl = `https://outlook.live.com/mail/0/deeplink/compose?to=${contactEmail}&subject=${encodeURIComponent(emailSubject)}&body=${encodeURIComponent(emailBody)}`;

// WhatsApp URL
const whatsappUrl = `https://wa.me/${contactPhone}?text=${encodeURIComponent('Halo Admin, saya ingin meminta dibukakan blokir akun saya.')}`;

// Copy email to clipboard
const copyEmail = async () => {
    try {
        await navigator.clipboard.writeText(contactEmail);
        emailCopied.value = true;
        setTimeout(() => emailCopied.value = false, 2000);
    } catch (e) {
        // Fallback for older browsers
        const input = document.createElement('input');
        input.value = contactEmail;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        emailCopied.value = true;
        setTimeout(() => emailCopied.value = false, 2000);
    }
};

onMounted(() => {
    gsap.fromTo('.blocked-icon', 
        { scale: 0, rotation: -180 }, 
        { scale: 1, rotation: 0, duration: 0.8, ease: 'back.out(1.7)' }
    );
    
    gsap.fromTo('.fade-item', 
        { opacity: 0, y: 20 }, 
        { opacity: 1, y: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out', delay: 0.3 }
    );
    
    // Auto-check status every 10 seconds to redirect when reactivated
    statusCheckInterval = setInterval(checkStatus, 10000);
});

// Auto status check interval
let statusCheckInterval = null;

// Check if user is still blocked
const checkStatus = async () => {
    isCheckingStatus.value = true;
    try {
        const response = await fetch('/api/user/check-status', {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.status === 'active') {
                // User has been reactivated, redirect to homepage
                if (statusCheckInterval) clearInterval(statusCheckInterval);
                window.location.href = '/';
                return;
            }
        }
    } catch (e) {
        console.warn('Failed to check status:', e);
    } finally {
        isCheckingStatus.value = false;
    }
};

// Logout
const handleLogout = async () => {
    isLoggingOut.value = true;
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'text/html',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin',
        });
        window.location.href = '/login';
    } catch (e) {
        isLoggingOut.value = false;
    }
};

// Create new account
const createNewAccount = async () => {
    isLoggingOut.value = true;
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'text/html',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'same-origin',
        });
        window.location.href = '/register';
    } catch (e) {
        isLoggingOut.value = false;
    }
};
</script>

<template>
    <Head title="Akun Diblokir" />
    
    <!-- Fixed full screen container -->
    <div class="fixed inset-0 bg-[#0a0f1a] flex items-center justify-center p-3 sm:p-4 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-red-950/30 via-transparent to-orange-950/20"></div>
            <div class="absolute top-10 left-[5%] w-48 sm:w-72 h-48 sm:h-72 bg-red-500/15 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-10 right-[5%] w-40 sm:w-64 h-40 sm:h-64 bg-orange-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        
        <!-- Main Card -->
        <div class="relative z-10 w-full max-w-sm">
            <!-- Glow -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-red-500/20 to-orange-500/20 rounded-2xl blur-lg opacity-50"></div>
            
            <div class="relative bg-white/[0.06] backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden">
                <!-- Top accent -->
                <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-red-400/50 to-transparent"></div>
                
                <!-- Header -->
                <div class="px-5 pt-6 pb-4 text-center">
                    <!-- Icon -->
                    <div class="blocked-icon inline-flex relative mb-4">
                        <div class="absolute -inset-3 bg-red-500/20 rounded-2xl blur-xl animate-pulse"></div>
                        <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-red-500 to-orange-600 flex items-center justify-center shadow-xl shadow-red-500/30">
                            <Lock class="w-6 h-6 sm:w-7 sm:h-7 text-white" />
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-gradient-to-br from-amber-400 to-orange-500 rounded-md flex items-center justify-center ring-2 ring-[#0a0f1a]">
                            <ShieldOff class="w-2.5 h-2.5 text-white" />
                        </div>
                    </div>
                    
                    <!-- Badge -->
                    <div class="fade-item inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-500/10 border border-red-500/20 mb-3">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                        </span>
                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Akses Dibatasi</span>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="fade-item text-lg sm:text-xl font-black text-white mb-1">Akun Diblokir</h1>
                    <p class="fade-item text-gray-400 text-xs sm:text-sm leading-relaxed">
                        Akun Anda dinonaktifkan oleh administrator.
                    </p>
                </div>
                
                <!-- Content -->
                <div class="px-5 pb-5 space-y-3">
                    <!-- Info Box -->
                    <div class="fade-item p-3 rounded-lg bg-amber-500/5 border border-amber-500/10">
                        <div class="flex gap-2">
                            <HelpCircle class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" />
                            <p class="text-[10px] sm:text-[11px] text-gray-400 leading-relaxed">
                                Hubungi admin untuk membuka blokir akun Anda.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Contact Cards -->
                    <div class="fade-item grid grid-cols-2 gap-2">
                        <!-- Email - Opens modal with options -->
                        <button @click="showEmailModal = true" type="button"
                           class="group p-3 rounded-xl bg-white/[0.03] border border-white/10 hover:bg-blue-500/10 hover:border-blue-500/20 transition-all text-left">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <Mail class="w-4 h-4 text-white" />
                            </div>
                            <p class="text-white font-semibold text-[11px] sm:text-xs">Email</p>
                            <p class="text-gray-500 text-[9px] sm:text-[10px] truncate">admin@tnll-explore.com</p>
                        </button>
                        
                        <!-- WhatsApp -->
                        <a :href="whatsappUrl" target="_blank" rel="noopener noreferrer"
                           class="group p-3 rounded-xl bg-white/[0.03] border border-white/10 hover:bg-green-500/10 hover:border-green-500/20 transition-all text-left">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <p class="text-white font-semibold text-[11px] sm:text-xs">WhatsApp</p>
                            <p class="text-gray-500 text-[9px] sm:text-[10px]">+62 877 5169 0646</p>
                        </a>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="fade-item space-y-2 pt-1">
                        <button @click="checkStatus" :disabled="isCheckingStatus"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs font-medium hover:bg-white/10 transition-all disabled:opacity-50">
                            <RefreshCw :class="['w-3.5 h-3.5', isCheckingStatus ? 'animate-spin' : '']" />
                            {{ isCheckingStatus ? 'Memeriksa...' : 'Cek Status Akun' }}
                        </button>
                        
                        <button @click="createNewAccount" :disabled="isLoggingOut"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0">
                            <UserPlus class="w-3.5 h-3.5" />
                            {{ isLoggingOut ? 'Memproses...' : 'Buat Akun Baru' }}
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Logout -->
            <button @click="handleLogout" :disabled="isLoggingOut"
                class="fade-item mt-4 mx-auto flex items-center gap-1.5 text-gray-500 hover:text-gray-300 text-[11px] transition-colors disabled:opacity-50">
                <LogOut class="w-3 h-3" />
                {{ isLoggingOut ? 'Keluar...' : 'Keluar dari akun' }}
            </button>
        </div>
        
        <!-- Email Modal -->
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showEmailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showEmailModal = false">
                <div class="w-full max-w-xs bg-[#151b2b] rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                        <h3 class="text-sm font-bold text-white">Hubungi via Email</h3>
                        <button @click="showEmailModal = false" class="p-1 rounded-lg hover:bg-white/10 transition-colors">
                            <X class="w-4 h-4 text-gray-400" />
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-4 space-y-3">
                        <!-- Email address with copy -->
                        <div class="flex items-center gap-2 p-3 rounded-xl bg-white/5 border border-white/10">
                            <Mail class="w-4 h-4 text-blue-400 flex-shrink-0" />
                            <span class="flex-1 text-white text-xs font-medium truncate">{{ contactEmail }}</span>
                            <button @click="copyEmail" 
                                class="flex items-center gap-1 px-2 py-1 rounded-md bg-white/10 hover:bg-white/20 transition-colors text-[10px] text-white font-medium">
                                <component :is="emailCopied ? Check : Copy" class="w-3 h-3" />
                                {{ emailCopied ? 'Tersalin!' : 'Salin' }}
                            </button>
                        </div>
                        
                        <p class="text-[10px] text-gray-500 text-center">Pilih layanan email:</p>
                        
                        <!-- Gmail -->
                        <a :href="gmailComposeUrl" target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-red-500/10 hover:border-red-500/20 transition-all">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-semibold text-xs">Gmail</p>
                                <p class="text-gray-500 text-[10px]">Buka di Gmail Web</p>
                            </div>
                        </a>
                        
                        <!-- Outlook -->
                        <a :href="outlookComposeUrl" target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-blue-500/10 hover:border-blue-500/20 transition-all">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 7.387v10.478c0 .23-.08.424-.238.576-.159.153-.358.229-.598.229h-8.23v-6.112l1.465 1.027c.11.075.235.112.375.112s.264-.037.375-.112l6.697-4.689c.095-.063.176-.14.237-.224v-.01-1.275zM23.164 5.33c-.16-.11-.347-.16-.563-.16H14.95l7.035 4.927c.064-.07.115-.147.152-.232.038-.085.057-.176.057-.274V5.593c0-.12-.01-.196-.03-.263zM12.164 21c1.612 0 2.993-.55 4.145-1.65 1.15-1.1 1.726-2.445 1.726-4.035v-9.72H.9c-.25 0-.462.08-.635.24-.173.16-.26.362-.26.608V19.1c0 .257.093.476.284.655.19.18.424.27.706.27h11.17zM7.09 17.11c-.65.51-1.394.767-2.23.767-1.09 0-2-.38-2.73-1.14-.73-.762-1.096-1.713-1.096-2.85 0-1.14.366-2.09 1.097-2.85.73-.76 1.64-1.14 2.73-1.14.835 0 1.58.256 2.23.766v-.584c0-.1.032-.184.095-.25.064-.063.143-.095.238-.095h1.094c.095 0 .174.032.238.094.063.066.094.15.094.25v7.34c0 .1-.03.183-.094.25-.064.062-.143.093-.238.093H9.423c-.095 0-.174-.03-.238-.094-.063-.066-.095-.15-.095-.25v-.31zm-.666-4.28c-.46-.38-1.003-.57-1.625-.57-.73 0-1.345.24-1.842.72-.498.48-.746 1.088-.746 1.82 0 .73.248 1.338.746 1.82.497.48 1.112.72 1.842.72.63 0 1.172-.19 1.63-.57.46-.38.69-.86.69-1.435v-1.07c0-.58-.23-1.055-.69-1.435z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-semibold text-xs">Outlook</p>
                                <p class="text-gray-500 text-[10px]">Buka di Outlook Web</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
