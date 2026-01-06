<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import FAQHeader from './Partials/FAQHeader.vue';
import FAQFilters from './Partials/FAQFilters.vue';
import FAQItem from './Partials/FAQItem.vue';
import FAQEmpty from './Partials/FAQEmpty.vue';
import { Save, CheckCircle, X } from 'lucide-vue-next';

const props = defineProps({
    faqItems: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) }
});

// State
const activeCategory = ref('all');
const items = ref([]);
const searchQuery = ref('');
const showSuccess = ref(false);
const showDeleteModal = ref(false);
const itemToDelete = ref(null);

// Categories
const categories = [
    { name: 'Tiket', gradient: 'from-violet-500 to-purple-600', bgLight: 'bg-violet-100', textColor: 'text-violet-700' },
    { name: 'Wisata', gradient: 'from-emerald-500 to-teal-600', bgLight: 'bg-emerald-100', textColor: 'text-emerald-700' },
    { name: 'Fasilitas', gradient: 'from-amber-500 to-orange-600', bgLight: 'bg-amber-100', textColor: 'text-amber-700' },
    { name: 'Pembayaran', gradient: 'from-blue-500 to-indigo-600', bgLight: 'bg-blue-100', textColor: 'text-blue-700' },
    { name: 'Umum', gradient: 'from-slate-500 to-gray-600', bgLight: 'bg-slate-100', textColor: 'text-slate-700' }
];

// Initialize
onMounted(() => {
    items.value = props.faqItems.map((item, index) => ({
        id: `faq-${index}-${Date.now()}`,
        question: item.question || '',
        answer: item.answer || '',
        category: item.category || 'Umum',
        expanded: false,
        isNew: false
    }));

    if (props.flash?.success) {
        showSuccess.value = true;
        setTimeout(() => showSuccess.value = false, 5000);
    }
});

// Computed
const filteredItems = computed(() => {
    let result = items.value;
    if (activeCategory.value !== 'all') {
        result = result.filter(item => item.category === activeCategory.value);
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(item => 
            item.question.toLowerCase().includes(query) || 
            item.answer.toLowerCase().includes(query)
        );
    }
    return result;
});

// Methods
const addItem = () => {
    const newItem = {
        id: `faq-new-${Date.now()}`,
        question: '',
        answer: '',
        category: activeCategory.value !== 'all' ? activeCategory.value : 'Tiket',
        expanded: true,
        isNew: true
    };
    items.value = [newItem, ...items.value];
    if (activeCategory.value !== 'all' && activeCategory.value !== newItem.category) {
        activeCategory.value = 'all';
    }
};

const confirmDelete = (item) => {
    itemToDelete.value = item;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    if (itemToDelete.value) {
        items.value = items.value.filter(i => i.id !== itemToDelete.value.id);
        itemToDelete.value = null;
        showDeleteModal.value = false;
    }
};

const submitForm = () => {
    const emptyQuestion = items.value.some(item => !item.question.trim());
    if (emptyQuestion) {
        alert('Semua pertanyaan harus diisi!');
        return;
    }

    router.put('/admin/site-info/faq', {
        questions: items.value.map(i => i.question),
        answers: items.value.map(i => i.answer),
        categories: items.value.map(i => i.category)
    }, {
        preserveScroll: true,
        onSuccess: () => {
            items.value = items.value.map(item => ({ ...item, isNew: false }));
            showSuccess.value = true;
            setTimeout(() => showSuccess.value = false, 5000);
        }
    });
};

const getCategoryConfig = (name) => categories.find(c => c.name === name) || categories[4];
const getCategoryCount = (name) => name === 'all' ? items.value.length : items.value.filter(i => i.category === name).length;
</script>

<template>
    <AdminLayout>
        <div class="min-h-screen space-y-8">
            <!-- Header -->
            <FAQHeader :count="items.length" :categories-count="categories.length" @add="addItem" />

            <!-- Success Alert -->
            <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-4" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showSuccess" class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-6 py-5 shadow-lg">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg">
                        <CheckCircle class="h-6 w-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-emerald-800">Berhasil!</p>
                        <p class="text-emerald-600 text-sm">FAQ berhasil diperbarui!</p>
                    </div>
                    <button @click="showSuccess = false" class="p-2 rounded-lg hover:bg-emerald-100"><X class="w-5 h-5 text-emerald-600" /></button>
                </div>
            </Transition>

            <!-- Filters -->
            <FAQFilters 
                v-model:search="searchQuery" 
                v-model:category="activeCategory"
                :categories="categories"
                :get-count="getCategoryCount"
            />

            <!-- FAQ Items -->
            <div class="space-y-4" id="faq-items-container">
                <TransitionGroup tag="div" class="space-y-4"
                    enter-active-class="transition-all duration-500" enter-from-class="opacity-0 -translate-y-8 scale-95"
                    leave-active-class="transition-all duration-300" leave-to-class="opacity-0 translate-y-4"
                >
                    <FAQItem 
                        v-for="item in filteredItems" 
                        :key="item.id"
                        :item="item"
                        :categories="categories"
                        :item-number="items.indexOf(item) + 1"
                        :get-category-config="getCategoryConfig"
                        @delete="confirmDelete"
                    />
                </TransitionGroup>

                <FAQEmpty v-if="filteredItems.length === 0" :search="searchQuery" :category="activeCategory" @add="addItem" />
            </div>

            <!-- Save Button -->
            <div v-if="items.length > 0" class="sticky bottom-6 flex justify-end z-20">
                <button @click="submitForm" class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-violet-500 via-purple-500 to-violet-600 px-10 py-5 text-white font-bold text-lg shadow-2xl shadow-violet-500/40 hover:shadow-violet-500/60 hover:-translate-y-1 hover:scale-105 transition-all duration-500">
                    <Save class="w-6 h-6 group-hover:rotate-12 transition-transform" />
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            :show="showDeleteModal"
            title="Hapus FAQ?"
            message="FAQ ini akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan."
            confirm-text="Ya, Hapus"
            type="danger"
            @confirm="executeDelete"
            @close="showDeleteModal = false"
        />
    </AdminLayout>
</template>
