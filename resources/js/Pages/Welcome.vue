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
        desc: 'Capture, score, and nurture leads from every channel. Import via CSV, track every touchpoint, and convert at the right moment — never lose a follow-up.',
    },
    {
        icon: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
        title: 'Client Portal',
        desc: 'Give every client a secure, branded portal to view projects, submit tickets, access documents, and communicate — without drowning in email chains.',
    },
    {
        icon: 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605',
        title: 'Project Tracking',
        desc: 'Plan milestones, manage deadlines, and deliver on time. Kanban views, file sharing, and progress reports keep your team and clients aligned.',
    },
    {
        icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z',
        title: 'AI Outreach',
        desc: 'Generate personalized cold emails, follow-ups, and templates with AI. Score leads, summarize websites, and automate your pipeline intelligently.',
    },
    {
        icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        title: 'Email Campaigns',
        desc: 'Send transactional and campaign emails directly from the platform. Track opens, manage templates, and keep every conversation history in one place.',
    },
    {
        icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
        title: 'Meeting Scheduling',
        desc: 'Schedule and manage client meetings with Zoom, Google Meet, and Teams. Calendar sync keeps everything in sync across your stack.',
    },
];

const steps = [
    {
        icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        title: 'Capture',
        desc: 'Import leads from CSV, forms, and integrations. Score by priority and start tracking every interaction automatically.',
    },
    {
        icon: 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
        title: 'Manage',
        desc: 'Convert leads to clients, assign projects, send documents, and collaborate with your team from a single dashboard.',
    },
    {
        icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        title: 'Scale',
        desc: 'Track revenue, automate outreach, and grow with confidence. Financial insights and AI tools help you scale without adding headcount.',
    },
];

const testimonials = [
    {
        quote: 'PlazaOS cut our lead-to-client time in half. The client portal alone saved us hours of emailing files back and forth every week.',
        name: 'Alex Chen',
        role: 'CEO, Pixel & Code Studio',
    },
    {
        quote: 'We tried Asana, Trello, and a dozen CRMs. PlazaOS replaced all of them. It\'s the only platform that actually understands how agencies work.',
        name: 'Sarah Mitchell',
        role: 'Operations Director, Crest Media',
    },
    {
        quote: 'The AI outreach feature is incredible. We\'re sending personalized emails at 10x the speed and seeing twice the response rate.',
        name: 'Marcus Johnson',
        role: 'Founder, Forge Digital',
    },
];

const integrations = [
    { name: 'Zoom', icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z', color: 'text-blue-500' },
    { name: 'Microsoft Teams', icon: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72', color: 'text-purple-500' },
    { name: 'Google Calendar', icon: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25', color: 'text-red-500' },
    { name: 'Trello', icon: 'M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125z', color: 'text-green-600' },
    { name: 'Resend', icon: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25', color: 'text-indigo-500' },
    { name: 'Maroni', icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-emerald-500' },
];
</script>

<template>
    <MarketingLayout title="Welcome" :can-login="canLogin" :can-register="canRegister">
        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-stone-900 pt-24">
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <div class="h-full w-full" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.12) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>
            <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 animate-float rounded-full bg-indigo-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 animate-float rounded-full bg-purple-500/10 blur-3xl" style="animation-delay: -3s;"></div>
            <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-20 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <div class="animate-fade-in-up">
                        <h1 class="text-5xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl">
                            <span class="text-white">Your Agency,</span><br />
                            <span class="bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text text-transparent">Operating at Full Capacity</span>
                        </h1>
                    </div>
                    <div class="animate-fade-in-up" style="animation-delay: 150ms; animation-fill-mode: both;">
                        <p class="mt-6 text-lg leading-8 text-indigo-200/80 sm:text-xl">
                            Manage leads, clients, projects, and meetings from a single platform.
                            Track every interaction, automate outreach with AI, and keep your entire team aligned.
                        </p>
                    </div>
                    <div class="animate-fade-in-up" style="animation-delay: 300ms; animation-fill-mode: both;">
                        <div class="mt-10 flex items-center justify-center gap-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center rounded-lg bg-indigo-500 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-400 hover:shadow-indigo-500/50"
                            >
                                Get Started Free
                            </Link>
                            <Link
                                :href="route('features')"
                                class="inline-flex items-center rounded-lg border border-indigo-400/30 px-8 py-3 text-base font-semibold text-indigo-200 transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-400/60 hover:bg-indigo-500/10"
                            >
                                Explore Features
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Bar -->
        <section class="border-b border-gray-100 bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <p class="text-center text-sm font-medium uppercase tracking-widest text-gray-400">
                        Trusted by hundreds of agencies worldwide
                    </p>
                </ScrollReveal>
                <div class="mt-8 grid gap-8 sm:grid-cols-3">
                    <ScrollReveal v-for="(stat, i) in [
                        { value: '50K+', label: 'Leads Managed' },
                        { value: '10K+', label: 'Projects Delivered' },
                        { value: '500+', label: 'Agencies Powered' },
                    ]" :key="stat.label" :delay="i * 100">
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-gray-800 sm:text-4xl">
                                {{ stat.value }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ stat.label }}
                            </div>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                            Everything you need to run your agency
                        </h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">
                            From first lead to project delivery — one platform, no duct tape.
                        </p>
                    </div>
                </ScrollReveal>
                <div class="mx-auto mt-16 grid max-w-5xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <ScrollReveal
                        v-for="(feature, i) in features"
                        :key="feature.title"
                        :delay="i * 80"
                    >
                        <div class="group h-full rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-8">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 transition-colors duration-300 group-hover:bg-indigo-200">
                                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-800">{{ feature.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ feature.desc }}</p>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                            How it works
                        </h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">
                            Three simple steps to transform your agency operations.
                        </p>
                    </div>
                </ScrollReveal>
                <div class="relative mt-16 grid gap-12 sm:grid-cols-3">
                    <div class="absolute left-1/2 top-16 hidden h-px w-2/3 -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300 to-transparent sm:block"></div>
                    <ScrollReveal
                        v-for="(step, i) in steps"
                        :key="step.title"
                        :delay="i * 150"
                    >
                        <div class="relative text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 shadow-sm">
                                <span class="text-lg font-bold text-indigo-500">{{ i + 1 }}</span>
                            </div>
                            <div class="mx-auto mt-6 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800">{{ step.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ step.desc }}</p>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Integrations -->
        <section class="overflow-hidden bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                            Works with your favorite tools
                        </h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">
                            PlazaOS connects with the tools you already use, so nothing gets left behind.
                        </p>
                    </div>
                </ScrollReveal>
            </div>
            <div class="relative mt-16 overflow-hidden">
                <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-32 bg-gradient-to-r from-gray-50 to-transparent"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-32 bg-gradient-to-l from-gray-50 to-transparent"></div>
                <div class="flex animate-marquee gap-8">
                    <template v-for="n in 2" :key="n">
                        <div
                            v-for="tool in integrations"
                            :key="tool.name + n"
                            class="flex flex-shrink-0 items-center gap-3 rounded-full border border-gray-200 bg-white px-6 py-3 shadow-sm"
                        >
                            <svg class="h-5 w-5 flex-shrink-0" :class="tool.color" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="tool.icon" />
                            </svg>
                            <span class="whitespace-nowrap text-sm font-medium text-gray-700">{{ tool.name }}</span>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                            Loved by agency teams
                        </h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">
                            See what our customers have to say about PlazaOS.
                        </p>
                    </div>
                </ScrollReveal>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <ScrollReveal
                        v-for="(t, i) in testimonials"
                        :key="t.name"
                        :delay="i * 100"
                    >
                        <div class="flex h-full flex-col rounded-xl bg-white p-8 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <svg class="h-6 w-6 text-indigo-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C9.591 11.69 11 13.166 11 15c0 1.933-1.567 3.5-3.5 3.5-1.271 0-2.404-.655-2.917-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C19.591 11.69 21 13.166 21 15c0 1.933-1.567 3.5-3.5 3.5-1.271 0-2.404-.655-2.917-1.179z" />
                            </svg>
                            <p class="mt-4 flex-1 text-sm leading-6 text-gray-600">&ldquo;{{ t.quote }}&rdquo;</p>
                            <div class="mt-6">
                                <div class="font-semibold text-gray-800">{{ t.name }}</div>
                                <div class="text-sm text-gray-500">{{ t.role }}</div>
                            </div>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-stone-900 py-24">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <ScrollReveal>
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Ready to streamline your agency?
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-indigo-200/80">
                        Join hundreds of agencies already operating at full capacity. Start free, no credit card required.
                    </p>
                    <div class="mt-8 flex items-center justify-center gap-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center rounded-lg bg-indigo-500 px-10 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-400 hover:shadow-indigo-500/50"
                        >
                            Get Started Free
                        </Link>
                        <Link
                            :href="route('features')"
                            class="inline-flex items-center rounded-lg border border-indigo-400/30 px-10 py-4 text-base font-semibold text-indigo-200 transition-all duration-300 hover:-translate-y-0.5 hover:border-indigo-400/60 hover:bg-indigo-500/10"
                        >
                            See All Features
                        </Link>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </MarketingLayout>
</template>
