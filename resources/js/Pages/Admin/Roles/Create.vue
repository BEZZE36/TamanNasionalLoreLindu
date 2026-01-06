<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { gsap } from 'gsap';
import { Shield, ArrowLeft, Save, Key, Check, Loader2, Sparkles, ChevronDown, ChevronUp } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    permissions: Object
});

const pageRef = ref(null);
const expandedGroups = ref({});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: []
});

const generateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
};

// Auto-generate slug from name
watch(() => form.name, () => {
    if (!form.slug || form.slug === generateSlugFromName(form.name.slice(0, -1))) {
        generateSlug();
    }
});

const generateSlugFromName = (name) => name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

const toggleGroup = (group) => {
    expandedGroups.value[group] = !expandedGroups.value[group];
};

const togglePermission = (id) => {
    const index = form.permissions.indexOf(id);
    if (index > -1) form.permissions.splice(index, 1);
    else form.permissions.push(id);
};

const selectAllGroup = (groupPermissions) => {
    const ids = groupPermissions.map(p => p.id);
    const allSelected = ids.every(id => form.permissions.includes(id));
    if (allSelected) {
        form.permissions = form.permissions.filter(id => !ids.includes(id));
    } else {
        ids.forEach(id => { if (!form.permissions.includes(id)) form.permissions.push(id); });
    }
};

const isGroupSelected = (groupPermissions) => groupPermissions.every(p => form.permissions.includes(p.id));
const isGroupPartial = (groupPermissions) => groupPermissions.some(p => form.permissions.includes(p.id)) && !isGroupSelected(groupPermissions);

const submit = () => form.post('/admin/roles');

// Initialize all groups as expanded
onMounted(() => {
    if (props.permissions) {
        Object.keys(props.permissions).forEach(group => {
            expandedGroups.value[group] = true;
        });
    }
    
    gsap.context(() => {
        gsap.fromTo('.form-card', 
            { opacity: 0, y: 20 }, 
            { opacity: 1, y: 0, duration: 0.4, stagger: 0.1, ease: 'power2.out' }
        );
    }, pageRef.value);
});
</script>

<template>
    <div ref="pageRef" class="max-w-4xl mx-auto space-y-5">
        <!-- Premium Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 p-5 shadow-2xl">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            
            <div class="relative flex items-start gap-3">
                <Link href="/admin/roles" class="p-2.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                    <ArrowLeft class="w-4 h-4 text-white" />
                </Link>
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-xl shadow-purple-500/30">
                            <Shield class="h-6 w-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <h1 class="text-lg font-bold text-white">Buat Role Baru</h1>
                            <Sparkles class="w-4 h-4 text-amber-400" />
                        </div>
                        <p class="text-slate-300/80 text-[11px]">Tentukan akses dan izin untuk role baru</p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Basic Info -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-slate-50 via-gray-50 to-slate-50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-600 to-gray-700 flex items-center justify-center shadow-lg">
                            <Shield class="w-4 h-4 text-white" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Informasi Role</h3>
                            <p class="text-[10px] text-gray-500">Detail dasar role</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Nama Role <span class="text-red-500">*</span></label>
                            <input v-model="form.name" @blur="generateSlug" type="text" required placeholder="Contoh: Editor"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50">
                            <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Slug <span class="text-red-500">*</span></label>
                            <input v-model="form.slug" type="text" required placeholder="editor"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 font-mono">
                            <p v-if="form.errors.slug" class="text-red-500 text-[10px] mt-1">{{ form.errors.slug }}</p>
                            <p v-else class="text-gray-400 text-[9px] mt-1">Auto-generate dari nama role</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 mb-1.5">Deskripsi</label>
                        <textarea v-model="form.description" rows="2" placeholder="Deskripsi singkat tentang role ini..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border-2 border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 transition-all bg-gray-50/50 resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="form-card rounded-xl bg-white shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-gradient-to-r from-purple-50 via-indigo-50 to-purple-50 border-b border-purple-100/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                                <Key class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Hak Akses</h3>
                                <p class="text-[10px] text-gray-500">Pilih izin untuk role ini</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-full">
                            {{ form.permissions.length }} dipilih
                        </span>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <div v-for="(perms, group) in permissions" :key="group" class="border border-gray-200 rounded-xl overflow-hidden">
                        <button type="button" @click="toggleGroup(group)" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 flex items-center justify-between hover:from-gray-100 hover:to-gray-150 transition-all">
                            <div class="flex items-center gap-3">
                                <button type="button" @click.stop="selectAllGroup(perms)" 
                                    :class="['w-5 h-5 rounded border-2 flex items-center justify-center transition-all', 
                                        isGroupSelected(perms) ? 'bg-purple-500 border-purple-500' : 
                                        isGroupPartial(perms) ? 'bg-purple-200 border-purple-500' : 'border-gray-300 hover:border-purple-400']">
                                    <Check v-if="isGroupSelected(perms)" class="w-3 h-3 text-white" />
                                </button>
                                <span class="font-bold text-gray-900 capitalize text-xs">{{ group }}</span>
                                <span class="text-[10px] text-gray-500">({{ perms.length }})</span>
                            </div>
                            <component :is="expandedGroups[group] ? ChevronUp : ChevronDown" class="w-4 h-4 text-gray-400" />
                        </button>
                        <Transition name="expand">
                            <div v-if="expandedGroups[group]" class="p-4 grid grid-cols-2 md:grid-cols-3 gap-2 border-t border-gray-100">
                                <label v-for="perm in perms" :key="perm.id" 
                                    :class="['flex items-center gap-2 p-2.5 rounded-lg cursor-pointer transition-all border', 
                                        form.permissions.includes(perm.id) ? 'bg-purple-50 border-purple-200' : 'border-transparent hover:bg-gray-50']">
                                    <input type="checkbox" :checked="form.permissions.includes(perm.id)" @change="togglePermission(perm.id)" 
                                        class="w-4 h-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="text-[11px] text-gray-700">{{ perm.name }}</span>
                                </label>
                            </div>
                        </Transition>
                    </div>
                    <p v-if="!Object.keys(permissions || {}).length" class="text-center text-gray-500 py-8 text-xs">Tidak ada permission tersedia</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" :disabled="form.processing"
                    class="flex-1 py-3 bg-gradient-to-r from-slate-600 to-gray-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-slate-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Save v-else class="w-4 h-4" />
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Role' }}
                </button>
                <Link href="/admin/roles" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </Link>
            </div>
        </form>
    </div>
</template>

<style scoped>
.expand-enter-active, .expand-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}
.expand-enter-from, .expand-leave-to {
    opacity: 0;
    max-height: 0;
}
.expand-enter-to, .expand-leave-from {
    max-height: 500px;
}
</style>
