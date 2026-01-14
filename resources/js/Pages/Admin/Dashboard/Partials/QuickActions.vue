<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { 
    Plus, QrCode, Ticket, Users, FileText, Image, MapPin, 
    Send, Settings, BarChart3, Megaphone, Mail
} from 'lucide-vue-next';

const actions = [
    { label: 'Booking Baru', icon: Plus, href: '/admin/bookings/create', gradient: 'from-emerald-500 to-teal-600', desc: 'Buat pemesanan' },
    { label: 'Scan Tiket', icon: QrCode, href: '/admin/tickets/scan', gradient: 'from-violet-500 to-purple-600', desc: 'Validasi tiket' },
    { label: 'Kupon Baru', icon: Ticket, href: '/admin/coupons/create', gradient: 'from-pink-500 to-rose-600', desc: 'Buat kupon' },
    { label: 'Tambah Destinasi', icon: MapPin, href: '/admin/destinations/create', gradient: 'from-blue-500 to-indigo-600', desc: 'Destinasi baru' },
    { label: 'Tambah Artikel', icon: FileText, href: '/admin/articles/create', gradient: 'from-amber-500 to-orange-600', desc: 'Tulis artikel' },
    { label: 'Upload Gallery', icon: Image, href: '/admin/gallery/create', gradient: 'from-cyan-500 to-blue-600', desc: 'Upload foto' },
    { label: 'Pengumuman', icon: Megaphone, href: '/admin/announcements/create', gradient: 'from-red-500 to-rose-600', desc: 'Buat pengumuman' },
    { label: 'Campaign Email', icon: Mail, href: '/admin/newsletter/campaigns', gradient: 'from-teal-500 to-emerald-600', desc: 'Kirim email' },
];

let ctx;

onMounted(() => {
    nextTick(() => {
        const btns = document.querySelectorAll('.action-btn');
        if (btns.length > 0) {
            ctx = gsap.context(() => {
                gsap.fromTo(btns, 
                    { opacity: 0, scale: 0.8, y: 10 }, 
                    { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.05, ease: 'back.out(1.7)' }
                );
            });
        }
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div class="rounded-xl bg-white p-4 shadow-lg border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                    <Settings class="w-4 h-4 text-white" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Aksi Cepat</h3>
                    <p class="text-[10px] text-gray-500">Shortcut menu admin</p>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-8 gap-2">
            <Link 
                v-for="(action, index) in actions" 
                :key="index"
                :href="action.href"
                class="action-btn group flex flex-col items-center gap-1.5 p-3 rounded-xl bg-gray-50 hover:bg-white border border-transparent hover:border-gray-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
            >
                <div class="relative">
                    <div :class="['absolute inset-0 rounded-lg blur-md opacity-0 group-hover:opacity-60 transition-opacity bg-gradient-to-br', action.gradient]"></div>
                    <div :class="['relative w-10 h-10 rounded-lg flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 shadow-md bg-gradient-to-br', action.gradient]">
                        <component :is="action.icon" class="w-5 h-5 text-white" />
                    </div>
                </div>
                <span class="text-[10px] font-semibold text-gray-700 text-center leading-tight group-hover:text-gray-900 transition-colors">{{ action.label }}</span>
            </Link>
        </div>
    </div>
</template>
