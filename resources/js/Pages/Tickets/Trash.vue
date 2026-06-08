<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Ticket, PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    tickets: PaginatedResponse<Ticket>;
    filters: {
        status?: string;
    };
}>();

function restore(ticket: Ticket): void {
    router.post(route('tickets.restore', ticket.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${ticket.subject}" restored.`);
        },
    });
}

async function forceDestroy(ticket: Ticket): Promise<void> {
    if (!await confirm({ title: 'Permanently delete?', message: `Permanently delete "${ticket.subject}"? This cannot be undone.`, confirmLabel: 'Delete Forever' })) return;
    router.delete(route('tickets.force-destroy', ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Ticket permanently deleted.');
        },
    });
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

const columns = [
    { key: 'subject', label: 'Subject' },
    { key: 'ticketable', label: 'Ticketable' },
    { key: 'deleted_at', label: 'Deleted At' },
];
</script>

<template>
    <Head title="Trash — Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Trash — Tickets">
                <template #actions>
                    <Link
                        href="/tickets"
                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Back to Tickets
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable
                            :items="tickets.data"
                            :columns="columns"
                            empty-icon="🗑️"
                            empty-title="Trash is empty"
                            empty-message="Deleted tickets will appear here."
                            empty-action-label="Back to Tickets"
                            empty-action-href="/tickets"
                        >
                            <template #cell-subject="{ item }">
                                <span class="font-medium text-gray-800">{{ item.subject }}</span>
                            </template>
                            <template #cell-ticketable="{ item }">
                                <span class="text-gray-600">
                                    {{ item.ticketable && 'company_name' in item.ticketable ? item.ticketable.company_name : item.ticketable && 'name' in item.ticketable ? item.ticketable.name : '-' }}
                                </span>
                            </template>
                            <template #cell-deleted_at="{ item }">
                                <span class="text-gray-500">{{ item.deleted_at }}</span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                        @click="restore(item)"
                                    >
                                        Restore
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                        @click="forceDestroy(item)"
                                    >
                                        Delete Forever
                                    </button>
                                </div>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ item.subject }}
                                        </span>
                                        <p class="mt-0.5 text-sm text-gray-600 truncate">
                                            {{ item.ticketable && 'company_name' in item.ticketable ? item.ticketable.company_name : item.ticketable && 'name' in item.ticketable ? item.ticketable.name : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span>Deleted: {{ item.deleted_at }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="tickets.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ tickets.from }} to {{ tickets.to }} of {{ tickets.total }} results
                            </div>
                            <div class="flex gap-1">
                                <Link
                                    v-for="page in tickets.last_page"
                                    :key="page"
                                    :href="pageUrl(`/tickets/trash?page=${page}`)"
                                    class="rounded-md px-3 py-1 text-sm"
                                    :class="page === tickets.current_page ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    {{ page }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
