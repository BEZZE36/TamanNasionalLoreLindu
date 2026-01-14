<script setup>
import StaticPageLayout from '@/Components/StaticPageLayout.vue';

defineProps({
    content: { type: String, default: '' }
});
</script>

<template>
    <StaticPageLayout
        title="Kebijakan Privasi"
        subtitle="Komitmen kami dalam melindungi data dan privasi Anda sebagai pengguna TNLL Explore"
        icon="shield"
        :content="content"
        last-updated="Januari 2026"
        :back-link="{ href: '/', text: 'Kembali ke Beranda' }"
        :next-link="{ href: '/terms', text: 'Syarat & Ketentuan' }"
    />
</template>
