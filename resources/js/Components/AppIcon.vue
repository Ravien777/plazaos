<script setup lang="ts">
defineProps<{
    name: 'dashboard' | 'leads' | 'clients' | 'projects' | 'meetings' | 'tasks' | 'calendar' | 'tickets' | 'sources' | 'forms' | 'templates' | 'testimonials' | 'team' | 'settings' | 'chevron-left' | 'chevron-right' | 'hamburger' | 'x' | 'logout' | 'bell' | 'cog';
}>();
</script>

<template>
    <!-- Dashboard -->
    <svg v-if="name === 'dashboard'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M3 3h6v6H3V3zm8 0h6v6h-6V3zm0 8h6v6h-6v-6zM3 11h6v6H3v-6z" />
    </svg>

    <!-- Leads (inbox) -->
    <svg v-else-if="name === 'leads'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M19 13l-7 7-7-7m14-8v6H5V5" />
    </svg>

    <!-- Clients (users) -->
    <svg v-else-if="name === 'clients'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>

    <!-- Projects (folder) -->
    <svg v-else-if="name === 'projects'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M2 7a2 2 0 012-2h4l2 2h6a2 2 0 012 2v1H2V7zm0 4h16v5a2 2 0 01-2 2H4a2 2 0 01-2-2v-5z" />
    </svg>

    <!-- Meetings (calendar-check) -->
    <svg v-else-if="name === 'meetings'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm2-5l2 2 4-4" />
    </svg>

    <!-- Tasks (check-square) -->
    <svg v-else-if="name === 'tasks'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
    </svg>

    <!-- Calendar -->
    <svg v-else-if="name === 'calendar'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>

    <!-- Tickets (ticket) -->
    <svg v-else-if="name === 'tickets'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M15 5H9a4 4 0 00-4 4v6a4 4 0 004 4h6a4 4 0 004-4V9a4 4 0 00-4-4zm-3 4v8m-3-4h6" />
    </svg>

    <!-- Sources (globe) -->
    <svg v-else-if="name === 'sources'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15.3 15.3 0 010 18 15.3 15.3 0 010-18z" />
    </svg>

    <!-- Forms (file-text) -->
    <svg v-else-if="name === 'forms'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>

    <!-- Templates (file-copy) -->
    <svg v-else-if="name === 'templates'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
    </svg>

    <!-- Testimonials (star) -->
    <svg v-else-if="name === 'testimonials'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.2 6.774 7.075.216c.96.03 1.352 1.228.654 1.866l-5.377 5.005 1.657 6.826c.262 1.076-.899 1.95-1.825 1.36L12 19.647l-6.335 3.327c-.926.59-2.087-.284-1.825-1.36l1.657-6.826-5.377-5.005c-.698-.638-.306-1.836.654-1.866l7.075-.216 2.2-6.774z" />
    </svg>

    <!-- Team (users) -->
    <svg v-else-if="name === 'team'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>

    <!-- Settings (cog) -->
    <svg v-else-if="name === 'settings'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>

    <!-- Bell (notifications) -->
    <svg v-else-if="name === 'bell'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>

    <!-- Cog (settings gear alternative) -->
    <svg v-else-if="name === 'cog'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>

    <!-- Chevron Left -->
    <svg v-else-if="name === 'chevron-left'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M15 19l-7-7 7-7" />
    </svg>

    <!-- Chevron Right -->
    <svg v-else-if="name === 'chevron-right'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M9 5l7 7-7 7" />
    </svg>

    <!-- Hamburger -->
    <svg v-else-if="name === 'hamburger'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path d="M4 6h16M4 12h16M4 18h16" />
    </svg>

    <!-- X -->
    <svg v-else-if="name === 'x'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path d="M6 18L18 6M6 6l12 12" />
    </svg>

    <!-- Logout -->
    <svg v-else-if="name === 'logout'" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
    </svg>
</template>
