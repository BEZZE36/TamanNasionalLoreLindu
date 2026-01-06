<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Settings, Plus, Trash2, Save, RefreshCw, CheckCircle } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    settings: Object,
    groups: Object
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const groupKeys = computed(() => Object.keys(props.groups || {}));
const activeTab = ref(groupKeys.value[0] || '');

// Form data - we need to track all settings values
const formData = ref({});

// Initialize form data from settings
const initFormData = () => {
    const data = {};
    for (const [groupKey, settingsList] of Object.entries(props.settings || {})) {
        for (const setting of settingsList) {
            data[setting.key] = setting.value || '';
        }
    }
    formData.value = data;
};
initFormData();

const processing = ref(false);

const submitForm = () => {
    processing.value = true;
    
    const data = new FormData();
    data.append('_method', 'PUT');
    
    for (const [key, value] of Object.entries(formData.value)) {
        if (value instanceof File) {
            data.append(key, value);
        } else {
            data.append(key, value ?? '');
        }
    }
    
    router.post('/admin/site-info/general', data, {
        preserveScroll: true,
        onFinish: () => processing.value = false
    });
};

const clearCache = () => {
    router.post('/admin/site-info/settings/clear-cache', {}, {
        preserveScroll: true
    });
};

const deleteSetting = (id) => {
    if (!confirm('Apakah Anda yakin ingin menghapus pengaturan ini?')) return;
    router.delete(`/admin/site-info/settings/${id}`, {
        preserveScroll: true
    });
};

const handleFileChange = (key, event) => {
    const file = event.target.files[0];
    if (file) {
        formData.value[key] = file;
    }
};
</script>

<template>
    <div class="min-h-screen">
        <!-- Header -->
        <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 via-emerald-600 to-green-500 p-8 shadow-xl">
            <div class="absolute inset-0 opacity-30 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.1)_25%,rgba(255,255,255,0.1)_50%,transparent_50%,transparent_75%,rgba(255,255,255,0.1)_75%)] bg-[length:60px_60px]"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm ring-2 ring-white/30">
                        <Settings class="h-8 w-8 text-white animate-spin" style="animation-duration: 8s" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white drop-shadow-lg">Pengaturan Umum</h1>
                        <p class="mt-1 text-emerald-100">Kelola pengaturan dasar website Anda</p>
                    </div>
                </div>
                <Link href="/admin/site-info/settings/create"
                    class="group flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-3 text-white font-semibold transition-all duration-300 hover:bg-white hover:text-teal-600 hover:shadow-lg hover:scale-105 ring-2 ring-white/30">
                    <Plus class="w-5 h-5 transition-transform group-hover:rotate-90" />
                    Tambah Setting
                </Link>
            </div>
        </div>

        <!-- Flash Message -->
        <Transition name="fade">
            <div v-if="flash.success" class="mb-6 flex items-center gap-3 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 px-5 py-4 text-green-700 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle class="h-5 w-5 text-green-600" />
                </div>
                <span class="font-medium">{{ flash.success }}</span>
            </div>
        </Transition>

        <form @submit.prevent="submitForm">
            <div class="rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden">
                <!-- Tabs -->
                <div class="border-b bg-gradient-to-r from-gray-50 to-slate-50 flex overflow-x-auto">
                    <button v-for="(label, key) in groups" :key="key" type="button"
                        @click="activeTab = key"
                        :class="[
                            'relative px-6 py-4 text-sm font-semibold whitespace-nowrap transition-all duration-300 focus:outline-none border-b-2',
                            activeTab === key 
                                ? 'border-teal-500 text-teal-600 bg-white shadow-sm' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/50'
                        ]">
                        {{ label }}
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="p-6 lg:p-8">
                    <TransitionGroup name="fade">
                        <div v-for="(label, groupKey) in groups" :key="groupKey" v-show="activeTab === groupKey">
                            <div v-if="settings[groupKey]?.length" class="space-y-4">
                                <div v-for="setting in settings[groupKey]" :key="setting.id"
                                    class="group relative grid grid-cols-1 md:grid-cols-3 gap-4 py-5 px-4 rounded-xl border border-gray-100 bg-gradient-to-r from-white to-gray-50/50 hover:shadow-md hover:border-teal-200 transition-all duration-300">
                                    <div class="flex flex-col">
                                        <label class="block font-semibold text-gray-800">{{ setting.label || setting.key }}</label>
                                        <p v-if="setting.description" class="text-sm text-gray-500 mt-1">{{ setting.description }}</p>
                                        
                                        <!-- Delete Button -->
                                        <div class="mt-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                            <button type="button" @click="deleteSetting(setting.id)"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors duration-200">
                                                <Trash2 class="w-3.5 h-3.5" />
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <!-- Textarea -->
                                        <textarea v-if="setting.type === 'textarea'" v-model="formData[setting.key]" rows="3"
                                            class="w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200"></textarea>
                                        
                                        <!-- Boolean Toggle -->
                                        <label v-else-if="setting.type === 'boolean'" class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="formData[setting.key]" :true-value="'1'" :false-value="'0'" class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-teal-500 peer-checked:to-emerald-500"></div>
                                            <span class="ml-3 text-sm font-medium text-gray-700">{{ formData[setting.key] == '1' ? 'Aktif' : 'Nonaktif' }}</span>
                                        </label>
                                        
                                        <!-- Image Upload -->
                                        <div v-else-if="setting.type === 'image'" class="space-y-3">
                                            <div v-if="setting.value" class="relative group/image w-fit">
                                                <img :src="`/storage/${setting.value}`" class="w-32 h-20 object-cover rounded-xl shadow-md ring-2 ring-gray-100 group-hover/image:ring-teal-300 transition-all duration-300">
                                            </div>
                                            <input type="file" @change="handleFileChange(setting.key, $event)" accept="image/*"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 file:cursor-pointer file:transition-colors">
                                        </div>
                                        
                                        <!-- Color Picker -->
                                        <div v-else-if="setting.type === 'color'" class="flex items-center gap-3">
                                            <input type="color" v-model="formData[setting.key]"
                                                class="w-14 h-14 rounded-xl border-2 border-gray-200 cursor-pointer shadow-inner hover:border-teal-400 transition-colors duration-200">
                                            <input type="text" :value="formData[setting.key]" readonly
                                                class="w-32 rounded-xl border-gray-200 bg-gray-50 text-center font-mono text-sm shadow-inner">
                                        </div>
                                        
                                        <!-- Number -->
                                        <input v-else-if="setting.type === 'number'" type="number" v-model="formData[setting.key]"
                                            class="w-full md:w-48 rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                                        
                                        <!-- Default Text/Email/URL -->
                                        <input v-else :type="setting.type === 'email' ? 'email' : setting.type === 'url' ? 'url' : 'text'" 
                                            v-model="formData[setting.key]"
                                            class="w-full rounded-xl border-gray-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200 hover:border-gray-300">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div v-else class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-5">
                                    <Settings class="w-10 h-10 text-gray-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada pengaturan</h3>
                                <p class="text-gray-500 mb-6">Mulai dengan menambahkan pengaturan baru untuk grup ini</p>
                                <Link href="/admin/site-info/settings/create"
                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 px-6 py-3 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                                    <Plus class="w-4 h-4" />
                                    Tambah Setting
                                </Link>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 lg:px-8 py-5 bg-gradient-to-r from-gray-50 to-slate-50 border-t border-gray-100 flex flex-wrap gap-4 justify-between items-center">
                    <button type="button" @click="clearCache"
                        class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-teal-600 font-medium px-4 py-2 rounded-lg hover:bg-teal-50 transition-all duration-200">
                        <RefreshCw class="w-4 h-4" />
                        Clear Cache
                    </button>
                    <button type="submit" :disabled="processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 px-6 py-3 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 disabled:opacity-50">
                        <Save class="w-5 h-5" />
                        {{ processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
