import { ref } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    type: ToastType;
    message: string;
    undo?: { label: string; handler: () => void };
}

const toasts = ref<Toast[]>([]);
let nextId = 1;

function add(type: ToastType, message: string, undo?: { label: string; handler: () => void }): void {
    const id = nextId++;
    toasts.value.push({ id, type, message, undo });
    setTimeout(() => remove(id), 4000);
}

function remove(id: number): void {
    const idx = toasts.value.findIndex(t => t.id === id);
    if (idx !== -1) toasts.value.splice(idx, 1);
}

export function useToast() {
    return {
        toasts,
        success: (message: string, undo?: { label: string; handler: () => void }) => add('success', message, undo),
        error: (message: string, undo?: { label: string; handler: () => void }) => add('error', message, undo),
        warning: (message: string, undo?: { label: string; handler: () => void }) => add('warning', message, undo),
        info: (message: string, undo?: { label: string; handler: () => void }) => add('info', message, undo),
        remove,
    };
}
