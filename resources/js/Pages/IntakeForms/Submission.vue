<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link } from '@inertiajs/vue3';

interface SubmissionField {
    id: string;
    intake_form_field_id: string;
    value: string | null;
    file_path: string | null;
    field: { id: string; label: string; field_type: string };
}

interface Submission {
    id: string;
    intake_form_id: string;
    client_id: string;
    submitted_at: string;
    created_at: string;
    client: { id: string; company_name: string };
    form: { id: string; title: string };
    data: SubmissionField[];
}

const props = defineProps<{
    submission: Submission;
}>();
</script>

<template>
    <Head title="Submission Details" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="`Submission: ${submission.form.title}`" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between text-sm">
                            <div>
                                <span class="text-gray-600">Client:</span>
                                <Link :href="`/clients/${submission.client_id}`" class="ml-1 text-indigo-500 hover:text-indigo-600">
                                    {{ submission.client.company_name }}
                                </Link>
                            </div>
                            <span class="text-gray-600">{{ new Date(submission.submitted_at).toLocaleString() }}</span>
                        </div>

                        <div class="space-y-4">
                            <div v-for="item in submission.data" :key="item.id" class="rounded-md bg-gray-50 p-4">
                                <label class="mb-1 block text-xs font-medium text-gray-600">{{ item.field.label }}</label>
                                <p v-if="item.field.field_type === 'file' && item.file_path" class="text-sm">
                                    <a :href="`/intake-forms/submissions/download?path=${encodeURIComponent(item.file_path)}`" class="text-indigo-500 hover:text-indigo-600 underline">
                                        {{ item.value || 'Download file' }}
                                    </a>
                                </p>
                                <p v-else-if="item.field.field_type === 'checkbox'" class="text-sm">
                                    {{ item.value === '1' || item.value === 'true' ? 'Yes' : 'No' }}
                                </p>
                                <p v-else class="text-sm text-gray-800 whitespace-pre-wrap">{{ item.value || '\u2014' }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link href="/intake-forms" class="text-sm text-indigo-500 hover:text-indigo-600">
                                &larr; Back to Forms
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
