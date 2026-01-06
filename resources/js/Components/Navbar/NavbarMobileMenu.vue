<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { 
    Home, MapPin, Compass, Info, FileText, Newspaper, 
    HelpCircle, Mail, LogIn, UserPlus, ChevronDown, LogOut,
    Map, Leaf, Bird, Image, Star, LayoutDashboard, Heart, User
} from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    user: Object,
});

const emit = defineEmits(['close']);

const page = usePage();
const exploreOpen = ref(false);

// Main navigation items (simple - no gradients/descriptions)
const navItems = [
    { name: 'Beranda', href: '/', icon: Home },
    { name: 'Destinasi', href: '/destinations', icon: MapPin },
    { name: 'Tentang', href: '/about', icon: Info },
    { name: 'Blog', href: '/blog', icon: FileText },
    { name: 'Berita', href: '/news', icon: Newspaper },
    { name: 'FAQ', href: '/faq', icon: HelpCircle },
    { name: 'Kontak', href: '/contact', icon: Mail },
];

// Explore sub-menu items with gradient colors and descriptions (matching desktop)
const exploreItems = [
    { name: 'Explore Map', href: '/explore/map', icon: Map, description: 'Interactive explore', gradient: 'from-blue-500 to-cyan-500' },
    { name: 'Flora', href: '/flora', icon: Leaf, description: 'Plant diversity', gradient: 'from-emerald-500 to-green-500' },
    { name: 'Fauna', href: '/fauna', icon: Bird, description: 'Endemic wildlife', gradient: 'from-amber-500 to-orange-500' },
    { name: 'Galeri', href: '/gallery', icon: Image, description: 'Photos & videos', gradient: 'from-purple-500 to-pink-500' },
    { name: 'Testimoni', href: '/testimonials', icon: Star, description: 'Visitor reviews', gradient: 'from-yellow-500 to-amber-500' },
];

// User menu items
const userMenuItems = [
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { name: 'Wishlist Saya', href: '/wishlist', icon: Heart },
    { name: 'Ulasan Saya', href: '/my-reviews', icon: Star },
    { name: 'Profil Saya', href: '/profile', icon: User },
];

// Check if route is active
const isActive = (href) => {
    if (href === '/') return page.url === '/';
    return page.url.startsWith(href);
};

const handleNavClick = () => {
    emit('close');
};

const logout = () => {
    router.post('/logout');
    emit('close');
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-4"
    >
        <div 
            v-if="open" 
            class="lg:hidden absolute top-full left-0 right-0 bg-white/98 backdrop-blur-xl border-b border-gray-100 shadow-2xl max-h-[85vh] sm:max-h-[80vh] overflow-y-auto"
        >
            <!-- Responsive padding for different screen sizes -->
            <div class="px-3 sm:px-4 md:px-6 py-4 sm:py-5 space-y-1 sm:space-y-1.5">
                <!-- Main Nav Items (First 2) - Simple style -->
                <Link 
                    v-for="item in navItems.slice(0, 2)" 
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2.5 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-medium transition-all duration-300 text-sm sm:text-base',
                        isActive(item.href)
                            ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/30'
                            : 'text-gray-700 hover:bg-violet-50 hover:text-violet-600'
                    ]"
                    @click="handleNavClick"
                >
                    <component :is="item.icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                    {{ item.name }}
                </Link>

                <!-- Jelajahi Accordion - With styled dropdown -->
                <div class="space-y-1">
                    <button 
                        @click="exploreOpen = !exploreOpen"
                        :class="[
                            'w-full flex items-center justify-between gap-2.5 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-medium transition-all duration-300 text-sm sm:text-base',
                            exploreOpen ? 'bg-violet-50 text-violet-600' : 'text-gray-700 hover:bg-violet-50 hover:text-violet-600'
                        ]"
                    >
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <Compass class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span>Jelajahi</span>
                        </div>
                        <ChevronDown :class="['w-4 h-4 transition-transform duration-300', exploreOpen ? 'rotate-180' : '']" />
                    </button>
                    
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-[500px]"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 max-h-[500px]"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <!-- Jelajahi Submenu - WITH colored icon backgrounds and descriptions -->
                        <div v-if="exploreOpen" class="overflow-hidden ml-3 sm:ml-4 pl-3 sm:pl-4 border-l-2 border-violet-200 space-y-1.5 sm:space-y-2 mt-1.5">
                            <Link 
                                v-for="item in exploreItems" 
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    'group flex items-center gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl transition-all duration-300',
                                    isActive(item.href)
                                        ? 'bg-violet-100'
                                        : 'hover:bg-gray-50'
                                ]"
                                @click="handleNavClick"
                            >
                                <!-- Colored Icon Background -->
                                <div :class="[
                                    'flex-shrink-0 w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center shadow-md transition-transform duration-300 group-hover:scale-110',
                                    `bg-gradient-to-br ${item.gradient}`
                                ]">
                                    <component :is="item.icon" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" />
                                </div>
                                <!-- Text Content with Description -->
                                <div class="flex-1 min-w-0">
                                    <p :class="[
                                        'font-semibold text-xs sm:text-sm',
                                        isActive(item.href) ? 'text-violet-700' : 'text-gray-700'
                                    ]">{{ item.name }}</p>
                                    <p :class="[
                                        'text-[9px] sm:text-[10px] truncate',
                                        isActive(item.href) ? 'text-violet-500' : 'text-gray-400'
                                    ]">{{ item.description }}</p>
                                </div>
                            </Link>
                        </div>
                    </Transition>
                </div>

                <!-- Rest of Nav Items - Simple style -->
                <Link 
                    v-for="item in navItems.slice(2)" 
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2.5 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-medium transition-all duration-300 text-sm sm:text-base',
                        isActive(item.href)
                            ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-500/30'
                            : 'text-gray-700 hover:bg-violet-50 hover:text-violet-600'
                    ]"
                    @click="handleNavClick"
                >
                    <component :is="item.icon" class="w-4 h-4 sm:w-5 sm:h-5" />
                    {{ item.name }}
                </Link>
            </div>
        </div>
    </Transition>
</template>
