<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
    projectId: string;
    clientEmail: string;
    clientName: string;
}>();

const emit = defineEmits<{
    close: [];
    sent: [];
}>();

const previewData = ref<any>(null);
const loading = ref(false);
const sending = ref(false);
const personalNote = ref('');
const error = ref('');

watch(() => props.show, async (val) => {
    if (!val) return;
    loading.value = true;
    error.value = '';
    previewData.value = null;
    personalNote.value = '';
    try {
        const res = await axios.get(`/projects/${props.projectId}/status-ping-preview`);
        previewData.value = res.data;
    } catch {
        error.value = 'Failed to load preview.';
    } finally {
        loading.value = false;
    }
});

async function send(): Promise<void> {
    sending.value = true;
    error.value = '';
    try {
        await axios.post(`/projects/${props.projectId}/send-status-ping`, {
            personal_note: personalNote.value || null,
        });
        emit('sent');
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'Failed to send status update.';
        }
    } finally {
        sending.value = false;
    }
}

function isEmpty(list: any[] | null | undefined): boolean {
    return !list || list.length === 0;
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800">
                    Send Status Update
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-600" @click="emit('close')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-gray-600">
                Preview of the weekly update email for <strong>{{ clientName }}</strong>
            </p>

            <div v-if="loading" class="space-y-3">
                <div v-for="n in 4" :key="n" class="h-4 animate-pulse rounded bg-gray-100" />
            </div>

            <div v-else-if="previewData" class="max-h-80 space-y-4 overflow-y-auto">
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-green-600">&#10003; Completed This Week</h4>
                    <ul v-if="!isEmpty(previewData.completed)" class="mt-1 space-y-1">
                        <li v-for="(item, idx) in previewData.completed" :key="idx" class="text-sm text-gray-700">
                            &bull; {{ item }}
                        </li>
                    </ul>
                    <p v-else class="mt-1 text-sm italic text-gray-400">Nothing completed yet this week.</p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-amber-600">&#9881; In Progress</h4>
                    <ul v-if="!isEmpty(previewData.in_progress)" class="mt-1 space-y-1">
                        <li v-for="(item, idx) in previewData.in_progress" :key="idx" class="text-sm text-gray-700">
                            &bull; {{ item }}
                        </li>
                    </ul>
                    <p v-else class="mt-1 text-sm italic text-gray-400">No active tasks.</p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-indigo-500">&#128196; Recent Activity</h4>
                    <ul v-if="!isEmpty(previewData.recent_activity)" class="mt-1 space-y-1">
                        <li v-for="(item, idx) in previewData.recent_activity" :key="idx" class="text-sm text-gray-700">
                            &bull; {{ item }}
                        </li>
                    </ul>
                    <p v-else class="mt-1 text-sm italic text-gray-400">No recent activity.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Personal Note (optional)</label>
                    <textarea
                        v-model="personalNote"
                        rows="3"
                        placeholder="Add a personal message at the top of the email..."
                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                    />
                </div>
            </div>

            <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

            <p v-if="error && previewData" class="mb-2 text-sm text-red-600">{{ error }}</p>

            <div class="mt-4 flex justify-end gap-3">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                    @click="emit('close')"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    :disabled="sending || loading || !previewData"
                    class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="send"
                >
                    {{ sending ? 'Sending...' : 'Send to Client' }}
                </button>
            </div>
        </div>
    </div>
</template>
