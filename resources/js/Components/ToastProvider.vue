<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { toasts, remove } = useToast();

const typeStyles: Record<string, string> = {
    success: 'bg-green-600 text-white',
    error: 'bg-red-600 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-gray-700 text-white',
};

function handleUndo(undo: { handler: () => void }, id: number): void {
    undo.handler();
    remove(id);
}
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end gap-2 p-4">
        <TransitionGroup name="toast" tag="div" class="flex w-full max-w-sm flex-col gap-2">
            <div
                v-for="t in toasts"
                :key="t.id"
                :class="typeStyles[t.type]"
                class="pointer-events-auto flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg transition-all"
            >
                <span class="flex-1 text-sm font-medium">{{ t.message }}</span>
                <button
                    v-if="t.undo"
                    class="whitespace-nowrap text-xs font-semibold uppercase underline underline-offset-2 hover:opacity-80"
                    @click="handleUndo(t.undo, t.id)"
                >
                    {{ t.undo.label }}
                </button>
                <button
                    class="ml-1 text-lg leading-none hover:opacity-70"
                    @click="remove(t.id)"
                >
                    &times;
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active {
    transition: all 0.3s ease-out;
}
.toast-leave-active {
    transition: all 0.2s ease-in;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
