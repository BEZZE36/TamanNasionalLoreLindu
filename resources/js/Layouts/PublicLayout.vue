<script setup>
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X, LogIn } from 'lucide-vue-next';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// Import navbar partials
import NavbarDesktopMenu from '@/Components/Navbar/NavbarDesktopMenu.vue';
import NavbarSearchButton from '@/Components/Navbar/NavbarSearchButton.vue';
import NavbarLanguageSwitcher from '@/Components/Navbar/NavbarLanguageSwitcher.vue';
import NavbarNotifications from '@/Components/Navbar/NavbarNotifications.vue';
import NavbarUserMenu from '@/Components/Navbar/NavbarUserMenu.vue';
import NavbarMobileMenu from '@/Components/Navbar/NavbarMobileMenu.vue';
import SearchModal from '@/Components/SearchModal.vue';

// Import FloatingUI components
import FloatingChatbot from '@/Components/FloatingUI/FloatingChatbot.vue';
import ScrollProgressBar from '@/Components/FloatingUI/ScrollProgressBar.vue';

// Import Page Loading Overlay
import PageLoadingOverlay from '@/Components/Skeleton/PageLoadingOverlay.vue';

// Import Block Detector for real-time block notification
import BlockDetector from '@/Components/BlockDetector.vue';

// Import Footer partials
import FooterBrand from '@/Components/Footer/FooterBrand.vue';
import FooterLinks from '@/Components/Footer/FooterLinks.vue';
import FooterContact from '@/Components/Footer/FooterContact.vue';
import FooterMap from '@/Components/Footer/FooterMap.vue';
import FooterNewsletter from '@/Components/Footer/FooterNewsletter.vue';
import FooterCopyright from '@/Components/Footer/FooterCopyright.vue';

const page = usePage();

// Get user and siteInfo from shared Inertia props
const user = computed(() => page.props.auth?.user);
const siteInfo = computed(() => page.props.siteInfo || {});

// Mobile menu state
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const footerRef = ref(null);
let footerCtx;

// Handle scroll
const handleScroll = () => {
    scrolled.value = window.scrollY > 50;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    
    // Footer GSAP Animations - optimized for performance
    if (footerRef.value) {
        footerCtx = gsap.context(() => {
            // Background orbs floating animation
            gsap.to('.footer-orb', {
                y: 'random(-15, 15)',
                x: 'random(-8, 8)',
                scale: 'random(0.95, 1.05)',
                duration: 'random(5, 8)',
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
                stagger: 0.8
            });

            // Footer sections entrance animation
            gsap.fromTo('.footer-brand',
                { opacity: 0, y: 30, scale: 0.95 },
                { opacity: 1, y: 0, scale: 1, duration: 0.7, ease: 'power3.out',
                  scrollTrigger: { trigger: footerRef.value, start: 'top 90%', toggleActions: 'play none none none' }
                }
            );

            gsap.fromTo('.footer-links-item',
                { opacity: 0, x: -15 },
                { opacity: 1, x: 0, duration: 0.5, stagger: 0.03, ease: 'power2.out',
                  scrollTrigger: { trigger: '.footer-links', start: 'top 85%', toggleActions: 'play none none none' }
                }
            );

            gsap.fromTo('.footer-contact-item',
                { opacity: 0, x: 15 },
                { opacity: 1, x: 0, duration: 0.5, stagger: 0.06, ease: 'power2.out',
                  scrollTrigger: { trigger: '.footer-contact', start: 'top 85%', toggleActions: 'play none none none' }
                }
            );



            gsap.fromTo('.footer-map',
                { opacity: 0, scale: 0.95 },
                { opacity: 1, scale: 1, duration: 0.6, ease: 'power2.out',
                  scrollTrigger: { trigger: '.footer-map', start: 'top 85%', toggleActions: 'play none none none' }
                }
            );

            gsap.fromTo('.footer-newsletter',
                { opacity: 0, y: 20 },
                { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out',
                  scrollTrigger: { trigger: '.footer-newsletter', start: 'top 90%', toggleActions: 'play none none none' }
                }
            );

            gsap.fromTo('.footer-copyright',
                { opacity: 0, y: 15 },
                { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out',
                  scrollTrigger: { trigger: '.footer-copyright', start: 'top 95%', toggleActions: 'play none none none' }
                }
            );

            // Gradient line animation
            gsap.fromTo('.footer-gradient-line',
                { scaleX: 0 },
                { scaleX: 1, duration: 1.2, ease: 'power2.inOut',
                  scrollTrigger: { trigger: footerRef.value, start: 'top 95%', toggleActions: 'play none none none' }
                }
            );
        }, footerRef.value);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    if (footerCtx) footerCtx.revert();
});

// Toggle mobile menu
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
        <!-- Page Loading Overlay -->
        <PageLoadingOverlay />
        
        <!-- Block Detector - checks if user has been blocked -->
        <BlockDetector />
        
        <!-- Navbar -->
        <nav 
            :class="[
                'fixed top-0 left-0 right-0 z-50 transition-[background,backdrop-filter,box-shadow] duration-300 ease-out',
                scrolled 
                    ? 'bg-white/95 backdrop-blur-xl shadow-[0_1px_3px_rgba(0,0,0,0.05),0_1px_0_rgba(0,0,0,0.03)]' 
                    : 'bg-gradient-to-b from-black/30 via-black/15 to-transparent backdrop-blur-[2px] shadow-none'
            ]"
        >
            <div class="w-full px-2 xs:px-3 sm:px-4 md:px-5 lg:px-6 xl:px-8 mx-auto max-w-[1920px]">
                <!-- Grid Layout - Responsive for all devices -->
                <div class="grid grid-cols-[auto_1fr_auto] items-center gap-2 sm:gap-3 lg:gap-6 h-12 sm:h-14">
                    
                    <!-- Logo - Click to toggle mobile menu on mobile/tablet, normal link on desktop -->
                    <div class="flex items-center gap-1.5 sm:gap-2.5 group flex-shrink-0">
                        <!-- Mobile: Button to toggle menu - Shows logo or X based on menu state -->
                        <button 
                            @click="toggleMobileMenu"
                            class="lg:hidden flex items-center gap-1.5 sm:gap-2.5"
                            :aria-expanded="mobileMenuOpen"
                            aria-label="Menu navigasi"
                        >
                            <div class="relative flex-shrink-0">
                                <div class="absolute -inset-2 bg-gradient-to-r from-emerald-400/30 via-teal-400/30 to-cyan-400/30 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                                <!-- Logo when menu is closed -->
                                <Transition
                                    enter-active-class="transition-all duration-300 ease-out"
                                    enter-from-class="opacity-0 rotate-90 scale-75"
                                    enter-to-class="opacity-100 rotate-0 scale-100"
                                    leave-active-class="transition-all duration-200 ease-in"
                                    leave-from-class="opacity-100 rotate-0 scale-100"
                                    leave-to-class="opacity-0 -rotate-90 scale-75"
                                    mode="out-in"
                                >
                                    <img 
                                        v-if="!mobileMenuOpen"
                                        src="/assets/logo.png" 
                                        alt="TNLL" 
                                        class="relative h-8 w-8 sm:h-10 sm:w-10 object-contain"
                                    >
                                    <!-- Close icon when menu is open -->
                                    <div 
                                        v-else
                                        :class="[
                                            'relative h-8 w-8 sm:h-10 sm:w-10 rounded-lg flex items-center justify-center transition-colors duration-300',
                                            scrolled ? 'bg-gray-100 text-gray-700' : 'bg-white/20 text-white'
                                        ]"
                                    >
                                        <X class="w-5 h-5 sm:w-6 sm:h-6" />
                                    </div>
                                </Transition>
                            </div>
                            <!-- Text - hidden when menu is open for cleaner look -->
                            <div v-if="!mobileMenuOpen" class="flex flex-col leading-tight">
                                <div class="flex items-baseline gap-0.5 sm:gap-1">
                                    <span :class="['text-sm sm:text-base font-extrabold tracking-tight transition-all duration-300', scrolled ? 'text-gray-900' : 'text-white']">TNLL</span>
                                    <span :class="['text-[9px] sm:text-[10px] font-bold uppercase tracking-widest transition-all duration-300', scrolled ? 'text-emerald-600' : 'text-emerald-400']">EXPLORE</span>
                                </div>
                            </div>
                        </button>
                        
                        <!-- Desktop: Normal link to homepage -->
                        <Link href="/" class="hidden lg:flex items-center gap-2.5" aria-label="TNLL Explore - Beranda">
                            <div class="relative flex-shrink-0">
                                <div class="absolute -inset-2 bg-gradient-to-r from-emerald-400/30 via-teal-400/30 to-cyan-400/30 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                                <img 
                                    src="/assets/logo.png" 
                                    alt="TNLL" 
                                    class="relative h-14 w-14 object-contain transition-transform duration-300 group-hover:scale-110"
                                >
                            </div>
                            <div class="flex flex-col leading-tight">
                                <div class="flex items-baseline gap-1">
                                    <span :class="['text-lg font-extrabold tracking-tight transition-all duration-300', scrolled ? 'text-gray-900' : 'text-white']">TNLL</span>
                                    <span :class="['text-xs font-bold uppercase tracking-widest transition-all duration-300', scrolled ? 'text-emerald-600' : 'text-emerald-400']">EXPLORE</span>
                                </div>
                                <span :class="['text-[9px] font-medium tracking-wide transition-all duration-300 italic', scrolled ? 'text-teal-600' : 'text-teal-300']">Taman Nasional Lore Lindu</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Desktop Menu -->
                    <NavbarDesktopMenu :scrolled="scrolled" />

                    <!-- Column 3: Right Side Actions - Now visible on all screen sizes -->
                    <div class="flex items-center justify-end flex-shrink-0 gap-1.5 sm:gap-2 lg:gap-3">
                        <!-- Search Button -->
                        <NavbarSearchButton :scrolled="scrolled" />
                        
                        <!-- Divider - Visible on all sizes -->
                        <div :class="['w-px h-3 sm:h-4 transition-colors duration-300', scrolled ? 'bg-gray-300' : 'bg-white/40']"></div>
                        
                        <!-- Language Switcher -->
                        <NavbarLanguageSwitcher :scrolled="scrolled" />
                        
                        <!-- Divider - Visible on all sizes -->
                        <div :class="['w-px h-3 sm:h-4 transition-colors duration-300', scrolled ? 'bg-gray-300' : 'bg-white/40']"></div>
                        
                        <!-- Notifications -->
                        <NavbarNotifications :scrolled="scrolled" :user="user" />
                        
                        <!-- Auth -->
                        <template v-if="!user">
                            <!-- Divider - Visible on all sizes -->
                            <div :class="['w-px h-3 sm:h-4 transition-colors duration-300', scrolled ? 'bg-gray-300' : 'bg-white/40']"></div>
                            
                            <!-- Login Button - Text hidden on mobile -->
                            <Link 
                                href="/login"
                                :class="[
                                    'h-8 sm:h-9 px-2 sm:px-3 flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold transition-all duration-300 rounded-lg',
                                    scrolled ? 'text-gray-700 hover:text-violet-600 hover:bg-gray-100' : 'text-white hover:bg-white/20'
                                ]"
                            >
                                <LogIn class="w-4 h-4" />
                                <span class="hidden sm:inline">Masuk</span>
                            </Link>
                        </template>
                        <template v-else>
                            <!-- Divider - Visible on all sizes -->
                            <div :class="['w-px h-3 sm:h-4 transition-colors duration-300', scrolled ? 'bg-gray-300' : 'bg-white/40']"></div>
                            
                            <!-- User Menu -->
                            <NavbarUserMenu :scrolled="scrolled" :user="user" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <NavbarMobileMenu :open="mobileMenuOpen" :user="user" @close="closeMobileMenu" />
        </nav>

        <!-- Search Modal -->
        <SearchModal :user="user" />

        <!-- Floating UI Components (Vue) -->
        <ScrollProgressBar />
        <FloatingChatbot />

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer ref="footerRef" class="relative bg-gradient-to-b from-[#030712] via-[#050a18] to-[#030712] text-white mt-10 overflow-hidden">
            <!-- Animated Gradient Line -->
            <div class="footer-gradient-line absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-emerald-500/60 to-transparent origin-center"></div>
            
            <!-- Background Orbs (GSAP animated) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="footer-orb absolute -top-16 -right-16 w-48 h-48 bg-gradient-to-br from-emerald-600/15 to-teal-600/8 rounded-full blur-3xl"></div>
                <div class="footer-orb absolute top-1/3 left-1/4 w-32 h-32 bg-gradient-to-br from-teal-500/10 to-cyan-500/5 rounded-full blur-3xl"></div>
                <div class="footer-orb absolute -bottom-16 -left-16 w-48 h-48 bg-gradient-to-br from-cyan-600/12 to-blue-600/8 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
                <!-- Main Footer Content - 5 Column Layout -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 lg:gap-8">
                    <!-- Brand Section -->
                    <div class="col-span-2 md:col-span-3 lg:col-span-1">
                        <FooterBrand :siteInfo="siteInfo" />
                    </div>

                    <!-- Links Section (Tautan + Jelajahi) -->
                    <div class="col-span-1">
                        <FooterLinks />
                    </div>

                    <!-- Contact Section -->
                    <div class="col-span-1">
                        <FooterContact :siteInfo="siteInfo" />
                    </div>

                    <!-- Map Section -->
                    <div class="col-span-2 md:col-span-1 lg:col-span-2">
                        <FooterMap :siteInfo="siteInfo" />
                    </div>
                </div>

                <!-- Newsletter Section -->
                <div class="mt-8 pt-6 border-t border-white/5">
                    <FooterNewsletter />
                </div>

                <!-- Copyright Section -->
                <FooterCopyright />
            </div>
        </footer>
    </div>
</template>
