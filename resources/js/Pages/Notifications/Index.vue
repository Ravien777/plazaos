<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { NotificationData, PaginatedResponse } from '@/Types';

const props = defineProps<{
    notifications: PaginatedResponse<NotificationData>;
}>();

function markAsRead(id: string): void {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => router.reload(),
    });
}

function markAllAsRead(): void {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        onSuccess: () => router.reload(),
    });
}

function formatTime(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function pageUrl(url: string | null): string {
    return url ?? '#';
}
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Notifications">
                <template #actions>
                    <button
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="markAllAsRead"
                    >
                        Mark All Read
                    </button>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="notifications.data.length === 0" class="py-8 text-center text-sm text-gray-500">
                            No notifications.
                        </div>

                        <div v-else class="divide-y divide-gray-200">
                            <div
                                v-for="n in notifications.data"
                                :key="n.id"
                                class="flex items-start justify-between py-4"
                                :class="{ 'bg-indigo-50': !n.read_at }"
                            >
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span v-if="!n.read_at" class="h-2 w-2 rounded-full bg-indigo-500" />
                                        <Link
                                            :href="n.data.link"
                                            class="text-sm font-medium text-gray-900 hover:text-indigo-600"
                                        >
                                            {{ n.data.title }}
                                        </Link>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ n.data.message }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ formatTime(n.created_at) }}</p>
                                </div>
                                <button
                                    v-if="!n.read_at"
                                    class="ml-4 text-xs text-indigo-600 hover:text-indigo-900"
                                    @click="markAsRead(n.id)"
                                >
                                    Mark read
                                </button>
                            </div>
                        </div>

                        <div v-if="notifications.last_page > 1" class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing {{ notifications.from }} to {{ notifications.to }} of {{ notifications.total }} results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-if="notifications.current_page > 1"
                                    :href="pageUrl(`/notifications?page=${notifications.current_page - 1}`)"
                                    class="rounded-md border px-3 py-1 text-sm hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="notifications.current_page < notifications.last_page"
                                    :href="pageUrl(`/notifications?page=${notifications.current_page + 1}`)"
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
