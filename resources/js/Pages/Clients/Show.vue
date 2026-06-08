<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DocumentList from '@/Components/DocumentList.vue';
import MaroniFinancialPanel from '@/Components/MaroniFinancialPanel.vue';
import MeetingList from '@/Components/MeetingList.vue';
import ProjectList from '@/Components/ProjectList.vue';
import NotesSection from '@/Components/NotesSection.vue';
import CommentSection from '@/Components/CommentSection.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import ClientUserList from '@/Components/ClientUserList.vue';
import EmailHistory from '@/Components/EmailHistory.vue';
import TicketList from '@/Components/TicketList.vue';
import IntakeFormSubmissionList from '@/Components/IntakeFormSubmissionList.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import type { Client } from '@/Types';
import { ref } from 'vue';

const toast = useToast();

const props = defineProps<{
    client: Client;
    maroniConfigured: boolean;
}>();

const portalUrl = ref<string | null>(null);
const portalExpires = ref<string | null>(null);
const generating = ref(false);
const copied = ref(false);
const requestingReview = ref(false);
const reviewSent = ref(false);

function destroy(): void {
    router.delete(`/clients/${props.client.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${props.client.company_name}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('clients.restore', props.client.id)),
            });
        },
    });
}

async function requestReview(): Promise<void> {
    requestingReview.value = true;
    try {
        await fetch(`/clients/${props.client.id}/request-review`, { method: 'POST' });
        reviewSent.value = true;
    } finally {
        requestingReview.value = false;
    }
}

async function generatePortalLink(): Promise<void> {
    generating.value = true;
    try {
        const res = await fetch(`/clients/${props.client.id}/portal-link`, { method: 'POST' });
        const data = await res.json();
        portalUrl.value = data.url;
        portalExpires.value = data.portal_token_expires_at;
    } finally {
        generating.value = false;
    }
}

function copyLink(): void {
    if (portalUrl.value) {
        navigator.clipboard.writeText(portalUrl.value);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    }
}

const statusClass = (s: string): string => {
    const map: Record<string, string> = {
        active: 'bg-green-100 text-green-700',
        inactive: 'bg-yellow-100 text-yellow-700',
        archived: 'bg-gray-100 text-gray-600',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head :title="client.company_name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="client.company_name">
                <template #actions>
                    <Link
                        :href="`/clients/${client.id}/edit`"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        Edit
                    </Link>
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
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-800">Portal Access</h3>
                            <button
                                @click="generatePortalLink"
                                :disabled="generating"
                                class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600 disabled:opacity-50"
                            >
                                {{ portalUrl ? 'Regenerate Link' : 'Generate Link' }}
                            </button>
                        </div>
                        <p v-if="!portalUrl && !generating" class="mt-2 text-sm text-gray-600">
                            Generate a magic link to give this client access to their portal.
                        </p>
                        <p v-if="generating" class="mt-2 text-sm text-gray-600">Generating...</p>
                        <div v-if="portalUrl" class="mt-3 flex items-center gap-2">
                            <input
                                :value="portalUrl"
                                readonly
                                class="block w-full rounded-md border-gray-200 bg-gray-50 text-sm shadow-sm"
                                @click="copyLink"
                            />
                            <button
                                @click="copyLink"
                                class="shrink-0 rounded-md bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-600"
                            >
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                        <p v-if="portalExpires" class="mt-1 text-xs text-gray-500">
                            Expires {{ new Date(portalExpires).toLocaleDateString() }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Company Name</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.company_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Contact Name</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.contact_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Email</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.email ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.phone ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Website</dt>
                                <dd class="mt-1 text-sm text-gray-800">
                                    <a v-if="client.website" :href="client.website" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ client.website }}</a>
                                    <span v-else>-</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Industry</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.industry ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">City</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.city ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Country</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.country ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Source</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.source ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="statusClass(client.status)">{{ client.status }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Last Contacted</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.last_contacted_at ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Created</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ client.created_at }}</dd>
                            </div>
                        </dl>
                        <div v-if="client.notes" class="mt-6">
                            <dt class="text-sm font-medium text-gray-600">Notes</dt>
                            <dd class="mt-1 whitespace-pre-wrap text-sm text-gray-800">{{ client.notes }}</dd>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-800">Request a Review</h3>
                                <p class="mt-1 text-sm text-gray-600">Send a friendly email asking for feedback.</p>
                            </div>
                            <button
                                v-if="!reviewSent"
                                @click="requestReview"
                                :disabled="requestingReview"
                                class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600 disabled:opacity-50"
                            >
                                {{ requestingReview ? 'Sending...' : 'Request a Review' }}
                            </button>
                            <p v-else class="text-sm font-medium text-green-600">Review requested! ✓</p>
                        </div>
                    </div>
                </div>

                <div v-if="maroniConfigured && client.maroni_client_id" class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <MaroniFinancialPanel :client-id="client.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <ProjectList :projects="client.projects ?? []" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DocumentList documentable-type="client" :documentable-id="client.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <MeetingList meetable-type="client" :meetable-id="client.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <NotesSection noteable-type="client" :noteable-id="client.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <CommentSection commentable-type="client" :commentable-id="client.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <EmailHistory
                            emailable-type="client"
                            :emailable-id="client.id"
                            :recipient-email="client.email ?? ''"
                            :recipient-name="client.contact_name"
                            :company-name="client.company_name"
                        />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <TicketList :tickets="(client.tickets ?? []) as any" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <ClientUserList :client-id="client.id" :users="client.portalUsers ?? []" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <IntakeFormSubmissionList :submissions="client.intakeFormSubmissions ?? []" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Activity</h3>
                        <div class="mt-4">
                            <ActivityFeed :activities="client.activities ?? []" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
