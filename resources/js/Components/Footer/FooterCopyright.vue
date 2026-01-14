<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUp } from 'lucide-vue-next';

const showBackToTop = ref(false);

// Custom smooth scroll function for more reliable animation
const scrollToTop = () => {
    const scrollDuration = 2000; // Duration in ms
    const scrollStart = window.scrollY;
    const startTime = performance.now();

    const animateScroll = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / scrollDuration, 1);
        
        // Easing function (ease-out-cubic)
        const easeOutCubic = 1 - Math.pow(1 - progress, 3);
        
        window.scrollTo(0, scrollStart * (1 - easeOutCubic));
        
        if (progress < 1) {
            requestAnimationFrame(animateScroll);
        }
    };

    requestAnimationFrame(animateScroll);
};

onMounted(() => {
    const handleScroll = () => {
        showBackToTop.value = window.scrollY > 500;
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="footer-copyright pt-4 mt-4 border-t border-white/10">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-3">
            <!-- Copyright -->
            <p class="text-[10px] sm:text-xs text-slate-500 text-center sm:text-left">
                &copy; {{ new Date().getFullYear() }} <span class="font-medium text-slate-400 hover:text-white transition-colors">TNLL Explore</span>. 
                Hak cipta dilindungi.
            </p>

            <!-- Legal Links & Back to Top -->
            <div class="flex items-center gap-3 sm:gap-4 flex-wrap justify-center">
                <Link 
                    href="/privacy" 
                    class="text-slate-500 hover:text-emerald-400 transition-all duration-200 text-[10px] sm:text-xs hover:translate-x-0.5 inline-block"
                >
                    Kebijakan Privasi
                </Link>
                <Link 
                    href="/terms" 
                    class="text-slate-500 hover:text-emerald-400 transition-all duration-200 text-[10px] sm:text-xs hover:translate-x-0.5 inline-block"
                >
                    Syarat & Ketentuan
                </Link>

                <!-- Back to Top Button -->
                <Transition
                    enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 scale-90"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-300"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-90"
                >
                    <button 
                        v-if="showBackToTop"
                        @click="scrollToTop"
                        class="group flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 hover:border-emerald-500 transition-all duration-200 text-[10px] sm:text-xs font-medium"
                        aria-label="Kembali ke atas"
                    >
                        <ArrowUp class="w-3 h-3 group-hover:-translate-y-0.5 transition-transform" />
                        <span>Atas</span>
                    </button>
                </Transition>
            </div>
        </div>
    </div>
</template>
