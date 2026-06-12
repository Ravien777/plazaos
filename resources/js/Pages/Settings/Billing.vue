<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PricingTable from '@/Components/PricingTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import type { Plan, Subscription, Invoice } from '@/Types';

const toast = useToast();

const props = defineProps<{
    currentPlan: Plan;
    subscription: Subscription | null;
    plans: Plan[];
    seatCount: number;
    invoices: Invoice[];
}>();

function formatDate(date: string | null): string {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatPrice(cents: number): string {
    return `$${(cents / 100).toFixed(2)}`;
}

function statusColor(status: string): string {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'trialing': return 'bg-blue-100 text-blue-800';
        case 'past_due': return 'bg-red-100 text-red-800';
        case 'canceled': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function openPortal() {
    router.post(route('billing.portal'), {}, {
        preserveScroll: true,
    });
}

function cancelSubscription() {
    if (!confirm('Are you sure you want to cancel your subscription? You will continue to have access until the end of your billing period.')) return;
    router.post(route('billing.cancel-subscription'), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Subscription cancelled.'),
    });
}

function resumeSubscription() {
    router.post(route('billing.resume-subscription'), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Subscription resumed.'),
    });
}
</script>

<template>
    <Head title="Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">
                Billing & Plan
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl space-y-8">
                <!-- Current Plan Card -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">Current Plan</h3>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="statusColor(subscription?.status ?? 'active')"
                                >
                                    {{ subscription?.status ?? 'Free' }}
                                </span>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    v-if="subscription?.stripe_customer_id"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                    @click="openPortal"
                                >
                                    Manage Billing
                                </button>
                                <button
                                    v-if="subscription?.status === 'canceled'"
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-700"
                                    @click="resumeSubscription"
                                >
                                    Resume
                                </button>
                                <button
                                    v-if="subscription?.status === 'active' || subscription?.status === 'trialing'"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 transition hover:bg-red-50"
                                    @click="cancelSubscription"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-6 sm:grid-cols-4">
                            <div>
                                <p class="text-sm text-gray-500">Plan</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">{{ currentPlan.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Price</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ formatPrice(currentPlan.monthly_price_cents) }}/mo
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Seats Used</p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ seatCount }}
                                    <span v-if="currentPlan.max_users" class="text-sm text-gray-500">/ {{ currentPlan.max_users }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">
                                    {{ subscription?.status === 'trialing' ? 'Trial Ends' : 'Period Ends' }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ formatDate(subscription?.trial_ends_at ?? subscription?.current_period_ends_at ?? null) }}
                                </p>
                            </div>
                        </div>

                        <!-- Trial banner -->
                        <div
                            v-if="subscription?.status === 'trialing'"
                            class="mt-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-700"
                        >
                            <p class="font-medium">You're on a free trial.</p>
                            <p class="mt-1">Upgrade to Pro or Team to keep using all features after your trial ends.</p>
                        </div>

                        <!-- Past due banner -->
                        <div
                            v-if="subscription?.status === 'past_due'"
                            class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700"
                        >
                            <p class="font-medium">Your payment is past due.</p>
                            <p class="mt-1">Please update your payment method to avoid service interruption.</p>
                        </div>

                        <!-- Canceled banner -->
                        <div
                            v-if="subscription?.status === 'canceled'"
                            class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-700"
                        >
                            <p class="font-medium">Your subscription has been canceled.</p>
                            <p
                                v-if="subscription.current_period_ends_at"
                                class="mt-1"
                            >
                                You'll have access until {{ formatDate(subscription.current_period_ends_at) }}.
                                You can resume your subscription at any time before then.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Plan Comparison -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Available Plans</h3>
                    <PricingTable
                        :plans="plans"
                        :current-plan-id="currentPlan.id"
                        compact
                    />
                </div>

                <!-- Invoices -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">Invoice History</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="invoices.length === 0" class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-500">
                            No invoices yet.
                        </div>
                        <table v-else class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500">
                                    <th class="pb-2 font-medium">Invoice</th>
                                    <th class="pb-2 font-medium">Date</th>
                                    <th class="pb-2 font-medium">Amount</th>
                                    <th class="pb-2 font-medium">Status</th>
                                    <th class="pb-2 font-medium" />
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="invoice in invoices"
                                    :key="invoice.id"
                                    class="border-b border-gray-100"
                                >
                                    <td class="py-3 text-gray-700">{{ invoice.number }}</td>
                                    <td class="py-3 text-gray-500">{{ formatDate(new Date(invoice.created * 1000).toISOString()) }}</td>
                                    <td class="py-3 text-gray-700">{{ formatPrice(invoice.amount_paid) }}</td>
                                    <td class="py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                        >
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a
                                            v-if="invoice.pdf_url"
                                            :href="invoice.pdf_url"
                                            target="_blank"
                                            class="text-indigo-600 hover:text-indigo-800"
                                        >
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
