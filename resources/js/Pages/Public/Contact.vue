<script setup>
import { ref } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { MapPin, Phone, Mail, Clock, Send, User, MessageSquare, ChevronDown, CheckCircle, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    contactInfo: Object,
    mapEmbed: String,
});

const form = useForm({
    name: '', email: '', phone: '', subject: '', message: ''
});

const subjects = ['Informasi Umum', 'Pemesanan Tiket', 'Kerjasama', 'Keluhan', 'Lainnya'];

const submit = () => {
    form.post('/contact', { preserveScroll: true, onSuccess: () => form.reset() });
};

// WhatsApp
const getWhatsAppLink = () => {
    const phone = props.contactInfo?.whatsapp?.replace(/[^\d]/g, '')?.replace(/^0/, '62') || '';
    return phone ? `https://wa.me/${phone}` : null;
};

const page = usePage();
</script>

<template>
    <PublicLayout>
        <!-- Hero -->
        <section class="relative min-h-[70vh] flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-[#040810]"></div>
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-emerald-900/30 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 right-0 w-full h-full bg-gradient-to-tl from-teal-900/20 via-transparent to-transparent"></div>
            </div>
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-emerald-500/[0.08] blur-[120px] animate-pulse" style="animation-duration: 4s"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] rounded-full bg-teal-500/[0.06] blur-[100px] animate-pulse" style="animation-duration: 6s"></div>

            <div class="container mx-auto px-4 relative z-10 py-24">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="inline-flex items-center gap-3 px-6 py-3 mb-10 rounded-full bg-gradient-to-r from-white/[0.08] to-white/[0.03] backdrop-blur-xl border border-white/[0.08] shadow-2xl">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping" style="animation-duration: 1.5s"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-gradient-to-r from-emerald-400 to-teal-400"></span>
                        </span>
                        <span class="text-sm font-medium bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent tracking-wide">Tim kami online — Respon dalam 24 jam</span>
                    </div>

                    <h1 class="mb-8">
                        <span class="block text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-extralight text-white/90 tracking-[-0.02em] leading-[0.9]">Hubungi</span>
                        <span class="block text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent">Kami</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed font-light">
                        Kami siap membantu setiap pertanyaan Anda tentang <span class="text-emerald-400 font-medium">Taman Nasional Lore Lindu</span>
                    </p>

                    <div class="flex flex-wrap justify-center gap-4 mt-10">
                        <a v-if="getWhatsAppLink()" :href="getWhatsAppLink()" target="_blank" class="group inline-flex items-center gap-3 px-6 py-3 rounded-full bg-[#25D366] hover:bg-[#20BD5A] text-white font-medium transition-all hover:shadow-lg hover:-translate-y-0.5">
                            <!-- Real WhatsApp SVG Icon -->
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            <span>WhatsApp</span>
                        </a>
                        <a href="#contact-form" class="group inline-flex items-center gap-3 px-6 py-3 rounded-full bg-white/[0.08] hover:bg-white/[0.12] border border-white/[0.1] text-white font-medium transition-all hover:-translate-y-0.5 backdrop-blur-sm">
                            <Mail class="w-5 h-5" />
                            <span>Kirim Email</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Form Section -->
        <section id="contact-form" class="relative bg-gradient-to-b from-[#fafbfc] to-white py-24 lg:py-32">
            <div class="container mx-auto px-4 relative z-10">
                <div class="text-center mb-16">
                    <span class="inline-flex items-center gap-2 px-4 py-2 mb-6 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 bg-emerald-50 rounded-full border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Kontak
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-semibold text-gray-900 tracking-tight">Pilih Cara Menghubungi Kami</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 max-w-6xl mx-auto">
                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
                            <div v-if="page.props.flash?.success" class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
                                <CheckCircle class="w-5 h-5 text-emerald-600" />
                                <span class="text-emerald-700 font-medium">{{ page.props.flash.success }}</span>
                            </div>

                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                            <User class="w-4 h-4 text-emerald-500" /> Nama Lengkap
                                        </label>
                                        <input v-model="form.name" type="text" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all" placeholder="Masukkan nama Anda">
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                            <Mail class="w-4 h-4 text-emerald-500" /> Email
                                        </label>
                                        <input v-model="form.email" type="email" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all" placeholder="email@example.com">
                                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                            <Phone class="w-4 h-4 text-emerald-500" /> No. WhatsApp
                                        </label>
                                        <input v-model="form.phone" type="tel" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all" placeholder="+62 812 3456 7890">
                                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">{{ form.errors.phone }}</p>
                                    </div>
                                    <div>
                                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                            <MessageSquare class="w-4 h-4 text-emerald-500" /> Subjek
                                        </label>
                                        <select v-model="form.subject" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all">
                                            <option value="">Pilih subjek...</option>
                                            <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                                        </select>
                                        <p v-if="form.errors.subject" class="mt-1 text-sm text-red-500">{{ form.errors.subject }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                        <MessageSquare class="w-4 h-4 text-emerald-500" /> Pesan
                                    </label>
                                    <textarea v-model="form.message" rows="5" class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all resize-none" placeholder="Tulis pesan Anda..."></textarea>
                                    <p v-if="form.errors.message" class="mt-1 text-sm text-red-500">{{ form.errors.message }}</p>
                                </div>

                                <button type="submit" :disabled="form.processing" class="w-full inline-flex items-center justify-center gap-3 px-6 py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all disabled:opacity-50">
                                    <Send class="w-5 h-5" />
                                    {{ form.processing ? 'Mengirim...' : 'Kirim Pesan' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="group p-6 rounded-2xl bg-white border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                    <MapPin class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">Alamat</h3>
                                    <p class="text-gray-500 text-sm leading-relaxed">{{ contactInfo?.address || 'Sulawesi Tengah, Indonesia' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="group p-6 rounded-2xl bg-white border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                    <Phone class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">Telepon</h3>
                                    <p class="text-gray-500 text-sm">{{ contactInfo?.phone || '+62 812 3456 7890' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="group p-6 rounded-2xl bg-white border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                    <Mail class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                                    <p class="text-gray-500 text-sm">{{ contactInfo?.email || 'info@tnll.id' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="group p-6 rounded-2xl bg-white border border-gray-100 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                    <Clock class="w-6 h-6 text-white" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-1">Jam Operasional</h3>
                                    <p class="text-gray-500 text-sm">{{ contactInfo?.hours || 'Senin - Jumat: 08:00 - 17:00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section v-if="mapEmbed" class="relative bg-white py-20">
            <div class="container mx-auto px-4">
                <div class="rounded-3xl overflow-hidden shadow-2xl border border-gray-100">
                    <div v-html="mapEmbed" class="w-full h-[400px]"></div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
</style>
