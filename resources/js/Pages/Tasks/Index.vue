<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import type { Task, User } from '@/Types';

const ready = ref(false);

onMounted(async () => {
    await nextTick();
    ready.value = true;
});

const props = defineProps<{
    tasks: Record<string, Task[]>;
    projects: { id: string; name: string }[];
    assignees: User[];
    filters: { project_id?: string; assignee_id?: string };
}>();

const selectedProject = ref(props.filters.project_id || '');
const selectedAssignee = ref(props.filters.assignee_id || '');

const totalTasks = computed(() => Object.values(props.tasks).reduce((sum, arr) => sum + arr.length, 0));

function applyFilters(): void {
    router.get(route('tasks.index'), {
        project_id: selectedProject.value || undefined,
        assignee_id: selectedAssignee.value || undefined,
    }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Tasks" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-4 flex items-center gap-4">
                    <select
                        v-model="selectedProject"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="applyFilters"
                    >
                        <option value="">All Projects</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <select
                        v-model="selectedAssignee"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="applyFilters"
                    >
                        <option value="">All Assignees</option>
                        <option v-for="a in assignees" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>

                <div v-if="!ready" class="flex gap-4 overflow-x-auto pb-4">
                    <div v-for="col in ['To Do', 'In Progress', 'Done']" :key="col" class="flex min-w-64 flex-1 flex-col rounded-lg border border-stone-200 border-t-4 bg-stone-50 p-3">
                        <SkeletonLoader class="mb-3" height="1rem" width="6rem" />
                        <div v-for="j in 3" :key="j" class="mb-2 rounded border border-stone-200 bg-white p-3">
                            <SkeletonLoader class="mb-2" height="0.875rem" width="80%" />
                            <SkeletonLoader height="0.75rem" width="40%" />
                        </div>
                    </div>
                </div>
                <EmptyState
                    v-else-if="totalTasks === 0"
                    icon="📋"
                    title="No tasks yet"
                    message="Tasks will appear here once you add them to a project."
                />
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <KanbanBoard
                            :tasks="tasks"
                            :projects="projects"
                            :assignees="assignees"
                            :project-filter="selectedProject || undefined"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
