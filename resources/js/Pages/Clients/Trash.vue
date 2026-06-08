<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SearchInput from '@/Components/SearchInput.vue';
import DataTable from '@/Components/DataTable.vue';
import FilterBar from '@/Components/FilterBar.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Client, PaginatedResponse } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    clients: PaginatedResponse<Client>;
    filters: {
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>();

const search = ref(props.filters.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

function applyFilters(): void {
    router.get('/clients/trash', {
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function restore(client: Client): void {
    router.post(route('clients.restore', client.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`"${client.company_name}" restored.`);
        },
    });
}

async function forceDestroy(client: Client): Promise<void> {
    if (!await confirm({ title: 'Permanently delete?', message: `Permanently delete "${client.company_name}"? This cannot be undone.`, confirmLabel: 'Delete Forever' })) return;
    router.delete(route('clients.force-destroy', client.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Client permanently deleted.');
        },
    });
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}

const columns = [
    { key: 'company_name', label: 'Company' },
    { key: 'contact_name', label: 'Contact' },
    { key: 'deleted_at', label: 'Deleted At' },
];
</script>

<template>
    <Head title="Trash — Clients" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Trash — Clients">
                <template #actions>
                    <Link
                        href="/clients"
                        class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    >
                        Back to Clients
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <FilterBar :columns="1">
                            <SearchInput v-model="search" />
                        </FilterBar>

                        <DataTable
                            :items="clients.data"
                            :columns="columns"
                            empty-icon="🗑️"
                            empty-title="Trash is empty"
                            empty-message="Deleted clients will appear here."
                            empty-action-label="Back to Clients"
                            empty-action-href="/clients"
                        >
                            <template #cell-company_name="{ item }">
                                <span class="font-medium text-gray-800">{{ item.company_name }}</span>
                            </template>
                            <template #cell-contact_name="{ item }">
                                <span class="text-gray-600">{{ item.contact_name }}</span>
                            </template>
                            <template #cell-deleted_at="{ item }">
                                <span class="text-gray-500">{{ item.deleted_at }}</span>
                            </template>
                            <template #actions="{ item }">
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                        @click="restore(item)"
                                    >
                                        Restore
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                        @click="forceDestroy(item)"
                                    >
                                        Delete Forever
                                    </button>
                                </div>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ item.company_name }}
                                        </span>
                                        <p class="mt-0.5 text-sm text-gray-600 truncate">{{ item.contact_name }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-1 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span>Deleted: {{ item.deleted_at }}</span>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="clients.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} results
                            </div>
                            <div class="flex gap-1">
                                <Link
                                    v-for="page in clients.last_page"
                                    :key="page"
                                    :href="pageUrl(`/clients/trash?page=${page}&search=${search}`)"
                                    class="rounded-md px-3 py-1 text-sm"
                                    :class="page === clients.current_page ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                >
                                    {{ page }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
