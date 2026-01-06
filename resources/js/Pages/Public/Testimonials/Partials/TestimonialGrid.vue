<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import { Quote, Plus } from 'lucide-vue-next';
import TestimonialCard from './TestimonialCard.vue';

const props = defineProps({ feedbacks: { type: Object, required: true } });
const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- CTA Button -->
            <div class="text-center mb-12">
                <Link v-if="user" href="/testimonials/create" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-pink-500/50 transition-all">
                    <Plus class="w-5 h-5" />Bagikan Pengalaman Anda
                </Link>
                <Link v-else href="/login" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-pink-500/50 transition-all">
                    <Plus class="w-5 h-5" />Login untuk Memberikan Ulasan
                </Link>
            </div>

            <div v-if="feedbacks.data?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <TestimonialCard v-for="(item, index) in feedbacks.data" :key="item.id" :feedback="item" :index="index" />
            </div>

            <div v-else class="text-center py-20">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center"><Quote class="w-12 h-12 text-gray-400" /></div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum Ada Testimonial</h3>
                <p class="text-gray-500 mb-8">Jadilah yang pertama memberikan ulasan!</p>
            </div>

            <div v-if="feedbacks.data?.length > 0 && feedbacks.last_page > 1" class="mt-12 flex justify-center">
                <nav class="flex items-center gap-2">
                    <Link v-if="feedbacks.prev_page_url" :href="feedbacks.prev_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-300 transition-all">Sebelumnya</Link>
                    <template v-for="p in feedbacks.last_page" :key="p">
                        <Link v-if="Math.abs(p - feedbacks.current_page) <= 2 || p === 1 || p === feedbacks.last_page" :href="`/testimonials?page=${p}`"
                            :class="['w-10 h-10 rounded-xl flex items-center justify-center font-semibold transition-all', p === feedbacks.current_page ? 'bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white shadow-lg shadow-pink-500/30' : 'bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-300']">{{ p }}</Link>
                    </template>
                    <Link v-if="feedbacks.next_page_url" :href="feedbacks.next_page_url" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-300 transition-all">Selanjutnya</Link>
                </nav>
            </div>
        </div>
    </section>
</template>
