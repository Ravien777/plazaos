<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import NotesSection from '@/Components/NotesSection.vue';
import CommentSection from '@/Components/CommentSection.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import EmailHistory from '@/Components/EmailHistory.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Lead } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    lead: Lead;
}>();

const summarizing = ref(false);
const websiteSummary = ref('');

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        new: 'New',
        qualified: 'Qualified',
        contacted: 'Contacted',
        interested: 'Interested',
        meeting_scheduled: 'Meeting Scheduled',
        proposal_sent: 'Proposal Sent',
        won: 'Won',
        lost: 'Lost',
    };
    return labels[s] ?? s;
}

function destroy(): void {
    router.delete(`/leads/${props.lead.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${props.lead.company_name}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('leads.restore', props.lead.id)),
            });
        },
    });
}

async function convertToClient(): Promise<void> {
    if (!await confirm({
        title: 'Convert to Client?',
        message: `Convert "${props.lead.company_name}" to a client? This will create a new client record, copy all notes and documents, and archive this lead.`,
        confirmLabel: 'Convert',
        variant: 'info',
    })) return;

    router.post(`/leads/${props.lead.id}/convert`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${props.lead.company_name}" converted to client.`);
        },
    });
}

async function summarizeWebsite(): Promise<void> {
    summarizing.value = true;
    websiteSummary.value = '';
    try {
        const res = await axios.post(`/ai/leads/${props.lead.id}/summarize-website`);
        websiteSummary.value = res.data.data.summary;
    } catch {
        websiteSummary.value = 'Failed to summarize website.';
    } finally {
        summarizing.value = false;
    }
}
</script>

<template>
    <Head title="Lead Details" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Lead Details">
                <template #actions>
                    <Link
                        :href="`/leads/${lead.id}/edit`"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="!lead.converted_at"
                        class="inline-flex items-center rounded-md border border-green-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-green-700 shadow-sm hover:bg-green-50"
                        @click="convertToClient"
                    >
                        Convert to Client
                    </button>
                    <button
                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm hover:bg-red-50"
                        @click="destroy"
                    >
                        Delete
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Company Name</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.company_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Contact Name</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.contact_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Email</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.email ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.phone ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Website</dt>
                                <dd class="mt-1 text-sm text-gray-800">
                                    <a v-if="lead.website" :href="lead.website" target="_blank" class="text-indigo-500 hover:text-indigo-600">
                                        {{ lead.website }}
                                    </a>
                                    <span v-else>-</span>
                                </dd>
                                <dd v-if="lead.website" class="mt-2">
                                    <button
                                        type="button"
                                        :disabled="summarizing"
                                        class="rounded-md bg-indigo-500 px-2.5 py-1 text-xs font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50"
                                        @click="summarizeWebsite"
                                    >
                                        {{ summarizing ? 'Summarizing...' : 'Summarize Website' }}
                                    </button>
                                    <p v-if="websiteSummary" class="mt-2 rounded bg-gray-50 p-3 text-sm text-gray-700">
                                        {{ websiteSummary }}
                                    </p>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Industry</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.industry ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">City</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.city ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Country</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.country ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Source</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.source ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Status</dt>
                                <dd class="mt-1">
                                    <StatusBadge :status="lead.status">{{ statusLabel(lead.status) }}</StatusBadge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Last Contacted</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.last_contacted_at ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Created</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ lead.created_at }}</dd>
                            </div>
                        </dl>
                        <div v-if="lead.notes" class="mt-6">
                            <dt class="text-sm font-medium text-gray-600">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-800 whitespace-pre-wrap">{{ lead.notes }}</dd>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <NotesSection noteable-type="lead" :noteable-id="lead.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <CommentSection commentable-type="lead" :commentable-id="lead.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <EmailHistory
                            emailable-type="lead"
                            :emailable-id="lead.id"
                            :recipient-email="lead.email ?? ''"
                            :recipient-name="lead.contact_name"
                            :company-name="lead.company_name"
                        />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Activity</h3>
                        <div class="mt-4">
                            <ActivityFeed :activities="lead.activities ?? []" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
