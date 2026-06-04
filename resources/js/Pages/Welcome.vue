<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col bg-white">
        <header class="border-b border-gray-200">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2">
                    <svg viewBox="0 0 316 316" class="h-8 w-8 fill-current text-indigo-500" xmlns="http://www.w3.org/2000/svg">
                        <line x1="158" y1="70" x2="248" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <line x1="158" y1="70" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <line x1="248" y1="186" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <circle cx="158" cy="70" r="28" fill="currentColor" />
                        <circle cx="248" cy="186" r="28" fill="currentColor" />
                        <circle cx="68" cy="186" r="28" fill="currentColor" />
                    </svg>
                    <span class="text-xl font-bold text-gray-800">PlazaOS</span>
                </Link>

                <nav v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-700 transition hover:text-gray-800"
                        >
                            Log in
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <section class="relative overflow-hidden">
                <div class="mx-auto max-w-7xl px-4 pb-24 pt-20 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h1 class="text-4xl font-extrabold tracking-tight text-gray-800 sm:text-5xl lg:text-6xl">
                            Your Agency,
                            <span class="text-indigo-500">Operating at Full Capacity</span>
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-gray-600">
                            Manage leads, clients, projects, and meetings from one place.
                            Track every interaction, automate outreach, and keep your team aligned.
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-indigo-500 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-400"
                            >
                                Get Started
                            </Link>
                            <Link
                                :href="route('login')"
                                class="rounded-lg border border-gray-200 px-8 py-3 text-base font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                Log in
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-gray-50 py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800">
                            Everything you need to run your agency
                        </h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">
                            From first lead to project delivery — one platform.
                        </p>
                    </div>

                    <div class="mx-auto mt-16 grid max-w-5xl gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-800">Lead Management</h3>
                            <p class="mt-2 text-sm text-gray-600">Import, track, and nurture leads from multiple sources. Score, convert, and never lose a follow-up.</p>
                        </div>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-800">Client Portal</h3>
                            <p class="mt-2 text-sm text-gray-600">Give clients a dedicated portal to view projects, submit tickets, and access documents.</p>
                        </div>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-800">Project Tracking</h3>
                            <p class="mt-2 text-sm text-gray-600">Manage milestones, deadlines, and deliverables. Keep everyone on the same page.</p>
                        </div>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-800">AI Outreach</h3>
                            <p class="mt-2 text-sm text-gray-600">Generate personalized emails and follow-ups with AI. Score leads and automate your pipeline.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-gray-800">Ready to streamline your agency?</h2>
                    <p class="mt-2 text-gray-600">Start managing leads, clients, and projects in one place.</p>
                    <div class="mt-6">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-500 px-8 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-400"
                        >
                            Get Started Free
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-gray-600">
                    &copy; {{ new Date().getFullYear() }} PlazaOS. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>
