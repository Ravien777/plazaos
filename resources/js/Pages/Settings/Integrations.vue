<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps<{
    settings: Record<string, string | null>;
}>();

const form = useForm({
    zoom_enabled: props.settings.zoom_enabled === 'true' ? 'true' : 'false',
    zoom_client_id: props.settings.zoom_client_id ?? '',
    zoom_client_secret: props.settings.zoom_client_secret ?? '',
    zoom_account_id: props.settings.zoom_account_id ?? '',
    teams_enabled: props.settings.teams_enabled === 'true' ? 'true' : 'false',
    teams_client_id: props.settings.teams_client_id ?? '',
    teams_client_secret: props.settings.teams_client_secret ?? '',
    teams_tenant_id: props.settings.teams_tenant_id ?? '',
    google_calendar_enabled: props.settings.google_calendar_enabled === 'true' ? 'true' : 'false',
    google_calendar_id: props.settings.google_calendar_id ?? '',
    google_calendar_credentials: props.settings.google_calendar_credentials ?? '',
    maroni_enabled: props.settings.maroni_enabled === 'true' ? 'true' : 'false',
    maroni_base_url: props.settings.maroni_base_url ?? '',
    maroni_api_key: props.settings.maroni_api_key ?? '',
    maroni_webhook_secret: props.settings.maroni_webhook_secret ?? '',
    trello_api_key: props.settings.trello_api_key ?? '',
    trello_api_token: props.settings.trello_api_token ?? '',
    resend_api_key: props.settings.resend_api_key ?? '',
    resend_webhook_secret: props.settings.resend_webhook_secret ?? '',
    mail_from_address: props.settings.mail_from_address ?? '',
    mail_from_name: props.settings.mail_from_name ?? '',
    openai_api_key: props.settings.openai_api_key ?? '',
    openai_model: props.settings.openai_model ?? '',
});

const syncingTrello = ref(false);
const syncingMaroni = ref(false);

async function syncMaroni(): Promise<void> {
    syncingMaroni.value = true;
    try {
        await router.post(route('maroni.sync'), {}, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Clients synced to Maroni.');
                syncingMaroni.value = false;
            },
            onError: () => {
                toast.error('Failed to sync clients to Maroni.');
                syncingMaroni.value = false;
            },
        });
    } catch {
        syncingMaroni.value = false;
    }
}

async function syncTrello(): Promise<void> {
    syncingTrello.value = true;
    try {
        await router.post(route('trello.sync'), {}, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Trello synced successfully.');
                syncingTrello.value = false;
            },
            onError: () => {
                toast.error('Failed to sync Trello.');
                syncingTrello.value = false;
            },
        });
    } catch {
        syncingTrello.value = false;
    }
}

function submit(): void {
    form.post(route('settings.integrations'));
}
</script>

<template>
    <Head title="Integrations" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">Integrations</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Zoom -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Zoom</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Enabled</label>
                                    <select v-model="form.zoom_enabled" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client ID</label>
                                    <input v-model="form.zoom_client_id" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Secret</label>
                                    <input v-model="form.zoom_client_secret" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Account ID</label>
                                    <input v-model="form.zoom_account_id" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Microsoft Teams -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Microsoft Teams</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Enabled</label>
                                    <select v-model="form.teams_enabled" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client ID</label>
                                    <input v-model="form.teams_client_id" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client Secret</label>
                                    <input v-model="form.teams_client_secret" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tenant ID</label>
                                    <input v-model="form.teams_tenant_id" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Google Calendar -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Google Calendar</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Enabled</label>
                                    <select v-model="form.google_calendar_enabled" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Calendar ID</label>
                                    <input v-model="form.google_calendar_id" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder="primary" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Credentials (JSON)</label>
                                    <textarea v-model="form.google_calendar_credentials" rows="4" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder='{"type": "service_account", ...}'></textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Maroni -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Maroni (Financial)</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Enabled</label>
                                    <select v-model="form.maroni_enabled" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm">
                                        <option value="false">No</option>
                                        <option value="true">Yes</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Base URL</label>
                                    <input v-model="form.maroni_base_url" type="url" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder="https://maroni.example.com" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Key</label>
                                    <input v-model="form.maroni_api_key" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                                    <input v-model="form.maroni_webhook_secret" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div class="sm:col-span-2">
                                    <button
                                        type="button"
                                        :disabled="syncingMaroni"
                                        class="inline-flex items-center rounded-md bg-emerald-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-emerald-600 disabled:opacity-50"
                                        @click="syncMaroni"
                                    >
                                        {{ syncingMaroni ? 'Syncing...' : 'Sync Clients to Maroni' }}
                                    </button>
                                    <span class="ml-2 text-sm text-gray-500">
                                        Sends all clients to Maroni
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Trello -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Trello</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Key</label>
                                    <input v-model="form.trello_api_key" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Token</label>
                                    <input v-model="form.trello_api_token" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div class="sm:col-span-2">
                                    <button
                                        type="button"
                                        :disabled="syncingTrello"
                                        class="inline-flex items-center rounded-md bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-green-600 disabled:opacity-50"
                                        @click="syncTrello"
                                    >
                                        {{ syncingTrello ? 'Syncing...' : 'Sync from Trello' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Resend (Email) -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Resend (Email)</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Key</label>
                                    <input v-model="form.resend_api_key" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Webhook Secret</label>
                                    <input v-model="form.resend_webhook_secret" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Address</label>
                                    <input v-model="form.mail_from_address" type="email" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder="noreply@example.com" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">From Name</label>
                                    <input v-model="form.mail_from_name" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder="PlazaOS" />
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- OpenAI (AI) -->
                        <div>
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">OpenAI (AI)</h3>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Key</label>
                                    <input v-model="form.openai_api_key" type="password" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Model</label>
                                    <input v-model="form.openai_model" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" placeholder="gpt-4o" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-50"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
