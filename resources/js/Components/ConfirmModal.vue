<script setup>
import { ref, watch } from 'vue';
import { AlertTriangle, X, Trash2, Check } from 'lucide-vue-next';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin?' },
    confirmText: { type: String, default: 'Ya, Lanjutkan' },
    cancelText: { type: String, default: 'Batal' },
    type: { type: String, default: 'danger' }, // danger, warning, info, success
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const typeConfig = {
    danger: {
        icon: Trash2,
        iconBg: 'bg-gradient-to-br from-red-100 to-red-200',
        iconColor: 'text-red-600',
        confirmBg: 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700',
        confirmShadow: 'shadow-red-500/30 hover:shadow-red-500/50',
    },
    warning: {
        icon: AlertTriangle,
        iconBg: 'bg-gradient-to-br from-amber-100 to-amber-200',
        iconColor: 'text-amber-600',
        confirmBg: 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700',
        confirmShadow: 'shadow-amber-500/30 hover:shadow-amber-500/50',
    },
    success: {
        icon: Check,
        iconBg: 'bg-gradient-to-br from-emerald-100 to-emerald-200',
        iconColor: 'text-emerald-600',
        confirmBg: 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700',
        confirmShadow: 'shadow-emerald-500/30 hover:shadow-emerald-500/50',
    },
    info: {
        icon: AlertTriangle,
        iconBg: 'bg-gradient-to-br from-blue-100 to-blue-200',
        iconColor: 'text-blue-600',
        confirmBg: 'bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700',
        confirmShadow: 'shadow-blue-500/30 hover:shadow-blue-500/50',
    },
};

const config = typeConfig[props.type] || typeConfig.danger;

const handleConfirm = () => {
    if (!props.loading) emit('confirm');
};

const handleCancel = () => {
    if (!props.loading) {
        emit('cancel');
        emit('close');
    }
};

const handleBackdropClick = () => {
    if (!props.loading) emit('close');
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div 
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="handleBackdropClick"
                ></div>
                
                <!-- Modal -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div 
                        v-if="show"
                        class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden"
                    >
                        <!-- Close Button -->
                        <button 
                            @click="handleCancel"
                            :disabled="loading"
                            class="absolute top-4 right-4 p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-50"
                        >
                            <X class="w-5 h-5" />
                        </button>

                        <!-- Content -->
                        <div class="p-8 text-center">
                            <!-- Icon -->
                            <div :class="['w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-6', config.iconBg]">
                                <component :is="config.icon" :class="['w-8 h-8', config.iconColor]" />
                            </div>

                            <!-- Title -->
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ title }}</h3>
                            
                            <!-- Message -->
                            <p class="text-gray-500 mb-8">{{ message }}</p>

                            <!-- Actions -->
                            <div class="flex items-center justify-center gap-3">
                                <button
                                    @click="handleCancel"
                                    :disabled="loading"
                                    class="px-6 py-3 rounded-xl font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all disabled:opacity-50"
                                >
                                    {{ cancelText }}
                                </button>
                                <button
                                    @click="handleConfirm"
                                    :disabled="loading"
                                    :class="[
                                        'px-6 py-3 rounded-xl font-semibold text-white shadow-lg transition-all disabled:opacity-50',
                                        config.confirmBg,
                                        config.confirmShadow
                                    ]"
                                >
                                    <span v-if="loading" class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                    <span v-else>{{ confirmText }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
