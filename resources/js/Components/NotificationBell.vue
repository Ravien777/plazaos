<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import type { NotificationData } from '@/Types';

const unreadCount = ref(0);
const notifications = ref<NotificationData[]>([]);
const showDropdown = ref(false);
let interval: ReturnType<typeof setInterval> | null = null;

async function fetchUnreadCount(): Promise<void> {
    try {
        const res = await axios.get('/notifications/unread-count');
        unreadCount.value = res.data.count ?? 0;
    } catch {
        // ignore
    }
}

async function fetchRecent(): Promise<void> {
    try {
        const res = await axios.get('/notifications/recent');
        notifications.value = res.data.data ?? [];
    } catch {
        // ignore
    }
}

function toggleDropdown(): void {
    showDropdown.value = !showDropdown.value;
    if (showDropdown.value) {
        fetchRecent();
    }
}

async function markAsRead(id: string): Promise<void> {
    try {
        await axios.post(`/notifications/${id}/read`);
        notifications.value = notifications.value.filter((n) => n.id !== id);
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch {
        // ignore
    }
}

async function markAllAsRead(): Promise<void> {
    try {
        await axios.post('/notifications/read-all');
        notifications.value = [];
        unreadCount.value = 0;
    } catch {
        // ignore
    }
}

function closeOnClickOutside(event: MouseEvent): void {
    const target = event.target as HTMLElement;
    if (!target.closest('.notification-bell')) {
        showDropdown.value = false;
    }
}

function formatTime(iso: string): string {
    const d = new Date(iso);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffMin = Math.floor(diffMs / 60000);

    if (diffMin < 1) return 'just now';
    if (diffMin < 60) return `${diffMin}m ago`;

    const diffHrs = Math.floor(diffMin / 60);
    if (diffHrs < 24) return `${diffHrs}h ago`;

    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

onMounted(() => {
    fetchUnreadCount();
    interval = setInterval(fetchUnreadCount, 30000);
    document.addEventListener('click', closeOnClickOutside);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
    document.removeEventListener('click', closeOnClickOutside);
});
</script>

<template>
    <div class="notification-bell relative">
        <button
            class="relative rounded-md p-1 text-gray-600 hover:text-gray-700 focus:outline-none"
            @click="toggleDropdown"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <div
            v-if="showDropdown"
            class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg"
        >
            <div class="border-b border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-800">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        class="text-xs text-indigo-500 hover:text-indigo-600"
                        @click="markAllAsRead"
                    >
                        Mark all read
                    </button>
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-600">
                    No new notifications.
                </div>

                <div
                    v-for="n in notifications"
                    :key="n.id"
                    class="border-b border-gray-100 px-4 py-3 transition hover:bg-gray-50"
                >
                    <div class="flex items-start justify-between">
                        <Link
                            :href="n.data.link"
                            class="flex-1"
                            @click="showDropdown = false"
                        >
                            <p class="text-sm font-medium text-gray-800">{{ n.data.title }}</p>
                            <p class="mt-0.5 text-xs text-gray-600">{{ n.data.message }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ formatTime(n.created_at) }}</p>
                        </Link>
                        <button
                            class="ml-2 text-xs text-indigo-500 hover:text-indigo-600"
                            @click.stop="markAsRead(n.id)"
                        >
                            Read
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 px-4 py-2">
                <Link
                    href="/notifications"
                    class="block text-center text-xs text-indigo-500 hover:text-indigo-600"
                    @click="showDropdown = false"
                >
                    View all notifications
                </Link>
            </div>
        </div>
    </div>
</template>
