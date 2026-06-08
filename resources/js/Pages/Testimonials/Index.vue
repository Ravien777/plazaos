<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DataTable from '@/Components/DataTable.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import type { Testimonial } from '@/Types';

const toast = useToast();
const { confirm } = useConfirm();

defineProps<{
    testimonials: {
        data: Testimonial[];
        current_page: number;
        last_page: number;
    };
}>();

function starString(rating: number): string {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
}

function copy(text: string): void {
    navigator.clipboard.writeText(text);
}

async function destroy(id: string): Promise<void> {
    if (!await confirm({ title: 'Remove testimonial?', message: 'Remove this testimonial?' })) return;
    router.delete(`/testimonials/${id}`, {
        onSuccess: () => toast.success('Testimonial removed.'),
    });
}

const columns = [
    { key: 'client_name', label: 'Client' },
    { key: 'rating', label: 'Rating' },
    { key: 'is_approved', label: 'Approved' },
    { key: 'created_at', label: 'Submitted' },
];
</script>

<template>
    <Head title="Testimonials" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Testimonials" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable
                            :items="testimonials.data"
                            :columns="columns"
                            empty-icon="⭐"
                            empty-title="No testimonials yet"
                            empty-message="Request reviews from your clients to build social proof."
                        >
                            <template #cell-client_name="{ item }">
                                <span class="font-medium text-gray-800">{{ item.client?.company_name ?? 'Unknown' }}</span>
                            </template>
                            <template #cell-rating="{ item }">
                                <span class="text-lg text-yellow-500">{{ starString(item.rating) }}</span>
                            </template>
                            <template #cell-is_approved="{ item }">
                                <span v-if="item.is_approved" class="text-green-600 text-sm">Yes</span>
                                <span v-else class="text-yellow-600 text-sm">Pending</span>
                            </template>
                            <template #cell-created_at="{ item }">
                                <span class="text-sm text-gray-500">{{ item.created_at }}</span>
                            </template>
                            <template #actions="{ item }">
                                <button
                                    v-if="item.content"
                                    type="button"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-indigo-500 hover:text-indigo-600"
                                    @click="copy(item.content)"
                                >
                                    Copy
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center min-h-[44px] min-w-[44px] text-sm text-red-600 hover:text-red-900"
                                    @click="destroy(item.id)"
                                >
                                    Delete
                                </button>
                            </template>
                            <template #card="{ item }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ item.client?.company_name ?? 'Unknown' }}</p>
                                        <p class="mt-0.5 text-lg text-yellow-500">{{ starString(item.rating) }}</p>
                                        <p v-if="item.content" class="mt-1 text-sm text-gray-700">{{ item.content }}</p>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ item.created_at }}</span>
                                            <span v-if="item.submitted_at">· Submitted</span>
                                            <span v-if="!item.is_approved" class="text-yellow-600">Pending</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </DataTable>

                        <div v-if="testimonials.last_page > 1" class="mt-6 flex justify-center gap-2">
                            <Link v-for="page in testimonials.last_page" :key="page" :href="`/testimonials?page=${page}`" class="rounded-md px-3 py-1 text-sm" :class="page === testimonials.current_page ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" as="button">
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
