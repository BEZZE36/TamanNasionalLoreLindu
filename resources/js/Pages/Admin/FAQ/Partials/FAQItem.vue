<script setup>
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
        'group relative rounded-xl shadow-lg border overflow-hidden transition-all duration-300 w-full max-w-full',
        item.isNew 
            ? 'bg-gradient-to-r from-violet-50 via-purple-50 to-violet-50 border-violet-300 ring-2 ring-violet-400 ring-offset-2' 
            : 'bg-white border-gray-100 hover:border-violet-200 hover:shadow-xl'
    ]">
        <!-- Header -->
        <div class="flex items-center gap-3 p-4 cursor-pointer select-none overflow-hidden" @click="toggleExpand">
            <div class="flex items-center gap-2 flex-shrink-0">
                <div class="hidden sm:flex items-center justify-center w-5 h-8 text-gray-300 cursor-grab">
                    <GripVertical class="w-4 h-4" />
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-100 to-purple-100 text-violet-600 font-bold text-xs shadow-inner group-hover:scale-110 transition-transform flex-shrink-0">
                    {{ itemNumber }}
                </div>
            </div>

            <div class="flex-shrink-0">
                <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold whitespace-nowrap', getCategoryConfig(item.category).bgLight, getCategoryConfig(item.category).textColor]">
                    <Tag class="w-2.5 h-2.5" /> {{ item.category }}
                </span>
            </div>

            <div class="flex-1 min-w-0 overflow-hidden">
                <p class="font-semibold text-gray-800 truncate group-hover:text-violet-600 transition-colors text-xs">
                    {{ item.question || 'Pertanyaan baru...' }}
                </p>
                <p v-if="!item.expanded && item.answer" class="text-[10px] text-gray-500 truncate mt-0.5">{{ item.answer }}</p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <span v-if="item.isNew" class="px-2 py-1 rounded-lg bg-gradient-to-r from-violet-500 to-purple-600 text-white text-[10px] font-bold shadow-lg animate-pulse whitespace-nowrap">
                    <Sparkles class="w-2.5 h-2.5 inline mr-1" /> Baru
                </span>
                <div :class="['w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-500 flex-shrink-0', item.expanded ? 'bg-violet-500 text-white rotate-180 shadow-lg' : 'bg-gray-100 text-gray-500 group-hover:bg-violet-100 group-hover:text-violet-600']">
                    <ChevronDown class="w-4 h-4" />
                </div>
            </div>
        </div>

        <!-- Expanded Content -->
        <Transition enter-active-class="transition-all duration-300" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-[600px] opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-[600px]" leave-to-class="max-h-0 opacity-0">
            <div v-if="item.expanded || item.isNew" class="border-t overflow-hidden" :class="item.isNew ? 'border-violet-200 bg-gradient-to-b from-violet-50/80 to-white' : 'border-gray-100 bg-gradient-to-b from-gray-50/50 to-white'">
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 mb-2">
                                <Tag class="w-3.5 h-3.5 text-violet-500" /> Kategori
                            </label>
                            <select v-model="item.category" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 focus:bg-white py-2.5 px-3 text-xs transition-all">
                                <option v-for="cat in categories" :key="cat.name" :value="cat.name">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="lg:col-span-3">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 mb-2">
                                <HelpCircle class="w-3.5 h-3.5 text-violet-500" /> Pertanyaan
                            </label>
                            <input type="text" v-model="item.question" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 focus:bg-white py-2.5 px-3 text-xs transition-all" placeholder="Tulis pertanyaan...">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 mb-2">
                            <MessageSquare class="w-3.5 h-3.5 text-violet-500" /> Jawaban
                        </label>
                        <textarea v-model="item.answer" rows="3" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 focus:bg-white py-2.5 px-3 text-xs resize-none transition-all" placeholder="Tulis jawaban..."></textarea>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <button type="button" @click.stop="emit('delete', item)" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-red-600 hover:bg-red-50 font-semibold text-xs transition-all hover:scale-105">
                            <Trash2 class="w-3.5 h-3.5" /> Hapus
                        </button>
                        <button type="button" @click.stop="doneEditing" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 text-white font-semibold text-xs shadow-lg hover:scale-105 transition-all">
                            <Check class="w-3.5 h-3.5" /> Selesai
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
