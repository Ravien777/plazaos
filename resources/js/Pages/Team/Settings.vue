<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    team: { id: string; name: string };
    invitations: Array<{ id: number; email: string; expires_at: string; expired: boolean }>;
}>();

const form = useForm({
    name: props.team.name,
});

const inviteForm = useForm({
    email: '',
});

const deleteConfirm = ref('');

const deleteForm = useForm({});

function updateTeam(): void {
    form.put(route('team.update'));
}

function sendInvite(): void {
    inviteForm.post(route('team.members.invite'), {
        preserveScroll: true,
        onSuccess: () => inviteForm.reset(),
    });
}

function cancelInvitation(id: number): void {
    router.delete(route('team.invitations.destroy', id));
}

function deleteWorkspace(): void {
    if (deleteConfirm.value !== 'DELETE') return;
    deleteForm.delete(route('team.destroy'));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Team Settings
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-800">
                        Team Name
                    </h3>

                    <form @submit.prevent="updateTeam" class="mt-4">
                        <input
                            v-model="form.name"
                            type="text"
                            class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>

                        <div class="mt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-800">
                        Invite Team Members
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Send an email invitation to join your team.
                    </p>

                    <form @submit.prevent="sendInvite" class="mt-4 flex gap-2">
                        <input
                            v-model="inviteForm.email"
                            type="email"
                            placeholder="colleague@company.com"
                            class="block flex-1 rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                        />
                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-50"
                        >
                            {{ inviteForm.processing ? 'Sending...' : 'Invite' }}
                        </button>
                    </form>
                    <p v-if="inviteForm.errors.email" class="mt-1 text-sm text-red-600">{{ inviteForm.errors.email }}</p>

                    <div v-if="invitations.length > 0" class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700">Pending Invitations</h4>
                        <ul class="mt-2 divide-y divide-gray-100">
                            <li v-for="inv in invitations" :key="inv.id" class="flex items-center justify-between py-2">
                                <div>
                                    <span class="text-sm text-gray-700">{{ inv.email }}</span>
                                    <span
                                        v-if="inv.expired"
                                        class="ml-2 text-xs text-red-500"
                                    >Expired</span>
                                    <span v-else class="ml-2 text-xs text-gray-400">Expires {{ inv.expires_at }}</span>
                                </div>
                                <button
                                    class="text-sm text-red-500 hover:text-red-700"
                                    @click="cancelInvitation(inv.id)"
                                >
                                    Cancel
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-l-4 border-red-400 bg-red-50 p-6">
                        <h3 class="text-lg font-medium text-red-800">
                            Delete Workspace
                        </h3>
                        <p class="mt-1 text-sm text-red-600">
                            This will permanently delete all team data including leads, clients, projects, and documents. This action cannot be undone.
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <input
                                v-model="deleteConfirm"
                                type="text"
                                placeholder='Type "DELETE" to confirm'
                                class="block w-48 rounded-md border-red-300 shadow-sm focus:border-red-400 focus:ring-red-400 sm:text-sm"
                            />
                            <button
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500 disabled:opacity-50"
                                :disabled="deleteForm.processing || deleteConfirm !== 'DELETE'"
                                @click="deleteWorkspace"
                            >
                                {{ deleteForm.processing ? 'Deleting...' : 'Delete Workspace' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
