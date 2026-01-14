<script setup>
/**
 * TestimonialGrid.vue - Premium Grid Layout with Enhanced CTA
 * Features: Section header, CTA button, grid layout, pagination, empty state
 */
import { usePage, Link } from '@inertiajs/vue3';
import { Quote, Plus, PenLine, MessageSquareHeart, ChevronLeft, ChevronRight, Sparkles } from 'lucide-vue-next';
import TestimonialCard from './TestimonialCard.vue';

const props = defineProps({ feedbacks: { type: Object, required: true } });
const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <section id="testimonials-grid" class="py-12 sm:py-16 lg:py-20 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
        <!-- Background Decorations -->
        <div class="absolute top-0 left-0 w-72 h-72 bg-pink-100/50 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-fuchsia-100/30 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Section Header -->
            <div class="text-center mb-8 sm:mb-12">
                <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-gradient-to-r from-pink-100 to-fuchsia-100 border border-pink-200/50 text-pink-600 text-[10px] sm:text-xs font-semibold mb-3 sm:mb-4">
                    <MessageSquareHeart class="w-3 h-3 sm:w-4 sm:h-4" />
                    <span>Ulasan Terbaru</span>
                </div>
                <h2 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-black text-gray-900 mb-2 sm:mb-3">
                    Apa Kata 
                    <span class="bg-gradient-to-r from-pink-500 to-fuchsia-600 bg-clip-text text-transparent">Mereka</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 max-w-lg mx-auto">
                    Pengalaman dan cerita nyata dari para pengunjung yang telah menikmati keindahan alam TNLL
                </p>
            </div>

            <!-- CTA Button - Only show when there are testimonials -->
            <div v-if="feedbacks.data?.length > 0" class="flex justify-center mb-8 sm:mb-12">
                <Link 
                    v-if="user" 
                    href="/testimonials/create" 
                    class="group inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1 hover:scale-105 transition-all duration-300"
                >
                    <PenLine class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                    <span>Bagikan Pengalaman Anda</span>
                    <Sparkles class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
                <Link 
                    v-else 
                    href="/login" 
                    class="group inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1 hover:scale-105 transition-all duration-300"
                >
                    <Plus class="w-4 h-4 group-hover:rotate-90 transition-transform" />
                    <span>Login untuk Memberikan Ulasan</span>
                </Link>
            </div>

            <!-- Testimonial Grid -->
            <div v-if="feedbacks.data?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <TestimonialCard 
                    v-for="(item, index) in feedbacks.data" 
                    :key="item.id" 
                    :feedback="item" 
                    :index="index" 
                />
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 sm:py-20">
                <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-5 sm:mb-6 bg-gradient-to-br from-pink-100 to-fuchsia-200 rounded-2xl flex items-center justify-center shadow-lg shadow-pink-200/50">
                    <Quote class="w-10 h-10 sm:w-12 sm:h-12 text-pink-500" />
                </div>
                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2 sm:mb-3">Belum Ada Testimoni</h3>
                <p class="text-xs sm:text-sm text-gray-500 mb-6 sm:mb-8 max-w-md mx-auto">
                    Jadilah yang pertama berbagi pengalaman Anda menjelajahi Taman Nasional Lore Lindu!
                </p>
                <Link 
                    v-if="user"
                    href="/testimonials/create" 
                    class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1 transition-all duration-300"
                >
                    <PenLine class="w-4 h-4" />
                    Tulis Ulasan Pertama
                </Link>
                <Link 
                    v-else
                    href="/login" 
                    class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-xl hover:shadow-pink-500/40 hover:-translate-y-1 transition-all duration-300"
                >
                    <Plus class="w-4 h-4" />
                    Login untuk Tulis Ulasan
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="feedbacks.data?.length > 0 && feedbacks.last_page > 1" class="mt-10 sm:mt-12 flex justify-center">
                <nav class="inline-flex items-center gap-1.5 sm:gap-2 bg-white rounded-xl sm:rounded-2xl p-1.5 sm:p-2 shadow-lg border border-gray-100">
                    <!-- Previous -->
                    <Link 
                        v-if="feedbacks.prev_page_url" 
                        :href="feedbacks.prev_page_url" 
                        class="flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-gray-700 text-xs sm:text-sm font-medium hover:bg-pink-50 hover:text-pink-600 transition-all"
                    >
                        <ChevronLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </Link>
                    <span v-else class="flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-gray-300 text-xs sm:text-sm font-medium cursor-not-allowed">
                        <ChevronLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </span>

                    <!-- Page Numbers -->
                    <div class="flex items-center gap-1">
                        <template v-for="p in feedbacks.last_page" :key="p">
                            <Link 
                                v-if="Math.abs(p - feedbacks.current_page) <= 2 || p === 1 || p === feedbacks.last_page" 
                                :href="`/testimonials?page=${p}`"
                                :class="[
                                    'w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl flex items-center justify-center text-xs sm:text-sm font-bold transition-all duration-300',
                                    p === feedbacks.current_page 
                                        ? 'bg-gradient-to-r from-pink-500 to-fuchsia-600 text-white shadow-lg shadow-pink-500/30' 
                                        : 'text-gray-700 hover:bg-pink-50 hover:text-pink-600'
                                ]"
                            >
                                {{ p }}
                            </Link>
                            <span 
                                v-else-if="(p === 2 && feedbacks.current_page > 4) || (p === feedbacks.last_page - 1 && feedbacks.current_page < feedbacks.last_page - 3)"
                                class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-gray-400 text-xs sm:text-sm"
                            >
                                ...
                            </span>
                        </template>
                    </div>

                    <!-- Next -->
                    <Link 
                        v-if="feedbacks.next_page_url" 
                        :href="feedbacks.next_page_url" 
                        class="flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-gray-700 text-xs sm:text-sm font-medium hover:bg-pink-50 hover:text-pink-600 transition-all"
                    >
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <ChevronRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </Link>
                    <span v-else class="flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-gray-300 text-xs sm:text-sm font-medium cursor-not-allowed">
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <ChevronRight class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    </span>
                </nav>
            </div>
        </div>
    </section>
</template>
