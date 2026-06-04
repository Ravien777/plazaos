<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, router } from '@inertiajs/vue3';
import type { Meeting } from '@/Types';

const props = defineProps<{
    meeting: Meeting;
}>();

function destroy(): void {
    if (confirm('Cancel this meeting?')) {
        router.delete(`/meetings/${props.meeting.id}`);
    }
}

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        scheduled: 'Scheduled',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[s] ?? s;
}

function statusClass(s: string): string {
    const map: Record<string, string> = {
        scheduled: 'bg-blue-100 text-blue-700',
        completed: 'bg-green-100 text-green-700',
        cancelled: 'bg-red-100 text-red-700',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
}

const providerLabels: Record<string, string> = {
    google_meet: 'Google Meet',
    zoom: 'Zoom',
    microsoft_teams: 'Microsoft Teams',
};

function providerClass(s: string): string {
    const map: Record<string, string> = {
        google_meet: 'bg-emerald-100 text-emerald-700',
        zoom: 'bg-blue-100 text-blue-700',
        microsoft_teams: 'bg-purple-100 text-purple-700',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
}

function formatDate(dt: string): string {
    const d = new Date(dt);
    return d.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="meeting.title" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="meeting.title">
                <template #actions>
                    <button
                        class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm hover:bg-red-50"
                        @click="destroy"
                    >
                        Cancel Meeting
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <dl class="space-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="statusClass(meeting.status)">
                                        {{ statusLabel(meeting.status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Provider</dt>
                                <dd class="mt-1">
                                    <span v-if="meeting.provider" class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="providerClass(meeting.provider)">
                                        {{ providerLabels[meeting.provider] ?? meeting.provider }}
                                    </span>
                                    <span v-else class="text-sm text-gray-500">—</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Start Time</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(meeting.start_time) }}</dd>
                            </div>
                            <div v-if="meeting.end_time">
                                <dt class="text-sm font-medium text-gray-500">End Time</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(meeting.end_time) }}</dd>
                            </div>
                            <div v-if="meeting.location">
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ meeting.location }}</dd>
                            </div>
                            <div v-if="meeting.meet_link">
                                <dt class="text-sm font-medium text-gray-500">Join Link</dt>
                                <dd class="mt-1">
                                    <a :href="meeting.meet_link" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900">{{ meeting.meet_link }}</a>
                                </dd>
                            </div>
                            <div v-if="meeting.description">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ meeting.description }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
