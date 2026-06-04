<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import type { Meeting } from '@/Types';

const props = defineProps<{
    meetableType: string;
    meetableId: string;
}>();

const meetings = ref<Meeting[]>([]);
const showForm = ref(false);
const form = useForm({
    title: '',
    description: '',
    start_time: '',
    end_time: '',
    location: '',
    meet_link: '',
    provider: 'google_meet',
});

const providerLabels: Record<string, string> = {
    google_meet: 'Google Meet',
    zoom: 'Zoom',
    microsoft_teams: 'Microsoft Teams',
};

async function fetchMeetings(): Promise<void> {
    try {
        const res = await axios.get(`/${props.meetableType}/${props.meetableId}/meetings`);
        meetings.value = res.data.data ?? [];
    } catch {
        meetings.value = [];
    }
}

function scheduleMeeting(): void {
    form.post(`/${props.meetableType}/${props.meetableId}/meetings`, {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset();
            fetchMeetings();
        },
    });
}

function deleteMeeting(meeting: Meeting): void {
    if (confirm(`Cancel "${meeting.title}"?`)) {
        router.delete(`/meetings/${meeting.id}`, {
            preserveScroll: true,
            onSuccess: () => fetchMeetings(),
        });
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

onMounted(fetchMeetings);
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Meetings</h3>
            <button
                class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                @click="showForm = !showForm"
            >
                {{ showForm ? 'Cancel' : 'Schedule' }}
            </button>
        </div>

        <div v-if="showForm" class="mb-6 rounded-md border border-gray-200 bg-gray-50 p-4">
            <form @submit.prevent="scheduleMeeting" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Time</label>
                        <input
                            v-model="form.start_time"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Time</label>
                        <input
                            v-model="form.end_time"
                            type="datetime-local"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input
                            v-model="form.location"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Provider</label>
                        <select
                            v-model="form.provider"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="google_meet">Google Meet</option>
                            <option value="zoom">Zoom</option>
                            <option value="microsoft_teams">Microsoft Teams</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                    >
                        {{ form.processing ? 'Saving...' : 'Schedule Meeting' }}
                    </button>
                </div>
            </form>
        </div>

        <div v-if="meetings.length === 0" class="text-sm text-gray-500">
            No meetings scheduled.
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="meeting in meetings"
                :key="meeting.id"
                class="flex items-center justify-between rounded-md border border-gray-200 p-3"
            >
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">{{ meeting.title }}</span>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(meeting.status)">
                            {{ statusLabel(meeting.status) }}
                        </span>
                        <span v-if="meeting.provider" class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="providerClass(meeting.provider)">
                            {{ providerLabels[meeting.provider] ?? meeting.provider }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500">{{ formatDate(meeting.start_time) }}</p>
                    <p v-if="meeting.location || meeting.meet_link" class="text-xs text-gray-500">
                        {{ meeting.location ?? '' }}{{ meeting.location && meeting.meet_link ? ' · ' : '' }}
                        <a v-if="meeting.meet_link" :href="meeting.meet_link" target="_blank" class="text-indigo-600 hover:text-indigo-900">Join</a>
                    </p>
                    <p v-if="meeting.description" class="mt-1 text-xs text-gray-500">{{ meeting.description }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="`/meetings/${meeting.id}/edit`"
                        class="text-xs text-indigo-600 hover:text-indigo-900"
                    >
                        Edit
                    </Link>
                    <button
                        class="text-xs text-red-600 hover:text-red-900"
                        @click="deleteMeeting(meeting)"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
