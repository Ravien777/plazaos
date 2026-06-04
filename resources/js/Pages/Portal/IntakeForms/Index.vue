<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    is_active: boolean;
    submissions_count: number;
}

interface Submission {
    id: string;
    intake_form_id: string;
    submitted_at: string;
    form: { id: string; title: string };
}

const props = defineProps<{
    forms: IntakeForm[];
    submissions: Submission[];
}>();

const submittedFormIds = new Set(props.submissions.map((s) => s.intake_form_id));
</script>

<template>
    <Head title="Intake Forms" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Intake Forms</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="forms.length === 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-sm text-gray-500">
                        No intake forms available at this time.
                    </div>
                </div>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div v-for="form in forms" :key="form.id" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-1 text-lg font-medium text-gray-900">{{ form.title }}</h3>
                            <p v-if="form.description" class="mb-4 text-sm text-gray-500">{{ form.description }}</p>

                            <div v-if="submittedFormIds.has(form.id)" class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                                You have already submitted this form.
                            </div>

                            <Link
                                v-if="!submittedFormIds.has(form.id)"
                                :href="`/portal/intake-forms/${form.id}`"
                                class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                            >
                                Fill Out Form
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="submissions.length > 0" class="mt-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-sm font-medium text-gray-700">Your Submissions</h3>
                        <div class="space-y-3">
                            <div v-for="submission in submissions" :key="submission.id" class="rounded-md bg-gray-50 p-3 text-sm">
                                <span class="font-medium text-gray-900">{{ submission.form.title }}</span>
                                <span class="ml-2 text-gray-500">submitted {{ new Date(submission.submitted_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
