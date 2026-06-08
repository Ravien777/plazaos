<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import SearchInput from '@/Components/SearchInput.vue';
import BulkActionBar from '@/Components/BulkActionBar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import type { Project, PaginatedResponse } from '@/Types';

const toast = useToast();

const props = defineProps<{
    projects: PaginatedResponse<Project>;
    filters: { search?: string; status?: string; client_id?: string; };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const selectedIds = ref<string[]>([]);

const projectStatusOptions = [
    { value: 'discovery', label: 'Discovery' },
    { value: 'design', label: 'Design' },
    { value: 'development', label: 'Development' },
    { value: 'testing', label: 'Testing' },
    { value: 'launch', label: 'Launch' },
    { value: 'completed', label: 'Completed' },
];

const allSelected = computed(() => {
    if (props.projects.data.length === 0) return false;
    return props.projects.data.every(p => selectedIds.value.includes(p.id));
});

const someSelected = computed(() => {
    return props.projects.data.some(p => selectedIds.value.includes(p.id)) && !allSelected.value;
});

watch(search, () => applyFilters());

function applyFilters(): void {
    selectedIds.value = [];
    router.get('/projects', {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters(): void {
    search.value = '';
    status.value = '';
    selectedIds.value = [];
    router.get('/projects', {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function toggleSelectAll(): void {
    if (allSelected.value) {
        selectedIds.value = selectedIds.value.filter(id => !props.projects.data.some(p => p.id === id));
    } else {
        const pageIds = props.projects.data.map(p => p.id);
        selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
    }
}

function toggleSelect(id: string): void {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
}

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        discovery: 'Discovery', design: 'Design', development: 'Development',
        testing: 'Testing', launch: 'Launch', completed: 'Completed',
    };
    return labels[s] ?? s;
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

function destroy(project: Project): void {
    router.delete(`/projects/${project.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success(`"${project.name}" deleted.`),
    });
}

function bulkArchive(): void {
    const count = selectedIds.value.length;
    router.post(route('projects.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} project(s) archived.`);
        },
    });
}

function bulkForceDelete(): void {
    const count = selectedIds.value.length;
    router.post(route('projects.bulk.force-delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} project(s) permanently deleted.`);
        },
    });
}

function bulkUpdateStatus(status: string): void {
    router.post(route('projects.bulk.status'), { ids: selectedIds.value, status }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Projects">
                <template #actions>
                    <Link
                        v-if="$page.props.auth.user.role === 'owner' || !$page.props.auth.user.team_id"
                        href="/projects/trash"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Trash
                    </Link>
                    <Link
                        href="/projects/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Project
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <SearchInput v-model="search" />
                            <select
                                v-model="status"
                                class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
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
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-3">
                                            <input
                                                type="checkbox"
                                                :checked="allSelected"
                                                :indeterminate="someSelected"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Client</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Budget</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Start Date</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Due Date</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr
                                        v-for="project in projects.data"
                                        :key="project.id"
                                        class="hover:bg-gray-50"
                                        :class="{ 'bg-indigo-50': selectedIds.includes(project.id) }"
                                    >
                                        <td class="px-3 py-4" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="selectedIds.includes(project.id)"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelect(project.id)"
                                            />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">
                                            <Link :href="`/projects/${project.id}`" class="text-indigo-500 hover:text-indigo-600">
                                                {{ project.name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            <Link v-if="project.client" :href="`/clients/${project.client.id}`" class="text-indigo-500 hover:text-indigo-600">
                                                {{ project.client.company_name }}
                                            </Link>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="project.status">
                                                {{ statusLabel(project.status) }}
                                            </StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            {{ project.budget ? `$${project.budget}` : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            {{ project.start_date ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            {{ project.due_date ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <Link
                                                :href="`/projects/${project.id}/edit`"
                                                class="text-indigo-500 hover:text-indigo-600"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                class="ml-2 text-red-600 hover:text-red-900"
                                                @click="destroy(project)"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="projects.data.length === 0">
                                        <td colspan="8" class="px-3 py-4">
                                            <EmptyState
                                                icon="📁"
                                                title="No projects yet"
                                                message="Start a new project to organize your work."
                                                action-label="New Project"
                                                action-href="/projects/create"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <BulkActionBar
                            :selected-count="selectedIds.length"
                            :show="selectedIds.length > 0"
                            :status-options="projectStatusOptions"
                            @archive="bulkArchive"
                            @force-delete="bulkForceDelete"
                            @update-status="bulkUpdateStatus"
                        />

                        <div v-if="projects.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
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
