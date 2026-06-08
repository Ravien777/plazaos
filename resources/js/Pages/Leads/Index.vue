<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import SearchInput from '@/Components/SearchInput.vue';
import DataTable from '@/Components/DataTable.vue';
import FilterBar from '@/Components/FilterBar.vue';
import BulkComposeEmailModal from '@/Components/BulkComposeEmailModal.vue';
import BulkActionBar from '@/Components/BulkActionBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Lead, PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

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

const showBulkEmail = ref(false);

const leadStatusOptions = [
    { value: 'new', label: 'New' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'interested', label: 'Interested' },
    { value: 'meeting_scheduled', label: 'Meeting Scheduled' },
    { value: 'proposal_sent', label: 'Proposal Sent' },
    { value: 'won', label: 'Won' },
    { value: 'lost', label: 'Lost' },
];

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
    const allSelected = props.leads.data.length > 0 && props.leads.data.every(l => selectedIds.value.includes(l.id));
    if (allSelected) {
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

const columns = [
    { key: 'company_name', label: 'Company', sortable: true },
    { key: 'contact_name', label: 'Contact', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'website', label: 'Website', sortable: true },
    { key: 'industry', label: 'Industry', sortable: true },
    { key: 'country', label: 'Country', sortable: true },
    { key: 'last_contacted_at', label: 'Last Contacted', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'source', label: 'Source', sortable: true },
];

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
    router.delete(route('leads.destroy', lead.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${lead.company_name}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('leads.restore', lead.id)),
            });
        },
    });
}

function bulkArchive(): void {
    const count = selectedIds.value.length;
    router.post(route('leads.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} lead(s) archived.`);
        },
    });
}

function bulkForceDelete(): void {
    const count = selectedIds.value.length;
    router.post(route('leads.bulk.force-delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} lead(s) permanently deleted.`);
        },
    });
}

function bulkUpdateStatus(status: string): void {
    router.post(route('leads.bulk.status'), { ids: selectedIds.value, status }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}

function exportSelected(): void {
    const params = new URLSearchParams();
    selectedIds.value.forEach(id => params.append('ids[]', id));
    window.location.href = `/leads/export?${params.toString()}`;
}

async function deleteAllMatching(): Promise<void> {
    const total = props.leads.total;
    if (!await confirm({ title: 'Delete all?', message: `Delete all ${total} ${filterSummary.value ? filterSummary.value + ' ' : ''}lead(s)? This cannot be undone.`, confirmLabel: 'Delete All' })) return;
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
                        v-if="$page.props.auth.user.role === 'owner' || !$page.props.auth.user.team_id"
                        href="/leads/trash"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Trash
                    </Link>
                    <Link
                        href="/leads/export?search=&status=&source="
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                        @click.prevent="exportLeads"
                    >
                        Export
                    </Link>
                    <Link
                        href="/leads/import"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Import
                    </Link>
                    <Link
                        href="/leads/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Lead
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <FilterBar :columns="3">
                            <SearchInput v-model="search" />
                            <select
                                v-model="status"
                                class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm min-h-[44px]"
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
                                class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm min-h-[44px]"
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
                        </FilterBar>

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
                            <span class="text-sm font-medium text-indigo-600">
                                {{ selectedIds.length }} selected
                            </span>
                            <button
                                type="button"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                @click="exportSelected"
                            >
                                Export Selected
                            </button>
                            <button
                                type="button"
                                class="rounded-md bg-indigo-500 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-400"
                                @click="showBulkEmail = true"
                            >
                                Email
                            </button>
                        </div>

                        <DataTable
                            :items="leads.data"
                            :columns="columns"
                            :sort-field="sortField"
                            :sort-direction="(sortDirection as 'asc' | 'desc')"
                            selectable
                            :selected-ids="selectedIds"
                            empty-icon="🎯"
                            empty-title="No leads yet"
                            empty-message="Create your first lead to start tracking potential clients."
                            empty-action-label="New Lead"
                            empty-action-href="/leads/create"
                            @sort="toggleSort"
                            @toggle-select="toggleSelect"
                            @toggle-select-all="toggleSelectAll"
                        >
                            <template #cell-company_name="{ item }">
                                <Link :href="`/leads/${item.id}`" class="text-indigo-500 hover:text-indigo-600 font-medium">
                                    {{ item.company_name }}
                                </Link>
                            </template>
                            <template #cell-website="{ item }">
                                <a v-if="item.website" :href="item.website" target="_blank" rel="noopener noreferrer" class="text-indigo-500 hover:text-indigo-600">
                                    {{ item.website }}
                                </a>
                            </template>
                            <template #cell-last_contacted_at="{ item }">
                                {{ item.last_contacted_at ? new Date(item.last_contacted_at).toLocaleDateString() : '—' }}
                            </template>
                            <template #cell-status="{ item }">
                                <StatusBadge :status="item.status">
                                    {{ statusLabel(item.status) }}
                                </StatusBadge>
                            </template>
                            <template #actions="{ item }">
                                <Link
                                    :href="`/leads/${item.id}/edit`"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                    @click="destroyLead(item)"
                                >
                                    Delete
                                </button>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/leads/${item.id}`" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 truncate block">
                                            {{ item.company_name }}
                                        </Link>
                                        <p class="mt-0.5 text-sm text-gray-600 truncate">{{ item.contact_name }}</p>
                                    </div>
                                    <StatusBadge :status="item.status" class="shrink-0 ml-2">
                                        {{ statusLabel(item.status) }}
                                    </StatusBadge>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                        <span class="truncate">{{ item.email || '—' }}</span>
                                    </div>
                                    <div v-if="item.source" class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                                        <span>{{ item.source }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>{{ item.last_contacted_at ? new Date(item.last_contacted_at).toLocaleDateString() : 'Not contacted' }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <BulkActionBar
                            :selected-count="selectedIds.length"
                            :show="selectedIds.length > 0"
                            :status-options="leadStatusOptions"
                            @archive="bulkArchive"
                            @force-delete="bulkForceDelete"
                            @update-status="bulkUpdateStatus"
                        />

                        <div v-if="leads.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
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
