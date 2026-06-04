<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { IntakeFormSubmission } from '@/Types';

defineProps<{
    submissions: IntakeFormSubmission[];
}>();
</script>

<template>
    <div>
        <h3 class="text-lg font-medium text-gray-900">Intake Form Submissions</h3>
        <div v-if="submissions.length === 0" class="mt-4 text-sm text-gray-500">No submissions yet.</div>
        <div v-else class="mt-4 space-y-3">
            <div
                v-for="sub in submissions"
                :key="sub.id"
                class="flex items-center justify-between rounded-md border border-gray-200 p-3"
            >
                <div>
                    <span class="text-sm font-medium text-gray-900">
                        {{ sub.form?.title ?? 'Unknown Form' }}
                    </span>
                    <div class="mt-1 text-xs text-gray-500">
                        Submitted {{ sub.submitted_at }}
                    </div>
                </div>
                <Link
                    :href="`/intake-forms/submissions/${sub.id}`"
                    class="text-sm text-indigo-600 hover:text-indigo-900"
                >
                    View
                </Link>
            </div>
        </div>
    </div>
</template>
