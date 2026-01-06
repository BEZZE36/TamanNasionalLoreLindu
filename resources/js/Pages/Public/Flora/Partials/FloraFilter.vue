<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    categories: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

// Handle search
const handleSearch = () => {
    router.get('/flora', {
        search: search.value || undefined,
        category: category.value || undefined
    }, { preserveState: true });
};

// Handle category change
const onCategoryChange = () => {
    router.get('/flora', {
        search: props.filters.search || undefined,
        category: category.value || undefined
    }, { preserveState: true });
};
</script>

<template>
    <section class="py-6 bg-white border-b border-gray-100 sticky top-20 z-30 backdrop-blur-xl bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row items-center gap-4">
                <!-- Search -->
                <div class="w-full md:w-auto flex-1 max-w-md relative">
                    <input 
                        v-model="search"
                        type="text"
                        placeholder="Cari flora..."
                        class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all"
                    >
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                </div>

                <!-- Category Filter -->
                <select 
                    v-model="category"
                    @change="onCategoryChange"
                    class="px-4 py-3 rounded-xl border border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                >
                    <option value="">Semua Kategori</option>
                    <option 
                        v-for="(label, key) in categories"
                        :key="key"
                        :value="key"
                    >
                        {{ label }}
                    </option>
                </select>

                <!-- Search Button -->
                <button 
                    type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 hover:shadow-green-500/50 transition-all flex items-center gap-2"
                >
                    <Search class="w-5 h-5" />
                    <span>Cari</span>
                </button>
            </form>
        </div>
    </section>
</template>
