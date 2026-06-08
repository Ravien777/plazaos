<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Meeting, PaginatedResponse } from '@/Types';

defineProps<{
    meetings: PaginatedResponse<Meeting>;
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { scheduled: 'Scheduled', completed: 'Completed', cancelled: 'Cancelled' };
    return labels[s] ?? s;
}
</script>

<template>
    <Head title="My Meetings" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">My Meetings</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Title</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Status</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Start Time</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Location</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="meeting in meetings.data" :key="meeting.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">{{ meeting.title }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">{{ statusLabel(meeting.status) }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ meeting.start_time }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                                            <a v-if="meeting.join_url" :href="meeting.join_url" target="_blank" class="text-indigo-500 hover:text-indigo-600">Join</a>
                                            <span v-else>-</span>
                                        </td>
                                    </tr>
                                    <tr v-if="meetings.data.length === 0">
                                        <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-600">No meetings found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
