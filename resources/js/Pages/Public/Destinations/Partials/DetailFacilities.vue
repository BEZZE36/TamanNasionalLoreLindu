<script setup>
/**
 * DetailFacilities.vue - Enhanced Facilities Grid
 * With animated icon mapping and premium hover effects
 */
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Check, Wifi, Car, Coffee, Camera, Bath, Utensils, Tent, Mountain, TreePine, Droplets, Zap, Shield, Users, Home, Globe, ParkingMeter, ShowerHead, Landmark, MapPin, Phone, Store, Bed, Wind, Sun, Waves, Bird, Fish, Flame, BookOpen, Music, Tv, Lock, Package, Truck, Plane, Train, Ship, Bike, Umbrella, Heart, Star, Gift, Award, Target, Compass, Flag, Bookmark, Database, Server, Cloud, Smartphone, Laptop, Monitor, Printer, Headphones, Radio, Speaker, Lightbulb, Power, Battery } from 'lucide-vue-next';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({ destination: { type: Object, required: true } });
const sectionRef = ref(null);
let ctx;

// Comprehensive icon mapping
const iconMap = {
    'wifi': Wifi, 'internet': Wifi, 'hotspot': Wifi,
    'parkir': Car, 'parking': Car, 'mobil': Car, 'kendaraan': Car,
    'cafe': Coffee, 'kafe': Coffee, 'kopi': Coffee, 'warung': Coffee, 'kantin': Coffee,
    'foto': Camera, 'camera': Camera, 'kamera': Camera, 'selfie': Camera,
    'toilet': Bath, 'kamar mandi': Bath, 'wc': Bath, 'bathroom': Bath, 'mck': Bath,
    'makan': Utensils, 'restaurant': Utensils, 'restoran': Utensils, 'rumah makan': Utensils,
    'camping': Tent, 'kemah': Tent, 'tenda': Tent, 'camp': Tent,
    'hiking': Mountain, 'pendakian': Mountain, 'gunung': Mountain, 'tracking': Mountain, 'trekking': Mountain,
    'hutan': TreePine, 'forest': TreePine, 'pohon': TreePine,
    'air terjun': Droplets, 'waterfall': Droplets, 'air': Droplets, 'sungai': Droplets,
    'listrik': Zap, 'electricity': Zap, 'power': Power, 'stop kontak': Zap,
    'keamanan': Shield, 'security': Shield, 'satpam': Shield, 'aman': Shield,
    'pemandu': Users, 'guide': Users, 'tour': Users, 'wisata': Users,
    'penginapan': Home, 'homestay': Home, 'villa': Home, 'hotel': Bed, 'cottage': Home,
    'mushola': Landmark, 'masjid': Landmark, 'tempat ibadah': Landmark,
    'gazebo': Umbrella, 'shelter': Umbrella, 'saung': Umbrella,
    'wahana': Star, 'permainan': Star, 'playground': Star,
    'souvenir': Gift, 'oleh': Gift, 'cendera': Gift,
    'default': Globe
};

const getIcon = (name) => {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(iconMap)) {
        if (lower.includes(key)) return icon;
    }
    return iconMap.default;
};

onMounted(() => {
    ctx = gsap.context(() => {
        gsap.fromTo('.facility-item', { opacity: 0, scale: 0.9, y: 15 }, { opacity: 1, scale: 1, y: 0, duration: 0.4, stagger: 0.05, ease: 'back.out(2)', scrollTrigger: { trigger: sectionRef.value, start: 'top 85%' } });
    }, sectionRef.value);
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });
</script>

<template>
    <div v-if="destination.facilities?.length > 0" ref="sectionRef" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <Check class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Fasilitas</h2>
                    <p class="text-[10px] text-gray-500">{{ destination.facilities.length }} fasilitas tersedia</p>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <div v-for="(facility, idx) in destination.facilities" :key="idx" class="facility-item group flex items-center gap-2.5 p-3 rounded-xl bg-gray-50 hover:bg-emerald-50 border border-gray-100 hover:border-emerald-200 cursor-default transition-all duration-300">
                    <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:bg-emerald-100 group-hover:shadow-md transition-all duration-300">
                        <component :is="getIcon(facility)" class="w-4 h-4 text-gray-500 group-hover:text-emerald-600 transition-colors duration-300" />
                    </div>
                    <span class="text-[11px] text-gray-700 font-medium group-hover:text-emerald-700 transition-colors duration-300">{{ facility }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
