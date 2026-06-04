<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Project } from '@/Types';

defineProps<{
    project: Project;
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { discovery: 'Discovery', design: 'Design', development: 'Development', testing: 'Testing', launch: 'Launch', completed: 'Completed' };
    return labels[s] ?? s;
}
</script>

<template>
    <Head :title="project.name" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">{{ project.name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
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
                            <dd class="mt-1 whitespace-pre-wrap text-sm text-gray-800">{{ project.description }}</dd>
                        </div>
                        <div class="mt-6">
                            <Link :href="route('portal.projects.index')" class="text-sm text-indigo-500 hover:text-indigo-600">&larr; Back to Projects</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
