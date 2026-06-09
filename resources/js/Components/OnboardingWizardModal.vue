<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
    done: [];
}>();

const page = usePage();
const user = page.props.auth.user as unknown as Record<string, unknown>;
const hasTeam = computed(() => !!user.team_id);

const teamForm = useForm({ name: '' });
const currentStep = ref(1);
const totalSteps = 4;

const steps = [
    { title: 'Welcome', description: 'Confirm your profile' },
    { title: 'Team', description: 'Set up your team' },
    { title: 'Overview', description: 'Feature highlights' },
    { title: 'Ready', description: 'Take your first action' },
];

function close(): void {
    emit('close');
}

function skipAll(): void {
    router.post(route('onboarding.skip'), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => done(),
    });
}

function nextStep(): void {
    if (currentStep.value < totalSteps) {
        currentStep.value++;
    }
}

function prevStep(): void {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

function skipStep(step: number): void {
    if (step < totalSteps) {
        currentStep.value++;
    } else {
        done();
    }
}

function finishWizard(): void {
    router.post(route('onboarding.complete'), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => done(),
    });
}

function createTeam(): void {
    teamForm.post(route('team.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            currentStep.value = 3;
        },
    });
}

function navigateTo(href: string): void {
    finishWizard();
    window.location.href = href;
}

function done(): void {
    emit('done');
    close();
}
</script>

<template>
    <Modal :show="show" :closeable="false" max-width="2xl" @close="close">
        <div class="p-6">
            <!-- Header with step indicator -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Welcome to PlazaOS</h2>
                        <p class="mt-1 text-sm text-gray-500">Let's get you set up in a few steps.</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        @click="close"
                        aria-label="Close"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <template v-for="(step, i) in steps" :key="i">
                        <div
                            class="flex items-center gap-2"
                            :class="i + 1 <= currentStep ? 'text-indigo-600' : 'text-gray-400'"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold"
                                :class="i + 1 < currentStep ? 'bg-indigo-600 text-white' : i + 1 === currentStep ? 'border-2 border-indigo-600 text-indigo-600' : 'border-2 border-gray-300 text-gray-400'"
                            >
                                <svg v-if="i + 1 < currentStep" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-else>{{ i + 1 }}</span>
                            </span>
                            <span class="hidden text-sm font-medium sm:inline">{{ step.title }}</span>
                        </div>
                        <div
                            v-if="i < steps.length - 1"
                            class="h-px flex-1"
                            :class="i + 1 < currentStep ? 'bg-indigo-600' : 'bg-gray-200'"
                        />
                    </template>
                </div>
            </div>

            <!-- Step 1: Welcome + Profile -->
            <div v-if="currentStep === 1" class="space-y-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-700">Your account</p>
                    <div class="mt-2 space-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <span class="w-12 font-medium text-gray-500">Name:</span>
                            <span>{{ user.name as string }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-12 font-medium text-gray-500">Email:</span>
                            <span>{{ user.email as string }}</span>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">
                    You can update your profile info anytime from your settings.
                </p>
            </div>

            <!-- Step 2: Team Setup -->
            <div v-if="currentStep === 2" class="space-y-4">
                <div v-if="hasTeam" class="rounded-lg border border-green-200 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">You're already on a team!</p>
                    <p class="mt-1 text-sm text-green-600">Your team is all set. You can manage members later.</p>
                </div>
                <div v-else>
                    <p class="text-sm text-gray-600">
                        Teams let you collaborate with colleagues on leads, clients, and projects.
                    </p>
                    <form @submit.prevent="createTeam" class="mt-4">
                        <label for="team-name" class="block text-sm font-medium text-gray-700">
                            What's your team called?
                        </label>
                        <input
                            id="team-name"
                            v-model="teamForm.name"
                            type="text"
                            placeholder="e.g. Acme Corp"
                            class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                        />
                        <InputError :message="teamForm.errors.name" class="mt-2" />
                        <button
                            type="submit"
                            :disabled="teamForm.processing"
                            class="mt-3 inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-50"
                        >
                            {{ teamForm.processing ? 'Creating...' : 'Create Team' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Step 3: Feature Highlights -->
            <div v-if="currentStep === 3" class="space-y-4">
                <p class="text-sm text-gray-600">Here's a quick look at what you can do with PlazaOS:</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p class="text-sm font-semibold text-indigo-800">Lead Management</p>
                        <p class="mt-1 text-xs text-indigo-600">Capture, organize, and nurture leads from every channel.</p>
                    </div>
                    <div class="rounded-lg border border-purple-100 bg-purple-50 p-4">
                        <p class="text-sm font-semibold text-purple-800">Client Portal</p>
                        <p class="mt-1 text-xs text-purple-600">Give every client a secure portal with projects and documents.</p>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm font-semibold text-blue-800">Project Management</p>
                        <p class="mt-1 text-xs text-blue-600">Track milestones, deadlines, and deliverables.</p>
                    </div>
                    <div class="rounded-lg border border-amber-100 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-800">Dashboard & Insights</p>
                        <p class="mt-1 text-xs text-amber-600">See your metrics, activity, and upcoming meetings at a glance.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4: First Action -->
            <div v-if="currentStep === 4" class="space-y-4">
                <p class="text-sm text-gray-600">
                    You're all set! Here are some things you can do next:
                </p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        type="button"
                        class="flex-1 rounded-lg border border-gray-200 bg-white p-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50"
                        @click="navigateTo('/leads/create')"
                    >
                        <p class="text-sm font-semibold text-gray-800">Add a Lead</p>
                        <p class="mt-1 text-xs text-gray-500">Start tracking your first potential client.</p>
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-lg border border-gray-200 bg-white p-4 text-left transition hover:border-purple-300 hover:bg-purple-50"
                        @click="navigateTo('/projects/create')"
                    >
                        <p class="text-sm font-semibold text-gray-800">Create a Project</p>
                        <p class="mt-1 text-xs text-gray-500">Organize work into a new project.</p>
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-lg border border-gray-200 bg-white p-4 text-left transition hover:border-gray-300 hover:bg-gray-50"
                        @click="done()"
                    >
                        <p class="text-sm font-semibold text-gray-800">Go to Dashboard</p>
                        <p class="mt-1 text-xs text-gray-500">Explore your dashboard and stats.</p>
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
                <button
                    type="button"
                    class="text-xs text-gray-400 underline transition hover:text-gray-600"
                    @click="skipAll"
                >
                    Skip all
                </button>
                <div class="flex items-center gap-3">
                    <button
                        v-if="currentStep > 1"
                        type="button"
                        class="text-sm font-medium text-gray-600 transition hover:text-gray-800"
                        @click="prevStep"
                    >
                        Back
                    </button>
                    <button
                        v-if="currentStep < totalSteps"
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                        @click="nextStep"
                    >
                        Continue
                    </button>
                    <button
                        v-if="currentStep < totalSteps"
                        type="button"
                        class="text-sm text-gray-400 transition hover:text-gray-600"
                        @click="skipStep(currentStep)"
                    >
                        Skip
                    </button>
                    <button
                        v-if="currentStep === totalSteps"
                        type="button"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-500"
                        @click="finishWizard"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>
