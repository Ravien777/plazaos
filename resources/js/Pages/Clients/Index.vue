<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchInput from '@/Components/SearchInput.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import type { Client, PaginatedResponse } from '@/Types';

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
const bulkStatus = ref('');

const allSelected = computed(() => {
    if (props.clients.data.length === 0) return false;
    return props.clients.data.every(c => selectedIds.value.includes(c.id));
});

const someSelected = computed(() => {
    return props.clients.data.some(c => selectedIds.value.includes(c.id)) && !allSelected.value;
});

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

function sortIndicator(field: string): string {
    if (sortField.value !== field) return '';
    return sortDirection.value === 'asc' ? '▲' : '▼';
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
    if (allSelected.value) {
        selectedIds.value = selectedIds.value.filter(id => !props.clients.data.some(c => c.id === id));
    } else {
        const pageIds = props.clients.data.map(c => c.id);
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

function destroyClient(client: Client): void {
    if (confirm(`Delete "${client.company_name}"? This cannot be undone.`)) {
        router.delete(`/clients/${client.id}`);
    }
}

function bulkDelete(): void {
    if (!confirm(`Delete ${selectedIds.value.length} client(s)? This cannot be undone.`)) return;
    router.post(route('clients.bulk.delete'), { ids: selectedIds.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}

function bulkUpdateStatus(): void {
    if (!bulkStatus.value) return;
    router.post(route('clients.bulk.status'), { ids: selectedIds.value, status: bulkStatus.value }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; bulkStatus.value = ''; },
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
                        href="/clients/create"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Client
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <SearchInput v-model="search" />
                            <select
                                v-model="status"
                                class="rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                @change="applyFilters"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
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
                                class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-500"
                                @click="bulkDelete"
                            >
                                Delete Selected
                            </button>
                            <select
                                v-model="bulkStatus"
                                class="rounded-md border-gray-200 text-xs shadow-sm focus:border-indigo-400 focus:ring-indigo-400"
                            >
                                <option value="">Change Status…</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                            <button
                                type="button"
                                :disabled="!bulkStatus"
                                class="rounded-md bg-indigo-500 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                                @click="bulkUpdateStatus"
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                @click="exportSelected"
                            >
                                Export Selected
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-3">
                                            <input
                                                type="checkbox"
                                                :checked="allSelected"
                                                :indeterminate="someSelected"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 hover:text-gray-700" @click="toggleSort('company_name')">
                                            Company <span v-if="sortIndicator('company_name')" class="ml-1">{{ sortIndicator('company_name') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 hover:text-gray-700" @click="toggleSort('contact_name')">
                                            Contact <span v-if="sortIndicator('contact_name')" class="ml-1">{{ sortIndicator('contact_name') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 hover:text-gray-700" @click="toggleSort('email')">
                                            Email <span v-if="sortIndicator('email')" class="ml-1">{{ sortIndicator('email') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 hover:text-gray-700" @click="toggleSort('industry')">
                                            Industry <span v-if="sortIndicator('industry')" class="ml-1">{{ sortIndicator('industry') }}</span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600 hover:text-gray-700" @click="toggleSort('status')">
                                            Status <span v-if="sortIndicator('status')" class="ml-1">{{ sortIndicator('status') }}</span>
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr
                                        v-for="client in clients.data"
                                        :key="client.id"
                                        class="hover:bg-gray-50"
                                        :class="{ 'bg-indigo-50': selectedIds.includes(client.id) }"
                                    >
                                        <td class="px-3 py-4" @click.stop>
                                            <input
                                                type="checkbox"
                                                :checked="selectedIds.includes(client.id)"
                                                class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                                @change="toggleSelect(client.id)"
                                            />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-800">
                                            <Link :href="`/clients/${client.id}`" class="text-indigo-500 hover:text-indigo-600">
                                                {{ client.company_name }}
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ client.contact_name }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ client.email }}</td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">{{ client.industry }}</td>
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <StatusBadge :status="client.status">
                                                {{ client.status }}
                                            </StatusBadge>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/clients/${client.id}/edit`"
                                                    class="text-indigo-500 hover:text-indigo-600"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="destroyClient(client)"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="clients.data.length === 0">
                                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-600">
                                            No clients found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
