<script setup>
import { ref } from 'vue';
import { ChevronDown, Tag, Sparkles, GripVertical, HelpCircle, MessageSquare, Check, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    item: { type: Object, required: true },
    categories: { type: Array, required: true },
    itemNumber: { type: Number, required: true },
    getCategoryConfig: { type: Function, required: true }
});

const emit = defineEmits(['delete']);

const toggleExpand = () => {
    props.item.expanded = !props.item.expanded;
};

const doneEditing = () => {
    props.item.expanded = false;
    props.item.isNew = false;
};

const catConfig = props.getCategoryConfig(props.item.category);
</script>

<template>
    <div :class="[
        'group relative rounded-3xl shadow-lg border overflow-hidden transition-all duration-500',
        item.isNew 
            ? 'bg-gradient-to-r from-violet-50 via-purple-50 to-violet-50 border-violet-300 ring-2 ring-violet-400 ring-offset-2' 
            : 'bg-white border-gray-100 hover:border-violet-200 hover:shadow-xl'
    ]">
        <!-- Header -->
        <div class="flex items-center gap-4 p-6 cursor-pointer select-none" @click="toggleExpand">
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center justify-center w-6 h-10 text-gray-300 cursor-grab">
                    <GripVertical class="w-5 h-5" />
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-100 to-purple-100 text-violet-600 font-bold text-sm shadow-inner group-hover:scale-110 transition-transform">
                    {{ itemNumber }}
                </div>
            </div>

            <div class="flex-shrink-0">
                <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold', getCategoryConfig(item.category).bgLight, getCategoryConfig(item.category).textColor]">
                    <Tag class="w-3 h-3" /> {{ item.category }}
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 truncate group-hover:text-violet-600 transition-colors text-lg">
                    {{ item.question || 'Pertanyaan baru...' }}
                </p>
                <p v-if="!item.expanded && item.answer" class="text-sm text-gray-500 truncate mt-1">{{ item.answer }}</p>
            </div>

            <div class="flex items-center gap-3">
                <span v-if="item.isNew" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white text-xs font-bold shadow-lg animate-pulse">
                    <Sparkles class="w-3 h-3 inline mr-1" /> Baru
                </span>
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-500', item.expanded ? 'bg-violet-500 text-white rotate-180 shadow-lg' : 'bg-gray-100 text-gray-500 group-hover:bg-violet-100 group-hover:text-violet-600']">
                    <ChevronDown class="w-5 h-5" />
                </div>
            </div>
        </div>

        <!-- Expanded Content -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-[600px] opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-[600px]" leave-to-class="max-h-0 opacity-0">
            <div v-if="item.expanded || item.isNew" class="border-t overflow-hidden" :class="item.isNew ? 'border-violet-200 bg-gradient-to-b from-violet-50/80 to-white' : 'border-gray-100 bg-gradient-to-b from-gray-50/50 to-white'">
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                                <Tag class="w-4 h-4 text-violet-500" /> Kategori
                            </label>
                            <select v-model="item.category" class="w-full rounded-2xl border-gray-200 bg-white shadow-sm focus:border-violet-400 focus:ring-4 focus:ring-violet-100 py-3.5 px-4 text-sm">
                                <option v-for="cat in categories" :key="cat.name" :value="cat.name">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="lg:col-span-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                                <HelpCircle class="w-4 h-4 text-violet-500" /> Pertanyaan
                            </label>
                            <input type="text" v-model="item.question" class="w-full rounded-2xl border-gray-200 bg-white shadow-sm focus:border-violet-400 focus:ring-4 focus:ring-violet-100 py-3.5 px-4 text-sm" placeholder="Tulis pertanyaan...">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                            <MessageSquare class="w-4 h-4 text-violet-500" /> Jawaban
                        </label>
                        <textarea v-model="item.answer" rows="4" class="w-full rounded-2xl border-gray-200 bg-white shadow-sm focus:border-violet-400 focus:ring-4 focus:ring-violet-100 py-3.5 px-4 text-sm resize-none" placeholder="Tulis jawaban..."></textarea>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <button type="button" @click.stop="emit('delete', item)" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-red-600 hover:bg-red-50 font-semibold text-sm transition-all hover:scale-105">
                            <Trash2 class="w-4 h-4" /> Hapus FAQ
                        </button>
                        <button type="button" @click.stop="doneEditing" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-semibold text-sm shadow-lg hover:scale-105 transition-all">
                            <Check class="w-4 h-4" /> Selesai Edit
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
