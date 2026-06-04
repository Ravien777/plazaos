<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { PaginatedResponse } from '@/Types';

interface IntakeFormField {
    id: string;
    label: string;
    field_type: string;
    required: boolean;
    options: string[] | null;
    placeholder: string | null;
    sort_order: number;
}

interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    is_active: boolean;
    fields: IntakeFormField[];
    created_at: string;
}

interface SubmissionData {
    id: string;
    intake_form_field_id: string;
    value: string | null;
    file_path: string | null;
    field: { id: string; label: string; field_type: string };
}

interface Submission {
    id: string;
    client_id: string;
    submitted_at: string;
    created_at: string;
    client: { id: string; company_name: string };
    data: SubmissionData[];
}

const props = defineProps<{
    form: IntakeForm;
    submissions: PaginatedResponse<Submission>;
}>();

function fieldValue(submission: Submission, fieldId: string): string | null {
    const item = submission.data.find((d) => d.intake_form_field_id === fieldId);
    return item?.value ?? null;
}
</script>

<template>
    <Head :title="form.title" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="form.title">
                <template #actions>
                    <Link :href="`/intake-forms/${form.id}/edit`" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">
                        Edit Form
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="form.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ form.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p v-if="form.description" class="text-sm text-gray-600">{{ form.description }}</p>

                        <h3 class="mb-3 mt-6 text-sm font-medium text-gray-700">Fields ({{ form.fields.length }})</h3>
                        <div class="space-y-2">
                            <div v-for="field in form.fields" :key="field.id" class="flex items-center gap-3 rounded-md bg-gray-50 px-3 py-2 text-sm">
                                <span class="font-medium text-gray-900">{{ field.label }}</span>
                                <span class="rounded bg-gray-200 px-1.5 py-0.5 text-xs text-gray-600">{{ field.field_type }}</span>
                                <span v-if="field.required" class="text-xs text-red-500">Required</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-sm font-medium text-gray-700">Submissions ({{ submissions.total }})</h3>

                        <div v-if="submissions.data.length === 0" class="py-8 text-center text-sm text-gray-500">
                            No submissions yet.
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="submission in submissions.data" :key="submission.id" class="rounded-lg border border-gray-200 p-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <Link :href="`/clients/${submission.client_id}`" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ submission.client.company_name }}
                                    </Link>
                                    <span class="text-xs text-gray-500">{{ new Date(submission.submitted_at).toLocaleString() }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="field in form.fields" :key="field.id" class="text-sm">
                                        <span class="text-xs text-gray-500">{{ field.label }}:</span>
                                        <p class="text-gray-900">{{ fieldValue(submission, field.id) || '\u2014' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="submissions.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing {{ submissions.from }} to {{ submissions.to }} of {{ submissions.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link v-if="submissions.current_page > 1" :href="`/intake-forms/${form.id}?page=${submissions.current_page - 1}`" class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50">Previous</Link>
                                <Link v-if="submissions.current_page < submissions.last_page" :href="`/intake-forms/${form.id}?page=${submissions.current_page + 1}`" class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50">Next</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
