<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import SearchInput from '@/Components/SearchInput.vue';
import BulkComposeEmailModal from '@/Components/BulkComposeEmailModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import type { Lead, PaginatedResponse } from '@/Types';

const props = defineProps<{
    leads: PaginatedResponse<Lead>;
    filters: {
        search?: string;
        status?: string;
        source?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const source = ref(props.filters.source ?? '');
const sortField = ref(props.filters.sort_field ?? '');
const sortDirection = ref(props.filters.sort_direction ?? '');
const selectedIds = ref<string[]>([]);

const bulkStatus = ref('');
const showBulkEmail = ref(false);

const hasActiveFilters = computed(() => {
    return !!(search.value || status.value || source.value);
});

const filterSummary = computed(() => {
    const parts: string[] = [];
    if (search.value) parts.push(`matching "${search.value}"`);
    if (status.value) {
        const labels: Record<string, string> = {
            new: 'New', qualified: 'Qualified', contacted: 'Contacted',
            interested: 'Interested', meeting_scheduled: 'Meeting Scheduled',
            proposal_sent: 'Proposal Sent', won: 'Won', lost: 'Lost',
        };
        parts.push(labels[status.value] ?? status.value);
    }
    if (source.value) parts.push(`from ${source.value}`);
    return parts.join(', ');
});

const allSelected = computed(() => {
    if (props.leads.data.length === 0) return false;
    return props.leads.data.every(l => selectedIds.value.includes(l.id));
});

const someSelected = computed(() => {
    return props.leads.data.some(l => selectedIds.value.includes(l.id)) && !allSelected.value;
});

watch(search, () => applyFilters());

function toggleSort(field: string): void {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFilters();
}

function sortIndicator(field: string): string {
    if (sortField.value !== field) return '';
    return sortDirection.value === 'asc' ? '▲' : '▼';
}

function applyFilters(): void {
    selectedIds.value = [];
    router.get('/leads', {
        search: search.value,
        status: status.value,
        source: source.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters(): void {
    search.value = '';
    status.value = '';
    source.value = '';
    sortField.value = '';
    sortDirection.value = '';
    selectedIds.value = [];
    router.get('/leads', {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function toggleSelectAll(): void {
    if (allSelected.value) {
        selectedIds.value = selectedIds.value.filter(id => !props.leads.data.some(l => l.id === id));
    } else {
        const pageIds = props.leads.data.map(l => l.id);
        selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
    }
}

function toggleSelect(id: string): void {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
}

function statusLabel(s: string): string {
    const labels: Record<string, string> = {
        new: 'New',
        qualified: 'Qualified',
        contacted: 'Contacted',
        interested: 'Interested',
        meeting_scheduled: 'Meeting Scheduled',
        proposal_sent: 'Proposal Sent',
        won: 'Won',
        lost: 'Lost',
    };
    return labels[s] ?? s;
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

function destroyLead(lead: Lead): void {
    if (confirm(`Delete "${lead.company_name}"? This cannot be undone.`)) {
        router.delete(`/leads/${lead.id}`);
    }
}

function bulkDelete(): void {
    if (!confirm(`Delete ${selectedIds.value.length} lead(s)? This cannot be undone.`)) return;
    router.post(route('leads.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}

function bulkUpdateStatus(): void {
    if (!bulkStatus.value) return;
    router.post(route('leads.bulk.status'), { ids: selectedIds.value, status: bulkStatus.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; bulkStatus.value = ''; },
    });
}

function exportSelected(): void {
    const params = new URLSearchParams();
    selectedIds.value.forEach(id => params.append('ids[]', id));
    window.location.href = `/leads/export?${params.toString()}`;
}

async function deleteAllMatching(): Promise<void> {
    const total = props.leads.total;
    if (!confirm(`Delete all ${total} ${filterSummary.value ? filterSummary.value + ' ' : ''}lead(s)? This cannot be undone.`)) return;
    try {
        await axios.post(route('leads.bulk.delete-by-filters'), {
            search: search.value,
            status: status.value,
            source: source.value,
        });
        applyFilters();
    } catch {
        alert('Failed to delete leads. Please try again.');
    }
}

function exportLeads(): void {
    const params = new URLSearchParams();
    if (search.value) params.set('search', search.value);
    if (status.value) params.set('status', status.value);
    if (source.value) params.set('source', source.value);
    window.location.href = `/leads/export?${params.toString()}`;
}
</script>

<template>
    <Head title="Leads" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Leads">
                <template #actions>
                    <Link
                        href="/leads/export?search=&status=&source="
                        class="mr-2 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        @click.prevent="exportLeads"
                    >
                        Export
                    </Link>
                    <Link
                        href="/leads/import"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Import
                    </Link>
                    <Link
                        href="/leads/create"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                    >
                        New Lead
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <SearchInput v-model="search" />
                            <select
                                v-model="status"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="new">New</option>
                                <option value="qualified">Qualified</option>
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="meeting_scheduled">Meeting Scheduled</option>
                                <option value="proposal_sent">Proposal Sent</option>
                                <option value="won">Won</option>
                                <option value="lost">Lost</option>
                            </select>
                            <select
                                v-model="source"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">All Sources</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="upwork">Upwork</option>
                                <option value="freelancer">Freelancer</option>
                                <option value="fiverr">Fiverr</option>
                                <option value="referral">Referral</option>
                                <option value="cold_email">Cold Email</option>
                                <option value="website">Website</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div
                            v-if="hasActiveFilters"
                            class="mb-4 flex items-center gap-3 rounded-md bg-yellow-50 px-4 py-3"
                        >
                            <span class="text-sm text-yellow-700">
                                Showing <strong>{{ leads.total }}</strong> {{ filterSummary }} lead(s)
                            </span>
                            <button
                                type="button"
                                class="ml-auto rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500"
                                @click="deleteAllMatching"
                            >
                                Delete All
                            </button>
                        </div>

                        <div
                            v-if="selectedIds.length > 0"
                            class="mb-4 flex items-center gap-3 rounded-md bg-indigo-50 px-4 py-3"
                        >
                            <span class="text-sm font-medium text-indigo-700">
                                {{ selectedIds.length }} selected
                            </span>
                            <button
                                type="button"
                                class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500"
                                @click="bulkDelete"
                            >
                                Delete Selected
                            </button>
                            <select
                                v-model="bulkStatus"
                                class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Change Status…</option>
                                <option value="new">New</option>
                                <option value="qualified">Qualified</option>
                                <option value="contacted">Contacted</option>
                                <option value="interested">Interested</option>
                                <option value="meeting_scheduled">Meeting Scheduled</option>
                                <option value="proposal_sent">Proposal Sent</option>
                                <option value="won">Won</option>
                                <option value="lost">Lost</option>
                            </select>
                            <button
                                type="button"
                                :disabled="!bulkStatus"
                                class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                                @click="bulkUpdateStatus"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                @click="exportSelected"
                            >
                                Export Selected
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500"
                                @click="showBulkEmail = true"
                            >
                                Email
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-3">
                                            <input
                                                type="checkbox"
                                                :checked="allSelected"
                                                :indeterminate="someSelected"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('company_name')">
                                            Company <span v-if="sortIndicator('company_name')" class="ml-1">{{ sortIndicator('company_name') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('contact_name')">
                                            Contact <span v-if="sortIndicator('contact_name')" class="ml-1">{{ sortIndicator('contact_name') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('email')">
                                            Email <span v-if="sortIndicator('email')" class="ml-1">{{ sortIndicator('email') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('website')">
                                            Website <span v-if="sortIndicator('website')" class="ml-1">{{ sortIndicator('website') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('industry')">
                                            Industry <span v-if="sortIndicator('industry')" class="ml-1">{{ sortIndicator('industry') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('country')">
                                            Country <span v-if="sortIndicator('country')" class="ml-1">{{ sortIndicator('country') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('last_contacted_at')">
                                            Last Contacted <span v-if="sortIndicator('last_contacted_at')" class="ml-1">{{ sortIndicator('last_contacted_at') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('status')">
                                            Status <span v-if="sortIndicator('status')" class="ml-1">{{ sortIndicator('status') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('source')">
                                            Source <span v-if="sortIndicator('source')" class="ml-1">{{ sortIndicator('source') }}</span>
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr
                                        v-for="lead in leads.data"
                                        :key="lead.id"
                                        class="hover:bg-gray-50"
                                        :class="{ 'bg-indigo-50': selectedIds.includes(lead.id) }"
                                    >
                                        <td class="px-3 py-4" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="selectedIds.includes(lead.id)"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                @change="toggleSelect(lead.id)"
                                            />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                            <Link :href="`/leads/${lead.id}`" class="text-indigo-600 hover:text-indigo-900">
                                                {{ lead.company_name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.contact_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.email }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <a v-if="lead.website" :href="lead.website" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-900">
                                                {{ lead.website }}
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.industry }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.country }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.last_contacted_at ? new Date(lead.last_contacted_at).toLocaleDateString() : '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="lead.status">
                                                {{ statusLabel(lead.status) }}
                                            </StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ lead.source }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/leads/${lead.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="destroyLead(lead)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="leads.data.length === 0">
                                        <td colspan="11" class="px-3 py-8 text-center text-sm text-gray-500">
                                            No leads found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="leads.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing {{ leads.from }} to {{ leads.to }} of {{ leads.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="leads.current_page > 1"
                                    :href="pageUrl(`/leads?page=${leads.current_page - 1}`)"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="leads.current_page < leads.last_page"
                                    :href="pageUrl(`/leads?page=${leads.current_page + 1}`)"
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

    <BulkComposeEmailModal
        :show="showBulkEmail"
        :lead-ids="selectedIds"
        :count="selectedIds.length"
        @close="showBulkEmail = false"
        @sent="showBulkEmail = false; selectedIds = []"
    />
</template>
