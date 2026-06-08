<script setup lang="ts">
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const features = [
    {
        icon: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
        title: 'Lead Management',
        desc: 'Capture, organize, and nurture leads from every channel. Score them by priority, track every touchpoint, and convert at the right moment.',
        bullets: ['Multi-source import (CSV, forms, manual)', 'Priority scoring & status tracking', 'Notes and activity history per lead', 'Bulk actions and filters'],
    },
    {
        icon: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
        title: 'Client Portal',
        desc: 'Give every client a secure, branded portal where they can see projects, submit tickets, access documents, and communicate — without email chains.',
        bullets: ['White-label portal experience', 'Project & document access', 'Ticket submission & tracking', 'Secure login with magic links'],
    },
    {
        icon: 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605',
        title: 'Project Management',
        desc: 'Plan, track, and deliver projects on time. From initial scope to final sign-off, keep every milestone visible and every stakeholder aligned.',
        bullets: ['Kanban & list views', 'Milestone & deadline tracking', 'File sharing & document management', 'Time tracking & progress reports'],
    },
    {
        icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z',
        title: 'AI-Powered Outreach',
        desc: 'Generate personalized cold emails, follow-ups, and templates with AI. Score leads, summarize websites, and automate your pipeline.',
        bullets: ['AI email generation per lead/client', 'Smart follow-up suggestions', 'Website summarization & insights', 'Bulk template generation'],
    },
    {
        icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        title: 'Email Campaigns',
        desc: 'Send transactional and campaign emails directly from PlazaOS. Track opens, manage templates, and keep every conversation in one place.',
        bullets: ['Resend-powered delivery', 'Open & engagement tracking', 'Customizable email templates', 'Per-lead and per-client history'],
    },
    {
        icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        title: 'Financial Integration',
        desc: 'Connect Maroni for real-time financial data. View client invoices, expenses, and revenue summaries directly in the dashboard.',
        bullets: ['Maroni sync for invoices & expenses', 'Revenue & outstanding balance KPIs', 'Per-client financial summary', 'Automated client sync'],
    },
    {
        icon: 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
        title: 'Support Tickets',
        desc: 'Let clients submit and track support tickets. Manage responses, priorities, and statuses without leaving the platform.',
        bullets: ['Client-submitted tickets', 'Priority & status workflow', 'Internal notes & replies', 'Ticket history per client'],
    },
    {
        icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        title: 'Meeting Scheduling',
        desc: 'Schedule and manage client meetings with Zoom, Google Meet, and Teams integration. Calendar sync keeps everything in sync.',
        bullets: ['Zoom, Meet & Teams integration', 'Calendar event creation & sync', 'Upcoming meetings dashboard', 'Per-client meeting history'],
    },
    {
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        title: 'Document Management',
        desc: 'Upload, share, and manage documents with R2 cloud storage. Generate signed download links and keep files organized per client.',
        bullets: ['Cloud storage with R2 integration', 'Signed download URLs', 'Per-client document organization', 'Drag-and-drop upload'],
    },
];
</script>

<template>
    <MarketingLayout title="Features" :can-login="canLogin" :can-register="canRegister">
        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-stone-900 pt-24">
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <div class="h-full w-full" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>
            <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-20 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-4xl text-center">
                        <h1 class="bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text text-5xl font-extrabold tracking-tight text-transparent sm:text-6xl lg:text-7xl">
                            All-in-One Agency OS
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-indigo-200/80 sm:text-xl">
                            Everything your agency needs to capture leads, manage clients, deliver projects, and grow —<br class="hidden sm:inline" />
                            all from a single, intelligent platform.
                        </p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Feature Grid -->
        <section class="bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <ScrollReveal
                        v-for="(feature, i) in features"
                        :key="feature.title"
                        :delay="i * 80"
                    >
                        <div class="group h-full rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-8">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 transition-colors duration-300 group-hover:bg-indigo-200">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800">{{ feature.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ feature.desc }}</p>
                            <ul class="mt-4 space-y-1.5">
                                <li v-for="bullet in feature.bullets" :key="bullet" class="flex items-start gap-2 text-sm text-gray-500">
                                    <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    {{ bullet }}
                                </li>
                            </ul>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                        Ready to see it in action?
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Start your free account today and discover what your agency can do with the right operating system.
                    </p>
                    <div class="mt-8 flex items-center justify-center gap-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-block rounded-lg bg-indigo-500 px-10 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-400 hover:shadow-indigo-500/50"
                        >
                            Get Started Free
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-block rounded-lg border border-gray-200 px-10 py-4 text-base font-semibold text-gray-700 transition-all duration-300 hover:-translate-y-0.5 hover:bg-gray-50"
                        >
                            Log in
                        </Link>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </MarketingLayout>
</template>
