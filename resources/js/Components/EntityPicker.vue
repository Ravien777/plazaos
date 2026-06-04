<script setup lang="ts">
import { ref, watch } from 'vue';
import { route } from '../../vendor/tightenco/ziggy';

interface Entity {
    id: string;
    label: string;
    type: string;
}

const props = withDefaults(defineProps<{
    modelValue: Entity | null;
    type: string;
    label?: string;
    placeholder?: string;
}>(), {
    label: 'Search',
    placeholder: 'Type to search...',
});

const emit = defineEmits<{
    'update:modelValue': [value: Entity | null];
}>();

const query = ref('');
const results = ref<Entity[]>([]);
const open = ref(false);
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(query, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    if (query.value.length < 1) {
        results.value = [];
        open.value = false;
        return;
    }
    loading.value = true;
    debounceTimer = setTimeout(async () => {
        try {
            const url = route('api.entity-search', { type: props.type, q: query.value });
            const response = await fetch(url);
            const data = await response.json() as Entity[];
            results.value = data;
            open.value = data.length > 0;
        } catch {
            results.value = [];
            open.value = false;
        } finally {
            loading.value = false;
        }
    }, 300);
});

function select(entity: Entity): void {
    emit('update:modelValue', entity);
    query.value = entity.label;
    open.value = false;
}

function clear(): void {
    emit('update:modelValue', null);
    query.value = '';
    results.value = [];
    open.value = false;
}

function onBlur(): void {
    setTimeout(() => { open.value = false; }, 200);
}

function onFocus(): void {
    if (results.value.length > 0) {
        open.value = true;
    }
}
</script>

<template>
    <div class="relative">
        <label class="block text-sm font-medium text-gray-700">{{ label }}</label>
        <div class="relative mt-1">
            <input
                :value="query"
                @input="query = ($event.target as HTMLInputElement).value"
                @focus="onFocus"
                @blur="onBlur"
                type="text"
                :placeholder="placeholder"
                class="block w-full rounded-md border-gray-300 pr-8 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <button
                v-if="query"
                type="button"
                @click="clear"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <svg
                v-if="loading"
                class="absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-gray-400"
                fill="none" viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </div>
        <ul
            v-if="open"
            class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg"
        >
            <li
                v-for="entity in results"
                :key="entity.id"
                @mousedown.prevent="select(entity)"
                class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm hover:bg-indigo-50"
            >
                <span class="flex-1 truncate">{{ entity.label }}</span>
                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs uppercase text-gray-500">{{ entity.type }}</span>
            </li>
        </ul>
    </div>
</template>
