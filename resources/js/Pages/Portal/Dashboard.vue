<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: {
        projectsCount: number;
        openTicketsCount: number;
        upcomingMeetingsCount: number;
    };
    recentTickets: {
        id: string;
        subject: string;
        status: string;
        created_at: string;
    }[];
    upcomingMeetings: {
        id: string;
        title: string;
        start_time: string;
    }[];
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}
</script>

<template>
    <Head title="Portal Dashboard" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-600">Projects</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">{{ stats.projectsCount }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-600">Open Tickets</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">{{ stats.openTicketsCount }}</p>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-600">Upcoming Meetings</p>
                            <p class="mt-2 text-3xl font-bold text-gray-800">{{ stats.upcomingMeetingsCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-800">Recent Tickets</h3>
                                <Link :href="route('portal.tickets.index')" class="text-sm text-indigo-500 hover:text-indigo-600">View all</Link>
                            </div>
                            <div v-if="recentTickets.length === 0" class="text-sm text-gray-600">No recent tickets.</div>
                            <div v-else class="space-y-3">
                                <div v-for="ticket in recentTickets" :key="ticket.id" class="flex items-center justify-between rounded-md border border-gray-200 p-3">
                                    <div>
                                        <Link :href="`/portal/tickets/${ticket.id}`" class="text-sm font-medium text-indigo-500 hover:text-indigo-600">{{ ticket.subject }}</Link>
                                        <div class="mt-1">
                                            <StatusBadge :status="ticket.status">{{ statusLabel(ticket.status) }}</StatusBadge>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ ticket.created_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-800">Upcoming Meetings</h3>
                                <Link :href="route('portal.meetings.index')" class="text-sm text-indigo-500 hover:text-indigo-600">View all</Link>
                            </div>
                            <div v-if="upcomingMeetings.length === 0" class="text-sm text-gray-600">No upcoming meetings.</div>
                            <div v-else class="space-y-3">
                                <div v-for="meeting in upcomingMeetings" :key="meeting.id" class="rounded-md border border-gray-200 p-3">
                                    <p class="text-sm font-medium text-gray-800">{{ meeting.title }}</p>
                                    <p class="mt-0.5 text-xs text-gray-600">{{ meeting.start_time }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
