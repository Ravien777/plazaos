<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';

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
});

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

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-8 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg sm:p-8">
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
