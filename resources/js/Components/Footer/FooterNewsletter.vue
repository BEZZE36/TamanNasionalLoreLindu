<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import { Mail, Send, CheckCircle, Loader2, Bell, LogIn, Lock, X, ShieldOff, RefreshCw } from 'lucide-vue-next';

const page = usePage();
const isLoggedIn = computed(() => !!page.props.auth?.user);
const newsletterSubscription = computed(() => page.props.auth?.newsletterSubscription);

// Local reactive state for instant UI updates
const localSubscriptionState = ref({
    is_active: newsletterSubscription.value?.is_active ?? false,
    disabled_by_admin: newsletterSubscription.value?.disabled_by_admin ?? false,
    email: newsletterSubscription.value?.email ?? '',
});

// Sync with page props (in case of navigation)
watch(() => page.props.auth?.newsletterSubscription, (newVal) => {
    if (newVal) {
        localSubscriptionState.value = {
            is_active: newVal.is_active ?? false,
            disabled_by_admin: newVal.disabled_by_admin ?? false,
            email: newVal.email ?? '',
        };
    }
}, { deep: true });

// Poll for newsletter status to detect admin changes (every 10 seconds)
let pollInterval = null;

const pollNewsletterStatus = async () => {
    if (!isLoggedIn.value) return;
    
    try {
        const response = await fetch('/api/newsletter/status', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        
        if (data.has_subscription) {
            localSubscriptionState.value = {
                is_active: data.is_active ?? false,
                disabled_by_admin: data.disabled_by_admin ?? false,
                email: data.email ?? localSubscriptionState.value.email,
            };
        }
    } catch (e) {
        console.error('Newsletter status poll failed', e);
    }
};

onMounted(() => {
    if (isLoggedIn.value && newsletterSubscription.value) {
        // Initial poll
        pollNewsletterStatus();
        // Poll every 10 seconds
        pollInterval = setInterval(pollNewsletterStatus, 10000);
    }
});

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// Computed for easier template access
const isSubscribed = computed(() => localSubscriptionState.value.is_active);
const isDisabledByAdmin = computed(() => localSubscriptionState.value.disabled_by_admin);
const hasSubscription = computed(() => !!newsletterSubscription.value || localSubscriptionState.value.email);

const form = useForm({
    email: '',
});

const isSuccess = ref(false);
const showUnsubscribeModal = ref(false);
const isProcessing = ref(false);

const submit = () => {
    isProcessing.value = true;
    form.post('/newsletter/subscribe', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSuccess.value = true;
            // Update local state immediately
            localSubscriptionState.value.is_active = true;
            localSubscriptionState.value.disabled_by_admin = false;
            localSubscriptionState.value.email = form.email;
            form.reset();
            setTimeout(() => {
                isSuccess.value = false;
            }, 5000);
        },
        onFinish: () => {
            isProcessing.value = false;
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
    // Optimistic update
    localSubscriptionState.value.is_active = false;
    
    router.post('/newsletter/unsubscribe', {}, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on error
            localSubscriptionState.value.is_active = true;
        }
    });
    closeUnsubscribeModal();
};

const resubscribe = () => {
    // Optimistic update
    localSubscriptionState.value.is_active = true;
    localSubscriptionState.value.disabled_by_admin = false;
    
    router.post('/newsletter/resubscribe', {}, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on error
            localSubscriptionState.value.is_active = false;
        }
    });
};
</script>

<template>
    <div class="footer-newsletter">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-3 lg:gap-4">
            <!-- Text Section -->
            <div class="flex items-start gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0">
                    <Bell class="w-4 h-4 text-emerald-400" />
                </div>
                <div>
                    <h3 class="text-white font-medium text-xs">Dapatkan Update Terbaru</h3>
                    <p class="text-slate-500 text-[10px] mt-0.5">Berlangganan newsletter untuk info terbaru</p>
                </div>
            </div>

            <!-- Already Subscribed - Show locked state with unsubscribe option -->
            <template v-if="isLoggedIn && isSubscribed">
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <div class="relative flex-1 lg:w-52">
                        <Lock class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-emerald-500" />
                        <input 
                            type="email" 
                            :value="localSubscriptionState.email"
                            disabled
                            class="w-full pl-8 pr-3 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs cursor-not-allowed"
                        />
                    </div>
                    <div class="px-3 py-2 rounded-lg font-medium text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 whitespace-nowrap">
                        <CheckCircle class="w-3.5 h-3.5" />
                        <span>Aktif</span>
                    </div>
                    <button 
                        @click="openUnsubscribeModal"
                        class="p-2 rounded-lg text-red-400/70 hover:text-red-400 hover:bg-red-500/10 transition-all"
                        title="Berhenti Langganan"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </template>

            <!-- Inactive/Admin Disabled State -->
            <template v-else-if="isLoggedIn && hasSubscription && !isSubscribed">
                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <div v-if="isDisabledByAdmin" class="px-4 py-2 rounded-lg font-medium text-xs bg-red-500/20 text-red-400 border border-red-500/30 flex items-center gap-1.5 whitespace-nowrap">
                        <ShieldOff class="w-3.5 h-3.5" />
                        <span>Dinonaktifkan Admin</span>
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <span class="text-slate-500 text-xs">Tidak aktif</span>
                        <button 
                            @click="resubscribe"
                            class="px-3 py-2 rounded-lg font-medium text-xs transition-all duration-200 bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 flex items-center gap-1.5"
                        >
                            <RefreshCw class="w-3.5 h-3.5" />
                            <span>Aktifkan</span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Form Section - Show if logged in but not subscribed -->
            <template v-else-if="isLoggedIn">
                <form @submit.prevent="submit" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                    <!-- Email Input -->
                    <div class="relative flex-1 lg:w-52">
                        <Mail class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500" />
                        <input 
                            type="email" 
                            v-model="form.email"
                            :disabled="form.processing || isSuccess || isProcessing"
                            placeholder="Email Anda"
                            class="w-full pl-8 pr-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white placeholder-slate-500 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/20 transition-all duration-200 text-xs disabled:opacity-50"
                            required
                        />
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        :disabled="form.processing || isSuccess || !form.email || isProcessing"
                        class="px-4 py-2 rounded-lg font-medium text-xs transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-1.5 whitespace-nowrap"
                        :class="isSuccess 
                            ? 'bg-emerald-500 text-white' 
                            : 'bg-emerald-500 hover:bg-emerald-600 text-white'"
                    >
                        <CheckCircle v-if="isSuccess" class="w-3.5 h-3.5" />
                        <Loader2 v-else-if="form.processing || isProcessing" class="w-3.5 h-3.5 animate-spin" />
                        <Send v-else class="w-3.5 h-3.5" />
                        <span>{{ isSuccess ? 'Berhasil!' : 'Langganan' }}</span>
                    </button>
                </form>
            </template>

            <!-- Login Prompt - Improved Design -->
            <template v-else>
                <div class="flex items-center gap-3 w-full lg:w-auto bg-white/5 rounded-xl px-4 py-3 border border-white/10">
                    <div class="flex-1">
                        <p class="text-white text-xs font-medium">Ingin dapat update?</p>
                        <p class="text-slate-500 text-[11px]">Login untuk berlangganan newsletter</p>
                    </div>
                    <Link 
                        href="/login" 
                        class="px-4 py-2.5 rounded-lg font-semibold text-xs transition-all duration-200 bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-0.5 flex items-center gap-1.5"
                    >
                        <LogIn class="w-3.5 h-3.5" />
                        <span>Login</span>
                    </Link>
                </div>
            </template>
        </div>

        <!-- Error Message -->
        <p v-if="form.errors.email" class="mt-1.5 text-red-400 text-[10px]">
            {{ form.errors.email }}
        </p>

        <!-- Unsubscribe Confirmation Modal -->
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
    </div>
</template>

<style scoped>
/* Modal transitions */
.modal-enter-active { transition: all 0.3s ease-out; }
.modal-leave-active { transition: all 0.2s ease-in; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: scale(0.95) translateY(10px); }
</style>
