<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import type { CommentData } from '@/Types';

const { confirm } = useConfirm();
const toast = useToast();
const page = usePage();

const props = defineProps<{
    commentableType: string;
    commentableId: string;
}>();

const currentUserId = computed(() => (page.props.auth.user as { id: number })?.id);

const comments = ref<CommentData[]>([]);
const newComment = ref('');
const loading = ref(false);

onMounted(() => {
    fetchComments();
});

function fetchComments(): void {
    loading.value = true;
    fetch(route('comments.index', { commentableType: props.commentableType, commentable: props.commentableId }))
        .then((res) => res.json())
        .then((data) => {
            comments.value = data.data ?? [];
        })
        .finally(() => {
            loading.value = false;
        });
}

function addComment(): void {
    if (!newComment.value.trim()) return;

    router.post(
        `/${props.commentableType}/${props.commentableId}/comments`,
        { body: newComment.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
                fetchComments();
            },
            onError: () => {
                toast.error('Failed to add comment.');
            },
        },
    );
}

async function deleteComment(comment: CommentData): Promise<void> {
    if (!await confirm({ title: 'Delete comment?', message: 'Remove this comment?' })) return;

    router.delete(`/comments/${comment.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            comments.value = comments.value.filter((c) => c.id !== comment.id);
        },
    });
}

function userInitials(name: string): string {
    return name
        .split(' ')
        .map((p) => p[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
}

function timeAgo(iso: string): string {
    const d = new Date(iso);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffMin = Math.floor(diffMs / 60000);

    if (diffMin < 1) return 'just now';
    if (diffMin < 60) return `${diffMin}m ago`;

    const diffHrs = Math.floor(diffMin / 60);
    if (diffHrs < 24) return `${diffHrs}h ago`;

    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}
</script>

<template>
    <div>
        <h3 class="text-lg font-medium text-gray-800">Comments</h3>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="i in 2" :key="i" class="flex gap-3">
                <SkeletonLoader class="shrink-0" width="2.25rem" height="2.25rem" shape="circle" />
                <div class="flex-1">
                    <SkeletonLoader class="mb-1" height="0.75rem" width="30%" />
                    <SkeletonLoader height="0.75rem" width="60%" />
                </div>
            </div>
        </div>

        <div v-else class="mt-4 space-y-4">
            <div v-if="comments.length === 0" class="text-sm text-gray-500">
                No comments yet. Start the conversation.
            </div>

            <div
                v-for="comment in comments"
                :key="comment.id"
                class="flex gap-3"
            >
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700"
                >
                    {{ userInitials(comment.user.name) }}
                </div>
                <div class="min-w-0 flex-1 rounded-lg bg-gray-50 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800">{{ comment.user.name }}</span>
                            <span class="text-xs text-gray-500">{{ timeAgo(comment.created_at) }}</span>
                        </div>
                        <button
                            v-if="comment.can_delete"
                            class="shrink-0 text-xs text-red-500 hover:text-red-700"
                            @click="deleteComment(comment)"
                        >
                            Delete
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap break-words">{{ comment.body }}</p>
                </div>
            </div>

            <div class="mt-4">
                <textarea
                    v-model="newComment"
                    rows="2"
                    placeholder="Write a comment... Use @name to mention someone."
                    class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                />
                <div class="mt-2 flex justify-end">
                    <button
                        class="rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-50"
                        :disabled="!newComment.trim()"
                        @click="addComment"
                    >
                        Post Comment
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
