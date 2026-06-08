<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useConfirm } from '@/composables/useConfirm';

const { confirm } = useConfirm();

const props = defineProps<{
    clientId: string;
    users: { id: string; name: string; email: string; created_at: string }[];
}>();

const showForm = ref(false);

const form = useForm({
    name: '',
    email: '',
});

function submit(): void {
    form.post(`/clients/${props.clientId}/portal-users`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

async function remove(userId: string): Promise<void> {
    if (!await confirm({ title: 'Remove portal user?', message: 'Remove this portal user? They will lose access immediately.', confirmLabel: 'Remove' })) return;

    router.delete(`/clients/${props.clientId}/portal-users/${userId}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800">Portal Users</h3>
            <button
                @click="showForm = !showForm"
                class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600"
            >
                {{ showForm ? 'Cancel' : 'Add User' }}
            </button>
        </div>

        <form v-if="showForm" @submit.prevent="submit" class="mt-4 rounded-md border border-gray-200 p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700">Name</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" required />
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Email</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" required />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600 disabled:opacity-50">
                    Create User
                </button>
            </div>
        </form>

        <div v-if="users.length === 0" class="mt-4 text-sm text-gray-600">No portal users yet.</div>
        <div v-else class="mt-4 space-y-2">
            <div v-for="user in users" :key="user.id" class="flex items-center justify-between rounded-md border border-gray-200 p-3">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ user.name }}</p>
                    <p class="text-xs text-gray-600">{{ user.email }} &middot; created {{ user.created_at }}</p>
                </div>
                <button @click="remove(user.id)" class="text-xs text-red-600 hover:text-red-900">Revoke</button>
            </div>
        </div>
    </div>
</template>
