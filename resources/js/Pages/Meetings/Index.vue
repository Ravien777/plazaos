<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head } from '@inertiajs/vue3';
import type { Meeting } from '@/Types';

const props = defineProps<{
    meetings: Meeting[];
}>();

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
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Upcoming Meetings" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Upcoming Meetings" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="meetings.length === 0" class="text-sm text-gray-600">
                            No upcoming meetings.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="meeting in meetings"
                                :key="meeting.id"
                                class="flex items-center justify-between rounded-md border border-gray-200 p-4"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-800">{{ meeting.title }}</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(meeting.status)">
                                            {{ statusLabel(meeting.status) }}
                                        </span>
                                        <span v-if="meeting.provider" class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="providerClass(meeting.provider)">
                                            {{ providerLabels[meeting.provider] ?? meeting.provider }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ formatDate(meeting.start_time) }}</p>
                                    <p v-if="meeting.location || meeting.meet_link" class="text-sm text-gray-600">
                                        {{ meeting.location ?? '' }}{{ meeting.location && meeting.meet_link ? ' · ' : '' }}
                                        <a v-if="meeting.meet_link" :href="meeting.meet_link" target="_blank" class="text-indigo-500 hover:text-indigo-600">Join</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
