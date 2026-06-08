<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps<{
    clientId: string;
}>();

interface MaroniSummary {
    totalBilled?: number;
    totalPaid?: number;
    outstanding?: number;
}

interface Invoice {
    id: string;
    number: string;
    amount: number;
    status: string;
    date: string;
    url?: string;
}

interface Expense {
    id: string;
    description: string;
    amount: number;
    category: string;
    date: string;
}

const configured = ref(false);
const loading = ref(true);
const summary = ref<MaroniSummary | null>(null);
const invoices = ref<Invoice[]>([]);
const expenses = ref<Expense[]>([]);

onMounted(async () => {
    try {
        const [summaryRes, invoicesRes, expensesRes] = await Promise.all([
            axios.get(`/maroni/clients/${props.clientId}/summary`),
            axios.get(`/maroni/clients/${props.clientId}/invoices`),
            axios.get(`/maroni/clients/${props.clientId}/expenses`),
        ]);

        configured.value = summaryRes.data.configured;
        summary.value = summaryRes.data.summary;
        invoices.value = invoicesRes.data.invoices ?? [];
        expenses.value = expensesRes.data.expenses ?? [];
    } catch {
        configured.value = false;
    } finally {
        loading.value = false;
    }
});

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

function statusClass(s: string): string {
    const map: Record<string, string> = {
        paid: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        overdue: 'bg-red-100 text-red-700',
        draft: 'bg-gray-100 text-gray-600',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
}
</script>

<template>
    <div v-if="loading" class="space-y-3">
        <div class="h-5 w-32 animate-pulse rounded bg-gray-200" />
        <div class="h-16 animate-pulse rounded bg-gray-200" />
    </div>

    <div v-else-if="!configured">
        <p class="text-sm text-gray-600">
            Maroni financial integration is not configured for this client.
        </p>
    </div>

    <div v-else>
        <h3 class="mb-4 text-lg font-medium text-gray-800">Financial Overview</h3>

        <div v-if="summary" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Billed</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800">{{ formatCurrency(summary.totalBilled ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Paid</p>
                <p class="mt-1 text-2xl font-semibold text-green-600">{{ formatCurrency(summary.totalPaid ?? 0) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Outstanding</p>
                <p class="mt-1 text-2xl font-semibold text-red-600">{{ formatCurrency(summary.outstanding ?? 0) }}</p>
            </div>
        </div>

        <div v-if="invoices.length > 0" class="mb-6">
            <h4 class="mb-2 text-sm font-semibold text-gray-700">Recent Invoices</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                        <th class="pb-2 pr-4">#</th>
                        <th class="pb-2 pr-4">Date</th>
                        <th class="pb-2 pr-4">Amount</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inv in invoices" :key="inv.id" class="border-b border-gray-100">
                        <td class="py-2 pr-4 font-medium text-gray-800">{{ inv.number }}</td>
                        <td class="py-2 pr-4 text-gray-600">{{ inv.date }}</td>
                        <td class="py-2 pr-4 text-gray-800">{{ formatCurrency(inv.amount) }}</td>
                        <td class="py-2 pr-4">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(inv.status)">{{ inv.status }}</span>
                        </td>
                        <td class="py-2">
                            <a v-if="inv.url" :href="inv.url" target="_blank" class="text-indigo-500 hover:text-indigo-600">View</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="expenses.length > 0">
            <h4 class="mb-2 text-sm font-semibold text-gray-700">Recent Expenses</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-xs font-medium uppercase text-gray-500">
                        <th class="pb-2 pr-4">Description</th>
                        <th class="pb-2 pr-4">Date</th>
                        <th class="pb-2 pr-4">Category</th>
                        <th class="pb-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="exp in expenses" :key="exp.id" class="border-b border-gray-100">
                        <td class="py-2 pr-4 text-gray-800">{{ exp.description }}</td>
                        <td class="py-2 pr-4 text-gray-600">{{ exp.date }}</td>
                        <td class="py-2 pr-4">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ exp.category }}</span>
                        </td>
                        <td class="py-2 text-gray-800">{{ formatCurrency(exp.amount) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="invoices.length === 0 && expenses.length === 0 && summary" class="text-sm text-gray-600">
            No financial records found.
        </p>
    </div>
</template>
