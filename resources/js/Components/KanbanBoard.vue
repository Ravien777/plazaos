<script setup lang="ts">
import TaskCard from '@/Components/TaskCard.vue';
import type { Task, User } from '@/Types';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';

const props = defineProps<{
    tasks: Record<string, Task[]>;
    projects?: { id: string; name: string }[];
    assignees?: User[];
    projectFilter?: string;
}>();

const columns = [
    { key: 'todo', label: 'To Do', color: 'border-t-yellow-400' },
    { key: 'in_progress', label: 'In Progress', color: 'border-t-blue-400' },
    { key: 'done', label: 'Done', color: 'border-t-green-400' },
];

const todoList = ref<Task[]>([]);
const inProgressList = ref<Task[]>([]);
const doneList = ref<Task[]>([]);

watch(() => props.tasks, (t) => {
    todoList.value = [...(t.todo || [])];
    inProgressList.value = [...(t.in_progress || [])];
    doneList.value = [...(t.done || [])];
}, { immediate: true, deep: true });

const newTaskInputs: Record<string, string> = {};
columns.forEach(c => (newTaskInputs[c.key] = ''));

function addTask(column: string): void {
    const title = newTaskInputs[column]?.trim();
    if (!title) return;
    router.post(route('tasks.store'), {
        title,
        status: column,
        project_id: props.projectFilter || null,
    }, {
        preserveScroll: true,
        onFinish: () => { newTaskInputs[column] = ''; },
    });
}

function onDragEnd(evt: any, column: string): void {
    const taskId = evt.item.getAttribute('data-task-id');
    if (!taskId) return;
    const newOrder = evt.newIndex;
    router.put(route('tasks.move', taskId), {
        status: column,
        order: newOrder,
    }, { preserveScroll: true });
}
</script>

<template>
    <div class="flex gap-4 overflow-x-auto pb-4">
        <div
            v-for="col in columns"
            :key="col.key"
            class="flex min-w-64 flex-1 flex-col rounded-lg border border-gray-200 border-t-4 bg-gray-50"
            :class="col.color"
        >
            <div class="border-b border-gray-200 px-3 py-2">
                <h3 class="text-sm font-semibold text-gray-700">
                    {{ col.label }}
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ (col.key === 'todo' ? todoList : col.key === 'in_progress' ? inProgressList : doneList).length }})</span>
                </h3>
            </div>

            <VueDraggable
                v-if="col.key === 'todo'"
                v-model="todoList"
                class="flex flex-col gap-2 p-3 min-h-24"
                group="tasks"
                item-key="id"
                ghost-class="opacity-30"
                @end="(evt: any) => onDragEnd(evt, col.key)"
            >
                <template #item="{ element }">
                    <div :data-task-id="element.id">
                        <TaskCard :task="element" />
                    </div>
                </template>
            </VueDraggable>
            <VueDraggable
                v-else-if="col.key === 'in_progress'"
                v-model="inProgressList"
                class="flex flex-col gap-2 p-3 min-h-24"
                group="tasks"
                item-key="id"
                ghost-class="opacity-30"
                @end="(evt: any) => onDragEnd(evt, col.key)"
            >
                <template #item="{ element }">
                    <div :data-task-id="element.id">
                        <TaskCard :task="element" />
                    </div>
                </template>
            </VueDraggable>
            <VueDraggable
                v-else
                v-model="doneList"
                class="flex flex-col gap-2 p-3 min-h-24"
                group="tasks"
                item-key="id"
                ghost-class="opacity-30"
                @end="(evt: any) => onDragEnd(evt, col.key)"
            >
                <template #item="{ element }">
                    <div :data-task-id="element.id">
                        <TaskCard :task="element" />
                    </div>
                </template>
            </VueDraggable>

            <div class="border-t border-gray-200 px-3 py-2">
                <form @submit.prevent="addTask(col.key)">
                    <input
                        v-model="newTaskInputs[col.key]"
                        type="text"
                        placeholder="+ Add task..."
                        class="w-full rounded border-0 bg-transparent px-1 py-1 text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-0"
                    />
                </form>
            </div>
        </div>
    </div>
</template>
