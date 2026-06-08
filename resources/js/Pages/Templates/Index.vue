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
import type { EmailTemplate } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps<{
    templates: EmailTemplate[];
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
});

function applyFilters(): void {
    router.get('/templates', {
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

async function destroy(id: string): Promise<void> {
    if (!await confirm({ title: 'Delete template?', message: 'Delete this template?' })) return;
    router.delete(`/templates/${id}`, {
        onSuccess: () => toast.success('Template deleted.'),
    });
}

const columns = [
    { key: 'key', label: 'Key' },
    { key: 'name', label: 'Name' },
    { key: 'subject', label: 'Subject' },
];
</script>

<template>
    <Head title="Email Templates" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Email Templates">
                <template #actions>
                    <Link
                        :href="route('templates.create')"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Template
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
                            :items="templates"
                            :columns="columns"
                            empty-icon="📄"
                            empty-title="No templates yet"
                            empty-message="Create an email template to speed up your outreach."
                            empty-action-label="New Template"
                            empty-action-href="/templates/create"
                        >
                            <template #cell-key="{ item }">
                                <span class="font-mono text-gray-600">{{ item.key }}</span>
                            </template>
                            <template #cell-name="{ item }">
                                <span class="font-medium text-gray-800">{{ item.name }}</span>
                            </template>
                            <template #cell-subject="{ item }">
                                <span class="max-w-md truncate text-gray-700 block">{{ item.subject }}</span>
                            </template>
                            <template #actions="{ item }">
                                <Link
                                    :href="`/templates/${item.id}/edit`"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                    @click="destroy(item.id!)"
                                >
                                    Delete
                                </button>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 truncate block">
                                            {{ item.name }}
                                        </span>
                                        <p class="mt-0.5 text-xs font-mono text-gray-500 truncate">{{ item.key }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 text-sm text-gray-500">
                                    <span>{{ item.subject }}</span>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
