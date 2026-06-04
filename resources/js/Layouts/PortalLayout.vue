<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingNav = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-100 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <Link :href="route('portal.dashboard')" class="text-lg font-bold text-gray-700">
                                Client Portal
                            </Link>
                            <div class="hidden sm:ms-10 sm:flex sm:space-x-8">
                                <Link :href="route('portal.dashboard')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.dashboard') }">
                                    Dashboard
                                </Link>
                                <Link :href="route('portal.projects.index')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.projects*') }">
                                    Projects
                                </Link>
                                <Link :href="route('portal.documents.index')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.documents*') }">
                                    Documents
                                </Link>
                                <Link :href="route('portal.meetings.index')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.meetings*') }">
                                    Meetings
                                </Link>
                                <Link :href="route('portal.tickets.index')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.tickets*') }">
                                    Tickets
                                </Link>
                                <Link :href="route('portal.intake-forms.index')" class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:text-gray-700" :class="{ 'border-indigo-400 text-gray-800': route().current('portal.intake-forms*') }">
                                    Intake Forms
                                </Link>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">{{ $page.props.auth.client_user?.client?.company_name }}</span>
                            <Link :href="route('portal.logout')" method="post" as="button" class="text-sm text-gray-600 underline hover:text-gray-700">
                                Logout
                            </Link>
                            <button @click="showingNav = !showingNav" class="sm:hidden">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="showingNav" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <Link :href="route('portal.dashboard')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Dashboard</Link>
                        <Link :href="route('portal.projects.index')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Projects</Link>
                        <Link :href="route('portal.documents.index')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Documents</Link>
                        <Link :href="route('portal.meetings.index')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Meetings</Link>
                        <Link :href="route('portal.tickets.index')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Tickets</Link>
                        <Link :href="route('portal.intake-forms.index')" class="block border-l-4 border-transparent px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-gray-50 hover:text-gray-700">Intake Forms</Link>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
