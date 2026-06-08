<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchInput from '@/Components/SearchInput.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import DataTable from '@/Components/DataTable.vue';
import FilterBar from '@/Components/FilterBar.vue';
import BulkActionBar from '@/Components/BulkActionBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import type { Client, PaginatedResponse } from '@/Types';

const toast = useToast();

const props = defineProps<{
    clients: PaginatedResponse<Client>;
    filters: {
        search?: string;
        status?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const sortField = ref(props.filters.sort_field ?? '');
const sortDirection = ref(props.filters.sort_direction ?? '');
const selectedIds = ref<string[]>([]);

const clientStatusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'archived', label: 'Archived' },
];

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

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
    router.get('/clients', {
        search: search.value,
        status: status.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function goToPage(page: number): void {
    selectedIds.value = [];
    router.get('/clients', {
        page,
        search: search.value,
        status: status.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function toggleSelectAll(): void {
    const all = props.clients.data.length > 0 && props.clients.data.every(c => selectedIds.value.includes(c.id));
    if (all) {
        selectedIds.value = selectedIds.value.filter(id => !props.clients.data.some(c => c.id === id));
    } else {
        const pageIds = props.clients.data.map(c => c.id);
        selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
    }
}

const columns = [
    { key: 'company_name', label: 'Company', sortable: true },
    { key: 'contact_name', label: 'Contact', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'industry', label: 'Industry', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
];

function toggleSelect(id: string): void {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
}

function destroyClient(client: Client): void {
    router.delete(`/clients/${client.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${client.company_name}" deleted.`, {
                label: 'Undo',
                handler: () => router.post(route('clients.restore', client.id)),
            });
        },
    });
}

function bulkArchive(): void {
    const count = selectedIds.value.length;
    router.post(route('clients.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} client(s) archived.`);
        },
    });
}

function bulkForceDelete(): void {
    const count = selectedIds.value.length;
    router.post(route('clients.bulk.force-delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success(`${count} client(s) permanently deleted.`);
        },
    });
}

function bulkUpdateStatus(status: string): void {
    router.post(route('clients.bulk.status'), { ids: selectedIds.value, status }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}

function exportSelected(): void {
    const params = new URLSearchParams();
    selectedIds.value.forEach(id => params.append('ids[]', id));
    window.location.href = `/clients/export?${params.toString()}`;
}
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Clients">
                <template #actions>
                    <Link
                        v-if="$page.props.auth.user.role === 'owner' || !$page.props.auth.user.team_id"
                        href="/clients/trash"
                        class="mr-2 inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Trash
                    </Link>
                    <Link
                        href="/clients/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Client
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <FilterBar :columns="2">
                            <SearchInput v-model="search" />
                            <select
                                v-model="status"
                                class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm min-h-[44px]"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </FilterBar>

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
                        </div>

                        <DataTable
                            :items="clients.data"
                            :columns="columns"
                            :sort-field="sortField"
                            :sort-direction="(sortDirection as 'asc' | 'desc')"
                            selectable
                            :selected-ids="selectedIds"
                            empty-icon="🤝"
                            empty-title="No clients yet"
                            empty-message="Convert a lead or add a client manually."
                            empty-action-label="New Client"
                            empty-action-href="/clients/create"
                            @sort="toggleSort"
                            @toggle-select="toggleSelect"
                            @toggle-select-all="toggleSelectAll"
                        >
                            <template #cell-company_name="{ item }">
                                <Link :href="`/clients/${item.id}`" class="text-indigo-500 hover:text-indigo-600 font-medium">
                                    {{ item.company_name }}
                                </Link>
                            </template>
                            <template #cell-status="{ item }">
                                <StatusBadge :status="item.status">
                                    {{ item.status }}
                                </StatusBadge>
                            </template>
                            <template #actions="{ item }">
                                <Link
                                    :href="`/clients/${item.id}/edit`"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                    @click="destroyClient(item)"
                                >
                                    Delete
                                </button>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <Link :href="`/clients/${item.id}`" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 truncate block">
                                            {{ item.company_name }}
                                        </Link>
                                        <p class="mt-0.5 text-sm text-gray-600 truncate">{{ item.contact_name }}</p>
                                    </div>
                                    <StatusBadge :status="item.status" class="shrink-0 ml-2">
                                        {{ item.status }}
                                    </StatusBadge>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                        <span class="truncate">{{ item.email || '—' }}</span>
                                    </div>
                                    <div v-if="item.industry" class="flex items-center gap-2">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>{{ item.industry }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <BulkActionBar
                            :selected-count="selectedIds.length"
                            :show="selectedIds.length > 0"
                            :status-options="clientStatusOptions"
                            @archive="bulkArchive"
                            @force-delete="bulkForceDelete"
                            @update-status="bulkUpdateStatus"
                        />

                        <div v-if="clients.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} results
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-if="clients.current_page > 1"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                    @click="goToPage(clients.current_page - 1)"
                                >
                                    Previous
                                </button>
                                <button
                                    v-if="clients.current_page < clients.last_page"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                    @click="goToPage(clients.current_page + 1)"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
