<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, Image, Video } from 'lucide-vue-next';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    destinations: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) }
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const category = ref(props.filters.category || '');
const destination = ref(props.filters.destination || '');
const sort = ref(props.filters.sort || 'newest');

const handleSearch = () => {
    router.get('/gallery', {
        search: search.value || undefined,
        type: type.value || undefined,
        category: category.value || undefined,
        destination: destination.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined
    }, { preserveState: true });
};

const onFilterChange = () => {
    router.get('/gallery', {
        search: props.filters.search || undefined,
        type: type.value || undefined,
        category: category.value || undefined,
        destination: destination.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined
    }, { preserveState: true });
};
</script>

<template>
    <section class="py-6 bg-white border-b border-gray-100 sticky top-20 z-30 backdrop-blur-xl bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row items-center gap-4 flex-wrap">
                <!-- Search -->
                <div class="w-full md:w-auto flex-1 max-w-md relative">
                    <input v-model="search" type="text" placeholder="Cari galeri..."
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                </div>

                <!-- Type Filter -->
                <div class="flex gap-2">
                    <button type="button" @click="type = ''; onFilterChange()"
                        :class="['px-4 py-2 rounded-xl border transition-all flex items-center gap-2', !type ? 'bg-violet-500 text-white border-violet-500' : 'border-gray-200 text-gray-600 hover:border-violet-300']">
                        Semua
                    </button>
                    <button type="button" @click="type = 'image'; onFilterChange()"
                        :class="['px-4 py-2 rounded-xl border transition-all flex items-center gap-2', type === 'image' ? 'bg-violet-500 text-white border-violet-500' : 'border-gray-200 text-gray-600 hover:border-violet-300']">
                        <Image class="w-4 h-4" />Foto
                    </button>
                    <button type="button" @click="type = 'video'; onFilterChange()"
                        :class="['px-4 py-2 rounded-xl border transition-all flex items-center gap-2', type === 'video' ? 'bg-violet-500 text-white border-violet-500' : 'border-gray-200 text-gray-600 hover:border-violet-300']">
                        <Video class="w-4 h-4" />Video
                    </button>
                </div>

                <!-- Category -->
                <select v-model="category" @change="onFilterChange()" class="px-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Semua Kategori</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                </select>

                <!-- Destination -->
                <select v-model="destination" @change="onFilterChange()" class="px-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Semua Destinasi</option>
                    <option v-for="dest in destinations" :key="dest.id" :value="dest.id">{{ dest.name }}</option>
                </select>

                <!-- Sort -->
                <select v-model="sort" @change="onFilterChange()" class="px-4 py-3 rounded-xl border border-gray-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="newest">Terbaru</option>
                    <option value="popular">Terpopuler</option>
                    <option value="alphabetical">A-Z</option>
                </select>

                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-violet-500 to-purple-600 text-white font-bold rounded-xl shadow-lg shadow-violet-500/30 hover:shadow-violet-500/50 transition-all flex items-center gap-2">
                    <Search class="w-5 h-5" /><span>Cari</span>
                </button>
            </form>
        </div>
    </section>
</template>
