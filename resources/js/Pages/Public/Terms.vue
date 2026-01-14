<script setup>
import StaticPageLayout from '@/Components/StaticPageLayout.vue';

defineProps({
    content: { type: String, default: '' }
});
</script>

<template>
    <StaticPageLayout
        title="Syarat & Ketentuan"
        subtitle="Ketentuan penggunaan layanan dan aturan kunjungan TNLL Explore"
        icon="file"
        :content="content"
        last-updated="Januari 2026"
        :back-link="{ href: '/', text: 'Kembali ke Beranda' }"
        :next-link="{ href: '/privacy', text: 'Kebijakan Privasi' }"
    />
</template>
