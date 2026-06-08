<script setup lang="ts">
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import type { SearchResult, SearchResults } from '@/Types';

const open = ref(false);
const query = ref('');
const results = ref<SearchResults>({
    leads: [],
    clients: [],
    projects: [],
    meetings: [],
    tickets: [],
    tasks: [],
});
const loading = ref(false);
const selectedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const groups: { key: keyof SearchResults; label: string }[] = [
    { key: 'leads', label: 'Leads' },
    { key: 'clients', label: 'Clients' },
    { key: 'projects', label: 'Projects' },
    { key: 'meetings', label: 'Meetings' },
    { key: 'tickets', label: 'Tickets' },
    { key: 'tasks', label: 'Tasks' },
];

const flattenedResults = computed<SearchResult[]>(() => {
    const all: SearchResult[] = [];
    for (const group of groups) {
        for (const item of results.value[group.key]) {
            all.push(item);
        }
    }
    return all;
});

function openModal(): void {
    open.value = true;
    query.value = '';
    results.value = { leads: [], clients: [], projects: [], meetings: [], tickets: [], tasks: [] };
    selectedIndex.value = -1;
    nextTick(() => inputRef.value?.focus());
}

function close(): void {
    open.value = false;
}

function handleKeydown(e: KeyboardEvent): void {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        const target = e.target as HTMLElement;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable) {
            return;
        }
        openModal();
    }
}

function onModalKeydown(e: KeyboardEvent): void {
    const items = flattenedResults.value;
    if (items.length === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        visit(items[selectedIndex.value]);
    }
}

function visit(result: SearchResult): void {
    close();
    router.visit(result.url);
}

defineExpose({ openModal });

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    if (debounceTimer) clearTimeout(debounceTimer);
});

watch(selectedIndex, () => {
    nextTick(() => {
        const el = document.querySelector('[data-search-selected="true"]');
        el?.scrollIntoView({ block: 'nearest' });
    });
});

watch(query, (val) => {
    if (debounceTimer) clearTimeout(debounceTimer);

    if (val.length < 2) {
        results.value = { leads: [], clients: [], projects: [], meetings: [], tickets: [], tasks: [] };
        selectedIndex.value = -1;
        return;
    }

    loading.value = true;

    debounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/api/search?q=${encodeURIComponent(val)}`);
            const json = await res.json();
            results.value = json.data ?? { leads: [], clients: [], projects: [], meetings: [], tickets: [], tasks: [] };
            selectedIndex.value = 0;
        } catch {
            results.value = { leads: [], clients: [], projects: [], meetings: [], tickets: [], tasks: [] };
        } finally {
            loading.value = false;
        }
    }, 300);
});
</script>

<template>
    <Modal :show="open" max-width="lg" @close="close">
        <div class="p-0" @keydown="onModalKeydown">
            <div class="flex items-center border-b border-gray-200 px-4">
                <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    ref="inputRef"
                    v-model="query"
                    type="text"
                    placeholder="Search leads, clients, projects..."
                    class="w-full border-0 bg-transparent px-3 py-4 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0"
                />
                <kbd class="hidden shrink-0 rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs text-gray-400 sm:inline-block">ESC</kbd>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <div v-if="loading" class="px-4 py-8 text-center text-sm text-gray-500">
                    Searching...
                </div>

                <div v-else-if="query.length >= 2 && flattenedResults.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                    No results found for "{{ query }}"
                </div>

                <template v-else>
                    <div v-for="group in groups" :key="group.key">
                        <div v-if="results[group.key].length > 0">
                            <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                {{ group.label }}
                            </div>
                            <div
                                v-for="(item, idx) in results[group.key]"
                                :key="item.id"
                                :data-search-selected="flattenedResults.indexOf(item) === selectedIndex || undefined"
                                class="flex cursor-pointer items-center gap-3 px-4 py-2.5 transition-colors"
                                :class="flattenedResults.indexOf(item) === selectedIndex ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                                @click="visit(item)"
                                @mouseenter="selectedIndex = flattenedResults.indexOf(item)"
                            >
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-500">
                                    <svg v-if="item.type === 'lead'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <svg v-else-if="item.type === 'client'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                    <svg v-else-if="item.type === 'project'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                    </svg>
                                    <svg v-else-if="item.type === 'meeting'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <svg v-else-if="item.type === 'ticket'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-6.75-1.5a2.25 2.25 0 01-2.25-2.25V7.5a2.25 2.25 0 012.25-2.25h9a2.25 2.25 0 012.25 2.25v6.75a2.25 2.25 0 01-2.25 2.25h-9zm0 0a2.25 2.25 0 01-1.5.577H4.5a2.25 2.25 0 01-2.25-2.25V9a2.25 2.25 0 012.25-2.25h.75A2.25 2.25 0 017.5 9v6.75a2.25 2.25 0 01-.75 1.667z" />
                                    </svg>
                                    <svg v-else-if="item.type === 'task'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-800">{{ item.title }}</p>
                                    <p v-if="item.subtitle" class="truncate text-xs text-gray-500">{{ item.subtitle }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </Modal>
</template>
