<script setup lang="ts">
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import type { Plan } from '@/Types';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    plans: Plan[];
}>();

function priceDisplay(cents: number): string {
    if (cents === 0) return 'Free';
    return `$${(cents / 100).toFixed(0)}`;
}

function features(plan: Plan): string[] {
    const map: Record<string, string[]> = {
        free: ['Leads', 'Clients', 'Projects', 'Documents', 'Notes'],
        pro: [
            'Leads', 'Clients', 'Projects', 'Documents', 'Notes',
            'Email Outreach', 'Meetings', 'Webhooks', 'AI Features',
            'Client Portal', 'CSV Import', 'Tickets',
        ],
        team: [
            'Leads', 'Clients', 'Projects', 'Documents', 'Notes',
            'Email Outreach', 'Meetings', 'Webhooks', 'AI Features',
            'Client Portal', 'CSV Import', 'Tickets',
            'Public API', 'Integrations', 'Testimonials', 'Intake Forms',
        ],
    };
    return map[plan.slug] ?? (plan.features ?? []);
}
</script>

<template>
    <Head title="Pricing" />

    <MarketingLayout title="Pricing">
        <div class="relative overflow-hidden py-24 sm:py-32">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Simple, transparent pricing
                    </h1>
                    <p class="mt-4 text-lg text-gray-600">
                        Start for free. Upgrade when you grow. No hidden fees, no surprise charges.
                    </p>
                </div>

                <div class="mx-auto mt-16 grid max-w-5xl gap-8 lg:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="relative flex flex-col rounded-2xl border bg-white p-8 shadow-sm transition-shadow hover:shadow-md"
                        :class="plan.slug === 'pro' ? 'border-indigo-400 ring-2 ring-indigo-100 scale-105' : 'border-gray-200'"
                    >
                        <div
                            v-if="plan.slug === 'pro'"
                            class="absolute -top-4 left-1/2 -translate-x-1/2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white"
                        >
                            Most Popular
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ plan.description }}</p>

                        <div class="mt-6">
                            <span class="text-4xl font-bold text-gray-900">{{ priceDisplay(plan.monthly_price_cents) }}</span>
                            <span v-if="plan.monthly_price_cents > 0" class="text-base text-gray-500">/month</span>
                        </div>

                        <ul class="mt-8 flex-1 space-y-3">
                            <li
                                v-for="feature in features(plan)"
                                :key="feature"
                                class="flex items-center gap-3 text-sm text-gray-600"
                            >
                                <svg class="h-5 w-5 shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>

                        <div class="mt-8">
                            <a
                                v-if="plan.slug === 'free'"
                                :href="route('register')"
                                class="block w-full rounded-lg border border-gray-300 px-6 py-3 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                Get Started Free
                            </a>
                            <a
                                v-else
                                :href="route('register')"
                                class="block w-full rounded-lg bg-indigo-600 px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-indigo-700"
                            >
                                Start Free Trial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketingLayout>
</template>
