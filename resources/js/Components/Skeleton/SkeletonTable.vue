<script setup>
/**
 * SkeletonTable.vue
 * Table skeleton for admin data tables
 */
defineProps({
    rows: { type: Number, default: 5 },
    columns: { type: Number, default: 5 },
    showHeader: { type: Boolean, default: true },
    showActions: { type: Boolean, default: true }
});
</script>

<template>
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden animate-pulse">
        <!-- Table Header -->
        <div v-if="showHeader" class="bg-gray-50 px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="skeleton-title w-48"></div>
                <div class="flex gap-2">
                    <div class="skeleton-button w-24"></div>
                    <div class="skeleton-button w-20"></div>
                </div>
            </div>
        </div>

        <!-- Table Body -->
        <div class="divide-y divide-gray-100">
            <div v-for="row in rows" :key="row" class="px-6 py-4 flex items-center gap-4">
                <!-- Checkbox -->
                <div class="skeleton-base w-5 h-5 rounded flex-shrink-0"></div>
                
                <!-- Avatar (optional) -->
                <div v-if="row === 1 || row % 2 === 0" class="skeleton-avatar w-10 h-10 flex-shrink-0"></div>
                
                <!-- Columns -->
                <div class="flex-1 grid gap-4" :style="{ gridTemplateColumns: `repeat(${columns}, 1fr)` }">
                    <div v-for="col in columns" :key="col" 
                         class="skeleton-text" 
                         :style="{ width: col === 1 ? '80%' : col === columns ? '50%' : '70%' }">
                    </div>
                </div>

                <!-- Actions -->
                <div v-if="showActions" class="flex gap-2 flex-shrink-0">
                    <div class="skeleton-base w-8 h-8 rounded-lg"></div>
                    <div class="skeleton-base w-8 h-8 rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="skeleton-text w-32"></div>
            <div class="flex gap-2">
                <div class="skeleton-base w-8 h-8 rounded"></div>
                <div class="skeleton-base w-8 h-8 rounded"></div>
                <div class="skeleton-base w-8 h-8 rounded"></div>
            </div>
        </div>
    </div>
</template>
