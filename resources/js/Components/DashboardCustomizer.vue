<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import type { DashboardLayout } from '@/Types';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    layout: DashboardLayout;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:layout', layout: DashboardLayout): void;
}>();

const statCardMeta: Record<string, { label: string }> = {
    'stat-new-leads': { label: 'New Leads' },
    'stat-active-leads': { label: 'Active Leads' },
    'stat-active-clients': { label: 'Active Clients' },
    'stat-open-projects': { label: 'Open Projects' },
    'stat-open-tickets': { label: 'Open Tickets' },
    'stat-upcoming-meetings': { label: 'Upcoming Meetings' },
};

const bottomWidgetMeta: Record<string, { label: string }> = {
    meetings: { label: 'Today\'s Meetings' },
    activity: { label: 'Recent Activity' },
    'wall-of-love': { label: 'Wall of Love' },
};

const hiddenStatCards = computed(() =>
    Object.keys(statCardMeta).filter(id => !props.layout.stat_cards.includes(id))
);

const hiddenBottomWidgets = computed(() =>
    Object.keys(bottomWidgetMeta).filter(id => !props.layout.bottom_widgets.includes(id))
);

function addStatCard(id: string): void {
    const updated: DashboardLayout = {
        ...props.layout,
        stat_cards: [...props.layout.stat_cards, id],
    };
    saveAndEmit(updated);
}

function removeStatCard(id: string): void {
    const updated: DashboardLayout = {
        ...props.layout,
        stat_cards: props.layout.stat_cards.filter(i => i !== id),
    };
    saveAndEmit(updated);
}

function addBottomWidget(id: string): void {
    const updated: DashboardLayout = {
        ...props.layout,
        bottom_widgets: [...props.layout.bottom_widgets, id],
    };
    saveAndEmit(updated);
}

function removeBottomWidget(id: string): void {
    const updated: DashboardLayout = {
        ...props.layout,
        bottom_widgets: props.layout.bottom_widgets.filter(i => i !== id),
    };
    saveAndEmit(updated);
}

function saveAndEmit(layout: DashboardLayout): void {
    emit('update:layout', layout);
    router.post(route('dashboard.layout.update'), {
        stat_cards: layout.stat_cards,
        bottom_widgets: layout.bottom_widgets,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800">Customize Dashboard</h3>
                <button
                    type="button"
                    class="text-gray-400 hover:text-gray-600"
                    @click="emit('close')"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <div>
                    <h4 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Stat Cards</h4>
                    <p class="mb-3 text-sm text-gray-500">Drag to reorder. Click Hide to remove.</p>

                    <div
                        v-for="id in layout.stat_cards"
                        :key="id"
                        class="flex items-center justify-between rounded-md border border-gray-200 bg-white px-4 py-3 mb-2"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 select-none">⠿</span>
                            <span class="text-sm font-medium text-gray-800">{{ statCardMeta[id]?.label ?? id }}</span>
                        </div>
                        <button
                            type="button"
                            class="text-xs text-red-500 hover:text-red-700"
                            @click="removeStatCard(id)"
                        >
                            Hide
                        </button>
                    </div>

                    <div v-if="hiddenStatCards.length > 0" class="mt-4">
                        <p class="mb-2 text-xs font-medium text-gray-500">Hidden — click Show to add back</p>
                        <div
                            v-for="id in hiddenStatCards"
                            :key="id"
                            class="mb-1 flex items-center justify-between rounded-md border border-dashed border-gray-200 bg-gray-50 px-4 py-2"
                        >
                            <span class="text-sm text-gray-500">{{ statCardMeta[id]?.label ?? id }}</span>
                            <button
                                type="button"
                                class="text-xs text-indigo-500 hover:text-indigo-700"
                                @click="addStatCard(id)"
                            >
                                Show
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Widgets</h4>
                    <p class="mb-3 text-sm text-gray-500">Drag to reorder. Click Hide to remove.</p>

                    <div
                        v-for="id in layout.bottom_widgets"
                        :key="id"
                        class="mb-2 flex items-center justify-between rounded-md border border-gray-200 bg-white px-4 py-3"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 select-none">⠿</span>
                            <span class="text-sm font-medium text-gray-800">{{ bottomWidgetMeta[id]?.label ?? id }}</span>
                        </div>
                        <button
                            type="button"
                            class="text-xs text-red-500 hover:text-red-700"
                            @click="removeBottomWidget(id)"
                        >
                            Hide
                        </button>
                    </div>

                    <div v-if="hiddenBottomWidgets.length > 0" class="mt-4">
                        <p class="mb-2 text-xs font-medium text-gray-500">Hidden — click Show to add back</p>
                        <div
                            v-for="id in hiddenBottomWidgets"
                            :key="id"
                            class="mb-1 flex items-center justify-between rounded-md border border-dashed border-gray-200 bg-gray-50 px-4 py-2"
                        >
                            <span class="text-sm text-gray-500">{{ bottomWidgetMeta[id]?.label ?? id }}</span>
                            <button
                                type="button"
                                class="text-xs text-indigo-500 hover:text-indigo-700"
                                @click="addBottomWidget(id)"
                            >
                                Show
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end border-t border-gray-100 pt-4">
                <button
                    type="button"
                    class="rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    @click="emit('close')"
                >
                    Done
                </button>
            </div>
        </div>
    </Modal>
</template>
