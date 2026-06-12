<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { Plan } from '@/Types';

const props = defineProps<{
    plans: Plan[];
    currentPlanId?: string | null;
    compact?: boolean;
}>();

function priceDisplay(plan: Plan): string {
    if (plan.monthly_price_cents === 0) return 'Free';
    return `$${(plan.monthly_price_cents / 100).toFixed(0)}`;
}

function checkout(plan: Plan) {
    router.post(route('billing.checkout', plan.id), {}, {
        preserveScroll: true,
    });
}

function isCurrentPlan(plan: Plan): boolean {
    return props.currentPlanId === plan.id;
}
</script>

<template>
    <div
        class="grid gap-6"
        :class="compact ? 'grid-cols-1 sm:grid-cols-3' : 'grid-cols-1 md:grid-cols-3'"
    >
        <div
            v-for="plan in plans"
            :key="plan.id"
            class="relative flex flex-col rounded-xl border bg-white p-6 transition-shadow hover:shadow-md"
            :class="[
                isCurrentPlan(plan) ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200',
            ]"
        >
            <!-- Current plan badge -->
            <div
                v-if="isCurrentPlan(plan)"
                class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white"
            >
                Current Plan
            </div>

            <!-- Popular badge -->
            <div
                v-if="plan.slug === 'pro' && !isCurrentPlan(plan)"
                class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-white"
            >
                Popular
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ plan.name }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ plan.description }}</p>
            </div>

            <div class="mb-6">
                <span class="text-3xl font-bold text-gray-800">{{ priceDisplay(plan) }}</span>
                <span v-if="plan.monthly_price_cents > 0" class="text-sm text-gray-500">/mo</span>
            </div>

            <ul class="mb-6 flex-1 space-y-2">
                <li
                    v-for="(feature, index) in (plan.features ?? [])"
                    :key="index"
                    class="flex items-center gap-2 text-sm text-gray-600"
                >
                    <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ feature.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
                </li>
            </ul>

            <button
                v-if="!isCurrentPlan(plan)"
                type="button"
                class="mt-auto w-full rounded-lg px-4 py-2 text-sm font-semibold transition"
                :class="
                    plan.monthly_price_cents === 0
                        ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                        : 'bg-indigo-600 text-white hover:bg-indigo-700'
                "
                @click="checkout(plan)"
            >
                {{ plan.monthly_price_cents === 0 ? 'Downgrade' : 'Upgrade' }}
            </button>
        </div>
    </div>
</template>
