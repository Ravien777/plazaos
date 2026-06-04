<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Activity, Meeting } from '@/Types';

defineProps<{
    stats: {
        newLeads: number;
        activeLeads: number;
        activeClients: number;
        openProjects: number;
        openTickets: number;
        upcomingMeetings: number;
    };
    recentActivities: Activity[];
    upcomingMeetings: Meeting[];
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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard label="New Leads" :value="stats.newLeads" icon="📥" />
                    <StatCard label="Active Leads" :value="stats.activeLeads" icon="📞" />
                    <StatCard label="Active Clients" :value="stats.activeClients" icon="👥" />
                    <StatCard label="Open Projects" :value="stats.openProjects" icon="📋" />
                    <StatCard label="Open Tickets" :value="stats.openTickets" icon="🎫" />
                    <StatCard label="Upcoming Meetings" :value="stats.upcomingMeetings" icon="📅" />
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-800">Upcoming Meetings</h3>
                                <Link href="/meetings/upcoming" class="text-sm text-indigo-500 hover:text-indigo-600">View all</Link>
                            </div>
                            <div v-if="upcomingMeetings.length === 0" class="text-sm text-gray-600">
                                No upcoming meetings.
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="meeting in upcomingMeetings"
                                    :key="meeting.id"
                                    class="flex items-center justify-between rounded-md border border-gray-200 p-3"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-800">{{ meeting.title }}</span>
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(meeting.status)">
                                                {{ statusLabel(meeting.status) }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-gray-600">{{ formatDate(meeting.start_time) }}</p>
                                    </div>
                                    <Link
                                        :href="`/meetings/${meeting.id}/edit`"
                                        class="text-xs text-indigo-500 hover:text-indigo-600"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-800">Recent Activity</h3>
                            <div class="mt-4">
                                <ActivityFeed :activities="recentActivities" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
