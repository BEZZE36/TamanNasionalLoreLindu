<script setup>
import { onMounted } from 'vue';
import { Facebook, Instagram, Youtube } from 'lucide-vue-next';

const props = defineProps({
    siteInfo: Object
});

// Debug: Log siteInfo to console
onMounted(() => {
    console.log('FooterBrand siteInfo:', props.siteInfo);
    console.log('Facebook URL:', props.siteInfo?.facebook_url);
    console.log('Instagram URL:', props.siteInfo?.instagram_url);
});

// Format WhatsApp number for link
const formatWhatsApp = (wa) => {
    if (!wa) return '';
    return wa.replace(/^0/, '62').replace(/[^\d]/g, '');
};
</script>

<template>
    <div class="footer-brand space-y-4">
        <!-- Logo Section -->
        <div class="flex items-center gap-3 group">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg blur-md opacity-30 group-hover:opacity-50 transition-all duration-500"></div>
                <div class="relative h-12 w-12 rounded-lg bg-slate-800/80 border border-white/10 p-1.5">
                    <img src="/assets/logo.png" alt="TNLL" class="h-full w-full object-contain">
                </div>
            </div>
            <div>
                <h3 class="flex items-baseline gap-1.5">
                    <span class="text-lg font-bold bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">TNLL</span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-400">Explore</span>
                </h3>
                <p class="text-[10px] text-slate-500">Taman Nasional Lore Lindu</p>
            </div>
        </div>

        <!-- Description -->
        <p class="text-slate-400 text-xs leading-relaxed">
            Jelajahi keajaiban alam Taman Nasional Lore Lindu — keanekaragaman hayati unik dan budaya megalitikum di Sulawesi Tengah.
        </p>

        <!-- Social Media Section -->
        <div>
            <h4 class="text-slate-500 text-[10px] uppercase tracking-wider mb-2">Ikuti Kami</h4>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Facebook -->
                <a 
                    :href="siteInfo?.facebook_url || '#'" 
                    :target="siteInfo?.facebook_url ? '_blank' : '_self'"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300',
                        siteInfo?.facebook_url 
                            ? 'bg-white/5 border border-white/10 text-slate-400 hover:bg-[#1877F2]/20 hover:text-[#1877F2] hover:border-[#1877F2]/40 hover:scale-110' 
                            : 'bg-slate-800/30 border border-white/5 text-slate-600 cursor-not-allowed'
                    ]"
                    aria-label="Facebook"
                >
                    <Facebook class="w-4 h-4" />
                </a>
                <!-- Instagram -->
                <a 
                    :href="siteInfo?.instagram_url || '#'" 
                    :target="siteInfo?.instagram_url ? '_blank' : '_self'"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300',
                        siteInfo?.instagram_url 
                            ? 'bg-white/5 border border-white/10 text-slate-400 hover:bg-pink-500/20 hover:text-pink-400 hover:border-pink-500/40 hover:scale-110' 
                            : 'bg-slate-800/30 border border-white/5 text-slate-600 cursor-not-allowed'
                    ]"
                    aria-label="Instagram"
                >
                    <Instagram class="w-4 h-4" />
                </a>
                <!-- YouTube -->
                <a 
                    :href="siteInfo?.youtube_url || '#'" 
                    :target="siteInfo?.youtube_url ? '_blank' : '_self'"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300',
                        siteInfo?.youtube_url 
                            ? 'bg-white/5 border border-white/10 text-slate-400 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/40 hover:scale-110' 
                            : 'bg-slate-800/30 border border-white/5 text-slate-600 cursor-not-allowed'
                    ]"
                    aria-label="YouTube"
                >
                    <Youtube class="w-4 h-4" />
                </a>
                <!-- TikTok -->
                <a 
                    :href="siteInfo?.tiktok_url || '#'" 
                    :target="siteInfo?.tiktok_url ? '_blank' : '_self'"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300',
                        siteInfo?.tiktok_url 
                            ? 'bg-white/5 border border-white/10 text-slate-400 hover:bg-white/10 hover:text-white hover:border-white/30 hover:scale-110' 
                            : 'bg-slate-800/30 border border-white/5 text-slate-600 cursor-not-allowed'
                    ]"
                    aria-label="TikTok"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                </a>
                <!-- WhatsApp -->
                <a 
                    :href="siteInfo?.contact_whatsapp ? `https://wa.me/${formatWhatsApp(siteInfo.contact_whatsapp)}` : '#'" 
                    :target="siteInfo?.contact_whatsapp ? '_blank' : '_self'"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-300',
                        siteInfo?.contact_whatsapp 
                            ? 'bg-white/5 border border-white/10 text-slate-400 hover:bg-emerald-500/20 hover:text-emerald-400 hover:border-emerald-500/40 hover:scale-110' 
                            : 'bg-slate-800/30 border border-white/5 text-slate-600 cursor-not-allowed'
                    ]"
                    aria-label="WhatsApp"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
            </div>
        </div>
    </div>
</template>
