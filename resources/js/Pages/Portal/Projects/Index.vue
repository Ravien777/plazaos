<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Project, PaginatedResponse } from '@/Types';

defineProps<{
    projects: PaginatedResponse<Project>;
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { discovery: 'Discovery', design: 'Design', development: 'Development', testing: 'Testing', launch: 'Launch', completed: 'Completed' };
    return labels[s] ?? s;
}
</script>

<template>
    <Head title="My Projects" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">My Projects</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Budget</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Start Date</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">
                                            <Link :href="`/portal/projects/${project.id}`" class="text-indigo-500 hover:text-indigo-600">{{ project.name }}</Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="project.status">{{ statusLabel(project.status) }}</StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ project.budget ? `$${project.budget}` : '-' }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ project.start_date ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ project.due_date ?? '-' }}</td>
                                    </tr>
                                    <tr v-if="projects.data.length === 0">
                                        <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-600">No projects found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
