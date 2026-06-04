<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import type { Ticket } from '@/Types';

const props = defineProps<{
    ticket: Ticket;
}>();

const form = useForm({ body: '' });

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}

function submitReply(): void {
    form.post(`/portal/tickets/${props.ticket.id}/replies`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="ticket.subject" />

    <PortalLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ ticket.subject }}</h2>
                <Link :href="route('portal.tickets.index')" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge>
                            <span class="text-xs text-gray-500">{{ ticket.priority }} priority &middot; {{ ticket.category }}</span>
                        </div>
                        <div v-if="ticket.description" class="mt-4 whitespace-pre-wrap text-sm text-gray-700">{{ ticket.description }}</div>
                        <p class="mt-3 text-xs text-gray-400">Created {{ ticket.created_at }}</p>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Replies</h3>
                        <div v-if="ticket.replies && ticket.replies.length > 0" class="mt-4 space-y-4">
                            <div v-for="reply in ticket.replies" :key="reply.id" class="rounded-md border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ reply.user?.name ?? 'Support' }}</span>
                                    <span class="text-xs text-gray-500">{{ reply.created_at }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ reply.body }}</p>
                            </div>
                        </div>
                        <div v-else class="mt-4 text-sm text-gray-500">No replies yet.</div>

                        <form @submit.prevent="submitReply" class="mt-6">
                            <textarea v-model="form.body" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Write a reply..." required></textarea>
                            <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">{{ form.errors.body }}</p>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 disabled:opacity-50">
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
