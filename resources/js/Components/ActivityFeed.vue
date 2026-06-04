<script setup lang="ts">
import type { Activity } from '@/Types';

defineProps<{
    activities: Activity[];
}>();
</script>

<template>
    <div>
        <div v-if="activities.length === 0" class="text-sm text-gray-600">
            No recent activity.
        </div>
        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="activity in activities"
                :key="activity.id"
                class="py-3"
            >
                <p class="text-sm text-gray-700">{{ activity.description }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ activity.created_at }}</p>
            </li>
        </ul>
    </div>
</template>
