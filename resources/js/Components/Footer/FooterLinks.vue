<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

const mainLinks = [
    { name: 'Beranda', href: '/' },
    { name: 'Destinasi', href: '/destinations' },
    { name: 'Galeri', href: '/gallery' },
    { name: 'Berita', href: '/news' },
    { name: 'FAQ', href: '/faq' },
    { name: 'Kontak', href: '/contact' },
];

const exploreLinks = [
    { name: 'Flora', href: '/flora' },
    { name: 'Fauna', href: '/fauna' },
    { name: 'Peta Interaktif', href: '/explore' },
];
</script>

<template>
    <div class="footer-links space-y-5">
        <!-- Tautan Section -->
        <div>
            <h3 class="text-white font-medium text-sm mb-3 flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Tautan
            </h3>
            <ul class="space-y-1.5">
                <li v-for="item in mainLinks" :key="item.name" class="footer-links-item">
                    <Link 
                        :href="item.href" 
                        class="group flex items-center text-slate-400 hover:text-emerald-400 transition-all duration-200 text-xs"
                    >
                        <ChevronRight class="w-3 h-3 text-emerald-400/50 group-hover:text-emerald-400 mr-1 transition-colors" />
                        <span>{{ item.name }}</span>
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Jelajahi Section -->
        <div>
            <h3 class="text-white font-medium text-sm mb-3 flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-violet-500"></span>
                </span>
                Jelajahi
            </h3>
            <ul class="space-y-1.5">
                <li v-for="item in exploreLinks" :key="item.name" class="footer-links-item">
                    <Link 
                        :href="item.href" 
                        class="group flex items-center text-slate-400 hover:text-violet-400 transition-all duration-200 text-xs"
                    >
                        <ChevronRight class="w-3 h-3 text-violet-400/50 group-hover:text-violet-400 mr-1 transition-colors" />
                        <span>{{ item.name }}</span>
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
