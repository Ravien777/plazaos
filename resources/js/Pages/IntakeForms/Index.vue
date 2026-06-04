<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { PaginatedResponse } from '@/Types';

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

function destroyForm(form: IntakeForm): void {
    if (confirm(`Delete "${form.title}"? All submissions will also be deleted. This cannot be undone.`)) {
        router.delete(`/intake-forms/${form.id}`);
    }
}
</script>

<template>
    <Head title="Intake Forms" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Intake Forms">
                <template #actions>
                    <Link
                        href="/intake-forms/create"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                    >
                        New Form
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Active</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Submissions</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="form in forms.data"
                                        :key="form.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                            <Link :href="`/intake-forms/${form.id}`" class="text-indigo-600 hover:text-indigo-900">
                                                {{ form.title }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                                :class="form.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            >
                                                {{ form.is_active ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ form.submissions_count }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ new Date(form.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/intake-forms/${form.id}`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="`/intake-forms/${form.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="destroyForm(form)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="forms.data.length === 0">
                                        <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">
                                            No intake forms yet.
                                            <Link href="/intake-forms/create" class="text-indigo-600 hover:text-indigo-900"> Create one </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="forms.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
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
