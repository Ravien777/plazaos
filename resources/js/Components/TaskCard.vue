<script setup lang="ts">
import InitialsAvatar from '@/Components/InitialsAvatar.vue';
import type { Task } from '@/Types';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    task: Task;
}>();

const editing = ref(false);
const editTitle = ref(props.task.title);

const priorityColors: Record<string, string> = {
    high: 'bg-red-400',
    medium: 'bg-yellow-400',
    low: 'bg-stone-300',
};

function saveTitle(): void {
    const title = editTitle.value.trim();
    if (!title) {
        editing.value = false;
        editTitle.value = props.task.title;
        return;
    }
    router.put(`/tasks/${props.task.id}`, { title }, {
        preserveScroll: true,
        onFinish: () => { editing.value = false; },
    });
}

function cancelEdit(): void {
    editing.value = false;
    editTitle.value = props.task.title;
}
</script>

<template>
    <div class="group rounded-lg border border-stone-200 bg-white px-3 py-2 shadow-sm transition hover:shadow-md">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" :class="priorityColors[task.priority]" />
                <div v-if="!editing" class="min-w-0 flex-1">
                    <span
                        class="block cursor-pointer truncate text-sm font-medium text-gray-800 hover:text-indigo-600"
                        @click="editing = true"
                    >
                        {{ task.title }}
                    </span>
                </div>
                <input
                    v-else
                    ref="inputEl"
                    v-model="editTitle"
                    class="w-full rounded border border-indigo-300 px-1 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400"
                    @keyup.enter="saveTitle"
                    @keyup.escape="cancelEdit"
                    @blur="saveTitle"
                    autofocus
                />
            </div>
            <div class="flex shrink-0 items-center gap-1">
                <InitialsAvatar v-if="task.assignee" :name="task.assignee.name" size="h-6 w-6 text-xs" />
            </div>
        </div>
    </div>
</template>
