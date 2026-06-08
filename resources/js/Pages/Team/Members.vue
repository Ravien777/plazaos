<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InitialsAvatar from '@/Components/InitialsAvatar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { useConfirm } from '@/composables/useConfirm';

const { confirm } = useConfirm();

const props = defineProps<{
    members: Array<{ id: number; name: string; email: string; role: string; created_at: string }>;
    isOwner: boolean;
}>();

async function removeMember(id: number, name: string): Promise<void> {
    if (!await confirm({ title: 'Remove member?', message: `Remove ${name} from the team?`, confirmLabel: 'Remove' })) return;
    router.delete(route('team.members.remove', id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Team Members
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <EmptyState
                        v-if="members.length === 0"
                        icon="👥"
                        title="No members yet"
                        message="Invite your teammates to collaborate on leads, projects, and more."
                        action-label="Invite Members"
                        action-href="/team/invite"
                    />

                    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="member in members"
                            :key="member.id"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 p-4"
                        >
                            <InitialsAvatar :name="member.name" class="h-10 w-10 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800">
                                    {{ member.name }}
                                </p>
                                <p class="truncate text-xs text-gray-500">
                                    {{ member.email }}
                                </p>
                                <span
                                    class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="member.role === 'owner'
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ member.role === 'owner' ? 'Owner' : 'Member' }}
                                </span>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a
                                    :href="route('tasks.index', { assignee_id: member.id })"
                                    class="text-sm text-blue-500 hover:text-blue-700"
                                >
                                    Assign
                                </a>
                                <a
                                    :href="'mailto:' + member.email"
                                    class="text-sm text-blue-500 hover:text-blue-700"
                                >
                                    Message
                                </a>
                                <button
                                    v-if="isOwner && member.role !== 'owner'"
                                    class="text-sm text-red-500 hover:text-red-700"
                                    @click="removeMember(member.id, member.name)"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
