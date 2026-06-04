<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Activity } from '@/Types';

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
    projects: {
        id: string;
        name: string;
        status: string;
        progress_percentage: number;
    }[];
    recentDocuments: {
        id: string;
        name: string;
        size: number;
        created_at: string;
    }[];
    recentActivities: Activity[];
    companyName: string;
    companyEmail: string | null;
}>();

const statusColors: Record<string, string> = {
    discovery: 'bg-blue-500',
    design: 'bg-purple-500',
    development: 'bg-indigo-500',
    testing: 'bg-yellow-500',
    launch: 'bg-green-500',
    completed: 'bg-emerald-600',
};

function statusLabel(s: string): string {
    const labels: Record<string, string> = { open: 'Open', in_progress: 'In Progress', waiting_client: 'Waiting Client', closed: 'Closed' };
    return labels[s] ?? s;
}

function projectStatusLabel(s: string): string {
    const labels: Record<string, string> = { discovery: 'Discovery', design: 'Design', development: 'Development', testing: 'Testing', launch: 'Launch', completed: 'Completed' };
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
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Project Progress</h3>
                            <div v-if="projects.length === 0" class="text-sm text-gray-600">No projects yet.</div>
                            <div v-else class="space-y-4">
                                <div v-for="project in projects" :key="project.id" class="rounded-md border border-gray-200 p-3">
                                    <div class="flex items-center justify-between">
                                        <Link :href="`/portal/projects/${project.id}`" class="text-sm font-medium text-indigo-500 hover:text-indigo-600">{{ project.name }}</Link>
                                        <span class="text-xs text-gray-500">{{ projectStatusLabel(project.status) }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200">
                                                <div
                                                    class="h-full rounded-full transition-all"
                                                    :class="statusColors[project.status] ?? 'bg-gray-500'"
                                                    :style="{ width: (project.progress_percentage ?? 0) + '%' }"
                                                />
                                            </div>
                                            <span class="text-xs font-medium text-gray-600">{{ project.progress_percentage ?? 0 }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Recent Activity</h3>
                            <div v-if="recentActivities.length === 0" class="text-sm text-gray-600">No recent activity.</div>
                            <div v-else class="space-y-3">
                                <div v-for="activity in recentActivities" :key="activity.id" class="rounded-md border border-gray-200 p-3">
                                    <p class="text-sm text-gray-800">{{ activity.description }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ activity.created_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-800">Recent Documents</h3>
                                <Link :href="route('portal.documents.index')" class="text-sm text-indigo-500 hover:text-indigo-600">View all</Link>
                            </div>
                            <div v-if="recentDocuments.length === 0" class="text-sm text-gray-600">No documents yet.</div>
                            <div v-else class="space-y-3">
                                <div v-for="doc in recentDocuments" :key="doc.id" class="flex items-center justify-between rounded-md border border-gray-200 p-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ doc.name }}</p>
                                        <p class="text-xs text-gray-500">{{ (doc.size / 1024).toFixed(1) }} KB</p>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ doc.created_at }}</span>
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

                <div class="mt-8 overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="p-6 text-center">
                        <h3 class="text-lg font-medium text-gray-800">Need help?</h3>
                        <p class="mt-1 text-sm text-gray-600">Contact your project manager anytime.</p>
                        <a
                            v-if="companyEmail"
                            :href="'mailto:' + companyEmail"
                            class="mt-3 inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600"
                        >
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
