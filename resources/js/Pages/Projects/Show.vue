<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import NotesSection from '@/Components/NotesSection.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import DocumentList from '@/Components/DocumentList.vue';
import EmailHistory from '@/Components/EmailHistory.vue';
import TicketList from '@/Components/TicketList.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { Project } from '@/Types';

const props = defineProps<{
    project: Project;
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        discovery: 'Discovery',
        design: 'Design',
        development: 'Development',
        testing: 'Testing',
        launch: 'Launch',
        completed: 'Completed',
    };
    return labels[s] ?? s;
}

function destroy(): void {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(`/projects/${props.project.id}`);
    }
}
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="project.name">
                <template #actions>
                    <Link
                        :href="`/projects/${project.id}/edit`"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
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
                                <dt class="text-sm font-medium text-gray-500">Project Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Client</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <Link v-if="project.client" :href="`/clients/${project.client.id}`" class="text-indigo-600 hover:text-indigo-900">
                                        {{ project.client.company_name }}
                                    </Link>
                                    <span v-else>-</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <StatusBadge :status="project.status">{{ statusLabel(project.status) }}</StatusBadge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Budget</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.budget ? `$${project.budget}` : '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.start_date ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.due_date ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.created_at }}</dd>
                            </div>
                        </dl>
                        <div v-if="project.description" class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ project.description }}</dd>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DocumentList documentable-type="project" :documentable-id="project.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <NotesSection noteable-type="project" :noteable-id="project.id" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <EmailHistory
                            emailable-type="project"
                            :emailable-id="project.id"
                            :recipient-email="project.client?.email ?? ''"
                            :recipient-name="project.client?.contact_name ?? ''"
                            :company-name="project.name"
                        />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <TicketList :tickets="(project.tickets ?? []) as any" />
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Activity</h3>
                        <div class="mt-4">
                            <ActivityFeed :activities="project.activities ?? []" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
