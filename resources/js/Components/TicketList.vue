<script setup lang="ts">
import StatusBadge from '@/Components/StatusBadge.vue';
import { Link } from '@inertiajs/vue3';
import type { Ticket } from '@/Types';

defineProps<{
    tickets: Ticket[];
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Tickets</h3>
            <Link :href="`/tickets/create`" class="text-sm text-indigo-600 hover:text-indigo-900">New Ticket</Link>
        </div>
        <div v-if="tickets.length === 0" class="mt-4 text-sm text-gray-500">No tickets.</div>
        <div v-else class="mt-4 space-y-3">
            <div
                v-for="ticket in tickets"
                :key="ticket.id"
                class="flex items-center justify-between rounded-md border border-gray-200 p-3"
            >
                <div>
                    <Link :href="`/tickets/${ticket.id}`" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                        {{ ticket.subject }}
                    </Link>
                    <div class="mt-1 flex items-center gap-2">
                        <StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge>
                        <span class="text-xs text-gray-500">{{ ticket.priority }} / {{ ticket.category }}</span>
                    </div>
                </div>
                <span class="text-xs text-gray-400">{{ ticket.created_at }}</span>
            </div>
        </div>
    </div>
</template>
