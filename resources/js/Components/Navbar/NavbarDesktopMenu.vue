<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Home, MapPin, Info, FileText, Newspaper, HelpCircle, Mail } from 'lucide-vue-next';
import NavbarExploreDropdown from './NavbarExploreDropdown.vue';

defineProps({ scrolled: Boolean });

const page = usePage();
const navItems = [
    { name: 'Beranda', href: '/', icon: Home, routeMatch: 'home' },
    { name: 'Destinasi', href: '/destinations', icon: MapPin, routeMatch: 'destinations' },
];
const navItemsAfterExplore = [
    { name: 'Tentang', href: '/about', icon: Info, routeMatch: 'about' },
    { name: 'Blog', href: '/blog', icon: FileText, routeMatch: 'blog' },
    { name: 'Berita', href: '/news', icon: Newspaper, routeMatch: 'news' },
    { name: 'FAQ', href: '/faq', icon: HelpCircle, routeMatch: 'faq' },
    { name: 'Kontak', href: '/contact', icon: Mail, routeMatch: 'contact' },
];

const isActive = (href, routeMatch) => {
    if (href === '/') return page.url === '/';
    return page.url.startsWith(`/${routeMatch}`);
};
</script>

<template>
    <div class="hidden lg:flex items-center justify-center gap-1.5">
        <!-- First group -->
        <Link 
            v-for="item in navItems" 
            :key="item.name"
            :href="item.href"
            :class="[
                'nav-link group px-3 py-1.5 text-xs font-semibold transition-all duration-300 flex items-center gap-1.5 relative rounded-lg hover:scale-105',
                isActive(item.href, item.routeMatch)
                    ? (scrolled ? 'text-slate-800 bg-slate-100/80' : 'text-white bg-white/15')
                    : (scrolled ? 'text-slate-600 hover:text-slate-800 hover:bg-slate-50' : 'text-white/80 hover:text-white hover:bg-white/10')
            ]"
            :aria-current="isActive(item.href, item.routeMatch) ? 'page' : undefined"
        >
            <component :is="item.icon" class="w-3.5 h-3.5 transition-all duration-300 group-hover:scale-110" />
            <span>{{ item.name }}</span>
            <!-- Active underline - violet/indigo gradient -->
            <span v-if="isActive(item.href, item.routeMatch)" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full"></span>
            <!-- Hover underline - purple gradient (hidden when active) -->
            <span v-if="!isActive(item.href, item.routeMatch)" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-purple-500 rounded-full scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-center"></span>
        </Link>

        <!-- Jelajahi Dropdown -->
        <NavbarExploreDropdown :scrolled="scrolled" />

        <!-- Second group -->
        <Link 
            v-for="item in navItemsAfterExplore" 
            :key="item.name"
            :href="item.href"
            :class="[
                'nav-link group px-3 py-1.5 text-xs font-semibold transition-all duration-300 flex items-center gap-1.5 relative rounded-lg hover:scale-105',
                isActive(item.href, item.routeMatch)
                    ? (scrolled ? 'text-slate-800 bg-slate-100/80' : 'text-white bg-white/15')
                    : (scrolled ? 'text-slate-600 hover:text-slate-800 hover:bg-slate-50' : 'text-white/80 hover:text-white hover:bg-white/10')
            ]"
            :aria-current="isActive(item.href, item.routeMatch) ? 'page' : undefined"
        >
            <component :is="item.icon" class="w-3.5 h-3.5 transition-all duration-300 group-hover:scale-110" />
            <span>{{ item.name }}</span>
            <!-- Active underline - violet/indigo gradient -->
            <span v-if="isActive(item.href, item.routeMatch)" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 rounded-full"></span>
            <!-- Hover underline - purple gradient (hidden when active) -->
            <span v-if="!isActive(item.href, item.routeMatch)" class="absolute bottom-0 left-1.5 right-1.5 h-0.5 bg-gradient-to-r from-violet-500 to-purple-500 rounded-full scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-center"></span>
        </Link>
    </div>
</template>
