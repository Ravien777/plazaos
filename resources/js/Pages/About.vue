<script setup lang="ts">
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <MarketingLayout title="About" :can-login="canLogin" :can-register="canRegister">
        <!-- Hero -->
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-stone-900 pt-24">
            <div class="pointer-events-none absolute inset-0 opacity-20">
                <div class="h-full w-full" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>
            <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-20 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <div class="mx-auto max-w-3xl text-center">
                        <h1 class="bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text text-5xl font-extrabold tracking-tight text-transparent sm:text-6xl lg:text-7xl">
                            Our Mission
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-indigo-200/80 sm:text-xl">
                            We believe agencies should spend their energy on clients, not on spreadsheets.<br />
                            PlazaOS is the operating system that makes that possible.
                        </p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Story -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl">
                    <ScrollReveal>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                            Built for agencies,<br />
                            <span class="text-indigo-500">by people who ran them.</span>
                        </h2>
                    </ScrollReveal>
                    <ScrollReveal :delay="100">
                        <p class="mt-6 text-lg leading-8 text-gray-600">
                            PlazaOS was born from years of hands-on agency experience. We saw firsthand how scattered tools,
                            endless spreadsheets, and manual follow-ups drained time and energy from the work that actually matters —
                            delivering exceptional results for clients.
                        </p>
                    </ScrollReveal>
                    <ScrollReveal :delay="200">
                        <p class="mt-4 text-lg leading-8 text-gray-600">
                            We set out to build a single platform that unifies every part of agency operations:
                            lead capture and nurturing, client onboarding and portals, project management, financial tracking,
                            and AI-powered outreach. One login. One source of truth. No duct tape.
                        </p>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-stone-900 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-3">
                    <ScrollReveal v-for="(stat, i) in [
                        { value: '500+', label: 'Agencies Powered' },
                        { value: '50K+', label: 'Leads Managed' },
                        { value: '99.9%', label: 'Uptime' },
                    ]" :key="stat.label" :delay="i * 100">
                        <div class="text-center">
                            <div class="text-5xl font-extrabold text-white sm:text-6xl">
                                {{ stat.value }}
                            </div>
                            <div class="mt-2 text-lg text-indigo-300/80">
                                {{ stat.label }}
                            </div>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Values -->
        <section class="bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ScrollReveal>
                    <h2 class="text-center text-3xl font-bold tracking-tight text-gray-800 sm:text-4xl">
                        What we stand for
                    </h2>
                </ScrollReveal>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <ScrollReveal v-for="(value, i) in [
                        { icon: 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605', title: 'Simplicity', desc: 'Powerful tools that don\'t get in the way. Every feature is designed to reduce complexity, not add to it.' },
                        { icon: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z', title: 'Integration', desc: 'Your tools should talk to each other. We connect everything so you don\'t have to copy-paste between tabs.' },
                        { icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z', title: 'Innovation', desc: 'AI isn\'t a gimmick — it\'s a force multiplier. We bake intelligence into every corner of the platform.' },
                    ]" :key="value.title" :delay="i * 100">
                        <div class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="value.icon" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-800">{{ value.title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ value.desc }}</p>
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
                        Ready to transform your agency?
                    </h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Join hundreds of agencies already operating at full capacity with PlazaOS.
                    </p>
                    <div class="mt-8">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-block rounded-lg bg-indigo-500 px-10 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-indigo-400 hover:shadow-indigo-500/50"
                        >
                            Get Started Free
                        </Link>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </MarketingLayout>
</template>
