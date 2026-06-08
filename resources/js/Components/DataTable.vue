<script setup lang="ts">
import { computed } from 'vue';
import EmptyState from '@/Components/EmptyState.vue';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
}

const props = withDefaults(defineProps<{
    items: any[];
    columns: Column[];
    sortField?: string;
    sortDirection?: 'asc' | 'desc';
    selectable?: boolean;
    selectedIds?: string[];
    emptyTitle?: string;
    emptyMessage?: string;
    emptyIcon?: string;
    emptyActionLabel?: string;
    emptyActionHref?: string;
}>(), {
    selectable: false,
    selectedIds: () => [],
    sortField: '',
    sortDirection: 'asc',
    emptyTitle: 'No items found',
    emptyMessage: '',
    emptyIcon: '',
    emptyActionLabel: '',
    emptyActionHref: '',
});

const emit = defineEmits<{
    sort: [field: string];
    'toggle-select': [id: string];
    'toggle-select-all': [];
}>();

const allSelected = computed(() => {
    if (props.items.length === 0) return false;
    return props.items.every((item: any) => props.selectedIds?.includes(item.id));
});

const someSelected = computed(() => {
    return props.items.some((item: any) => props.selectedIds?.includes(item.id)) && !allSelected.value;
});

function sortIndicator(field: string): string {
    if (props.sortField !== field) return '';
    return props.sortDirection === 'asc' ? '▲' : '▼';
}

function cellSlotName(key: string): string {
    return `cell-${key}`;
}
</script>

<template>
    <div>
        <!-- Desktop table (sm+) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th v-if="selectable" class="w-10 px-3 py-3">
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                :indeterminate="someSelected"
                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                @change="emit('toggle-select-all')"
                            />
                        </th>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600',
                                col.sortable ? 'cursor-pointer hover:text-gray-700' : '',
                                col.class || '',
                            ]"
                            @click="col.sortable ? emit('sort', col.key) : undefined"
                        >
                            {{ col.label }}
                            <span v-if="sortIndicator(col.key)" class="ml-1">{{ sortIndicator(col.key) }}</span>
                        </th>
                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="hover:bg-gray-50"
                        :class="{ 'bg-indigo-50': selectedIds?.includes(item.id) }"
                    >
                        <td v-if="selectable" class="px-3 py-4" @click.stop>
                            <input
                                type="checkbox"
                                :checked="selectedIds?.includes(item.id)"
                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                @change="emit('toggle-select', item.id)"
                            />
                        </td>
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'whitespace-nowrap px-3 py-4 text-sm',
                                col.class || '',
                            ]"
                        >
                            <slot :name="cellSlotName(col.key)" :item="item" :value="item[col.key]">
                                <span class="text-gray-600">{{ item[col.key] ?? '—' }}</span>
                            </slot>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <slot name="actions" :item="item" />
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td :colspan="columns.length + (selectable ? 2 : 1)" class="px-3 py-4">
                            <slot name="empty">
                                <EmptyState
                                    :icon="emptyIcon"
                                    :title="emptyTitle"
                                    :message="emptyMessage"
                                    :action-label="emptyActionLabel"
                                    :action-href="emptyActionHref"
                                />
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile cards (<sm) -->
        <div class="block sm:hidden space-y-3">
            <div
                v-for="item in items"
                :key="item.id"
                class="rounded-lg border border-gray-200 bg-white shadow-sm"
                :class="{ 'ring-2 ring-indigo-300': selectedIds?.includes(item.id) }"
            >
                <div v-if="selectable" class="flex justify-end px-4 pt-3 pb-0">
                    <input
                        type="checkbox"
                        :checked="selectedIds?.includes(item.id)"
                        class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                        @change="emit('toggle-select', item.id)"
                    />
                </div>
                <div class="p-4" :class="{ 'pt-2': selectable }">
                    <slot name="card" :item="item" />
                </div>
                <div class="flex items-center gap-3 border-t border-gray-100 px-4 py-3">
                    <slot name="card-actions" :item="item">
                        <slot name="actions" :item="item" />
                    </slot>
                </div>
            </div>
            <div v-if="items.length === 0">
                <slot name="empty">
                    <EmptyState
                        :icon="emptyIcon"
                        :title="emptyTitle"
                        :message="emptyMessage"
                        :action-label="emptyActionLabel"
                        :action-href="emptyActionHref"
                    />
                </slot>
            </div>
        </div>
    </div>
</template>
