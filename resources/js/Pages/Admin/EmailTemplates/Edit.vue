<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, Mail, FileText, Code, Power } from 'lucide-vue-next';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    emailTemplate: { type: Object, required: true },
    templateTypes: { type: Object, default: () => ({}) },
    defaultVariables: { type: Object, default: () => ({}) }
});

const form = useForm({
    name: props.emailTemplate.name || '',
    subject: props.emailTemplate.subject || '',
    body: props.emailTemplate.body || '',
    description: props.emailTemplate.description || '',
    is_active: props.emailTemplate.is_active ?? true
});

const submit = () => { form.put(`/admin/email-templates/${props.emailTemplate.id}`); };
const availableVariables = computed(() => props.emailTemplate.variables || props.defaultVariables[props.emailTemplate.slug] || []);
const insertVariable = (v) => { form.body += `{{ ${v} }}`; };
</script>

<template>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-500 via-rose-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/email-templates" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Template</h1>
                    <p class="mt-1 text-pink-100/90">{{ emailTemplate.name }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Info -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-pink-50 to-rose-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Mail class="w-6 h-6 text-pink-500" />Informasi Template</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Template *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Template</label>
                            <input :value="emailTemplate.slug" type="text" disabled
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 font-mono text-gray-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject Email *</label>
                        <input v-model="form.subject" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <input v-model="form.description" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20">
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><FileText class="w-6 h-6 text-blue-500" />Konten Email</h2>
                </div>
                <div class="p-8 space-y-4">
                    <div v-if="availableVariables.length" class="flex flex-wrap gap-2 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-sm text-gray-500 mr-2"><Code class="w-4 h-4 inline" /> Variabel:</span>
                        <button v-for="v in availableVariables" :key="v" type="button" @click="insertVariable(v)"
                            class="px-3 py-1 bg-pink-100 text-pink-700 rounded-lg text-sm font-mono hover:bg-pink-200 transition-colors"
                            v-text="'{{ ' + v + ' }}'">
                        </button>
                    </div>
                    <textarea v-model="form.body" rows="12" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-mono text-sm"></textarea>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-pink-500 focus:ring-pink-500">
                    <Power class="w-5 h-5 text-pink-500" />
                    <span class="font-medium text-gray-700">Aktifkan Template</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-pink-500 to-rose-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-pink-500/30 hover:shadow-pink-500/50 hover:-translate-y-1 transition-all disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Perbarui Template' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
