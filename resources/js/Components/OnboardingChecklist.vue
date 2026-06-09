<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    steps: Record<string, boolean>;
    completed: boolean;
}>();

interface StepDef {
    key: string;
    label: string;
    href: string;
    description: string;
}

const stepDefs: StepDef[] = [
    { key: 'profile', label: 'Complete your profile', href: '/profile/edit', description: 'Set your name and preferences' },
    { key: 'team', label: 'Set up your team', href: '/onboard/team', description: 'Invite teammates to collaborate' },
    { key: 'first_lead', label: 'Add your first lead', href: '/leads/create', description: 'Start tracking potential clients' },
    { key: 'first_project', label: 'Create a project', href: '/projects/create', description: 'Organize work into projects' },
    { key: 'integrations', label: 'Explore integrations', href: '/settings/integrations', description: 'Connect your tools' },
];

const completedCount = computed(() => stepDefs.filter(s => props.steps[s.key]).length);
const totalCount = stepDefs.length;
const percent = computed(() => Math.round((completedCount.value / totalCount) * 100));

const emit = defineEmits<{
    dismiss: [];
}>();

function dismiss(): void {
    router.post(route('onboarding.dismiss'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
    emit('dismiss');
}
</script>

<template>
    <div class="rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <div v-if="!completed">
                    <h3 class="text-lg font-medium text-gray-800">Getting Started</h3>
                    <p class="text-sm text-gray-500">{{ completedCount }} of {{ totalCount }} complete</p>
                </div>
                <div v-else>
                    <h3 class="text-lg font-medium text-green-700">All done!</h3>
                    <p class="text-sm text-green-600">You've completed all setup steps.</p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                    @click="dismiss"
                    aria-label="Dismiss"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div v-if="!completed" class="mb-4 h-1.5 overflow-hidden rounded-full bg-gray-100">
                <div
                    class="h-full rounded-full bg-indigo-500 transition-all duration-500"
                    :style="{ width: `${percent}%` }"
                ></div>
            </div>

            <ul class="space-y-1">
                <li
                    v-for="step in stepDefs"
                    :key="step.key"
                >
                    <Link
                        v-if="steps[step.key]"
                        :href="step.href"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-gray-400 transition hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="line-through">{{ step.label }}</span>
                    </Link>
                    <Link
                        v-else
                        :href="step.href"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ step.label }}</span>
                    </Link>
                </li>
            </ul>
        </div>
    </div>
</template>
