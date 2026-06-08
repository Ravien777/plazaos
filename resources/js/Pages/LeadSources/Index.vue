<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { LeadSource, PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    sources: PaginatedResponse<LeadSource>;
}>();

async function runSource(source: LeadSource): Promise<void> {
    if (!await confirm({ title: 'Start scraping?', message: `Start scraping "${source.name}"?`, confirmLabel: 'Start Scraping' })) return;
    router.post(`/lead-sources/${source.id}/run`);
}

async function destroySource(source: LeadSource): Promise<void> {
    if (!await confirm({ title: 'Delete source?', message: `Delete "${source.name}"? This cannot be undone.` })) return;
    router.delete(`/lead-sources/${source.id}`, {
        onSuccess: () => toast.success(`"${source.name}" deleted.`),
    });
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

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'frequency', label: 'Frequency' },
    { key: 'is_active', label: 'Active' },
    { key: 'last_run_at', label: 'Last Run' },
    { key: 'next_run_at', label: 'Next Run' },
];
</script>

<template>
    <Head title="Lead Sources" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Lead Sources">
                <template #actions>
                    <Link
                        href="/lead-sources/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Source
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable
                            :items="sources.data"
                            :columns="columns"
                            empty-icon="📡"
                            empty-title="No lead sources yet"
                            empty-message="Add a lead source to start tracking where leads come from."
                            empty-action-label="New Source"
                            empty-action-href="/lead-sources/create"
                        >
                            <template #cell-type="{ item }">
                                {{ typeLabels[item.type] ?? item.type }}
                            </template>
                            <template #cell-frequency="{ item }">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-gray-100 text-gray-600': item.frequency === 'manual',
                                        'bg-blue-100 text-blue-700': item.frequency === 'hourly',
                                        'bg-purple-100 text-purple-700': item.frequency === 'daily',
                                        'bg-orange-100 text-orange-700': item.frequency === 'weekly',
                                    }"
                                >
                                    {{ frequencyLabels[item.frequency] ?? item.frequency }}
                                </span>
                            </template>
                            <template #cell-is_active="{ item }">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ item.is_active ? 'Yes' : 'No' }}
                                </span>
                            </template>
                            <template #cell-last_run_at="{ item }">
                                {{ item.last_run_at ?? 'Never' }}
                            </template>
                            <template #cell-next_run_at="{ item }">
                                {{ nextRun(item) }}
                            </template>
                            <template #actions="{ item }">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                        @click="runSource(item)"
                                    >
                                        Run
                                    </button>
                                    <Link
                                        :href="`/lead-sources/${item.id}/edit`"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                        @click="destroySource(item)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ item.name }}
                                        </span>
                                    </div>
                                    <span
                                        class="shrink-0 ml-2 inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span>Type: {{ typeLabels[item.type] ?? item.type }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>Frequency: {{ frequencyLabels[item.frequency] ?? item.frequency }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>Last run: {{ item.last_run_at ?? 'Never' }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="sources.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
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
