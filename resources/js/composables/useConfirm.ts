import { reactive, readonly } from 'vue';

export interface ConfirmOptions {
    title: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'info';
}

interface ConfirmState {
    show: boolean;
    options: ConfirmOptions;
    resolve: ((value: boolean) => void) | null;
}

const defaultOptions: ConfirmOptions = {
    title: 'Are you sure?',
    message: '',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
    variant: 'danger',
};

const state = reactive<ConfirmState>({
    show: false,
    options: { ...defaultOptions },
    resolve: null,
});

export function useConfirm() {
    function confirm(options: ConfirmOptions): Promise<boolean> {
        state.options = { ...defaultOptions, ...options };
        state.show = true;

        return new Promise<boolean>((resolve) => {
            state.resolve = resolve;
        });
    }

    function resolveConfirm(value: boolean): void {
        if (state.resolve) {
            state.resolve(value);
        }
        state.show = false;
        state.resolve = null;
    }

    return {
        state: readonly(state),
        confirm,
        resolveConfirm,
    };
}
