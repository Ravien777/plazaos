<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import WallOfLove from '@/Components/WallOfLove.vue';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';
import DashboardCustomizer from '@/Components/DashboardCustomizer.vue';
import { VueDraggable } from 'vue-draggable-plus';
import { nextTick, onMounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { Activity, DashboardLayout, Meeting, Testimonial } from '@/Types';

const ready = ref(false);

onMounted(async () => {
    await nextTick();
    ready.value = true;
});

const props = defineProps<{
    hasTeam: boolean;
    stats: {
        newLeads: number;
        activeLeads: number;
        activeClients: number;
        openProjects: number;
        openTickets: number;
        upcomingMeetings: number;
        monthlyRevenue?: number;
        outstandingTotal?: number;
    };
    recentActivities: Activity[];
    upcomingMeetings: Meeting[];
    recentTestimonials: Testimonial[];
    dashboardLayout: DashboardLayout;
    hasMaroni: boolean;
}>();

const layout = ref<DashboardLayout>(JSON.parse(JSON.stringify(props.dashboardLayout)));
const showCustomizer = ref(false);

const statCardMeta: Record<string, { label: string; icon: string }> = {
    'stat-new-leads': { label: 'New Leads', icon: '📥' },
    'stat-active-leads': { label: 'Active Leads', icon: '📞' },
    'stat-active-clients': { label: 'Active Clients', icon: '👥' },
    'stat-open-projects': { label: 'Open Projects', icon: '📋' },
    'stat-open-tickets': { label: 'Open Tickets', icon: '🎫' },
    'stat-upcoming-meetings': { label: 'Upcoming Meetings', icon: '📅' },
    'stat-maroni-revenue': { label: 'Monthly Revenue', icon: '💰' },
    'stat-maroni-outstanding': { label: 'Outstanding', icon: '📊' },
};

function statValue(id: string): number {
    const map: Record<string, number> = {
        'stat-new-leads': props.stats.newLeads,
        'stat-active-leads': props.stats.activeLeads,
        'stat-active-clients': props.stats.activeClients,
        'stat-open-projects': props.stats.openProjects,
        'stat-open-tickets': props.stats.openTickets,
        'stat-upcoming-meetings': props.stats.upcomingMeetings,
        'stat-maroni-revenue': props.stats.monthlyRevenue ?? 0,
        'stat-maroni-outstanding': props.stats.outstandingTotal ?? 0,
    };
    return map[id] ?? 0;
}

function saveLayout(): void {
    router.post(route('dashboard.layout.update'), {
        stat_cards: layout.value.stat_cards,
        bottom_widgets: layout.value.bottom_widgets,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function removeWidget(section: 'stat_cards' | 'bottom_widgets', id: string): void {
    layout.value[section] = layout.value[section].filter(i => i !== id);
    saveLayout();
}

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
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-700">
                    Dashboard
                </h2>
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    @click="showCustomizer = true"
                >
                    Customize
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
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

                <div v-if="!ready" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="i in 6" :key="i" class="rounded-lg border border-gray-200 bg-white p-6">
                        <div class="mb-2"><SkeletonLoader height="0.75rem" width="5rem" /></div>
                        <SkeletonLoader height="1.75rem" width="3rem" />
                    </div>
                </div>
                <VueDraggable
                    v-if="layout.stat_cards.length > 0"
                    v-model="layout.stat_cards"
                    tag="div"
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                    ghost-class="opacity-30"
                    @end="saveLayout"
                >
                    <StatCard
                        v-for="id in layout.stat_cards"
                        :key="id"
                        :label="statCardMeta[id]?.label ?? id"
                        :value="statValue(id)"
                        :icon="statCardMeta[id]?.icon ?? ''"
                        removable
                        @remove="removeWidget('stat_cards', id)"
                    />
                </VueDraggable>

                <VueDraggable
                    v-if="layout.bottom_widgets.length > 0"
                    v-model="layout.bottom_widgets"
                    tag="div"
                    class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3"
                    ghost-class="opacity-30"
                    @end="saveLayout"
                >
                    <template v-for="id in layout.bottom_widgets" :key="id">
                        <div v-if="id === 'meetings'" class="group relative overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-800">Today's Meetings</h3>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100"
                                            @click="removeWidget('bottom_widgets', 'meetings')"
                                            title="Remove widget"
                                        >
                                            ✕
                                        </button>
                                        <Link :href="route('calendar.index')" class="text-sm text-indigo-500 hover:text-indigo-600">View Calendar</Link>
                                    </div>
                                </div>
                                <div v-if="upcomingMeetings.length === 0" class="text-sm text-gray-600">
                                    No meetings today.
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
                                            </div>
                                            <p class="mt-0.5 text-xs text-gray-600">{{ formatDate(meeting.start_time) }}</p>
                                        </div>
                                        <Link
                                            :href="`/meetings/${meeting.id}`"
                                            class="text-xs text-indigo-500 hover:text-indigo-600"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="id === 'activity'" class="group relative overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-800">Recent Activity</h3>
                                    <button
                                        type="button"
                                        class="text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100"
                                        @click="removeWidget('bottom_widgets', 'activity')"
                                        title="Remove widget"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <div>
                                    <ActivityFeed :activities="recentActivities" />
                                </div>
                            </div>
                        </div>

                        <div v-else-if="id === 'wall-of-love'">
                            <WallOfLove :testimonials="recentTestimonials" removable @remove="removeWidget('bottom_widgets', 'wall-of-love')" />
                        </div>
                    </template>
                </VueDraggable>
            </div>
        </div>
    </AuthenticatedLayout>

    <DashboardCustomizer
        :show="showCustomizer"
        :layout="layout"
        @close="showCustomizer = false"
        @update:layout="layout = $event"
    />
</template>
