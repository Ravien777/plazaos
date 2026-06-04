<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import StatusBadge from '@/Components/StatusBadge.vue';
import type { Project } from '@/Types';

defineProps<{
    projects: Project[];
}>();

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        discovery: 'Discovery',
        design: 'Design',
        development: 'Development',
        testing: 'Testing',
        launch: 'Launch',
        completed: 'Completed',
    };
    return labels[s] ?? s;
}
</script>

<template>
    <div>
        <h3 class="mb-4 text-lg font-medium text-gray-900">Projects</h3>

        <div v-if="projects.length === 0" class="text-sm text-gray-500">
            No projects yet.
            <Link :href="`/projects/create`" class="text-indigo-600 hover:text-indigo-900">Create one</Link>.
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="project in projects"
                :key="project.id"
                class="flex items-center justify-between rounded-md border border-gray-200 p-3"
            >
                <div>
                    <Link
                        :href="`/projects/${project.id}`"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                    >
                        {{ project.name }}
                    </Link>
                    <p v-if="project.start_date || project.due_date" class="text-xs text-gray-500">
                        {{ project.start_date ?? '?' }} → {{ project.due_date ?? '?' }}
                    </p>
                </div>
                <StatusBadge :status="project.status">
                    {{ statusLabel(project.status) }}
                </StatusBadge>
            </div>
        </div>
    </div>
</template>
