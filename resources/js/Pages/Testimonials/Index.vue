<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, router } from '@inertiajs/vue3';
import type { Testimonial } from '@/Types';

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

function destroy(id: string): void {
    if (confirm('Remove this testimonial?')) {
        router.delete(`/testimonials/${id}`);
    }
}
</script>

<template>
    <Head title="Testimonials" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Testimonials" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="testimonials.data.length === 0" class="text-sm text-gray-600">No testimonials yet. Request one from a client or project.</div>
                        <div v-else class="space-y-4">
                            <div v-for="t in testimonials.data" :key="t.id" class="rounded-md border border-gray-200 p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ t.client?.company_name ?? 'Unknown' }}</p>
                                        <p class="mt-0.5 text-lg text-yellow-500">{{ starString(t.rating) }}</p>
                                        <p v-if="t.content" class="mt-1 text-sm text-gray-700">{{ t.content }}</p>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-gray-500">
                                            <span>{{ t.created_at }}</span>
                                            <span v-if="t.submitted_at">· Submitted</span>
                                            <span v-if="!t.is_approved" class="text-yellow-600">Pending</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button v-if="t.content" @click="copy(t.content)" class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-600 hover:bg-gray-200">Copy</button>
                                        <button @click="destroy(t.id)" class="rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
