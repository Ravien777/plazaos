<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { LeadSource, PaginatedResponse } from '@/Types';

const props = defineProps<{
    sources: PaginatedResponse<LeadSource>;
}>();

function runSource(source: LeadSource): void {
    if (confirm(`Start scraping "${source.name}"?`)) {
        router.post(`/lead-sources/${source.id}/run`);
    }
}

function destroySource(source: LeadSource): void {
    if (confirm(`Delete "${source.name}"? This cannot be undone.`)) {
        router.delete(`/lead-sources/${source.id}`);
    }
}

const typeLabels: Record<string, string> = {
    linkedin: 'LinkedIn',
    upwork: 'Upwork',
    freelancer: 'Freelancer',
    fiverr: 'Fiverr',
    guru: 'Guru',
    people_per_hour: 'PeoplePerHour',
    toptal: 'Toptal',
    angel_list: 'AngelList',
    wellfound: 'Wellfound',
    remote_ok: 'Remote OK',
    we_work_remotely: 'We Work Remotely',
    cold_email: 'Cold Email',
    referral: 'Referral',
    website: 'Website',
    other: 'Other',
};

const frequencyLabels: Record<string, string> = {
    manual: 'Manual',
    hourly: 'Hourly',
    daily: 'Daily',
    weekly: 'Weekly',
};

function nextRun(source: LeadSource): string {
    if (!source.is_active || source.frequency === 'manual') {
        return '—';
    }

    if (!source.last_run_at) {
        return 'Next schedule';
    }

    const intervals: Record<string, number> = {
        hourly: 60 * 60 * 1000,
        daily: 24 * 60 * 60 * 1000,
        weekly: 7 * 24 * 60 * 60 * 1000,
    };

    const ms = intervals[source.frequency];
    if (!ms) return '—';

    const next = new Date(new Date(source.last_run_at).getTime() + ms);
    return next.toLocaleString();
}
</script>

<template>
    <Head title="Lead Sources" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Lead Sources">
                <template #actions>
                    <Link
                        href="/lead-sources/create"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                    >
                        New Source
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Active</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Frequency</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Run</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Next Run</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="source in sources.data"
                                        :key="source.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                            {{ source.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ typeLabels[source.type] ?? source.type }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                                :class="source.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            >
                                                {{ source.is_active ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <span
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                                :class="{
                                                    'bg-gray-100 text-gray-500': source.frequency === 'manual',
                                                    'bg-blue-100 text-blue-700': source.frequency === 'hourly',
                                                    'bg-purple-100 text-purple-700': source.frequency === 'daily',
                                                    'bg-orange-100 text-orange-700': source.frequency === 'weekly',
                                                }"
                                            >
                                                {{ frequencyLabels[source.frequency] ?? source.frequency }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ source.last_run_at ?? 'Never' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ nextRun(source) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    @click="runSource(source)"
                                                >
                                                    Run
                                                </button>
                                                <Link
                                                    :href="`/lead-sources/${source.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="destroySource(source)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="sources.data.length === 0">
                                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                                            No lead sources found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="sources.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing {{ sources.from }} to {{ sources.to }} of {{ sources.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="sources.current_page > 1"
                                    :href="`/lead-sources?page=${sources.current_page - 1}`"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="sources.current_page < sources.last_page"
                                    :href="`/lead-sources?page=${sources.current_page + 1}`"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
