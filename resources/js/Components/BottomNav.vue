<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/Components/AppIcon.vue';

const links = [
    { href: route('dashboard'), label: 'Dashboard', icon: 'dashboard' as const, active: () => route().current('dashboard') },
    { href: route('leads.index'), label: 'Leads', icon: 'leads' as const, active: () => route().current('leads*') },
    { href: route('clients.index'), label: 'Clients', icon: 'clients' as const, active: () => route().current('clients*') },
    { href: route('projects.index'), label: 'Projects', icon: 'projects' as const, active: () => route().current('projects*') },
    { href: route('meetings.upcoming'), label: 'Meetings', icon: 'meetings' as const, active: () => route().current('meetings*') },
    { href: route('tickets.index'), label: 'Tickets', icon: 'tickets' as const, active: () => route().current('tickets*') },
];
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white flex sm:hidden">
        <Link
            v-for="link in links"
            :key="link.href"
            :href="link.href"
            class="flex flex-1 flex-col items-center justify-center gap-0.5 py-2 text-xs font-medium transition-colors min-h-[56px]"
            :class="link.active() ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'"
        >
            <AppIcon :name="link.icon" />
            <span>{{ link.label }}</span>
        </Link>
    </nav>
</template>
