<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Ticket, PaginatedResponse } from '@/Types';

defineProps<{
    tickets: PaginatedResponse<Ticket>;
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}
</script>

<template>
    <Head title="My Tickets" />

    <PortalLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-700">My Tickets</h2>
                <Link :href="route('portal.tickets.create')" class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600">
                    New Ticket
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Subject</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Priority</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">
                                            <Link :href="`/portal/tickets/${ticket.id}`" class="text-indigo-500 hover:text-indigo-600">{{ ticket.subject }}</Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ ticket.priority }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ ticket.created_at }}</td>
                                    </tr>
                                    <tr v-if="tickets.data.length === 0">
                                        <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-600">No tickets found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
