<script setup lang="ts">
import type { CalendarEvent, User } from '@/Types';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    teamMembers: User[];
}>();

interface DayCell {
    date: Date;
    isCurrentMonth: boolean;
    isToday: boolean;
    events: CalendarEvent[];
}

type ViewMode = 'month' | 'week';

const viewMode = ref<ViewMode>('month');
const currentDate = ref(new Date());
const events = ref<CalendarEvent[]>([]);
const loading = ref(false);

const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const memberColors = computed(() => {
    const gradients = [
        'border-l-pink-400 bg-pink-50',
        'border-l-purple-400 bg-purple-50',
        'border-l-indigo-400 bg-indigo-50',
        'border-l-blue-400 bg-blue-50',
        'border-l-teal-400 bg-teal-50',
        'border-l-emerald-400 bg-emerald-50',
        'border-l-green-400 bg-green-50',
        'border-l-yellow-400 bg-yellow-50',
        'border-l-orange-400 bg-orange-50',
        'border-l-red-400 bg-red-50',
    ];
    const map: Record<number, string> = {};
    props.teamMembers.forEach((m, i) => {
        map[m.id] = gradients[i % gradients.length];
    });
    return map;
});

function memberColor(userId: number | null): string {
    if (!userId) return 'border-l-gray-400 bg-gray-50';
    return memberColors.value[userId] ?? 'border-l-gray-400 bg-gray-50';
}

function yearMonthKey(d: Date): string {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

const rangeStart = computed(() => {
    if (viewMode.value === 'month') {
        const d = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
        d.setDate(d.getDate() - d.getDay());
        return d;
    }
    const d = new Date(currentDate.value);
    d.setDate(d.getDate() - d.getDay());
    d.setHours(0, 0, 0, 0);
    return d;
});

const rangeEnd = computed(() => {
    if (viewMode.value === 'month') {
        const d = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
        d.setDate(d.getDate() + (6 - d.getDay()));
        d.setHours(23, 59, 59, 999);
        return d;
    }
    const d = new Date(rangeStart.value);
    d.setDate(d.getDate() + 6);
    d.setHours(23, 59, 59, 999);
    return d;
});

function fetchEvents(): void {
    loading.value = true;
    axios.get(route('calendar.events'), {
        params: {
            start: rangeStart.value.toISOString(),
            end: rangeEnd.value.toISOString(),
        },
    }).then((res) => {
        events.value = res.data;
    }).finally(() => {
        loading.value = false;
    });
}

watch([rangeStart, rangeEnd], fetchEvents);
onMounted(fetchEvents);

const weeks = computed(() => {
    const cells: DayCell[] = [];
    const now = new Date();
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const currMonth = currentDate.value.getMonth();
    const currYear = currentDate.value.getFullYear();

    const d = new Date(rangeStart.value);
    const end = new Date(rangeEnd.value);

    while (d <= end) {
        const dateStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        cells.push({
            date: new Date(d),
            isCurrentMonth: d.getMonth() === currMonth && d.getFullYear() === currYear,
            isToday: dateStr === todayStr,
            events: events.value.filter((e) => {
                const eDate = e.start_time.slice(0, 10);
                return eDate === dateStr;
            }),
        });
        d.setDate(d.getDate() + 1);
    }
    return cells;
});

const weekRows = computed(() => {
    const rows: DayCell[][] = [];
    for (let i = 0; i < weeks.value.length; i += 7) {
        rows.push(weeks.value.slice(i, i + 7));
    }
    return rows;
});

function prev(): void {
    if (viewMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    } else {
        const d = new Date(currentDate.value);
        d.setDate(d.getDate() - 7);
        currentDate.value = d;
    }
}

function next(): void {
    if (viewMode.value === 'month') {
        currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    } else {
        const d = new Date(currentDate.value);
        d.setDate(d.getDate() + 7);
        currentDate.value = d;
    }
}

function goToday(): void {
    currentDate.value = new Date();
}

function monthLabel(): string {
    return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
}

function weekLabel(): string {
    const start = rangeStart.value;
    const end = rangeEnd.value;
    const opts: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric' };
    if (start.getMonth() !== end.getMonth()) {
        return `${start.toLocaleDateString('en-US', opts)} – ${end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    }
    return `${start.toLocaleDateString('en-US', opts)} – ${end.toLocaleDateString('en-US', { day: 'numeric', year: 'numeric' })}`;
}
</script>

<template>
    <div class="rounded-lg bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <span class="text-lg font-semibold text-gray-800">{{ viewMode === 'month' ? monthLabel() : weekLabel() }}</span>
                <div class="flex items-center gap-1">
                    <button class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700" @click="prev">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700" @click="next">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
                <button class="rounded-md border border-gray-200 px-3 py-1 text-sm text-gray-600 hover:bg-gray-50" @click="goToday">Today</button>
            </div>
            <div class="flex items-center gap-1 rounded-md border border-gray-200 p-0.5">
                <button
                    class="rounded px-3 py-1 text-sm transition"
                    :class="viewMode === 'month' ? 'bg-gray-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
                    @click="viewMode = 'month'"
                >Month</button>
                <button
                    class="rounded px-3 py-1 text-sm transition"
                    :class="viewMode === 'week' ? 'bg-gray-700 text-white' : 'text-gray-600 hover:bg-gray-100'"
                    @click="viewMode = 'week'"
                >Week</button>
            </div>
        </div>

        <div class="p-4">
            <div v-if="loading" class="p-4 animate-pulse">
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <div v-for="i in 7" :key="i" class="bg-gray-50 px-2 py-2">
                        <div class="mx-auto h-3 w-12 rounded bg-stone-300" />
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <div v-for="i in 35" :key="i" class="min-h-24 bg-white px-2 py-1">
                        <div class="mb-1 h-5 w-5 rounded-full bg-stone-300" />
                        <div class="mt-1 h-3 w-3/4 rounded bg-stone-200" />
                        <div class="mt-1 h-3 w-1/2 rounded bg-stone-200" />
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="grid grid-cols-7 gap-px bg-gray-200">
                    <div
                        v-for="day in dayHeaders"
                        :key="day"
                        class="bg-gray-50 px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-600"
                    >
                        {{ day }}
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200" v-if="viewMode === 'month'">
                    <div
                        v-for="(cell, i) in weeks"
                        :key="i"
                        class="min-h-24 bg-white px-2 py-1 transition hover:bg-gray-50"
                        :class="{ 'bg-gray-50/50': !cell.isCurrentMonth }"
                    >
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs"
                            :class="cell.isToday ? 'bg-indigo-600 text-white font-semibold' : cell.isCurrentMonth ? 'text-gray-800' : 'text-gray-400'"
                        >
                            {{ cell.date.getDate() }}
                        </span>
                        <div class="mt-1 space-y-0.5">
                            <div
                                v-for="ev in cell.events.slice(0, 3)"
                                :key="ev.id"
                                class="cursor-pointer truncate rounded border-l-2 px-1 text-xs"
                                :class="memberColor(ev.user_id)"
                            >
                                <Link :href="`/meetings/${ev.id}`" class="text-gray-700 hover:text-indigo-600">
                                    {{ ev.title }}
                                </Link>
                            </div>
                            <div v-if="cell.events.length > 3" class="text-xs text-gray-500">
                                +{{ cell.events.length - 3 }} more
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-200" v-else>
                    <div
                        v-for="(cell, i) in weeks"
                        :key="i"
                        class="min-h-32 bg-white px-2 py-1"
                    >
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs"
                            :class="cell.isToday ? 'bg-indigo-600 text-white font-semibold' : 'text-gray-800'"
                        >
                            {{ cell.date.getDate() }}
                        </span>
                        <div class="mt-1 space-y-1">
                            <div
                                v-for="ev in cell.events"
                                :key="ev.id"
                                class="cursor-pointer truncate rounded border-l-2 px-1 py-0.5 text-xs"
                                :class="memberColor(ev.user_id)"
                            >
                                <Link :href="`/meetings/${ev.id}`" class="text-gray-700 hover:text-indigo-600">
                                    {{ ev.title }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
