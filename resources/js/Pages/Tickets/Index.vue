<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import SearchInput from '@/Components/SearchInput.vue';
import BulkActionBar from '@/Components/BulkActionBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import type { Ticket, PaginatedResponse } from '@/Types';

const toast = useToast();

const props = defineProps<{
    tickets: PaginatedResponse<Ticket>;
    filters: {
        search?: string;
        status?: string;
        priority?: string;
        category?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const priority = ref(props.filters.priority ?? '');
const category = ref(props.filters.category ?? '');
const selectedIds = ref<string[]>([]);

const ticketStatusOptions = [
    { value: 'open', label: 'Open' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'waiting_client', label: 'Waiting Client' },
    { value: 'closed', label: 'Closed' },
];

const allSelected = computed(() => {
    if (props.tickets.data.length === 0) return false;
    return props.tickets.data.every(t => selectedIds.value.includes(t.id));
});

const someSelected = computed(() => {
    return props.tickets.data.some(t => selectedIds.value.includes(t.id)) && !allSelected.value;
});

watch(search, () => applyFilters());

function applyFilters(): void {
    selectedIds.value = [];
    router.get('/tickets', {
        search: search.value || undefined,
        status: status.value || undefined,
        priority: priority.value || undefined,
        category: category.value || undefined,
    }, { preserveState: true, preserveScroll: true });
}

function clearFilters(): void {
    search.value = '';
    status.value = '';
    priority.value = '';
    category.value = '';
    selectedIds.value = [];
    router.get('/tickets', {}, { preserveState: true, preserveScroll: true });
}

function toggleSelectAll(): void {
    if (allSelected.value) {
        selectedIds.value = selectedIds.value.filter(id => !props.tickets.data.some(t => t.id === id));
    } else {
        const pageIds = props.tickets.data.map(t => t.id);
        selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
    }
}

function toggleSelect(id: string): void {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
}

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}

function priorityLabel(s: string): string {
    const labels: Record<string, string> = { low: 'Low', medium: 'Medium', high: 'High' };
    return labels[s] ?? s;
}

function categoryLabel(s: string): string {
    const labels: Record<string, string> = { bug_report: 'Bug Report', feature_request: 'Feature Request', support: 'Support', other: 'Other' };
    return labels[s] ?? s;
}

function ticketableLabel(ticket: Ticket): string {
    if (!ticket.ticketable) return '-';
    if ('company_name' in ticket.ticketable) return (ticket.ticketable as Record<string, string>).company_name;
    if ('name' in ticket.ticketable) return (ticket.ticketable as Record<string, string>).name;
    return ticket.ticketable_type ?? '-';
}

function ticketableLink(ticket: Ticket): string {
    if (!ticket.ticketable_type || !ticket.ticketable_id) return '#';
    const prefix = ticket.ticketable_type === 'client' ? 'clients' : 'projects';
    return `/${prefix}/${ticket.ticketable_id}`;
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

function destroy(ticket: Ticket): void {
    router.delete(`/tickets/${ticket.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${ticket.subject}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('tickets.restore', ticket.id)),
            });
        },
    });
}

function bulkArchive(): void {
    const count = selectedIds.value.length;
    router.post(route('tickets.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} ticket(s) archived.`);
        },
    });
}

function bulkForceDelete(): void {
    const count = selectedIds.value.length;
    router.post(route('tickets.bulk.force-delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} ticket(s) permanently deleted.`);
        },
    });
}

function bulkUpdateStatus(status: string): void {
    router.post(route('tickets.bulk.status'), { ids: selectedIds.value, status }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Tickets">
                <template #actions>
                    <Link
                        v-if="$page.props.auth.user.role === 'owner' || !$page.props.auth.user.team_id"
                        href="/tickets/trash"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Trash
                    </Link>
                    <Link
                        href="/tickets/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Ticket
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <SearchInput v-model="search" />
                            <select v-model="status" class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" @change="applyFilters">
                                <option value="">All Statuses</option>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="waiting_client">Waiting Client</option>
                                <option value="closed">Closed</option>
                            </select>
                            <select v-model="priority" class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" @change="applyFilters">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <select v-model="category" class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" @change="applyFilters">
                                <option value="">All Categories</option>
                                <option value="bug_report">Bug Report</option>
                                <option value="feature_request">Feature Request</option>
                                <option value="support">Support</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-3">
                                            <input
                                                type="checkbox"
                                                :checked="allSelected"
                                                :indeterminate="someSelected"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Subject</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Related To</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Priority</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Category</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Created</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50" :class="{ 'bg-indigo-50': selectedIds.includes(ticket.id) }">
                                        <td class="px-3 py-4" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="selectedIds.includes(ticket.id)"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelect(ticket.id)"
                                            />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">
                                            <Link :href="`/tickets/${ticket.id}`" class="text-indigo-500 hover:text-indigo-600">
                                                {{ ticket.subject }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            <Link v-if="ticket.ticketable && ticket.ticketable_id" :href="ticketableLink(ticket)" class="text-indigo-500 hover:text-indigo-600">
                                                {{ ticketableLabel(ticket) }}
                                            </Link>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ priorityLabel(ticket.priority) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ categoryLabel(ticket.category) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ ticket.created_at }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <Link :href="`/tickets/${ticket.id}/edit`" class="text-indigo-500 hover:text-indigo-600">Edit</Link>
                                            <button class="ml-2 text-red-600 hover:text-red-900" @click="destroy(ticket)">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="tickets.data.length === 0">
                                        <td colspan="8" class="px-3 py-4">
                                            <EmptyState
                                                icon="🎫"
                                                title="No tickets yet"
                                                message="Support tickets will appear here when created."
                                                action-label="New Ticket"
                                                action-href="/tickets/create"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <BulkActionBar
                            :selected-count="selectedIds.length"
                            :show="selectedIds.length > 0"
                            :status-options="ticketStatusOptions"
                            @archive="bulkArchive"
                            @force-delete="bulkForceDelete"
                            @update-status="bulkUpdateStatus"
                        />

                        <div v-if="tickets.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ tickets.from }} to {{ tickets.to }} of {{ tickets.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link v-if="tickets.current_page > 1" :href="pageUrl(`/tickets?page=${tickets.current_page - 1}`)" class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50">Previous</Link>
                                <Link v-if="tickets.current_page < tickets.last_page" :href="pageUrl(`/tickets?page=${tickets.current_page + 1}`)" class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50">Next</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
