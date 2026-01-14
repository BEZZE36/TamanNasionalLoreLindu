<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import FAQHeader from './Partials/FAQHeader.vue';
import FAQFilters from './Partials/FAQFilters.vue';
import FAQItem from './Partials/FAQItem.vue';
import FAQEmpty from './Partials/FAQEmpty.vue';
import { gsap } from 'gsap';
import { Save, CheckCircle, X, Loader2 } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    faqItems: { type: Array, default: () => [] },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

// State
const activeCategory = ref('all');
const items = ref([]);
const searchQuery = ref('');
const showSuccess = ref(false);
const showDeleteModal = ref(false);
const itemToDelete = ref(null);
const isSaving = ref(false);
let ctx;

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

    if (flash.value?.success) {
        showSuccess.value = true;
        setTimeout(() => showSuccess.value = false, 5000);
    }

    // GSAP animations
    ctx = gsap.context(() => {
        gsap.fromTo('.faq-item', 
            { opacity: 0, y: 20, scale: 0.98 }, 
            { opacity: 1, y: 0, scale: 1, duration: 0.4, stagger: 0.08, delay: 0.2, ease: 'power2.out' }
        );
    });
});

onBeforeUnmount(() => { if (ctx) ctx.revert(); });

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
        
        // Auto-save after delete
        router.put('/admin/site-info/faq', {
            questions: items.value.map(i => i.question),
            answers: items.value.map(i => i.answer),
            categories: items.value.map(i => i.category)
        }, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess.value = true;
                setTimeout(() => showSuccess.value = false, 3000);
            }
        });
    }
};

const submitForm = () => {
    const emptyQuestion = items.value.some(item => !item.question.trim());
    if (emptyQuestion) {
        alert('Semua pertanyaan harus diisi!');
        return;
    }

    isSaving.value = true;
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
        },
        onFinish: () => {
            isSaving.value = false;
        }
    });
};

const getCategoryConfig = (name) => categories.find(c => c.name === name) || categories[4];
const getCategoryCount = (name) => name === 'all' ? items.value.length : items.value.filter(i => i.category === name).length;
</script>

<template>
    <div class="min-h-screen space-y-5 overflow-x-hidden">
        <!-- Header -->
        <FAQHeader :count="items.length" :categories-count="categories.length" @add="addItem" />

        <!-- Flash Message -->
        <Transition name="slide">
            <div v-if="showSuccess" class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 px-5 py-3.5 shadow-lg">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 shadow-lg animate-bounce">
                    <CheckCircle class="h-4 w-4 text-white" />
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-emerald-800">FAQ berhasil diperbarui!</p>
                </div>
                <button @click="showSuccess = false" class="p-1.5 rounded-lg hover:bg-emerald-100 transition-colors"><X class="w-4 h-4 text-emerald-600" /></button>
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
        <div class="space-y-3">
            <TransitionGroup tag="div" class="space-y-3"
                enter-active-class="transition-all duration-500" enter-from-class="opacity-0 -translate-y-8 scale-95"
                leave-active-class="transition-all duration-300" leave-to-class="opacity-0 translate-y-4"
            >
                <div v-for="item in filteredItems" :key="item.id" class="faq-item">
                    <FAQItem 
                        :item="item"
                        :categories="categories"
                        :item-number="items.indexOf(item) + 1"
                        :get-category-config="getCategoryConfig"
                        @delete="confirmDelete"
                    />
                </div>
            </TransitionGroup>

            <FAQEmpty v-if="filteredItems.length === 0" :search="searchQuery" :category="activeCategory" @add="addItem" />
        </div>

        <!-- Save Button -->
        <div v-if="items.length > 0" class="sticky bottom-4 flex justify-end z-20">
            <button @click="submitForm" :disabled="isSaving"
                class="group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 px-6 py-3 text-white text-xs font-bold shadow-2xl shadow-purple-500/40 hover:shadow-purple-500/60 hover:-translate-y-0.5 hover:scale-105 transition-all duration-300 disabled:opacity-50">
                <Loader2 v-if="isSaving" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4 group-hover:rotate-12 transition-transform" />
                {{ isSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
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
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    transform: translateY(-16px);
}
</style>
