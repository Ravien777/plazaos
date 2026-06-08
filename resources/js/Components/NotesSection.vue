<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';
import { useConfirm } from '@/composables/useConfirm';
import type { Note } from '@/Types';

const { confirm } = useConfirm();

const props = defineProps<{
    noteableType: string;
    noteableId: string;
}>();

const notes = ref<Note[]>([]);
const newNoteContent = ref('');
const editingNoteId = ref<string | null>(null);
const editContent = ref('');
const loading = ref(false);

onMounted(() => {
    fetchNotes();
});

function fetchNotes(): void {
    loading.value = true;
    fetch(route('notes.index', { noteableType: props.noteableType, noteable: props.noteableId }))
        .then((res) => res.json())
        .then((data) => {
            notes.value = data.data ?? [];
        })
        .finally(() => {
            loading.value = false;
        });
}

function addNote(): void {
    if (!newNoteContent.value.trim()) return;

    router.post(
        `/${props.noteableType}/${props.noteableId}/notes`,
        { content: newNoteContent.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                newNoteContent.value = '';
                fetchNotes();
            },
        },
    );
}

function startEdit(note: Note): void {
    editingNoteId.value = note.id;
    editContent.value = note.content;
}

function cancelEdit(): void {
    editingNoteId.value = null;
    editContent.value = '';
}

function updateNote(note: Note): void {
    if (!editContent.value.trim()) return;

    router.put(
        `/notes/${note.id}`,
        { content: editContent.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                editingNoteId.value = null;
                editContent.value = '';
                fetchNotes();
            },
        },
    );
}

async function deleteNote(note: Note): Promise<void> {
    if (!await confirm({ title: 'Delete note?', message: 'Delete this note?' })) return;

    router.delete(`/notes/${note.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            fetchNotes();
        },
    });
}
</script>

<template>
    <div>
        <h3 class="text-lg font-medium text-gray-800">Notes</h3>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="i in 3" :key="i" class="rounded-lg border border-gray-200 bg-white p-4">
                <SkeletonLoader class="mb-2" height="0.75rem" width="40%" />
                <SkeletonLoader height="0.75rem" width="70%" />
            </div>
        </div>

        <div v-else class="mt-4 space-y-4">
            <div
                v-for="note in notes"
                :key="note.id"
                class="rounded-lg border bg-gray-50 p-4"
            >
                <div v-if="editingNoteId === note.id">
                    <textarea
                        v-model="editContent"
                        rows="3"
                        class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                    />
                    <div class="mt-2 flex justify-end gap-2">
                        <button
                            class="rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50"
                            @click="cancelEdit"
                        >
                            Cancel
                        </button>
                        <button
                            class="rounded-md bg-gray-700 px-3 py-1 text-xs font-medium text-white hover:bg-gray-600"
                            @click="updateNote(note)"
                        >
                            Save
                        </button>
                    </div>
                </div>
                <div v-else>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ note.content }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs text-gray-500">{{ note.created_at }}</p>
                        <div class="flex gap-2">
                            <button
                                class="text-xs text-indigo-500 hover:text-indigo-600"
                                @click="startEdit(note)"
                            >
                                Edit
                            </button>
                            <button
                                class="text-xs text-red-600 hover:text-red-900"
                                @click="deleteNote(note)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <textarea
                    v-model="newNoteContent"
                    rows="2"
                    placeholder="Add a note..."
                    class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                />
                <div class="mt-2 flex justify-end">
                    <button
                        class="rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                        @click="addNote"
                    >
                        Add Note
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
