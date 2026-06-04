<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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

function priorityLabel(s: string): string {
    const labels: Record<string, string> = { low: 'Low', medium: 'Medium', high: 'High' };
    return labels[s] ?? s;
}

function categoryLabel(s: string): string {
    const labels: Record<string, string> = { bug_report: 'Bug Report', feature_request: 'Feature Request', support: 'Support', other: 'Other' };
    return labels[s] ?? s;
}

function ticketableLink(): string {
    if (!props.ticket.ticketable_type || !props.ticket.ticketable_id) return '#';
    const prefix = props.ticket.ticketable_type === 'client' ? 'clients' : 'projects';
    return `/${prefix}/${props.ticket.ticketable_id}`;
}

function ticketableName(): string {
    if (!props.ticket.ticketable) return '-';
    const t = props.ticket.ticketable as Record<string, string>;
    return (t.company_name || t.name) ?? '-';
}

function submitReply(): void {
    form.post(`/tickets/${props.ticket.id}/replies`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function closeTicket(): void {
    router.post(`/tickets/${props.ticket.id}/close`);
}

function reopenTicket(): void {
    router.post(`/tickets/${props.ticket.id}/reopen`);
}

function destroy(): void {
    if (confirm('Are you sure you want to delete this ticket?')) {
        router.delete(`/tickets/${props.ticket.id}`);
    }
}
</script>

<template>
    <Head :title="ticket.subject" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="ticket.subject">
                <template #actions>
                    <Link :href="`/tickets/${ticket.id}/edit`" class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600">Edit</Link>
                    <button v-if="ticket.status !== 'closed'" class="inline-flex items-center rounded-md border border-green-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-green-700 shadow-sm hover:bg-green-50" @click="closeTicket">Close</button>
                    <button v-else class="inline-flex items-center rounded-md border border-yellow-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-yellow-700 shadow-sm hover:bg-yellow-50" @click="reopenTicket">Reopen</button>
                    <button class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm hover:bg-red-50" @click="destroy">Delete</button>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Subject</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ ticket.subject }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Related To</dt>
                                <dd class="mt-1 text-sm text-gray-800">
                                    <Link v-if="ticket.ticketable && ticket.ticketable_id" :href="ticketableLink()" class="text-indigo-500 hover:text-indigo-600">{{ ticketableName() }}</Link>
                                    <span v-else>-</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Status</dt>
                                <dd class="mt-1"><StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Priority</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ priorityLabel(ticket.priority) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Category</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ categoryLabel(ticket.category) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Created</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ ticket.created_at }}</dd>
                            </div>
                        </dl>
                        <div v-if="ticket.description" class="mt-6">
                            <dt class="text-sm font-medium text-gray-600">Description</dt>
                            <dd class="mt-1 text-sm text-gray-800 whitespace-pre-wrap">{{ ticket.description }}</dd>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Replies</h3>
                        <div v-if="ticket.replies && ticket.replies.length > 0" class="mt-4 space-y-4">
                            <div v-for="reply in ticket.replies" :key="reply.id" class="rounded-md border border-gray-200 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-800">{{ reply.user?.name ?? 'Unknown' }}</span>
                                    <span class="text-xs text-gray-600">{{ reply.created_at }}</span>
                                </div>
                                <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ reply.body }}</p>
                            </div>
                        </div>
                        <div v-else class="mt-4 text-sm text-gray-600">No replies yet.</div>

                        <form @submit.prevent="submitReply" class="mt-6">
                            <textarea
                                v-model="form.body"
                                rows="3"
                                class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                placeholder="Write a reply..."
                                required
                            ></textarea>
                            <p v-if="form.errors.body" class="mt-1 text-sm text-red-600">{{ form.errors.body }}</p>
                            <div class="mt-3 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-50"
                                >
                                    Add Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Activity</h3>
                        <div class="mt-4">
                            <ActivityFeed :activities="ticket.activities ?? []" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
