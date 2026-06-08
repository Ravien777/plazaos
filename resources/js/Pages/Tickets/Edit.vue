<script setup lang="ts">
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EntityPicker from '@/Components/EntityPicker.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import type { Ticket } from '@/Types';

interface Entity {
    id: string;
    label: string;
    type: string;
}

const props = defineProps<{
    ticket: Ticket;
}>();

const form = useForm({
    subject: props.ticket.subject,
    description: props.ticket.description ?? '',
    status: props.ticket.status,
    priority: props.ticket.priority,
    category: props.ticket.category,
    ticketable_type: props.ticket.ticketable_type ?? '',
    ticketable_id: props.ticket.ticketable_id ?? '',
});

const ticketableTypes = [
    { value: '', label: 'None (standalone)' },
    { value: 'client', label: 'Client' },
    { value: 'project', label: 'Project' },
];

const initialLabel = props.ticket.ticketable
    ? ((props.ticket.ticketable as Record<string, unknown>).company_name as string
        ?? (props.ticket.ticketable as Record<string, unknown>).name as string
        ?? '')
    : '';

const selectedEntity = ref<Entity | null>(
    form.ticketable_id && form.ticketable_type
        ? { id: form.ticketable_id, label: initialLabel, type: form.ticketable_type }
        : null,
);

watch(selectedEntity, (entity) => {
    if (entity) {
        form.ticketable_type = entity.type;
        form.ticketable_id = entity.id;
    } else {
        form.ticketable_type = '';
        form.ticketable_id = '';
    }
});

function submit(): void {
    form.put(`/tickets/${props.ticket.id}`);
}
</script>

<template>
    <Head :title="'Edit: ' + ticket.subject" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Edit Ticket" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    required
                                />
                                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">{{ form.errors.subject }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                ></textarea>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    >
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="waiting_client">Waiting Client</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                                    <select
                                        v-model="form.priority"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select
                                        v-model="form.category"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    >
                                        <option value="bug_report">Bug Report</option>
                                        <option value="feature_request">Feature Request</option>
                                        <option value="support">Support</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Related To (optional)</label>
                                <select
                                    v-model="form.ticketable_type"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                >
                                    <option v-for="t in ticketableTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>

                                <div v-if="form.ticketable_type" class="mt-2">
                                    <EntityPicker
                                        v-model="selectedEntity"
                                        :type="form.ticketable_type"
                                        label=""
                                        :placeholder="'Search ' + form.ticketable_type + 's...'"
                                    />
                                </div>
                                <p v-if="form.errors.ticketable_id" class="mt-1 text-sm text-red-600">{{ form.errors.ticketable_id }}</p>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-50"
                                >
                                    Update Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
