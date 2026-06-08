<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { DocumentRecord } from '@/Types';

defineProps<{
    documents: DocumentRecord[];
}>();
</script>

<template>
    <Head title="My Documents" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">My Documents</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="documents.length === 0" class="text-sm text-gray-600">No documents available.</div>
                        <div v-else class="space-y-3">
                            <div v-for="doc in documents" :key="doc.id" class="flex items-center justify-between rounded-md border border-gray-200 p-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ doc.name }}</p>
                                    <p class="text-xs text-gray-600">{{ (doc.size / 1024).toFixed(1) }} KB &middot; {{ doc.created_at }}</p>
                                </div>
                                <Link
                                    :href="`/portal/documents/${doc.id}/download`"
                                    class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600"
                                >
                                    Download
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
