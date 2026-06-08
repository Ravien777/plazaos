<script setup lang="ts">
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps<{
    selectedCount: number;
    statusOptions?: { value: string; label: string }[];
    show?: boolean;
}>();

const emit = defineEmits<{
    archive: [];
    'force-delete': [];
    'update-status': [status: string];
}>();

const { confirm } = useConfirm();
const selectedStatus = ref('');

async function handleForceDelete(): Promise<void> {
    const confirmed = await confirm({
        title: 'Permanently delete?',
        message: `Are you sure you want to permanently delete ${props.selectedCount} item(s)? This cannot be undone.`,
        variant: 'danger',
        confirmLabel: 'Delete forever',
    });

    if (confirmed) {
        emit('force-delete');
    }
}

function handleUpdateStatus(): void {
    if (selectedStatus.value) {
        emit('update-status', selectedStatus.value);
        selectedStatus.value = '';
    }
}
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="show && selectedCount > 0"
            class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white px-4 py-3 shadow-lg"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                <span class="text-sm font-medium text-gray-700">
                    {{ selectedCount }} selected
                </span>

                <div class="flex items-center gap-3">
                    <button
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                        @click="emit('archive')"
                    >
                        Archive
                    </button>

                    <div class="flex items-center gap-1.5">
                        <select
                            v-model="selectedStatus"
                            class="rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Change status</option>
                            <option
                                v-for="opt in statusOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                        <button
                            class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!selectedStatus"
                            @click="handleUpdateStatus"
                        >
                            Apply
                        </button>
                    </div>

                    <button
                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                        @click="handleForceDelete"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
