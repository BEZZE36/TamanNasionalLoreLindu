<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ArrowLeft, Star, Send, AlertTriangle, MapPin, Info } from 'lucide-vue-next';

const props = defineProps({
    hasBooking: Boolean,
    destinations: Array,
});

const rating = ref(5);
const form = useForm({
    destination_id: '',
    rating: 5,
    message: '',
    is_anonymous: false,
});

const setRating = (value) => {
    rating.value = value;
    form.rating = value;
};

const submit = () => {
    form.post(route('testimonials.store'));
};
</script>

<template>
    <Head title="Tulis Ulasan" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-600 via-pink-700 to-rose-900"></div>
            <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-pink-400/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 animate-pulse"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-2xl">
                    <Link :href="route('testimonials')" class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-4 transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                        Kembali ke Testimoni
                    </Link>
                    <h1 class="text-3xl md:text-4xl font-black text-white leading-tight mb-4">Tulis Ulasan ✍️</h1>
                    <p class="text-white/80">Bagikan pengalaman wisata Anda di Taman Nasional Lore Lindu</p>
                </div>
            </div>
        </section>

        <!-- Form Section -->
        <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
            <div class="container mx-auto px-4 max-w-2xl">
                <!-- No Booking Warning -->
                <div v-if="!hasBooking" class="bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 bg-amber-100 rounded-2xl flex items-center justify-center">
                        <AlertTriangle class="w-10 h-10 text-amber-600" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Riwayat Kunjungan</h2>
                    <p class="text-gray-600 mb-6">Anda perlu memiliki minimal satu pemesanan tiket yang sudah selesai untuk dapat memberikan ulasan.</p>
                    <Link :href="route('destinations.index')" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all shadow-lg">
                        <MapPin class="w-5 h-5" />
                        Booking Tiket Sekarang
                    </Link>
                </div>

                <!-- Feedback Form -->
                <div v-else class="bg-white rounded-2xl shadow-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Destination (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destinasi yang Dikunjungi (Opsional)</label>
                            <select v-model="form.destination_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                                <option value="">Pilih destinasi...</option>
                                <option v-for="destination in destinations" :key="destination.id" :value="destination.id">
                                    {{ destination.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Rating -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating Pengalaman Anda *</label>
                            <div class="flex items-center gap-2">
                                <button v-for="i in 5" :key="i" type="button" @click="setRating(i)"
                                    :class="['w-12 h-12 rounded-xl transition-all flex items-center justify-center',
                                             rating >= i ? 'bg-yellow-400 text-white scale-110' : 'bg-gray-100 text-gray-400 hover:bg-yellow-100']">
                                    <Star class="w-6 h-6" :fill="rating >= i ? 'currentColor' : 'none'" />
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Klik bintang untuk memberikan rating</p>
                            <p v-if="form.errors.rating" class="text-red-500 text-xs mt-1">{{ form.errors.rating }}</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ceritakan Pengalaman Anda *</label>
                            <textarea v-model="form.message" rows="5" required
                                placeholder="Bagikan pengalaman wisata Anda di TNLL... Apa yang paling berkesan? Tips untuk pengunjung lain?"
                                :class="['w-full px-4 py-3 rounded-xl border transition-all resize-none',
                                         form.errors.message ? 'border-red-500' : 'border-gray-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20']"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimal 20 karakter, maksimal 1000 karakter</p>
                            <p v-if="form.errors.message" class="text-red-500 text-xs mt-1">{{ form.errors.message }}</p>
                        </div>

                        <!-- Anonymous -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.is_anonymous" id="is_anonymous"
                                class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <label for="is_anonymous" class="text-sm text-gray-600">
                                Tampilkan sebagai "Anonim" (nama Anda tidak akan ditampilkan)
                            </label>
                        </div>

                        <!-- Info Box -->
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex gap-3">
                                <Info class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
                                <p class="text-sm text-blue-700">
                                    Ulasan Anda akan diverifikasi oleh admin sebelum ditampilkan ke publik. Proses moderasi biasanya memakan waktu 1-2 hari kerja.
                                </p>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-bold rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all shadow-lg disabled:opacity-50">
                            <Send class="w-5 h-5" />
                            Kirim Ulasan
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
