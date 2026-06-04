<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Ticket, PaginatedResponse } from '@/Types';

const props = defineProps<{
    tickets: PaginatedResponse<Ticket>;
    filters: {
        status?: string;
        priority?: string;
        category?: string;
    };
}>();

const status = ref(props.filters.status ?? '');
const priority = ref(props.filters.priority ?? '');
const category = ref(props.filters.category ?? '');

function applyFilters(): void {
    router.get('/tickets', {
        status: status.value || undefined,
        priority: priority.value || undefined,
        category: category.value || undefined,
    }, { preserveState: true, preserveScroll: true });
}

function clearFilters(): void {
    status.value = '';
    priority.value = '';
    category.value = '';
    router.get('/tickets', {}, { preserveState: true, preserveScroll: true });
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

function destroy(id: string): void {
    if (confirm('Are you sure you want to delete this ticket?')) {
        router.delete(`/tickets/${id}`);
    }
}
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Tickets">
                <template #actions>
                    <Link
                        href="/tickets/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Ticket
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50">
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
                                            <button class="ml-2 text-red-600 hover:text-red-900" @click="destroy(ticket.id)">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="tickets.data.length === 0">
                                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-600">No tickets found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
