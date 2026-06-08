<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
import AppIcon from '@/Components/AppIcon.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import InitialsAvatar from '@/Components/InitialsAvatar.vue';
import ToastProvider from '@/Components/ToastProvider.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import GlobalSearchModal from '@/Components/GlobalSearchModal.vue';
import BottomNav from '@/Components/BottomNav.vue';

const searchModalRef = ref<InstanceType<typeof GlobalSearchModal> | null>(null);

const sidebarCollapsed = ref(
    localStorage.getItem('sidebarCollapsed') === 'true'
);
const mobileSidebarOpen = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);

watch(sidebarCollapsed, (val) => {
    localStorage.setItem('sidebarCollapsed', String(val));
});

function toggleCollapse() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

function closeMobileSidebar() {
    mobileSidebarOpen.value = false;
}

function handleClickOutside(event: MouseEvent) {
    if (
        userMenuRef.value &&
        !userMenuRef.value.contains(event.target as Node)
    ) {
        userMenuOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <ToastProvider />
        <ConfirmModal />
        <GlobalSearchModal ref="searchModalRef" />

        <!-- Mobile overlay -->
        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-black/50 md:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-50 flex h-full flex-col bg-white border-r border-gray-200 transition-all duration-200"
            :class="[
                sidebarCollapsed ? 'w-16' : 'w-60',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center border-b border-gray-100 px-4 gap-3">
                <Link :href="route('dashboard')" class="shrink-0">
                    <ApplicationLogo class="block h-8 w-auto text-gray-700" />
                </Link>
                <Transition name="fade" mode="out-in">
                    <span v-if="!sidebarCollapsed" key="expanded" class="text-sm font-semibold text-gray-700 whitespace-nowrap truncate">
                        PlazaOS
                    </span>
                </Transition>
            </div>

            <!-- Mobile close -->
            <button
                class="absolute right-2 top-4 p-1 text-gray-500 hover:text-gray-700 md:hidden"
                @click="closeMobileSidebar"
            >
                <AppIcon name="x" />
            </button>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-2 py-4 space-y-0.5 scrollbar-thin">
                <SidebarLink :href="route('dashboard')" :active="route().current('dashboard')" :collapsed="sidebarCollapsed" title="Dashboard">
                    <template #icon><AppIcon name="dashboard" /></template>
                    Dashboard
                </SidebarLink>
                <SidebarLink :href="route('leads.index')" :active="route().current('leads*')" :collapsed="sidebarCollapsed" title="Leads">
                    <template #icon><AppIcon name="leads" /></template>
                    Leads
                </SidebarLink>
                <SidebarLink :href="route('clients.index')" :active="route().current('clients*')" :collapsed="sidebarCollapsed" title="Clients">
                    <template #icon><AppIcon name="clients" /></template>
                    Clients
                </SidebarLink>
                <SidebarLink :href="route('projects.index')" :active="route().current('projects*')" :collapsed="sidebarCollapsed" title="Projects">
                    <template #icon><AppIcon name="projects" /></template>
                    Projects
                </SidebarLink>
                <SidebarLink :href="route('meetings.upcoming')" :active="route().current('meetings*')" :collapsed="sidebarCollapsed" title="Meetings">
                    <template #icon><AppIcon name="meetings" /></template>
                    Meetings
                </SidebarLink>
                <SidebarLink :href="route('tasks.index')" :active="route().current('tasks*')" :collapsed="sidebarCollapsed" title="Tasks">
                    <template #icon><AppIcon name="tasks" /></template>
                    Tasks
                </SidebarLink>
                <SidebarLink :href="route('calendar.index')" :active="route().current('calendar*')" :collapsed="sidebarCollapsed" title="Calendar">
                    <template #icon><AppIcon name="calendar" /></template>
                    Calendar
                </SidebarLink>
                <SidebarLink :href="route('tickets.index')" :active="route().current('tickets*')" :collapsed="sidebarCollapsed" title="Tickets">
                    <template #icon><AppIcon name="tickets" /></template>
                    Tickets
                </SidebarLink>

                <!-- Separator -->
                <div class="my-3 border-t border-gray-200" />

                <SidebarLink :href="route('lead-sources.index')" :active="route().current('lead-sources*')" :collapsed="sidebarCollapsed" title="Sources">
                    <template #icon><AppIcon name="sources" /></template>
                    Sources
                </SidebarLink>
                <SidebarLink :href="route('intake-forms.index')" :active="route().current('intake-forms*')" :collapsed="sidebarCollapsed" title="Forms">
                    <template #icon><AppIcon name="forms" /></template>
                    Forms
                </SidebarLink>
                <SidebarLink :href="route('templates.index')" :active="route().current('templates*')" :collapsed="sidebarCollapsed" title="Templates">
                    <template #icon><AppIcon name="templates" /></template>
                    Templates
                </SidebarLink>
                <SidebarLink :href="route('testimonials.index')" :active="route().current('testimonials*')" :collapsed="sidebarCollapsed" title="Testimonials">
                    <template #icon><AppIcon name="testimonials" /></template>
                    Testimonials
                </SidebarLink>
                <SidebarLink
                    :href="$page.props.auth.user.team_id ? route('team.members') : route('team.create')"
                    :active="route().current('team*')"
                    :collapsed="sidebarCollapsed"
                    title="Team"
                >
                    <template #icon><AppIcon name="team" /></template>
                    Team
                </SidebarLink>
            </nav>

            <!-- Bottom section -->
            <div class="shrink-0 border-t border-gray-200 px-2 py-3">
                <!-- Collapse toggle (desktop only) -->
                <button
                    class="hidden md:flex items-center justify-center w-full p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    @click="toggleCollapse"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <AppIcon :name="sidebarCollapsed ? 'chevron-right' : 'chevron-left'" />
                </button>

                <!-- User area -->
                <div class="relative mt-2" ref="userMenuRef">
                    <button
                        class="flex w-full items-center gap-3 rounded-lg p-2 text-gray-700 hover:bg-gray-100 transition-colors"
                        :class="{ 'justify-center': sidebarCollapsed }"
                        @click="userMenuOpen = !userMenuOpen"
                    >
                        <InitialsAvatar :name="$page.props.auth.user.name" size="h-8 w-8" />
                        <Transition name="fade" mode="out-in">
                            <span v-if="!sidebarCollapsed" key="name" class="text-sm font-medium truncate">
                                {{ $page.props.auth.user.name }}
                            </span>
                        </Transition>
                    </button>

                    <!-- User dropdown (opens upward) -->
                    <Transition name="dropdown">
                        <div
                            v-if="userMenuOpen"
                            class="absolute bottom-full left-0 mb-2 w-56 rounded-lg bg-white shadow-lg border border-gray-200 py-1 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-700 truncate">
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Profile
                            </Link>
                            <Link
                                :href="route('settings.integrations')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Integrations
                            </Link>
                            <Link
                                :href="route('settings.webhooks')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Webhooks
                            </Link>
                            <Link
                                :href="route('settings.notifications')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Notifications
                            </Link>
                            <Link
                                :href="route('settings.security')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Security
                            </Link>
                            <Link
                                v-if="!$page.props.auth.user.team_id"
                                :href="route('team.create')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Create Team
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.team_id && $page.props.auth.user.role === 'owner'"
                                :href="route('team.edit')"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                Team Settings
                            </Link>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                    @click="userMenuOpen = false"
                                >
                                    <AppIcon name="logout" />
                                    Log Out
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div
            class="flex flex-1 flex-col min-h-screen transition-all duration-200"
            :class="sidebarCollapsed ? 'md:ml-16' : 'md:ml-60'"
        >
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between bg-white border-b border-gray-200 px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        class="p-1.5 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors md:hidden"
                        @click="mobileSidebarOpen = true"
                    >
                        <AppIcon name="hamburger" />
                    </button>
                </div>
                <button
                    class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors"
                    @click="searchModalRef?.openModal()"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span class="hidden sm:inline">Search</span>
                    <kbd class="hidden rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs text-gray-400 sm:inline-block">⌘K</kbd>
                </button>
                <NotificationBell />
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <div v-if="$slots.header" class="border-b border-gray-200 px-4 pt-4 sm:px-6 sm:pt-6 lg:px-6 lg:pt-6">
                    <slot name="header" />
                </div>
                <div class="p-4 sm:p-6 lg:p-6 pb-20 sm:pb-6 lg:pb-6">
                    <slot />
                </div>
            </main>
        </div>

        <BottomNav />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.dropdown-enter-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-leave-active {
    transition: opacity 0.1s ease;
}
.dropdown-enter-from {
    opacity: 0;
    transform: translateY(4px);
}
.dropdown-leave-to {
    opacity: 0;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 2px;
}
</style>
