<script setup lang="ts">
import ComposeEmailModal from '@/Components/ComposeEmailModal.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import type { Email } from '@/Types';

const props = defineProps<{
    emailableType: string;
    emailableId: string;
    recipientEmail: string;
    recipientName: string;
    companyName: string;
}>();

const emails = ref<Email[]>([]);
const showCompose = ref(false);
const expandedId = ref<string | null>(null);

async function fetchEmails(): Promise<void> {
    try {
        const res = await axios.get(`/${props.emailableType}/${props.emailableId}/emails`);
        emails.value = res.data.data;
    } catch {
        // silently fail
    }
}

onMounted(fetchEmails);

function toggleExpand(id: string): void {
    expandedId.value = expandedId.value === id ? null : id;
}

function onEmailSent(): void {
    showCompose.value = false;
    fetchEmails();
}

function statusClass(status: string): string {
    return status === 'sent'
        ? 'bg-green-100 text-green-700'
        : 'bg-red-100 text-red-700';
}

function formatDate(dt: string | null): string {
    if (!dt) return '';
    const d = new Date(dt);
    return d.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800">Email History</h3>
            <button
                type="button"
                class="rounded-md bg-gray-700 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                @click="showCompose = true"
            >
                Compose
            </button>
        </div>

        <div v-if="emails.length === 0" class="py-6 text-center text-sm text-gray-600">
            No emails sent yet.
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="email in emails"
                :key="email.id"
                class="rounded-lg border border-gray-200"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50"
                    @click="toggleExpand(email.id)"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800">{{ email.subject }}</p>
                        <p class="text-xs text-gray-600">To: {{ email.to }}</p>
                    </div>
                    <div class="ml-3 flex items-center gap-3">
                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="statusClass(email.status)"
                        >
                            {{ email.status }}
                        </span>
                        <span
                            v-if="email.opened_at"
                            class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700"
                        >
                            Opened {{ formatDate(email.opened_at) }}
                        </span>
                        <span class="text-xs text-gray-500">{{ email.sent_at ?? email.created_at }}</span>
                        <svg
                            class="h-4 w-4 text-gray-500 transition-transform"
                            :class="{ 'rotate-180': expandedId === email.id }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>
                <div v-if="expandedId === email.id" class="border-t border-gray-200 px-4 py-3">
                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ email.body }}</p>
                </div>
            </div>
        </div>

        <ComposeEmailModal
            :show="showCompose"
            :emailable-type="emailableType"
            :emailable-id="emailableId"
            :recipient-email="recipientEmail"
            :recipient-name="recipientName"
            :company-name="companyName"
            @close="showCompose = false"
            @sent="onEmailSent"
        />
    </div>
</template>
