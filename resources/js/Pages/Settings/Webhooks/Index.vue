<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { AllowedEvent, Webhook } from '@/Types';

const props = defineProps<{
    webhooks: Webhook[];
    allowedEvents: AllowedEvent[];
}>();

function eventLabels(events: string[]): string {
    const map = Object.fromEntries(props.allowedEvents.map((e) => [e.value, e.label]));
    const labels = events.map((e) => map[e] ?? e);
    if (labels.length <= 3) return labels.join(', ');
    return labels.slice(0, 3).join(', ') + ` +${labels.length - 3} more`;
}

function destroy(webhook: Webhook): void {
    if (confirm(`Delete this webhook? "${webhook.url}"`)) {
        router.delete(route('settings.webhooks.destroy', webhook.id), {
            preserveScroll: true,
        });
    }
}

function testWebhook(webhook: Webhook): void {
    router.post(route('settings.webhooks.test', webhook.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Webhooks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-700">Webhooks</h2>
                <Link
                    :href="route('settings.webhooks.create')"
                    class="rounded-md bg-stone-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-stone-600"
                >
                    Add Webhook
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <EmptyState
                    v-if="webhooks.length === 0"
                    icon="🔗"
                    title="No webhooks yet"
                    message="Create webhooks to send real-time data to Zapier, Make, n8n, or your own server."
                    :action-href="route('settings.webhooks.create')"
                    action-label="Add Webhook"
                />

                <div v-else class="space-y-4">
                    <div
                        v-for="webhook in webhooks"
                        :key="webhook.id"
                        class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm"
                    >
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-stone-800">{{ webhook.url }}</p>
                                <p class="mt-0.5 text-xs text-stone-500">{{ eventLabels(webhook.events) }}</p>
                            </div>
                            <div class="ml-4 flex items-center gap-2 shrink-0">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="webhook.active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500'"
                                >
                                    {{ webhook.active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center gap-3 text-xs text-stone-400">
                            <span v-if="webhook.last_sent_at">Last sent: {{ webhook.last_sent_at }}</span>
                            <span v-else>Not yet sent</span>
                            <span v-if="webhook.last_error_message" class="text-red-500" :title="webhook.last_error_message">
                                &#x26A0; Error
                            </span>
                        </div>

                        <div class="mt-3 flex items-center gap-2 border-t border-stone-100 pt-3">
                            <button
                                type="button"
                                class="text-xs text-stone-500 hover:text-stone-700"
                                @click="testWebhook(webhook)"
                            >
                                Test
                            </button>
                            <Link
                                :href="route('settings.webhooks.edit', webhook.id)"
                                class="text-xs text-stone-500 hover:text-stone-700"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="text-xs text-red-500 hover:text-red-700"
                                @click="destroy(webhook)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
