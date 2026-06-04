<script setup lang="ts">
import type { Meeting } from '@/Types';
import { Link } from '@inertiajs/vue3';

defineProps<{
    meetings: Meeting[];
}>();

function formatTime(dt: string): string {
    const d = new Date(dt);
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <div class="rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-800">Today's Focus</h3>
            <p class="mt-1 text-xs text-gray-500">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}</p>
            <div v-if="meetings.length === 0" class="mt-4 text-sm text-gray-500">
                No meetings scheduled for today.
            </div>
            <div v-else class="mt-4 space-y-3">
                <div
                    v-for="meeting in meetings"
                    :key="meeting.id"
                    class="rounded-md border-l-4 border-indigo-400 bg-indigo-50 px-3 py-2"
                >
                    <Link :href="`/meetings/${meeting.id}`" class="text-sm font-medium text-gray-800 hover:text-indigo-600">
                        {{ meeting.title }}
                    </Link>
                    <p class="text-xs text-gray-500">{{ formatTime(meeting.start_time) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
