<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import { useConfirm } from '@/composables/useConfirm';

const { state, resolveConfirm } = useConfirm();

const variantStyles: Record<string, string> = {
    danger: 'bg-red-600 hover:bg-red-500 focus:ring-red-400',
    warning: 'bg-yellow-500 hover:bg-yellow-400 focus:ring-yellow-400',
    info: 'bg-blue-600 hover:bg-blue-500 focus:ring-blue-400',
};
</script>

<template>
    <Modal :show="state.show" max-width="sm" @close="resolveConfirm(false)">
        <div class="px-6 py-5">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ state.options.title }}
            </h3>
            <p class="mt-2 text-sm text-gray-600">
                {{ state.options.message }}
            </p>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
            <button
                type="button"
                class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                @click="resolveConfirm(false)"
            >
                {{ state.options.cancelLabel }}
            </button>
            <button
                type="button"
                :class="variantStyles[state.options.variant ?? 'danger']"
                class="inline-flex items-center rounded-md px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                @click="resolveConfirm(true)"
            >
                {{ state.options.confirmLabel }}
            </button>
        </div>
    </Modal>
</template>
