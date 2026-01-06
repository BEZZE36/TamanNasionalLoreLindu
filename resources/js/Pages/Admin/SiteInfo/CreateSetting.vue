<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Settings, ArrowLeft, Save, Key, Tag, Folder, Type, FileText, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    groups: Object,
    types: Object
});

const form = useForm({
    key: '',
    label: '',
    group: Object.keys(props.groups || {})[0] || '',
    type: 'text',
    description: '',
    is_public: true,
});

const submit = () => {
    form.post('/admin/site-info/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 shadow-lg">
                        <Settings class="w-4 h-4 text-white" />
                    </div>
                    Tambah Pengaturan Baru
                </h1>
                <p class="text-gray-600 mt-1">Buat pengaturan sistem kustom</p>
            </div>
            <Link href="/admin/site-info/general" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">
                <ArrowLeft class="w-5 h-5" />
                Kembali
            </Link>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-6 lg:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Key -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <Key class="w-4 h-4 text-gray-400" />
                            Key (Unique) *
                        </label>
                        <input type="text" v-model="form.key" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 font-mono focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            placeholder="ex: site_logo">
                        <p class="text-xs text-gray-500 mt-1">Gunakan format snake_case. Unik.</p>
                        <p v-if="form.errors.key" class="text-red-500 text-xs mt-1">{{ form.errors.key }}</p>
                    </div>

                    <!-- Label -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <Tag class="w-4 h-4 text-gray-400" />
                            Label *
                        </label>
                        <input type="text" v-model="form.label"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            placeholder="ex: Logo Website">
                        <p v-if="form.errors.label" class="text-red-500 text-xs mt-1">{{ form.errors.label }}</p>
                    </div>

                    <!-- Group -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <Folder class="w-4 h-4 text-gray-400" />
                            Group *
                        </label>
                        <select v-model="form.group"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <option v-for="(label, key) in groups" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.group" class="text-red-500 text-xs mt-1">{{ form.errors.group }}</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <Type class="w-4 h-4 text-gray-400" />
                            Type *
                        </label>
                        <select v-model="form.type"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all">
                            <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                            <FileText class="w-4 h-4 text-gray-400" />
                            Description (Optional)
                        </label>
                        <textarea v-model="form.description" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            placeholder="Deskripsi pengaturan ini..."></textarea>
                    </div>

                    <!-- Is Public -->
                    <div class="md:col-span-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_public" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-teal-500 peer-checked:to-emerald-500"></div>
                            <span class="ml-3 flex items-center gap-2 text-sm font-medium text-gray-900">
                                <Eye class="w-4 h-4 text-gray-400" />
                                Public Setting (Visible to frontend)
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="px-6 lg:px-8 py-5 bg-gradient-to-r from-gray-50 to-slate-50 border-t border-gray-100 flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 px-6 py-3 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 disabled:opacity-50">
                    <Save class="w-5 h-5" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                </button>
            </div>
        </form>
    </div>
</template>
