<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import NotesSection from '@/Components/NotesSection.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import DocumentList from '@/Components/DocumentList.vue';
import EmailHistory from '@/Components/EmailHistory.vue';
import TicketList from '@/Components/TicketList.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import type { Project, User } from '@/Types';
import { ref } from 'vue';

const toast = useToast();

const props = defineProps<{
    project: Project;
    assignees: User[];
}>();

const requestingReview = ref(false);
const reviewSent = ref(false);

const groupedTasks: Record<string, any[]> = {
    todo: (props.project.tasks ?? []).filter((t: any) => t.status === 'todo'),
    in_progress: (props.project.tasks ?? []).filter((t: any) => t.status === 'in_progress'),
    done: (props.project.tasks ?? []).filter((t: any) => t.status === 'done'),
};

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
    router.delete(`/projects/${props.project.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${props.project.name}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('projects.restore', props.project.id)),
            });
        },
    });
}

async function requestReview(): Promise<void> {
    requestingReview.value = true;
    try {
        await fetch(`/projects/${props.project.id}/request-review`, { method: 'POST' });
        reviewSent.value = true;
    } finally {
        requestingReview.value = false;
    }
}
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="project.name">
                <template #actions>
                    <button
                        v-if="!reviewSent"
                        @click="requestReview"
                        :disabled="requestingReview"
                        class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-200 disabled:opacity-50"
                    >
                        {{ requestingReview ? 'Sending...' : 'Request Review' }}
                    </button>
                    <span v-else class="text-xs font-medium text-green-600">Review requested ✓</span>
                    <Link
                        :href="`/projects/${project.id}/edit`"
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
                                <dt class="text-sm font-medium text-gray-600">Project Name</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ project.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Client</dt>
                                <dd class="mt-1 text-sm text-gray-800">
                                    <Link v-if="project.client" :href="`/clients/${project.client.id}`" class="text-indigo-500 hover:text-indigo-600">
                                        {{ project.client.company_name }}
                                    </Link>
                                    <span v-else>-</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Status</dt>
                                <dd class="mt-1">
                                    <StatusBadge :status="project.status">{{ statusLabel(project.status) }}</StatusBadge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Budget</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ project.budget ? `$${project.budget}` : '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ project.start_date ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ project.due_date ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Created</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ project.created_at }}</dd>
                            </div>
                        </dl>
                        <div v-if="project.description" class="mt-6">
                            <dt class="text-sm font-medium text-gray-600">Description</dt>
                            <dd class="mt-1 text-sm text-gray-800 whitespace-pre-wrap">{{ project.description }}</dd>
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
                        <h3 class="text-lg font-medium text-gray-800">Tasks</h3>
                        <div class="mt-4">
                            <KanbanBoard
                                :tasks="groupedTasks"
                                :project-filter="project.id"
                            />
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Activity</h3>
                        <div class="mt-4">
                            <ActivityFeed :activities="project.activities ?? []" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
