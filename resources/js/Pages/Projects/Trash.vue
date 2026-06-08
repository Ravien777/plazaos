<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Project, PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    projects: PaginatedResponse<Project>;
    filters: {
        status?: string;
    };
}>();

function restore(project: Project): void {
    router.post(route('projects.restore', project.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${project.name}" restored.`);
        },
    });
}

async function forceDestroy(project: Project): Promise<void> {
    if (!await confirm({ title: 'Permanently delete?', message: `Permanently delete "${project.name}"? This cannot be undone.`, confirmLabel: 'Delete Forever' })) return;
    router.delete(route('projects.force-destroy', project.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Project permanently deleted.');
        },
    });
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'client_name', label: 'Client' },
    { key: 'deleted_at', label: 'Deleted At' },
];
</script>

<template>
    <Head title="Trash — Projects" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Trash — Projects">
                <template #actions>
                    <Link
                        href="/projects"
                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Back to Projects
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable
                            :items="projects.data"
                            :columns="columns"
                            empty-icon="🗑️"
                            empty-title="Trash is empty"
                            empty-message="Deleted projects will appear here."
                            empty-action-label="Back to Projects"
                            empty-action-href="/projects"
                        >
                            <template #cell-name="{ item }">
                                <span class="font-medium text-gray-800">{{ item.name }}</span>
                            </template>
                            <template #cell-client_name="{ item }">
                                <span class="text-gray-600">{{ item.client?.company_name ?? '-' }}</span>
                            </template>
                            <template #cell-deleted_at="{ item }">
                                <span class="text-gray-500">{{ item.deleted_at }}</span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                        @click="restore(item)"
                                    >
                                        Restore
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                        @click="forceDestroy(item)"
                                    >
                                        Delete Forever
                                    </button>
                                </div>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ item.name }}
                                        </span>
                                        <p class="mt-0.5 text-sm text-gray-600 truncate">{{ item.client?.company_name ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span>Deleted: {{ item.deleted_at }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="projects.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ projects.from }} to {{ projects.to }} of {{ projects.total }} results
                            </div>
                            <div class="flex gap-1">
                                <Link
                                    v-for="page in projects.last_page"
                                    :key="page"
                                    :href="pageUrl(`/projects/trash?page=${page}`)"
                                    class="rounded-md px-3 py-1 text-sm"
                                    :class="page === projects.current_page ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    {{ page }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
