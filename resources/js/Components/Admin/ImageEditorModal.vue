<script setup>
import { ref, onUnmounted, watch, nextTick } from 'vue';
import { X, Save, Crop, RotateCw, FlipHorizontal, Palette, Type, Sparkles, ImageIcon, Wand2 } from 'lucide-vue-next';

const props = defineProps({
    show: { type: Boolean, default: false },
    imageUrl: { type: String, default: '' },
});

const emit = defineEmits(['close', 'save']);

const editorContainer = ref(null);
const loading = ref(false);
const saving = ref(false);
let editorInstance = null;
let assetsLoaded = false;

// Load Toast UI assets dynamically
const loadScript = (src) => {
    return new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) { resolve(); return; }
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
};

const loadAssets = async () => {
    if (assetsLoaded) return;
    loading.value = true;
    try {
        if (!document.getElementById('tui-editor-css')) {
            const link = document.createElement('link');
            link.id = 'tui-editor-css';
            link.rel = 'stylesheet';
            link.href = '/vendor/tui-image-editor/tui-image-editor.min.css';
            document.head.appendChild(link);
        }
        if (!document.getElementById('tui-color-picker-css')) {
            const link = document.createElement('link');
            link.id = 'tui-color-picker-css';
            link.rel = 'stylesheet';
            link.href = '/vendor/tui-image-editor/tui-color-picker.min.css';
            document.head.appendChild(link);
        }
        
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js');
        await loadScript('/vendor/tui-image-editor/tui-code-snippet.min.js');
        await loadScript('/vendor/tui-image-editor/tui-color-picker.min.js');
        await loadScript('/vendor/tui-image-editor/tui-image-editor.min.js');
        
        assetsLoaded = true;
    } catch (e) {
        console.error('Failed to load Toast UI assets:', e);
    } finally {
        loading.value = false;
    }
};

const initEditor = async () => {
    if (!props.imageUrl || !editorContainer.value) return;
    
    await loadAssets();
    
    if (editorInstance) {
        try { editorInstance.destroy(); } catch (e) {}
        editorInstance = null;
    }
    
    await nextTick();
    
    // Premium dark theme with modern colors
    const customTheme = {
        'common.bi.image': '',
        'common.bisize.width': '0',
        'common.bisize.height': '0',
        'common.backgroundImage': 'none',
        'common.backgroundColor': 'transparent',
        'common.border': '0px',
        
        // Header & logo - hidden
        'header.backgroundImage': 'none',
        'header.backgroundColor': 'transparent',
        'header.border': '0px',
        'header.display': 'none',
        
        // Load button - hidden
        'loadButton.display': 'none',
        'downloadButton.display': 'none',
        
        // Main canvas area
        'menu.normalIcon.color': '#a1a1aa',
        'menu.activeIcon.color': '#ffffff',
        'menu.disabledIcon.color': '#52525b',
        'menu.hoverIcon.color': '#3b82f6',
        'menu.iconSize.width': '20px',
        'menu.iconSize.height': '20px',
        'menu.backgroundColor': 'transparent',
        
        // Submenu
        'submenu.backgroundColor': '#18181b',
        'submenu.partition.color': '#3f3f46',
        'submenu.normalIcon.color': '#a1a1aa',
        'submenu.activeIcon.color': '#3b82f6',
        'submenu.iconSize.width': '28px',
        'submenu.iconSize.height': '28px',
        
        // Submenu labels
        'submenu.normalLabel.color': '#a1a1aa',
        'submenu.normalLabel.fontWeight': '500',
        'submenu.activeLabel.color': '#ffffff',
        'submenu.activeLabel.fontWeight': '600',
        
        // Range slider
        'range.pointer.color': '#3b82f6',
        'range.bar.color': '#3f3f46',
        'range.subbar.color': '#3b82f6',
        'range.value.color': '#ffffff',
        'range.value.fontWeight': '500',
        'range.value.fontSize': '12px',
        'range.value.border': '1px solid #3f3f46',
        'range.value.backgroundColor': '#27272a',
        'range.title.color': '#a1a1aa',
        'range.title.fontWeight': '500',
        
        // Color picker
        'colorpicker.button.border': '1px solid #3f3f46',
        'colorpicker.title.color': '#a1a1aa',
    };
    
    editorInstance = new window.tui.ImageEditor(editorContainer.value, {
        includeUI: {
            loadImage: { path: props.imageUrl, name: 'Image' },
            theme: customTheme,
            menu: ['crop', 'flip', 'rotate', 'draw', 'shape', 'text', 'filter'],
            initMenu: 'crop',
            uiSize: { width: '100%', height: '100%' },
            menuBarPosition: 'bottom',
        },
        cssMaxWidth: 520,
        cssMaxHeight: 360,
        usageStatistics: false,
    });
};

const handleSave = async () => {
    if (!editorInstance) return;
    saving.value = true;
    try {
        const dataUrl = editorInstance.toDataURL();
        emit('save', dataUrl);
        handleClose();
    } catch (e) {
        console.error('Failed to save image:', e);
    } finally {
        saving.value = false;
    }
};

const handleClose = () => {
    if (editorInstance) {
        try { editorInstance.destroy(); } catch (e) {}
        editorInstance = null;
    }
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (newVal && props.imageUrl) {
        setTimeout(initEditor, 150);
    }
});

onUnmounted(() => {
    if (editorInstance) {
        try { editorInstance.destroy(); } catch (e) {}
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-[10000] flex items-center justify-center p-6">
                <!-- Backdrop with blur -->
                <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="handleClose"></div>
                
                <!-- Modal Container -->
                <div class="relative w-full max-w-4xl bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-950 rounded-3xl shadow-2xl shadow-black/50 border border-zinc-800/50 overflow-hidden flex flex-col" style="height: 80vh; max-height: 720px;">
                    
                    <!-- Premium Header -->
                    <div class="relative flex items-center justify-between px-6 py-4 border-b border-zinc-800/50 flex-shrink-0">
                        <!-- Gradient line -->
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-500/50 to-transparent"></div>
                        
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                                <ImageIcon class="w-4 h-4 text-white" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Edit Gambar</h3>
                                <p class="text-[10px] text-zinc-500">Crop, rotate, filter dan edit gambar Anda</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <button type="button" @click="handleClose" 
                                class="px-4 py-2.5 text-sm text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-xl transition-all duration-200">
                                Batal
                            </button>
                            <button type="button" @click="handleSave" :disabled="saving || loading"
                                class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-200 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 disabled:opacity-50 disabled:hover:translate-y-0">
                                <Save class="w-4 h-4" />
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Editor Container -->
                    <div class="flex-1 relative overflow-hidden flex items-center justify-center bg-zinc-950/50">
                        <!-- Subtle grid pattern -->
                        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                        
                        <!-- Loading State -->
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-zinc-950/80 z-10">
                            <div class="text-center space-y-4">
                                <div class="relative w-16 h-16 mx-auto">
                                    <div class="absolute inset-0 rounded-full border-4 border-zinc-700"></div>
                                    <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-500 animate-spin"></div>
                                    <div class="absolute inset-2 rounded-full bg-gradient-to-br from-blue-500/20 to-indigo-600/20 flex items-center justify-center">
                                        <Wand2 class="w-6 h-6 text-blue-400" />
                                    </div>
                                </div>
                                <div>
                                    <p class="text-white font-medium">Memuat Editor</p>
                                    <p class="text-zinc-500 text-sm">Tunggu sebentar...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Editor -->
                        <div ref="editorContainer" class="editor-wrapper w-full h-full"></div>
                    </div>
                    
                    <!-- Bottom gradient -->
                    <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-indigo-500/30 to-transparent"></div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
/* Premium Toast UI Editor - Minimal Override (Preserve Layout) */

/* Container */
.editor-wrapper .tui-image-editor-container {
    background: transparent !important;
}

/* Canvas area */
.editor-wrapper .tui-image-editor-canvas-container {
    background: #0f0f12 !important;
    border-radius: 12px !important;
    margin: 16px !important;
}

.editor-wrapper .tui-image-editor-main-container {
    background: transparent !important;
}

/* Hide header */
.editor-wrapper .tui-image-editor-header {
    display: none !important;
}

/* Menu bar (bottom tools) */
.editor-wrapper .tui-image-editor-menu {
    background: #18181b !important;
    border-top: 1px solid #27272a !important;
}

/* Submenu panel */
.editor-wrapper .tui-image-editor-submenu {
    background: #18181b !important;
    border-top: 1px solid #27272a !important;
}

/* All buttons - simple styling */
.editor-wrapper .tui-image-editor-button {
    background: #27272a !important;
    border: 1px solid #3f3f46 !important;
    border-radius: 8px !important;
    color: #a1a1aa !important;
    transition: all 0.15s ease !important;
}

.editor-wrapper .tui-image-editor-button:hover {
    background: #3f3f46 !important;
    border-color: #52525b !important;
    color: #ffffff !important;
}

/* Active/selected state */
.editor-wrapper .tui-image-editor-button.active {
    background: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: #ffffff !important;
}

/* Apply button */
.editor-wrapper .tui-image-editor-button.apply {
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%) !important;
    border: none !important;
    color: #ffffff !important;
}

.editor-wrapper .tui-image-editor-button.apply:hover {
    opacity: 0.9 !important;
}

/* Cancel button */
.editor-wrapper .tui-image-editor-button.cancel {
    background: transparent !important;
    border: 1px solid #52525b !important;
    color: #a1a1aa !important;
}

.editor-wrapper .tui-image-editor-button.cancel:hover {
    background: #27272a !important;
    color: #ffffff !important;
}

/* Labels */
.editor-wrapper .tui-image-editor-submenu label {
    color: #a1a1aa !important;
}

/* Range sliders */
.editor-wrapper .tui-image-editor-virtual-range-bar {
    background: #3f3f46 !important;
}

.editor-wrapper .tui-image-editor-virtual-range-subbar {
    background: #3b82f6 !important;
}

.editor-wrapper .tui-image-editor-virtual-range-pointer {
    background: #ffffff !important;
    border: 2px solid #3b82f6 !important;
}

/* Partition */
.editor-wrapper .tui-image-editor-partition > div {
    background: #3f3f46 !important;
}

/* Checkboxes */
.editor-wrapper .tui-image-editor-checkbox-wrap {
    color: #a1a1aa !important;
}

/* Range value input */
.editor-wrapper .tui-image-editor-range-value {
    background: #27272a !important;
    border: 1px solid #3f3f46 !important;
    color: #ffffff !important;
}

/* Controls */
.editor-wrapper .tui-image-editor-controls {
    background: transparent !important;
}
</style>
