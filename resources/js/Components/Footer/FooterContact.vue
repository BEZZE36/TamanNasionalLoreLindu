<script setup>
import { computed } from 'vue';
import { MapPin, Mail, Phone, Clock } from 'lucide-vue-next';

const props = defineProps({
    siteInfo: Object
});

// Format phone number to +62 XXX XXXX XXXX
const formatPhone = (phone) => {
    if (!phone) return '';
    // Remove all non-digits
    let digits = phone.replace(/[^\d]/g, '');
    // Remove leading 0 or 62
    if (digits.startsWith('0')) {
        digits = digits.substring(1);
    } else if (digits.startsWith('62')) {
        digits = digits.substring(2);
    }
    // Format: XXX XXXX XXXX
    if (digits.length >= 10) {
        return `+62 ${digits.substring(0, 3)} ${digits.substring(3, 7)} ${digits.substring(7)}`;
    }
    return `+62 ${digits}`;
};

// Get raw digits for tel: link
const getRawPhone = (phone) => {
    if (!phone) return '';
    let digits = phone.replace(/[^\d]/g, '');
    if (digits.startsWith('0')) {
        digits = '62' + digits.substring(1);
    } else if (!digits.startsWith('62')) {
        digits = '62' + digits;
    }
    return digits;
};

// Day mapping for Indonesian day names
const dayMapping = {
    0: 'minggu',
    1: 'senin',
    2: 'selasa',
    3: 'rabu',
    4: 'kamis',
    5: 'jumat',
    6: 'sabtu'
};

const dayLabels = {
    'minggu': 'Minggu',
    'senin': 'Senin',
    'selasa': 'Selasa',
    'rabu': 'Rabu',
    'kamis': 'Kamis',
    'jumat': 'Jumat',
    'sabtu': 'Sabtu'
};

// Get today's operational hours
const todayHours = computed(() => {
    const today = new Date();
    const dayKey = dayMapping[today.getDay()];
    const hours = props.siteInfo?.operational_hours?.[dayKey];
    
    if (!hours) {
        return { day: dayLabels[dayKey], text: '08:00 - 17:00', isOpen: true };
    }
    
    if (!hours.is_open) {
        return { day: dayLabels[dayKey], text: 'Tutup', isOpen: false };
    }
    
    return { 
        day: dayLabels[dayKey], 
        text: `${hours.open_time || '08:00'} - ${hours.close_time || '17:00'}`,
        isOpen: true 
    };
});
</script>

<template>
    <div class="footer-contact">
        <h3 class="text-white font-medium text-sm mb-3 flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
            </span>
            Hubungi Kami
        </h3>

        <div class="space-y-4">

            <!-- Phone -->
            <a 
                v-if="siteInfo?.contact_phone"
                :href="`tel:+${getRawPhone(siteInfo.contact_phone)}`"
                class="footer-contact-item group flex items-center gap-2 text-slate-400 hover:text-teal-400 transition-all duration-200"
            >
                <Phone class="w-3.5 h-3.5 text-teal-400 flex-shrink-0 group-hover:scale-110 transition-transform" />
                <span class="text-xs">{{ formatPhone(siteInfo.contact_phone) }}</span>
            </a>

            <!-- Email -->
            <a 
                v-if="siteInfo?.contact_email"
                :href="`mailto:${siteInfo.contact_email}`"
                class="footer-contact-item group flex items-center gap-2 text-slate-400 hover:text-blue-400 transition-all duration-200"
            >
                <Mail class="w-3.5 h-3.5 text-blue-400 flex-shrink-0 group-hover:scale-110 transition-transform" />
                <span class="text-xs truncate max-w-[180px]">{{ siteInfo.contact_email }}</span>
            </a>

            <!-- WhatsApp -->
            <a 
                v-if="siteInfo?.contact_whatsapp"
                :href="`https://wa.me/${siteInfo.contact_whatsapp?.replace(/^0/, '62').replace(/[^\d]/g, '')}`"
                target="_blank"
                class="footer-contact-item group flex items-center gap-2 text-slate-400 hover:text-green-400 transition-all duration-200"
            >
                <svg class="w-3.5 h-3.5 text-green-400 flex-shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span class="text-xs">WhatsApp</span>
            </a>

            <!-- Operating Hours -->
            <div class="footer-contact-item flex items-center gap-2 text-slate-400">
                <Clock class="w-3.5 h-3.5 flex-shrink-0" :class="todayHours.isOpen ? 'text-amber-400' : 'text-red-400'" />
                <div class="text-xs">
                    <span class="text-slate-500">{{ todayHours.day }}:</span>
                    <span :class="todayHours.isOpen ? 'text-slate-300' : 'text-red-400'" class="ml-1">{{ todayHours.text }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
