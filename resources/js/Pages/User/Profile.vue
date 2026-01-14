<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { 
    User, Lock, Ticket, Calendar, Home, Save, CheckCircle, AlertCircle, 
    Edit, Mail, X, RefreshCw, Shield, ShieldOff, MapPin, CreditCard, ChevronRight,
    Sparkles, TrendingUp, Star, Phone, Settings, Bell, BellRing, Award, Clock,
    Camera, AtSign, Hash, Globe, Eye, EyeOff, Copy, Check, Trash2, Upload
} from 'lucide-vue-next';
// PushNotificationToggle removed - push notifications are now always active

gsap.registerPlugin(ScrollTrigger);

defineOptions({ layout: PublicLayout });

const { props } = usePage();
const user = computed(() => props.userData || props.auth?.user);
const newsletterSubscription = computed(() => props.newsletterSubscription);
const sectionRef = ref(null);
const showPassword = ref(false);
const showNewPassword = ref(false);
const copiedUsername = ref(false);
const avatarInput = ref(null);
const isUploadingAvatar = ref(false);
let ctx;

// Phone formatting function - format to +62 877 5169 0646
const formatPhoneNumber = (phone) => {
    if (!phone) return '';
    
    // Remove all non-digit characters
    let digits = phone.replace(/\D/g, '');
    
    // Convert 08xxx to 628xxx
    if (digits.startsWith('0')) {
        digits = '62' + digits.slice(1);
    }
    // Ensure it starts with 62
    if (!digits.startsWith('62')) {
        digits = '62' + digits;
    }
    
    // Format: +62 XXX XXXX XXXX (3-4-4 pattern)
    const countryCode = '+62';
    const rest = digits.slice(2); // Remove 62
    
    if (rest.length === 0) return countryCode;
    
    // Split into groups: first 3, then 4, then 4
    let formatted = countryCode;
    if (rest.length > 0) formatted += ' ' + rest.slice(0, 3);
    if (rest.length > 3) formatted += ' ' + rest.slice(3, 7);
    if (rest.length > 7) formatted += ' ' + rest.slice(7, 11);
    
    return formatted;
};

// Parse formatted phone back to digits
const parsePhoneToDigits = (formatted) => {
    return formatted.replace(/\D/g, '');
};

// Animation on mount
onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.hero-content > *', 
            { opacity: 0, y: 30 }, 
            { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power2.out', delay: 0.2 }
        );
        
        gsap.fromTo('.profile-card', 
            { opacity: 0, y: 40 }, 
            { 
                opacity: 1, y: 0, duration: 0.5, stagger: 0.1, ease: 'power2.out',
                scrollTrigger: { trigger: '.profile-section', start: 'top 85%' }
            }
        );
        
        gsap.fromTo('.stat-card', 
            { opacity: 0, scale: 0.9 }, 
            { 
                opacity: 1, scale: 1, duration: 0.4, stagger: 0.08, ease: 'back.out(1.7)',
                scrollTrigger: { trigger: '.stats-grid', start: 'top 90%' }
            }
        );
    }, sectionRef.value);
});

onBeforeUnmount(() => { 
    if (ctx) ctx.revert(); 
    // Clear polling interval
    if (newsletterPollInterval) clearInterval(newsletterPollInterval);
});


// Profile form with phone formatting
const phoneDisplay = ref(formatPhoneNumber(user.value?.phone || ''));

// Real-time validation states
const emailError = ref('');
const phoneError = ref('');
const isCheckingEmail = ref(false);
const isCheckingPhone = ref(false);

// Debounce timer refs
let emailCheckTimeout = null;
let phoneCheckTimeout = null;

const profileForm = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
    username: user.value?.username || '',
});

// Check uniqueness via API
const checkUnique = async (field, value) => {
    if (!value) return;
    
    try {
        const response = await fetch('/api/user/check-unique', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ field, value }),
        });
        
        const data = await response.json();
        
        if (!data.available) {
            if (field === 'email') {
                emailError.value = data.message;
            } else if (field === 'phone') {
                phoneError.value = data.message;
            }
        }
    } catch (e) {
        console.error('Uniqueness check failed', e);
    } finally {
        if (field === 'email') isCheckingEmail.value = false;
        if (field === 'phone') isCheckingPhone.value = false;
    }
};

// Watch email changes with debounce
watch(() => profileForm.email, (newVal, oldVal) => {
    // Clear previous error
    emailError.value = '';
    
    // Skip if same as original or empty
    if (newVal === user.value?.email || !newVal) return;
    
    // Clear previous timeout
    if (emailCheckTimeout) clearTimeout(emailCheckTimeout);
    
    // Debounce check
    isCheckingEmail.value = true;
    emailCheckTimeout = setTimeout(() => {
        checkUnique('email', newVal);
    }, 500);
});

// Watch phone display and update form
watch(phoneDisplay, (newVal) => {
    profileForm.phone = parsePhoneToDigits(newVal);
    
    // Clear previous error
    phoneError.value = '';
    
    const phoneDigits = parsePhoneToDigits(newVal);
    
    // Skip if same as original or empty
    if (phoneDigits === user.value?.phone || !phoneDigits || phoneDigits.length < 10) return;
    
    // Clear previous timeout
    if (phoneCheckTimeout) clearTimeout(phoneCheckTimeout);
    
    // Debounce check
    isCheckingPhone.value = true;
    phoneCheckTimeout = setTimeout(() => {
        checkUnique('phone', phoneDigits);
    }, 500);
});

// Handle phone input with auto-formatting
const handlePhoneInput = (e) => {
    const formatted = formatPhoneNumber(e.target.value);
    phoneDisplay.value = formatted;
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Current password validation states
const currentPasswordError = ref('');
const isCheckingCurrentPassword = ref(false);
const isCurrentPasswordVerified = ref(false);
let currentPasswordCheckTimeout = null;

// Watch current password changes with debounce
watch(() => passwordForm.current_password, (newVal) => {
    // Clear states
    currentPasswordError.value = '';
    isCurrentPasswordVerified.value = false;
    
    // Skip if empty or too short
    if (!newVal || newVal.length < 4) return;
    
    // Clear previous timeout
    if (currentPasswordCheckTimeout) clearTimeout(currentPasswordCheckTimeout);
    
    // Debounce check
    isCheckingCurrentPassword.value = true;
    currentPasswordCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch('/api/user/verify-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ password: newVal }),
            });
            
            const data = await response.json();
            if (data.valid) {
                isCurrentPasswordVerified.value = true;
            } else {
                currentPasswordError.value = data.message;
            }
        } catch (e) {
            console.error('Password verification failed', e);
        } finally {
            isCheckingCurrentPassword.value = false;
        }
    }, 600);
});

// Newsletter forms
const newsletterEmailForm = useForm({
    newsletter_email: newsletterSubscription.value?.email || '',
});

const newsletterSubscribeForm = useForm({
    email: user.value?.email || '',
});

// Newsletter email validation states
const newsletterEmailError = ref('');
const isCheckingNewsletterEmail = ref(false);
let newsletterEmailCheckTimeout = null;

// Watch newsletter email changes with debounce
watch(() => newsletterEmailForm.newsletter_email, (newVal) => {
    // Clear previous error
    newsletterEmailError.value = '';
    
    // Skip if same as original or empty
    if (newVal === newsletterSubscription.value?.email || !newVal) return;
    
    // Clear previous timeout
    if (newsletterEmailCheckTimeout) clearTimeout(newsletterEmailCheckTimeout);
    
    // Debounce check - use newsletter-specific API
    isCheckingNewsletterEmail.value = true;
    newsletterEmailCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch('/api/newsletter/check-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ 
                    email: newVal,
                    current_email: newsletterSubscription.value?.email || null
                }),
            });
            
            const data = await response.json();
            if (!data.available) {
                newsletterEmailError.value = data.message;
            }
        } catch (e) {
            console.error('Newsletter email check failed', e);
        } finally {
            isCheckingNewsletterEmail.value = false;
        }
    }, 500);
});

const isEditingNewsletterEmail = ref(false);
const activeTab = ref('profile');

// Modal states for confirmation dialogs
const showUnsubscribeModal = ref(false);
const showDeleteAvatarModal = ref(false);

// Newsletter status polling for real-time admin-disable detection
let newsletterPollInterval = null;
const localNewsletterStatus = ref({
    is_active: newsletterSubscription.value?.is_active ?? false,
    disabled_by_admin: newsletterSubscription.value?.disabled_by_admin ?? false,
    email: newsletterSubscription.value?.email ?? '',
});

// Poll for newsletter status when on newsletter tab
const pollNewsletterStatus = async () => {
    try {
        const response = await fetch('/api/newsletter/status', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        
        // Update local status
        if (data.has_subscription) {
            localNewsletterStatus.value = {
                is_active: data.is_active,
                disabled_by_admin: data.disabled_by_admin,
                email: data.email,
            };
        }
    } catch (e) {
        console.error('Newsletter status poll failed', e);
    }
};

// Start/stop polling based on active tab
watch(activeTab, (newTab) => {
    if (newTab === 'newsletter') {
        // Start polling every 5 seconds
        pollNewsletterStatus(); // Initial poll
        newsletterPollInterval = setInterval(pollNewsletterStatus, 5000);
    } else {
        // Stop polling
        if (newsletterPollInterval) {
            clearInterval(newsletterPollInterval);
            newsletterPollInterval = null;
        }
    }
});


const submitProfile = () => {
    profileForm.put('/profile', { 
        preserveScroll: true,
        preserveState: true, // Keep errors visible on validation failure
        onSuccess: () => {
            // Reload page to get fresh data after successful update
            window.location.reload();
        },
        onError: (errors) => {
            // Errors are automatically set to profileForm.errors
            console.log('Profile validation errors:', errors);
        },
    });
};

const submitPassword = () => {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        // Don't use preserveState: false - it clears errors 
        onSuccess: () => {
            passwordForm.reset();
            // Reload page to refresh user data
            window.location.reload();
        },
        onError: () => {
            // Errors will be displayed in form
        },
    });
};

const subscribeNewsletter = () => {
    newsletterSubscribeForm.post('/newsletter/subscribe', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            newsletterSubscribeForm.reset();
            // Update local state immediately
            localNewsletterStatus.value = {
                is_active: true,
                disabled_by_admin: false,
                email: newsletterSubscribeForm.email || props.userData?.email || '',
            };
        },
    });
};

const openUnsubscribeModal = () => {
    showUnsubscribeModal.value = true;
};

const closeUnsubscribeModal = () => {
    showUnsubscribeModal.value = false;
};

const confirmUnsubscribe = () => {
    // Update local state immediately (optimistic update)
    localNewsletterStatus.value.is_active = false;
    localNewsletterStatus.value.disabled_by_admin = false;
    
    router.post('/newsletter/unsubscribe', {}, { 
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on error
            localNewsletterStatus.value.is_active = true;
        }
    });
    closeUnsubscribeModal();
};

const resubscribeNewsletter = () => {
    // Update local state immediately
    localNewsletterStatus.value.is_active = true;
    localNewsletterStatus.value.disabled_by_admin = false;
    
    router.post('/newsletter/resubscribe', {}, { 
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on error
            localNewsletterStatus.value.is_active = false;
        }
    });
};

const submitNewsletterEmail = () => {
    const newEmail = newsletterEmailForm.newsletter_email;
    newsletterEmailForm.put('/newsletter/update-email', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isEditingNewsletterEmail.value = false;
            // Update local state with new email
            localNewsletterStatus.value.email = newEmail;
        },
    });
};

// Copy username to clipboard
const copyUsername = async () => {
    if (user.value?.username) {
        await navigator.clipboard.writeText('@' + user.value.username);
        copiedUsername.value = true;
        setTimeout(() => copiedUsername.value = false, 2000);
    }
};

// Avatar upload
const triggerAvatarUpload = () => {
    avatarInput.value?.click();
};

const handleAvatarChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file
    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
        alert('Format file harus JPEG, PNG, JPG, atau WEBP');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        return;
    }
    
    isUploadingAvatar.value = true;
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    router.post('/profile/avatar', formData, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false, // Refresh to show new avatar
        onFinish: () => {
            isUploadingAvatar.value = false;
            e.target.value = '';
        }
    });
};

const openDeleteAvatarModal = () => {
    showDeleteAvatarModal.value = true;
};

const closeDeleteAvatarModal = () => {
    showDeleteAvatarModal.value = false;
};

const confirmDeleteAvatar = () => {
    router.delete('/profile/avatar', { 
        preserveScroll: true,
        preserveState: false,
    });
    closeDeleteAvatarModal();
};

// Stats
const stats = computed(() => ({
    totalBookings: props.stats?.totalBookings || 0,
    completedBookings: props.stats?.completedBookings || 0,
    totalSpent: props.stats?.totalSpent || 'Rp 0',
    memberSince: props.stats?.memberSince || '-',
}));

// Tabs configuration
const tabs = [
    { id: 'profile', label: 'Profil', icon: User, color: 'emerald' },
    { id: 'security', label: 'Keamanan', icon: Shield, color: 'amber' },
    { id: 'newsletter', label: 'Newsletter', icon: Mail, color: 'cyan' },
];

// Get initials
const getInitials = (name) => {
    if (!name) return 'U';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return parts[0].charAt(0) + parts[1].charAt(0);
    }
    return name.charAt(0);
};

// Password validation computed properties
const isPasswordSameAsCurrent = computed(() => {
    return passwordForm.current_password && passwordForm.password && 
           passwordForm.current_password === passwordForm.password;
});

const isPasswordConfirmationMismatch = computed(() => {
    return passwordForm.password && passwordForm.password_confirmation && 
           passwordForm.password !== passwordForm.password_confirmation;
});

const isPasswordStrong = computed(() => {
    const p = passwordForm.password;
    if (!p) return false;
    return p.length >= 8 && 
           /[A-Z]/.test(p) && 
           /[a-z]/.test(p) && 
           /[0-9]/.test(p) && 
           /[@#?!$%^&*_\-+=]/.test(p);
});

const canSubmitPassword = computed(() => {
    return passwordForm.current_password && 
           passwordForm.password && 
           passwordForm.password_confirmation &&
           isCurrentPasswordVerified.value &&
           !currentPasswordError.value &&
           !isPasswordSameAsCurrent.value &&
           !isPasswordConfirmationMismatch.value &&
           isPasswordStrong.value;
});
</script>

<template>
    <Head title="Profil Saya" />

    <div ref="sectionRef">
        <!-- Premium Hero Section -->
        <section class="relative pt-28 pb-28 overflow-hidden">
            <!-- Animated Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700">
                <div class="absolute inset-0 bg-[url('/images/pattern-hero.svg')] opacity-10"></div>
            </div>
            
            <!-- Floating Orbs -->
            <div class="absolute top-20 right-[15%] w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-10 left-[10%] w-64 h-64 bg-cyan-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-teal-500/10 rounded-full blur-3xl"></div>
            
            <!-- Decorative Particles -->
            <div class="absolute top-32 left-20 hidden lg:block">
                <div class="w-3 h-3 rounded-full bg-white/30 animate-bounce" style="animation-delay: 0.2s"></div>
            </div>
            <div class="absolute top-40 left-32 hidden lg:block">
                <div class="w-2 h-2 rounded-full bg-cyan-300/40 animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
            <div class="absolute bottom-20 right-32 hidden lg:block">
                <div class="w-2 h-2 rounded-full bg-cyan-300/50 animate-bounce" style="animation-delay: 0.5s"></div>
            </div>
            
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="hero-content flex flex-col items-center text-center">
                    <!-- Hidden file input -->
                    <input type="file" ref="avatarInput" @change="handleAvatarChange" accept=".jpg,.jpeg,.png,.webp" class="hidden">
                    
                    <!-- Avatar with Glow Effect -->
                    <div class="relative group mb-5">
                        <div class="absolute -inset-2 bg-gradient-to-r from-cyan-400 via-emerald-400 to-teal-400 rounded-3xl blur opacity-50 group-hover:opacity-75 transition duration-500 animate-pulse"></div>
                        <div @click="triggerAvatarUpload" 
                            class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-2xl bg-white/20 backdrop-blur-xl flex items-center justify-center text-white text-4xl sm:text-5xl font-black shadow-2xl ring-2 ring-white/30 overflow-hidden cursor-pointer">
                            <!-- Always show initials as fallback background -->
                            <span class="relative z-0 bg-gradient-to-br from-white to-white/80 bg-clip-text text-transparent">
                                {{ getInitials(user?.name) }}
                            </span>
                            <!-- Show actual avatar if exists (local or Google) - on top of initials -->
                            <img v-if="user?.avatar_url && !user?.avatar_url.includes('ui-avatars')" 
                                :src="user.avatar_url" 
                                :alt="user?.name"
                                class="absolute inset-0 w-full h-full object-cover z-20">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent z-10"></div>
                            
                            <!-- Upload overlay -->
                            <div class="absolute inset-0 rounded-2xl bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center z-20">
                                <div v-if="isUploadingAvatar" class="text-white">
                                    <div class="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                </div>
                                <Camera v-else class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                            </div>
                        </div>
                        
                        <!-- Online Status Badge -->
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl border-3 border-white flex items-center justify-center shadow-lg">
                            <CheckCircle class="w-4 h-4 text-white" />
                        </div>
                        
                        <!-- Delete avatar button -->
                        <button v-if="user?.avatar && !user?.avatar_url?.includes('ui-avatars')" 
                            @click.stop="openDeleteAvatarModal"
                            class="absolute -top-2 -left-2 w-7 h-7 bg-red-500 hover:bg-red-600 rounded-lg flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-30">
                            <Trash2 class="w-3.5 h-3.5 text-white" />
                        </button>
                    </div>

                    
                    <!-- User Info -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-sm border border-white/20 text-white/90 mb-3 hover:bg-white/20 transition-all duration-300">
                        <Shield class="w-3 h-3 text-cyan-300" />
                        <span class="text-[11px] font-semibold tracking-wide uppercase">Member Aktif</span>
                        <Award class="w-3 h-3 text-amber-400" />
                    </div>
                    
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                        {{ user?.name }}
                    </h1>
                    
                    <!-- Username with copy -->
                    <button v-if="user?.username" @click="copyUsername" 
                        class="flex items-center gap-1.5 text-white/70 hover:text-white text-sm mb-3 transition-colors group">
                        <AtSign class="w-3.5 h-3.5" />
                        <span class="font-medium">{{ user?.username }}</span>
                        <component :is="copiedUsername ? Check : Copy" 
                            :class="['w-3.5 h-3.5 transition-all', copiedUsername ? 'text-emerald-400' : 'opacity-0 group-hover:opacity-100']" />
                    </button>
                    
                    <p class="text-white/60 text-xs sm:text-sm flex items-center gap-2">
                        <Mail class="w-3.5 h-3.5" />
                        {{ user?.email }}
                    </p>
                    
                    <!-- Quick Stats on Hero -->
                    <div class="flex items-center gap-3 mt-6">
                        <div class="text-center px-5 py-3 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                            <p class="text-2xl font-black text-white">{{ stats.totalBookings }}</p>
                            <p class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Pesanan</p>
                        </div>
                        <div class="text-center px-5 py-3 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                            <p class="text-2xl font-black text-white">{{ stats.completedBookings }}</p>
                            <p class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Selesai</p>
                        </div>
                        <div class="text-center px-5 py-3 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 hover:bg-white/15 transition-all duration-300">
                            <p class="text-sm font-bold text-white">{{ stats.memberSince }}</p>
                            <p class="text-[10px] text-white/70 uppercase tracking-wider font-medium">Member</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Wave Bottom -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" class="w-full h-auto">
                    <path d="M0,60 C240,120 480,0 720,60 C960,120 1200,0 1440,60 L1440,120 L0,120 Z" fill="white" fill-opacity="0.1"/>
                    <path d="M0,80 C240,140 480,20 720,80 C960,140 1200,20 1440,80 L1440,120 L0,120 Z" fill="white"/>
                </svg>
            </div>
        </section>

        <!-- Main Content Section -->
        <section class="profile-section py-12 md:py-16 bg-gradient-to-b from-white via-gray-50/30 to-white relative overflow-hidden">
            <!-- Background Orbs -->
            <div class="absolute top-20 right-[10%] w-72 h-72 bg-emerald-100/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-20 left-[5%] w-64 h-64 bg-cyan-100/30 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <Transition name="slide-fade">
                    <div v-if="$page.props.flash?.success" 
                         class="flex items-center gap-2.5 p-3.5 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 rounded-xl text-emerald-700 mb-6 shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                            <CheckCircle class="w-4 h-4 text-white" />
                        </div>
                        <span class="text-xs font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </Transition>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                    <!-- Sidebar Navigation -->
                    <div class="lg:col-span-1">
                        <div class="profile-card bg-white rounded-xl shadow-sm border border-gray-100 p-4 sticky top-24">
                            <nav class="space-y-1">
                                <button 
                                    v-for="tab in tabs" 
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-left transition-all duration-300',
                                        activeTab === tab.id 
                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25' 
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                    ]"
                                >
                                    <component :is="tab.icon" class="w-4 h-4" />
                                    <span class="text-xs font-semibold">{{ tab.label }}</span>
                                </button>
                            </nav>

                            <!-- Quick Links -->
                            <div class="mt-5 pt-5 border-t border-gray-100">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2.5 px-2">Menu Cepat</p>
                                <Link href="/dashboard" class="flex items-center gap-2.5 px-3.5 py-2 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all duration-300">
                                    <Home class="w-4 h-4" />
                                    <span class="text-xs font-medium">Dashboard</span>
                                </Link>
                                <Link href="/my-bookings" class="flex items-center gap-2.5 px-3.5 py-2 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all duration-300">
                                    <Ticket class="w-4 h-4" />
                                    <span class="text-xs font-medium">Tiket Saya</span>
                                </Link>
                            </div>

                            <!-- Account Info Card -->
                            <div class="mt-5 pt-5 border-t border-gray-100">
                                <div class="p-3 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100/50">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase mb-2">Info Akun</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 text-[11px]">
                                            <Calendar class="w-3 h-3 text-gray-400" />
                                            <span class="text-gray-600">Member sejak <strong>{{ stats.memberSince }}</strong></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px]">
                                            <CreditCard class="w-3 h-3 text-gray-400" />
                                            <span class="text-gray-600">Total Pembelian : <strong>{{ stats.totalSpent }}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Area -->
                    <div class="lg:col-span-3 space-y-5">
                        <!-- Profile Tab -->
                        <Transition name="fade" mode="out-in">
                            <div v-if="activeTab === 'profile'" key="profile">
                                <div class="profile-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                    <!-- Card Header -->
                                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-emerald-100/50">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg">
                                                <User class="w-4 h-4 text-white" />
                                            </div>
                                            <div>
                                                <h2 class="text-sm font-bold text-gray-900">Informasi Pribadi</h2>
                                                <p class="text-[11px] text-gray-500">Kelola data profil Anda</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form -->
                                    <form @submit.prevent="submitProfile" class="p-5 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Name -->
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                    <User class="w-3 h-3" /> Nama Lengkap
                                                </label>
                                                <input type="text" v-model="profileForm.name"
                                                    class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300"
                                                    :class="{ 'border-red-400': profileForm.errors.name }" required>
                                                <p v-if="profileForm.errors.name" class="text-red-500 text-[10px] mt-1">{{ profileForm.errors.name }}</p>
                                            </div>
                                            
                                            <!-- Email -->
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                    <Mail class="w-3 h-3" /> Email
                                                    <span v-if="isCheckingEmail" class="ml-1">
                                                        <RefreshCw class="w-3 h-3 animate-spin text-gray-400" />
                                                    </span>
                                                </label>
                                                <input type="email" v-model="profileForm.email"
                                                    class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300"
                                                    :class="{ 'border-red-400': profileForm.errors.email || emailError, 'bg-gray-50': user?.google_id }"
                                                    :readonly="user?.google_id" required>
                                                <p v-if="profileForm.errors.email" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ profileForm.errors.email }}
                                                </p>
                                                <p v-else-if="emailError" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ emailError }}
                                                </p>
                                                <p v-else-if="user?.google_id" class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                                    <Globe class="w-3 h-3" />
                                                    Email dari akun Google tidak dapat diubah
                                                </p>
                                            </div>
                                            
                                            <!-- Phone with +62 format -->
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                    <Phone class="w-3 h-3" /> Nomor HP
                                                    <span v-if="isCheckingPhone" class="ml-1">
                                                        <RefreshCw class="w-3 h-3 animate-spin text-gray-400" />
                                                    </span>
                                                </label>
                                                <div class="relative">
                                                    <input type="tel" 
                                                        v-model="phoneDisplay"
                                                        @input="handlePhoneInput"
                                                        placeholder="+62 877 5169 0646"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all duration-300"
                                                        :class="{ 'border-red-400': profileForm.errors.phone || phoneError }">
                                                </div>
                                                <p v-if="profileForm.errors.phone" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ profileForm.errors.phone }}
                                                </p>
                                                <p v-else-if="phoneError" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ phoneError }}
                                                </p>
                                                <p v-else class="text-[10px] text-gray-400 mt-1">Format: +62 XXX XXXX XXXX</p>
                                            </div>
                                            
                                            <!-- Username with @ symbol -->
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                    <AtSign class="w-3 h-3" /> Username
                                                </label>
                                                <div class="relative">
                                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">@</span>
                                                    <input type="text" v-model="profileForm.username"
                                                        class="w-full pl-8 pr-3.5 py-2.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-600"
                                                        readonly>
                                                </div>
                                                <p class="text-[10px] text-gray-400 mt-1">Username tidak dapat diubah</p>
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-3 border-t border-gray-100">
                                            <button type="submit" :disabled="profileForm.processing || emailError || phoneError || isCheckingEmail || isCheckingPhone"
                                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-xs font-semibold rounded-lg hover:shadow-lg hover:shadow-emerald-500/25 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:translate-y-0">
                                                <Save class="w-3.5 h-3.5" />
                                                {{ profileForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </Transition>

                        <!-- Security Tab -->
                        <Transition name="fade" mode="out-in">
                            <div v-if="activeTab === 'security'" key="security">
                                <div v-if="!user?.google_id" class="profile-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                    <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100/50">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                                                <Lock class="w-4 h-4 text-white" />
                                            </div>
                                            <div>
                                                <h2 class="text-sm font-bold text-gray-900">Ubah Password</h2>
                                                <p class="text-[11px] text-gray-500">Jaga keamanan akun Anda</p>
                                            </div>
                                        </div>
                                    </div>

                                    <form @submit.prevent="submitPassword" class="p-5 space-y-4">
                                        <!-- Current Password -->
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                Password Saat Ini
                                                <span v-if="isCheckingCurrentPassword" class="ml-1">
                                                    <RefreshCw class="w-3 h-3 animate-spin text-gray-400" />
                                                </span>
                                                <span v-else-if="isCurrentPasswordVerified" class="ml-1">
                                                    <CheckCircle class="w-3 h-3 text-emerald-500" />
                                                </span>
                                            </label>
                                            <div class="relative">
                                                <input :type="showPassword ? 'text' : 'password'" v-model="passwordForm.current_password"
                                                    class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300"
                                                    :class="{ 
                                                        'border-red-400': passwordForm.errors.current_password || currentPasswordError,
                                                        'border-emerald-400': isCurrentPasswordVerified
                                                    }" required>
                                                <button type="button" @click="showPassword = !showPassword" 
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                    <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <p v-if="passwordForm.errors.current_password" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                <AlertCircle class="w-3 h-3" />
                                                {{ passwordForm.errors.current_password }}
                                            </p>
                                            <p v-else-if="currentPasswordError" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                <AlertCircle class="w-3 h-3" />
                                                {{ currentPasswordError }}
                                            </p>
                                            <p v-else-if="isCurrentPasswordVerified" class="text-emerald-500 text-[10px] mt-1 flex items-center gap-1">
                                                <CheckCircle class="w-3 h-3" />
                                                Password terverifikasi
                                            </p>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5">Password Baru</label>
                                                <div class="relative">
                                                    <input :type="showNewPassword ? 'text' : 'password'" v-model="passwordForm.password"
                                                        class="w-full px-3.5 py-2.5 pr-10 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300"
                                                        :class="{ 'border-red-400': passwordForm.errors.password || isPasswordSameAsCurrent }" required>
                                                    <button type="button" @click="showNewPassword = !showNewPassword" 
                                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                        <component :is="showNewPassword ? EyeOff : Eye" class="w-4 h-4" />
                                                    </button>
                                                </div>
                                                <p v-if="passwordForm.errors.password" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ passwordForm.errors.password }}
                                                </p>
                                                <p v-else-if="isPasswordSameAsCurrent" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    Password baru tidak boleh sama dengan password saat ini
                                                </p>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5">Konfirmasi Password</label>
                                                <input :type="showNewPassword ? 'text' : 'password'" v-model="passwordForm.password_confirmation"
                                                    class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-300"
                                                    :class="{ 'border-red-400': passwordForm.errors.password_confirmation || isPasswordConfirmationMismatch }" required>
                                                <p v-if="passwordForm.errors.password_confirmation" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    {{ passwordForm.errors.password_confirmation }}
                                                </p>
                                                <p v-else-if="isPasswordConfirmationMismatch" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                    <AlertCircle class="w-3 h-3" />
                                                    Konfirmasi password tidak cocok dengan password baru
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Password Requirements -->
                                        <div class="p-3 bg-gray-50 rounded-lg">
                                            <p class="text-[10px] font-semibold text-gray-500 mb-2">Syarat Password:</p>
                                            <ul class="text-[10px] text-gray-500 space-y-1">
                                                <li class="flex items-center gap-1.5">
                                                    <div :class="['w-1.5 h-1.5 rounded-full', passwordForm.password.length >= 8 ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                                                    Minimal 8 karakter
                                                </li>
                                                <li class="flex items-center gap-1.5">
                                                    <div :class="['w-1.5 h-1.5 rounded-full', /[A-Z]/.test(passwordForm.password) ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                                                    Mengandung huruf besar
                                                </li>
                                                <li class="flex items-center gap-1.5">
                                                    <div :class="['w-1.5 h-1.5 rounded-full', /[a-z]/.test(passwordForm.password) ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                                                    Mengandung huruf kecil
                                                </li>
                                                <li class="flex items-center gap-1.5">
                                                    <div :class="['w-1.5 h-1.5 rounded-full', /[0-9]/.test(passwordForm.password) ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                                                    Mengandung angka
                                                </li>
                                                <li class="flex items-center gap-1.5">
                                                    <div :class="['w-1.5 h-1.5 rounded-full', /[@#?!$%^&*_\-+=]/.test(passwordForm.password) ? 'bg-emerald-500' : 'bg-gray-300']"></div>
                                                    Mengandung karakter unik (@#?!$%^&amp;*_-+=)
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="flex justify-end pt-3 border-t border-gray-100">
                                            <button type="submit" :disabled="passwordForm.processing || !canSubmitPassword"
                                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-semibold rounded-lg hover:shadow-lg hover:shadow-amber-500/25 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none disabled:hover:translate-y-0">
                                                <Lock class="w-3.5 h-3.5" />
                                                {{ passwordForm.processing ? 'Menyimpan...' : 'Ubah Password' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Google Account Notice -->
                                <div v-else class="profile-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                    <div class="p-8 text-center">
                                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                            <Globe class="w-8 h-8 text-white" />
                                        </div>
                                        <h3 class="text-base font-bold text-gray-900 mb-2">Akun Google</h3>
                                        <p class="text-xs text-gray-500 max-w-sm mx-auto">
                                            Keamanan akun Anda dikelola oleh Google. Untuk mengubah password, silakan kelola langsung melalui akun Google Anda.
                                        </p>
                                        <a href="https://myaccount.google.com/security" target="_blank" 
                                            class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                                            <Settings class="w-3.5 h-3.5" />
                                            Kelola Akun Google
                                            <ChevronRight class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <!-- Newsletter Tab -->
                        <Transition name="fade" mode="out-in">
                            <div v-if="activeTab === 'newsletter'" key="newsletter">
                                <div class="profile-card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                    <div class="px-5 py-4 bg-gradient-to-r from-cyan-50 to-blue-50 border-b border-cyan-100/50">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg">
                                                <Mail class="w-4 h-4 text-white" />
                                            </div>
                                            <div>
                                                <h2 class="text-sm font-bold text-gray-900">Newsletter</h2>
                                                <p class="text-[11px] text-gray-500">Kelola langganan newsletter</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-5">
                                        <!-- Active Subscription -->
                                        <div v-if="localNewsletterStatus.is_active || newsletterSubscription?.is_active" class="space-y-4">
                                            <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 rounded-xl">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                                                        <CheckCircle class="w-3.5 h-3.5 text-white" />
                                                    </div>
                                                    <span class="text-xs font-bold text-emerald-700">Langganan Aktif</span>
                                                </div>
                                                <p class="text-[11px] text-emerald-600">
                                                    Anda akan menerima newsletter di: 
                                                    <strong class="font-semibold">{{ localNewsletterStatus.email || newsletterSubscription?.email }}</strong>
                                                </p>
                                            </div>

                                            <div v-if="isEditingNewsletterEmail">
                                                <form @submit.prevent="submitNewsletterEmail" class="space-y-3">
                                                    <div>
                                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 flex items-center gap-1">
                                                            Email Newsletter Baru
                                                            <span v-if="isCheckingNewsletterEmail" class="ml-1">
                                                                <RefreshCw class="w-3 h-3 animate-spin text-gray-400" />
                                                            </span>
                                                        </label>
                                                        <input type="email" v-model="newsletterEmailForm.newsletter_email"
                                                            class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                                                            :class="{ 'border-red-400': newsletterEmailError }" required>
                                                        <p v-if="newsletterEmailError" class="text-red-500 text-[10px] mt-1 flex items-center gap-1">
                                                            <AlertCircle class="w-3 h-3" />
                                                            {{ newsletterEmailError }}
                                                        </p>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button type="submit" 
                                                            :disabled="newsletterEmailError || isCheckingNewsletterEmail"
                                                            class="inline-flex items-center gap-1 px-3 py-2 bg-cyan-600 text-white text-xs font-semibold rounded-lg hover:bg-cyan-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <Save class="w-3 h-3" /> Simpan
                                                        </button>
                                                        <button type="button" @click="isEditingNewsletterEmail = false; newsletterEmailError = ''" class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                                                            Batal
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div v-else class="flex flex-wrap gap-2">
                                                <button @click="isEditingNewsletterEmail = true; newsletterEmailForm.newsletter_email = localNewsletterStatus.email || newsletterSubscription?.email"
                                                    class="inline-flex items-center gap-1 px-3 py-2 bg-cyan-600 text-white text-xs font-semibold rounded-lg hover:bg-cyan-700 transition-colors">
                                                    <Edit class="w-3 h-3" /> Ganti Email
                                                </button>
                                                <button @click="openUnsubscribeModal"
                                                    class="inline-flex items-center gap-1 px-3 py-2 bg-red-100 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-200 transition-colors">
                                                    <X class="w-3 h-3" /> Berhenti Langganan
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Inactive Subscription -->
                                        <div v-else-if="(newsletterSubscription && !localNewsletterStatus.is_active) || (newsletterSubscription && !newsletterSubscription.is_active)" class="space-y-4">
                                            <!-- Admin Disabled State -->
                                            <div v-if="localNewsletterStatus.disabled_by_admin || newsletterSubscription.disabled_by_admin" 
                                                class="p-4 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200/60 rounded-xl">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center">
                                                        <ShieldOff class="w-3.5 h-3.5 text-white" />
                                                    </div>
                                                    <span class="text-xs font-bold text-red-700">Dinonaktifkan oleh Admin</span>
                                                </div>
                                                <p class="text-[11px] text-red-600">
                                                    Langganan newsletter Anda telah dinonaktifkan oleh administrator. 
                                                    Hubungi admin untuk mengaktifkan kembali.
                                                </p>
                                            </div>
                                            <!-- User Unsubscribed State -->
                                            <div v-else class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <AlertCircle class="w-4 h-4 text-gray-500" />
                                                    <span class="text-xs font-bold text-gray-700">Tidak Aktif</span>
                                                </div>
                                                <p class="text-[11px] text-gray-600">Langganan newsletter Anda tidak aktif.</p>
                                            </div>
                                            <!-- Resubscribe button - disabled if admin disabled -->
                                            <button 
                                                @click="resubscribeNewsletter"
                                                :disabled="localNewsletterStatus.disabled_by_admin || newsletterSubscription.disabled_by_admin"
                                                :class="[
                                                    'inline-flex items-center gap-1 px-4 py-2.5 text-xs font-semibold rounded-lg transition-colors',
                                                    (localNewsletterStatus.disabled_by_admin || newsletterSubscription.disabled_by_admin)
                                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        : 'bg-cyan-600 text-white hover:bg-cyan-700'
                                                ]">
                                                <RefreshCw class="w-3.5 h-3.5" /> 
                                                {{ (localNewsletterStatus.disabled_by_admin || newsletterSubscription.disabled_by_admin) ? 'Menunggu Admin' : 'Aktifkan Kembali' }}
                                            </button>
                                        </div>

                                        <!-- No Subscription -->
                                        <div v-else class="space-y-4">
                                            <div class="p-4 bg-gradient-to-r from-cyan-50 to-blue-50 border border-cyan-200/60 rounded-xl">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                                                        <Bell class="w-5 h-5 text-white" />
                                                    </div>
                                                    <div>
                                                        <h4 class="text-xs font-bold text-gray-900 mb-1">Dapatkan Update Terbaru!</h4>
                                                        <p class="text-[11px] text-gray-600">Berlangganan newsletter untuk mendapatkan informasi destinasi baru, promo spesial, dan tips perjalanan menarik.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <form @submit.prevent="subscribeNewsletter" class="space-y-3">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-gray-600 mb-1.5">Email untuk Newsletter</label>
                                                    <input type="email" v-model="newsletterSubscribeForm.email" placeholder="email@example.com"
                                                        class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-gray-200 focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500" required>
                                                </div>
                                                <button type="submit" :disabled="newsletterSubscribeForm.processing"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50">
                                                    <Mail class="w-3.5 h-3.5" /> 
                                                    {{ newsletterSubscribeForm.processing ? 'Memproses...' : 'Berlangganan Sekarang' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>


                    </div>
                </div>
            </div>
        </section>

        <!-- Unsubscribe Newsletter Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showUnsubscribeModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div @click="closeUnsubscribeModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all duration-300">
                        <!-- Icon -->
                        <div class="flex justify-center mb-4">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg">
                                <Mail class="w-7 h-7 text-white" />
                            </div>
                        </div>
                        
                        <!-- Title & Description -->
                        <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Berhenti Berlangganan?</h3>
                        <p class="text-sm text-gray-600 text-center mb-6">
                            Apakah Anda yakin ingin berhenti berlangganan newsletter? Anda tidak akan menerima update terbaru dari kami.
                        </p>
                        
                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button @click="closeUnsubscribeModal"
                                class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                                Batal
                            </button>
                            <button @click="confirmUnsubscribe"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white text-sm font-semibold rounded-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                                Ya, Berhenti
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Avatar Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showDeleteAvatarModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div @click="closeDeleteAvatarModal" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                    
                    <!-- Modal Content -->
                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all duration-300">
                        <!-- Icon -->
                        <div class="flex justify-center mb-4">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg">
                                <Trash2 class="w-7 h-7 text-white" />
                            </div>
                        </div>
                        
                        <!-- Title & Description -->
                        <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Hapus Foto Profil?</h3>
                        <p class="text-sm text-gray-600 text-center mb-6">
                            Apakah Anda yakin ingin menghapus foto profil Anda? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        
                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button @click="closeDeleteAvatarModal"
                                class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition-colors">
                                Batal
                            </button>
                            <button @click="confirmDeleteAvatar"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white text-sm font-semibold rounded-lg hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s ease-in; }
.slide-fade-enter-from { transform: translateY(-10px); opacity: 0; }
.slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }

.fade-enter-active { transition: all 0.3s ease-out; }
.fade-leave-active { transition: all 0.2s ease-in; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }

/* Modal transitions */
.modal-enter-active { transition: all 0.3s ease-out; }
.modal-leave-active { transition: all 0.2s ease-in; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.95) translateY(10px); }
</style>

