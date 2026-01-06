<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, MapPin, Leaf, Bird, Image, Megaphone, 
    FileText, Newspaper, Settings, ChevronDown, Mail, Lock, ChevronRight
} from 'lucide-vue-next';

const emit = defineEmits(['navigate']);
const page = usePage();

// Get admin permissions
const admin = computed(() => page.props.admin);
const isSuperAdmin = computed(() => admin.value?.role === 'super_admin');
const hasPermission = (permission) => {
    if (isSuperAdmin.value) return true;
    return admin.value?.permissions?.includes(permission) || false;
};

// Menu visibility (access-* permission)
const canAccessDashboard = computed(() => hasPermission('access-dashboard'));
const canAccessDestinations = computed(() => hasPermission('access-destinations'));
const canAccessFlora = computed(() => hasPermission('access-flora'));
const canAccessFauna = computed(() => hasPermission('access-fauna'));
const canAccessGallery = computed(() => hasPermission('access-gallery'));
const canAccessAnnouncements = computed(() => hasPermission('access-announcements'));
const canAccessArticles = computed(() => hasPermission('access-articles'));
const canAccessNews = computed(() => hasPermission('access-news'));
const canAccessNewsletter = computed(() => hasPermission('access-newsletter'));
const canAccessSiteInfo = computed(() => hasPermission('access-site-info'));

// Menu can open (read-* or write-* permission)
const canOpenDashboard = computed(() => hasPermission('read-dashboard') || hasPermission('write-dashboard'));
const canOpenDestinations = computed(() => hasPermission('read-destinations') || hasPermission('write-destinations'));
const canOpenFlora = computed(() => hasPermission('read-flora') || hasPermission('write-flora'));
const canOpenFauna = computed(() => hasPermission('read-fauna') || hasPermission('write-fauna'));
const canOpenGallery = computed(() => hasPermission('read-gallery') || hasPermission('write-gallery'));
const canOpenAnnouncements = computed(() => hasPermission('read-announcements') || hasPermission('write-announcements'));
const canOpenArticles = computed(() => hasPermission('read-articles') || hasPermission('write-articles'));
const canOpenNews = computed(() => hasPermission('read-news') || hasPermission('write-news'));
const canOpenNewsletter = computed(() => hasPermission('read-newsletter') || hasPermission('write-newsletter'));
const canOpenSiteInfo = computed(() => hasPermission('read-site-info') || hasPermission('write-site-info'));

// Show section if any menu is visible
const showKontenSection = computed(() => {
    return canAccessDestinations.value || canAccessFlora.value || canAccessFauna.value || 
           canAccessGallery.value || canAccessAnnouncements.value || canAccessArticles.value || 
           canAccessNews.value || canAccessNewsletter.value || canAccessSiteInfo.value;
});

// Check if route is active
const isActive = (path) => page.url.startsWith(path);

// Expanded menus state
const expandedMenus = ref({
    destinations: false,
    flora: false,
    fauna: false,
    gallery: false,
    siteInfo: false,
});

// Auto-expand active menu
if (isActive('/admin/destinations')) expandedMenus.value.destinations = true;
if (isActive('/admin/flora')) expandedMenus.value.flora = true;
if (isActive('/admin/fauna')) expandedMenus.value.fauna = true;
if (isActive('/admin/gallery')) expandedMenus.value.gallery = true;
if (isActive('/admin/site-info')) expandedMenus.value.siteInfo = true;

const toggleMenu = (key) => {
    expandedMenus.value[key] = !expandedMenus.value[key];
};

// Menu items
const menuItems = {
    destinations: [
        { name: 'Semua Destinasi', href: '/admin/destinations' },
        { name: 'Tambah Baru', href: '/admin/destinations/create' },
    ],
    flora: [
        { name: 'Semua Flora', href: '/admin/flora' },
        { name: 'Tambah Baru', href: '/admin/flora/create' },
    ],
    fauna: [
        { name: 'Semua Fauna', href: '/admin/fauna' },
        { name: 'Tambah Baru', href: '/admin/fauna/create' },
    ],
    gallery: [
        { name: 'Semua Foto', href: '/admin/gallery' },
        { name: 'Upload Baru', href: '/admin/gallery/create' },
    ],
    siteInfo: [
        { name: 'Detail Web', href: '/admin/site-info/detail-web' },
        { name: 'FAQ', href: '/admin/site-info/faq' },
        { name: 'Kebijakan Privasi', href: '/admin/site-info/privacy' },
        { name: 'Syarat & Ketentuan', href: '/admin/site-info/terms' },
    ],
};

const handleNavigate = () => emit('navigate');

// Icon colors
const iconColors = {
    dashboard: { gradient: 'from-blue-500 to-indigo-600', text: 'text-blue-400', hover: 'group-hover:from-blue-400 group-hover:to-indigo-500' },
    destinations: { gradient: 'from-emerald-500 to-teal-600', text: 'text-emerald-400', hover: 'group-hover:from-emerald-400 group-hover:to-teal-500' },
    flora: { gradient: 'from-lime-500 to-green-600', text: 'text-lime-400', hover: 'group-hover:from-lime-400 group-hover:to-green-500' },
    fauna: { gradient: 'from-amber-500 to-orange-600', text: 'text-amber-400', hover: 'group-hover:from-amber-400 group-hover:to-orange-500' },
    gallery: { gradient: 'from-pink-500 to-rose-600', text: 'text-pink-400', hover: 'group-hover:from-pink-400 group-hover:to-rose-500' },
    announcements: { gradient: 'from-orange-500 to-red-600', text: 'text-orange-400', hover: 'group-hover:from-orange-400 group-hover:to-red-500' },
    articles: { gradient: 'from-indigo-500 to-violet-600', text: 'text-indigo-400', hover: 'group-hover:from-indigo-400 group-hover:to-violet-500' },
    news: { gradient: 'from-red-500 to-rose-600', text: 'text-red-400', hover: 'group-hover:from-red-400 group-hover:to-rose-500' },
    newsletter: { gradient: 'from-teal-500 to-cyan-600', text: 'text-teal-400', hover: 'group-hover:from-teal-400 group-hover:to-cyan-500' },
    siteinfo: { gradient: 'from-cyan-500 to-blue-600', text: 'text-cyan-400', hover: 'group-hover:from-cyan-400 group-hover:to-blue-500' },
};
</script>

<template>
    <!-- ===== DASHBOARD ===== -->
    <!-- Dashboard Locked -->
    <div v-if="canAccessDashboard && !canOpenDashboard" class="menu-locked group">
        <div class="icon-wrap icon-locked">
            <LayoutDashboard class="w-4 h-4" />
            <div class="lock-badge"><Lock class="w-2 h-2" /></div>
        </div>
        <span class="menu-label">Dashboard</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    
    <!-- Dashboard Normal -->
    <Link v-else-if="canAccessDashboard" href="/admin/dashboard" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/dashboard') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/dashboard') 
                ? 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-blue-500/80 group-hover:to-indigo-600/80']">
            <LayoutDashboard :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/dashboard') ? 'text-white' : 'text-blue-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Dashboard</span>
        <div v-if="isActive('/admin/dashboard')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- ===== SECTION: KONTEN ===== -->
    <div v-if="showKontenSection" class="section-divider">
        <div class="section-line"></div>
        <span class="section-text">Konten</span>
        <div class="section-line"></div>
    </div>

    <!-- ===== DESTINATIONS DROPDOWN ===== -->
    <div v-if="canAccessDestinations && !canOpenDestinations" class="menu-locked group">
        <div class="icon-wrap icon-locked"><MapPin class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Destinasi</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <div v-else-if="canAccessDestinations" class="relative">
        <button @click="toggleMenu('destinations')" 
            :class="['menu-item group w-full', isActive('/admin/destinations') ? 'menu-active' : '']">
            <div :class="['icon-wrap transition-all duration-300', 
                isActive('/admin/destinations') 
                    ? 'bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30' 
                    : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-emerald-500/80 group-hover:to-teal-600/80']">
                <MapPin :class="['w-4 h-4 transition-all duration-300', 
                    isActive('/admin/destinations') ? 'text-white' : 'text-emerald-400 group-hover:text-white']" />
            </div>
            <span class="menu-label flex-1 text-left">Destinasi</span>
            <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-all duration-300', expandedMenus.destinations ? 'rotate-180 text-emerald-400' : '']" />
        </button>
        <Transition name="submenu">
            <div v-if="expandedMenus.destinations" class="submenu-container">
                <Link v-for="item in menuItems.destinations" :key="item.name" :href="item.href" @click="handleNavigate"
                    :class="['submenu-item', isActive(item.href) ? 'submenu-active' : '']">
                    <span class="submenu-dot"></span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </Transition>
    </div>

    <!-- ===== FLORA DROPDOWN ===== -->
    <div v-if="canAccessFlora && !canOpenFlora" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Leaf class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Flora</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <div v-else-if="canAccessFlora" class="relative">
        <button @click="toggleMenu('flora')" 
            :class="['menu-item group w-full', isActive('/admin/flora') ? 'menu-active' : '']">
            <div :class="['icon-wrap transition-all duration-300', 
                isActive('/admin/flora') 
                    ? 'bg-gradient-to-br from-lime-500 to-green-600 shadow-lg shadow-lime-500/30' 
                    : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-lime-500/80 group-hover:to-green-600/80']">
                <Leaf :class="['w-4 h-4 transition-all duration-300', 
                    isActive('/admin/flora') ? 'text-white' : 'text-lime-400 group-hover:text-white']" />
            </div>
            <span class="menu-label flex-1 text-left">Flora</span>
            <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-all duration-300', expandedMenus.flora ? 'rotate-180 text-lime-400' : '']" />
        </button>
        <Transition name="submenu">
            <div v-if="expandedMenus.flora" class="submenu-container">
                <Link v-for="item in menuItems.flora" :key="item.name" :href="item.href" @click="handleNavigate"
                    :class="['submenu-item', isActive(item.href) ? 'submenu-active' : '']">
                    <span class="submenu-dot"></span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </Transition>
    </div>

    <!-- ===== FAUNA DROPDOWN ===== -->
    <div v-if="canAccessFauna && !canOpenFauna" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Bird class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Fauna</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <div v-else-if="canAccessFauna" class="relative">
        <button @click="toggleMenu('fauna')" 
            :class="['menu-item group w-full', isActive('/admin/fauna') ? 'menu-active' : '']">
            <div :class="['icon-wrap transition-all duration-300', 
                isActive('/admin/fauna') 
                    ? 'bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30' 
                    : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-amber-500/80 group-hover:to-orange-600/80']">
                <Bird :class="['w-4 h-4 transition-all duration-300', 
                    isActive('/admin/fauna') ? 'text-white' : 'text-amber-400 group-hover:text-white']" />
            </div>
            <span class="menu-label flex-1 text-left">Fauna</span>
            <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-all duration-300', expandedMenus.fauna ? 'rotate-180 text-amber-400' : '']" />
        </button>
        <Transition name="submenu">
            <div v-if="expandedMenus.fauna" class="submenu-container">
                <Link v-for="item in menuItems.fauna" :key="item.name" :href="item.href" @click="handleNavigate"
                    :class="['submenu-item', isActive(item.href) ? 'submenu-active' : '']">
                    <span class="submenu-dot"></span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </Transition>
    </div>

    <!-- ===== GALLERY DROPDOWN ===== -->
    <div v-if="canAccessGallery && !canOpenGallery" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Image class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Gallery</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <div v-else-if="canAccessGallery" class="relative">
        <button @click="toggleMenu('gallery')" 
            :class="['menu-item group w-full', isActive('/admin/gallery') ? 'menu-active' : '']">
            <div :class="['icon-wrap transition-all duration-300', 
                isActive('/admin/gallery') 
                    ? 'bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30' 
                    : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-pink-500/80 group-hover:to-rose-600/80']">
                <Image :class="['w-4 h-4 transition-all duration-300', 
                    isActive('/admin/gallery') ? 'text-white' : 'text-pink-400 group-hover:text-white']" />
            </div>
            <span class="menu-label flex-1 text-left">Gallery</span>
            <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-all duration-300', expandedMenus.gallery ? 'rotate-180 text-pink-400' : '']" />
        </button>
        <Transition name="submenu">
            <div v-if="expandedMenus.gallery" class="submenu-container">
                <Link v-for="item in menuItems.gallery" :key="item.name" :href="item.href" @click="handleNavigate"
                    :class="['submenu-item', isActive(item.href) ? 'submenu-active' : '']">
                    <span class="submenu-dot"></span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </Transition>
    </div>

    <!-- ===== ANNOUNCEMENTS ===== -->
    <div v-if="canAccessAnnouncements && !canOpenAnnouncements" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Megaphone class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Pengumuman</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessAnnouncements" href="/admin/announcements" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/announcements') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/announcements') 
                ? 'bg-gradient-to-br from-orange-500 to-red-600 shadow-lg shadow-orange-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-orange-500/80 group-hover:to-red-600/80']">
            <Megaphone :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/announcements') ? 'text-white' : 'text-orange-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Pengumuman</span>
        <div v-if="isActive('/admin/announcements')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- ===== ARTICLES ===== -->
    <div v-if="canAccessArticles && !canOpenArticles" class="menu-locked group">
        <div class="icon-wrap icon-locked"><FileText class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Artikel</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessArticles" href="/admin/articles" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/articles') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/articles') 
                ? 'bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-indigo-500/80 group-hover:to-violet-600/80']">
            <FileText :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/articles') ? 'text-white' : 'text-indigo-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Artikel</span>
        <div v-if="isActive('/admin/articles')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- ===== NEWS ===== -->
    <div v-if="canAccessNews && !canOpenNews" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Newspaper class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Berita</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessNews" href="/admin/news" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/news') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/news') 
                ? 'bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-red-500/80 group-hover:to-rose-600/80']">
            <Newspaper :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/news') ? 'text-white' : 'text-red-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Berita</span>
        <div v-if="isActive('/admin/news')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- ===== NEWSLETTER ===== -->
    <div v-if="canAccessNewsletter && !canOpenNewsletter" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Mail class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Newsletter</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <Link v-else-if="canAccessNewsletter" href="/admin/newsletter" @click="handleNavigate"
        :class="['menu-item group', isActive('/admin/newsletter') ? 'menu-active' : '']">
        <div :class="['icon-wrap transition-all duration-300', 
            isActive('/admin/newsletter') 
                ? 'bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-500/30' 
                : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-teal-500/80 group-hover:to-cyan-600/80']">
            <Mail :class="['w-4 h-4 transition-all duration-300', 
                isActive('/admin/newsletter') ? 'text-white' : 'text-teal-400 group-hover:text-white']" />
        </div>
        <span class="menu-label">Newsletter</span>
        <div v-if="isActive('/admin/newsletter')" class="active-indicator"></div>
        <ChevronRight v-else class="w-3 h-3 text-slate-600 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>

    <!-- ===== SITE INFO DROPDOWN ===== -->
    <div v-if="canAccessSiteInfo && !canOpenSiteInfo" class="menu-locked group">
        <div class="icon-wrap icon-locked"><Settings class="w-4 h-4" /><div class="lock-badge"><Lock class="w-2 h-2" /></div></div>
        <span class="menu-label">Edit Info</span>
        <Lock class="w-3 h-3 text-slate-600 ml-auto" />
    </div>
    <div v-else-if="canAccessSiteInfo" class="relative">
        <button @click="toggleMenu('siteInfo')" 
            :class="['menu-item group w-full', isActive('/admin/site-info') ? 'menu-active' : '']">
            <div :class="['icon-wrap transition-all duration-300', 
                isActive('/admin/site-info') 
                    ? 'bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/30' 
                    : 'bg-slate-800/80 group-hover:bg-gradient-to-br group-hover:from-cyan-500/80 group-hover:to-blue-600/80']">
                <Settings :class="['w-4 h-4 transition-all duration-300', 
                    isActive('/admin/site-info') ? 'text-white' : 'text-cyan-400 group-hover:text-white']" />
            </div>
            <span class="menu-label flex-1 text-left">Edit Info</span>
            <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-all duration-300', expandedMenus.siteInfo ? 'rotate-180 text-cyan-400' : '']" />
        </button>
        <Transition name="submenu">
            <div v-if="expandedMenus.siteInfo" class="submenu-container">
                <Link v-for="item in menuItems.siteInfo" :key="item.name" :href="item.href" @click="handleNavigate"
                    :class="['submenu-item', isActive(item.href) ? 'submenu-active' : '']">
                    <span class="submenu-dot"></span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Base Menu Item */
.menu-item {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 0.625rem;
    color: rgb(203 213 225);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.menu-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
    transform: translateX(-100%);
    transition: transform 0.5s;
}

.menu-item:hover::before {
    transform: translateX(100%);
}

.menu-item:hover {
    background: rgba(255,255,255,0.05);
    transform: translateX(2px);
}

/* Active Menu */
.menu-active {
    background: linear-gradient(90deg, rgba(139,92,246,0.15), rgba(168,85,247,0.1), transparent);
    border-left: 2px solid rgb(139 92 246);
    color: white;
}

/* Locked Menu */
.menu-locked {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0.625rem;
    border-radius: 0.625rem;
    color: rgb(71 85 105);
    cursor: not-allowed;
    opacity: 0.6;
}

/* Icon Wrapper */
.icon-wrap {
    width: 2.125rem;
    height: 2.125rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.menu-item:hover .icon-wrap {
    transform: scale(1.05);
}

.icon-locked {
    background: rgba(51,65,85,0.5);
    color: rgb(100 116 139);
}

.lock-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgb(51 65 85);
    border: 1px solid rgb(71 85 105);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgb(148 163 184);
}

/* Menu Label */
.menu-label {
    font-size: 0.8125rem;
    font-weight: 500;
    letter-spacing: 0.01em;
}

/* Active Indicator */
.active-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgb(139 92 246), rgb(192 132 252));
    margin-left: auto;
    box-shadow: 0 0 8px rgba(139,92,246,0.6);
    animation: glow 2s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 8px rgba(139,92,246,0.6); }
    50% { box-shadow: 0 0 16px rgba(139,92,246,0.8); }
}

/* Section Divider */
.section-divider {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 0.5rem;
    margin-top: 0.375rem;
}

.section-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(16,185,129,0.3), transparent);
}

.section-text {
    font-size: 0.625rem;
    font-weight: 700;
    color: rgba(52,211,153,0.8);
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

/* Submenu */
.submenu-container {
    margin-left: 1.125rem;
    padding-left: 0.75rem;
    margin-top: 0.25rem;
    border-left: 1px solid rgba(51,65,85,0.6);
    overflow: hidden;
}

.submenu-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.625rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    color: rgb(148 163 184);
    transition: all 0.2s;
    cursor: pointer;
}

.submenu-item:hover {
    color: white;
    background: rgba(255,255,255,0.05);
    transform: translateX(2px);
}

.submenu-active {
    color: rgb(196 181 253);
    background: rgba(139,92,246,0.1);
}

.submenu-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.5;
    transition: all 0.2s;
}

.submenu-item:hover .submenu-dot {
    opacity: 1;
    transform: scale(1.3);
}

/* Submenu Animation */
.submenu-enter-active {
    animation: submenuIn 0.3s ease-out forwards;
}
.submenu-leave-active {
    animation: submenuOut 0.2s ease-in forwards;
}

@keyframes submenuIn {
    from {
        opacity: 0;
        max-height: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        max-height: 200px;
        transform: translateY(0);
    }
}

@keyframes submenuOut {
    from {
        opacity: 1;
        max-height: 200px;
    }
    to {
        opacity: 0;
        max-height: 0;
    }
}
</style>
