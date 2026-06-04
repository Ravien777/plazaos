<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Project, PaginatedResponse } from '@/Types';

const props = defineProps<{
    projects: PaginatedResponse<Project>;
    filters: {
        status?: string;
        client_id?: string;
    };
}>();

const status = ref(props.filters.status ?? '');

function applyFilters(): void {
    router.get('/projects', { status: status.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters(): void {
    status.value = '';
    router.get('/projects', {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

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

function pageUrl(url: string | null): string {
    return url ?? '#';
}

function destroy(id: string): void {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(`/projects/${id}`);
    }
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Projects">
                <template #actions>
                    <Link
                        href="/projects/create"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                    >
                        New Project
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <select
                                v-model="status"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="discovery">Discovery</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="testing">Testing</option>
                                <option value="launch">Launch</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Client</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Budget</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Start Date</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Due Date</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="project in projects.data"
                                        :key="project.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                            <Link :href="`/projects/${project.id}`" class="text-indigo-600 hover:text-indigo-900">
                                                {{ project.name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <Link v-if="project.client" :href="`/clients/${project.client.id}`" class="text-indigo-600 hover:text-indigo-900">
                                                {{ project.client.company_name }}
                                            </Link>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="project.status">
                                                {{ statusLabel(project.status) }}
                                            </StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ project.budget ? `$${project.budget}` : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ project.start_date ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ project.due_date ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <Link
                                                :href="`/projects/${project.id}/edit`"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                class="ml-2 text-red-600 hover:text-red-900"
                                                @click="destroy(project.id)"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="projects.data.length === 0">
                                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                            No projects found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="projects.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing {{ projects.from }} to {{ projects.to }} of {{ projects.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="projects.current_page > 1"
                                    :href="pageUrl(`/projects?page=${projects.current_page - 1}`)"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="projects.current_page < projects.last_page"
                                    :href="pageUrl(`/projects?page=${projects.current_page + 1}`)"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
