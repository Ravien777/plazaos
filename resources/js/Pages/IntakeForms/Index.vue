<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    is_active: boolean;
    submissions_count: number;
    created_at: string;
}

const props = defineProps<{
    forms: PaginatedResponse<IntakeForm>;
}>();

async function destroyForm(form: IntakeForm): Promise<void> {
    if (!await confirm({ title: 'Delete form?', message: `Delete "${form.title}"? All submissions will also be deleted. This cannot be undone.` })) return;
    router.delete(`/intake-forms/${form.id}`, {
        onSuccess: () => toast.success(`"${form.title}" deleted.`),
    });
}

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'is_active', label: 'Active' },
    { key: 'submissions_count', label: 'Submissions' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <Head title="Intake Forms" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Intake Forms">
                <template #actions>
                    <Link
                        href="/intake-forms/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Form
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable
                            :items="forms.data"
                            :columns="columns"
                            empty-icon="📋"
                            empty-title="No intake forms yet"
                            empty-message="Create an intake form to collect information from prospects."
                            empty-action-label="New Form"
                            empty-action-href="/intake-forms/create"
                        >
                            <template #cell-title="{ item }">
                                <Link :href="`/intake-forms/${item.id}`" class="text-indigo-500 hover:text-indigo-600 font-medium">
                                    {{ item.title }}
                                </Link>
                            </template>
                            <template #cell-is_active="{ item }">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ item.is_active ? 'Yes' : 'No' }}
                                </span>
                            </template>
                            <template #cell-submissions_count="{ item }">
                                {{ item.submissions_count }}
                            </template>
                            <template #cell-created_at="{ item }">
                                {{ new Date(item.created_at).toLocaleDateString() }}
                            </template>
                            <template #actions="{ item }">
                                <div class="flex gap-2">
                                    <Link
                                        :href="`/intake-forms/${item.id}`"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="`/intake-forms/${item.id}/edit`"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                        @click="destroyForm(item)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/intake-forms/${item.id}`" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 truncate block">
                                            {{ item.title }}
                                        </Link>
                                    </div>
                                    <span
                                        class="shrink-0 ml-2 inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span>{{ item.submissions_count }} submission(s)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>Created {{ new Date(item.created_at).toLocaleDateString() }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="forms.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ forms.from }} to {{ forms.to }} of {{ forms.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="forms.current_page > 1"
                                    :href="`/intake-forms?page=${forms.current_page - 1}`"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="forms.current_page < forms.last_page"
                                    :href="`/intake-forms?page=${forms.current_page + 1}`"
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
