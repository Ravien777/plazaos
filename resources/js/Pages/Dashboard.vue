<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Activity, Meeting } from '@/Types';

const props = defineProps<{
    hasTeam: boolean;
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

const showBanner = ref(localStorage.getItem('dismissed_team_banner') !== '1');

function dismissBanner(): void {
    localStorage.setItem('dismissed_team_banner', '1');
    showBanner.value = false;
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
                <div
                    v-if="!hasTeam && showBanner"
                    class="mb-6 flex items-center justify-between rounded-lg border border-indigo-200 bg-indigo-50 p-4"
                >
                    <div>
                        <p class="text-sm font-medium text-indigo-800">
                            You're using PlazaOS solo.
                        </p>
                        <p class="text-xs text-indigo-600">
                            Create a team to invite teammates and collaborate.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('team.create')"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                        >
                            Create Team
                        </Link>
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-indigo-500 transition hover:bg-indigo-100 hover:text-indigo-700"
                            @click="dismissBanner"
                            aria-label="Dismiss"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

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
