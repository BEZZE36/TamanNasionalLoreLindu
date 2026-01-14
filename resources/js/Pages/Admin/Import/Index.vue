<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Upload, Download, FileSpreadsheet, CheckCircle, XCircle, AlertTriangle, MapPin, Users, Leaf, Bug } from 'lucide-vue-next';

defineOptions({ layout: AdminLayout });

const importForm = useForm({ type: 'destinations', file: null });
const dragActive = ref(false);

const types = [
    { value: 'destinations', label: 'Destinasi', icon: MapPin, color: 'emerald' },
    { value: 'users', label: 'Users', icon: Users, color: 'blue' },
    { value: 'flora', label: 'Flora', icon: Leaf, color: 'lime' },
    { value: 'fauna', label: 'Fauna', icon: Bug, color: 'amber' }
];

const handleDrop = (e) => {
    dragActive.value = false;
    if (e.dataTransfer.files.length) importForm.file = e.dataTransfer.files[0];
};

const submitImport = () => {
    importForm.post('/admin/import', { forceFormData: true });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-xl shadow-teal-500/30">
                <Upload class="w-7 h-7 text-white" />
            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-900">Import <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-500 to-emerald-600">Data</span></h1>
                <p class="text-gray-500 text-sm">Import data dari file CSV ke database</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Import Form -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <form @submit.prevent="submitImport" class="space-y-4">
                    <!-- Type Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Pilih Tipe Data</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button v-for="t in types" :key="t.value" type="button" @click="importForm.type = t.value" :class="['p-4 rounded-xl border-2 transition-all flex items-center gap-3', importForm.type === t.value ? `border-${t.color}-500 bg-${t.color}-50` : 'border-gray-200 hover:border-gray-300']">
                                <component :is="t.icon" :class="`w-5 h-5 text-${t.color}-500`" />
                                <span class="font-semibold text-gray-900">{{ t.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- File Drop Zone -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Upload File CSV</label>
                        <div @dragover.prevent="dragActive = true" @dragleave="dragActive = false" @drop.prevent="handleDrop" :class="['border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer', dragActive ? 'border-teal-500 bg-teal-50' : 'border-gray-300 hover:border-gray-400']" @click="$refs.fileInput.click()">
                            <input ref="fileInput" type="file" @change="e => importForm.file = e.target.files[0]" accept=".csv,.txt" class="hidden">
                            <FileSpreadsheet class="w-12 h-12 mx-auto mb-3 text-gray-400" />
                            <p v-if="importForm.file" class="font-semibold text-teal-600">{{ importForm.file.name }}</p>
                            <p v-else class="text-gray-500">Drag & drop file CSV atau klik untuk pilih</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <a :href="`/admin/import/template/${importForm.type}`" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 flex items-center justify-center gap-2">
                            <Download class="w-4 h-4" /> Download Template
                        </a>
                        <button type="submit" :disabled="!importForm.file || importForm.processing" class="flex-1 px-4 py-3 bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-teal-500/30 flex items-center justify-center gap-2 disabled:opacity-50">
                            <Upload class="w-4 h-4" /> {{ importForm.processing ? 'Mengimpor...' : 'Import Data' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Instructions -->
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2"><AlertTriangle class="w-5 h-5" /> Petunjuk Import</h3>
                    <ul class="space-y-2 text-sm text-blue-800">
                        <li class="flex items-start gap-2"><CheckCircle class="w-4 h-4 text-green-500 mt-0.5" /> Download template CSV terlebih dahulu</li>
                        <li class="flex items-start gap-2"><CheckCircle class="w-4 h-4 text-green-500 mt-0.5" /> Isi data sesuai format kolom template</li>
                        <li class="flex items-start gap-2"><CheckCircle class="w-4 h-4 text-green-500 mt-0.5" /> Simpan file dalam format .csv</li>
                        <li class="flex items-start gap-2"><CheckCircle class="w-4 h-4 text-green-500 mt-0.5" /> Upload file dan klik Import Data</li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Format Template</h3>
                    <div class="space-y-3 text-sm">
                        <div class="p-3 bg-gray-50 rounded-lg"><strong class="text-emerald-600">Destinasi:</strong> name, slug, city, description, address, latitude, longitude</div>
                        <div class="p-3 bg-gray-50 rounded-lg"><strong class="text-blue-600">Users:</strong> name, email, phone, address</div>
                        <div class="p-3 bg-gray-50 rounded-lg"><strong class="text-lime-600">Flora:</strong> name, scientific_name, description, habitat, conservation_status</div>
                        <div class="p-3 bg-gray-50 rounded-lg"><strong class="text-amber-600">Fauna:</strong> name, scientific_name, description, habitat, conservation_status</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
