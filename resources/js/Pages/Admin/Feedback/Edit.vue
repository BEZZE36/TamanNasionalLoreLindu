<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ArrowLeft, Save, MessageSquare, Star, MapPin, Power, Eye } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });
const props = defineProps({
    feedback: { type: Object, required: true },
    destinations: { type: Array, default: () => [] }
});

const form = useForm({
    display_name: props.feedback.display_name || '',
    destination_id: props.feedback.destination_id || '',
    message: props.feedback.message || '',
    rating: props.feedback.rating || 5,
    status: props.feedback.status || 'unread',
    is_published: props.feedback.is_published ?? false
});

const submit = () => { form.put(`/admin/feedback/${props.feedback.id}`); };
</script>

<template>
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-5 shadow-xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            </div>
            <div class="relative flex items-center gap-3">
                <Link href="/admin/feedback" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/20 text-white hover:bg-white/30 transition-colors">
                    <ArrowLeft class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-lg">Edit Feedback</h1>
                    <p class="mt-1 text-amber-100/90">{{ feedback.display_name }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Info -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><MessageSquare class="w-6 h-6 text-amber-500" />Informasi Feedback</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pengirim *</label>
                            <input v-model="form.display_name" type="text" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><MapPin class="w-4 h-4 inline mr-1" />Destinasi</label>
                            <select v-model="form.destination_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                                <option value="">Pilih Destinasi</option>
                                <option v-for="d in destinations" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pesan Feedback *</label>
                        <textarea v-model="form.message" rows="4" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3"><Star class="w-4 h-4 inline mr-1" />Rating *</label>
                        <div class="flex gap-2">
                            <button v-for="i in 5" :key="i" type="button" @click="form.rating = i"
                                :class="['text-3xl transition-transform hover:scale-110', i <= form.rating ? 'text-amber-400' : 'text-gray-300']">
                                ⭐
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2"><Power class="w-6 h-6 text-blue-500" />Status & Publikasi</h2>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="form.status" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            <option value="unread">Belum Dibaca</option>
                            <option value="read">Sudah Dibaca</option>
                            <option value="replied">Sudah Dibalas</option>
                            <option value="archived">Diarsipkan</option>
                        </select>
                    </div>
                    <div>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-green-300 transition-all">
                            <input type="checkbox" v-model="form.is_published" class="w-5 h-5 rounded border-gray-300 text-green-500 focus:ring-green-500">
                            <Eye class="w-5 h-5 text-green-500" />
                            <div><span class="font-medium text-gray-700">Tampilkan ke Publik</span></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                    class="inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-amber-500/30 hover:shadow-amber-500/50 hover:-translate-y-1 transition-all disabled:opacity-50">
                    <Save class="w-6 h-6" /><span>{{ form.processing ? 'Menyimpan...' : 'Perbarui Feedback' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>
