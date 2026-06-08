<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { AllowedEvent } from '@/Types';

const props = defineProps<{
    allowedEvents: AllowedEvent[];
}>();

const url = ref('');
const selectedEvents = ref<string[]>([]);
const saving = ref(false);

function toggleEvent(value: string): void {
    const idx = selectedEvents.value.indexOf(value);
    if (idx === -1) {
        selectedEvents.value.push(value);
    } else {
        selectedEvents.value.splice(idx, 1);
    }
}

function submit(): void {
    saving.value = true;
    router.post(route('settings.webhooks.store'), {
        url: url.value,
        events: selectedEvents.value,
    }, {
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="Create Webhook" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">Create Webhook</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700">Webhook URL</label>
                            <input
                                id="url"
                                v-model="url"
                                type="url"
                                required
                                placeholder="https://hooks.zapier.com/..."
                                class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                            />
                            <p class="mt-1 text-xs text-gray-500">The URL that will receive POST requests when events occur.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Events</label>
                            <p class="mt-1 text-xs text-gray-500">Select the events that should trigger this webhook.</p>
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label
                                    v-for="event in allowedEvents"
                                    :key="event.value"
                                    class="flex cursor-pointer items-center gap-3 rounded-md border border-gray-200 p-3 transition hover:bg-gray-50"
                                    :class="selectedEvents.includes(event.value) ? 'border-indigo-300 bg-indigo-50' : ''"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="selectedEvents.includes(event.value)"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @change="toggleEvent(event.value)"
                                    />
                                    <span class="text-sm text-gray-700">{{ event.label }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button
                                type="submit"
                                :disabled="saving || !url || selectedEvents.length === 0"
                                class="rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-50"
                            >
                                {{ saving ? 'Saving...' : 'Create Webhook' }}
                            </button>
                            <a
                                :href="route('settings.webhooks')"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
