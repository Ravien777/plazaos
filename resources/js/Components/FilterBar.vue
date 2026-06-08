<script setup lang="ts">
import { ref } from 'vue';

withDefaults(defineProps<{
    columns?: number;
}>(), {
    columns: 3,
});

const open = ref(false);
</script>

<template>
    <div>
        <!-- Mobile toggle -->
        <button
            type="button"
            class="sm:hidden mb-3 inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 min-h-[44px]"
            @click="open = !open"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            {{ open ? 'Hide Filters' : 'Show Filters' }}
        </button>

        <div :class="[open ? 'block' : 'hidden', 'sm:block']">
            <div
                class="grid grid-cols-1 gap-4"
                :style="{ gridTemplateColumns: `repeat(${Math.min(columns, 4)}, minmax(0, 1fr))` }"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
