<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DocumentList from '@/Components/DocumentList.vue';
import MeetingList from '@/Components/MeetingList.vue';
import ProjectList from '@/Components/ProjectList.vue';
import NotesSection from '@/Components/NotesSection.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import ClientUserList from '@/Components/ClientUserList.vue';
import EmailHistory from '@/Components/EmailHistory.vue';
import TicketList from '@/Components/TicketList.vue';
import IntakeFormSubmissionList from '@/Components/IntakeFormSubmissionList.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { Client } from '@/Types';

const props = defineProps<{
    client: Client;
}>();

function destroy(): void {
    if (confirm('Are you sure you want to delete this client?')) {
        router.delete(`/clients/${props.client.id}`);
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

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
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
