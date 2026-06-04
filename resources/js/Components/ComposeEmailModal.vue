<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import type { EmailTemplate } from '@/Types';

const props = defineProps<{
    show: boolean;
    emailableType: string;
    emailableId: string;
    recipientEmail: string;
    recipientName: string;
    companyName: string;
}>();

const emit = defineEmits<{
    close: [];
    sent: [];
}>();

const subject = ref('');
const body = ref('');
const selectedTemplate = ref('');
const templates = ref<EmailTemplate[]>([]);
const sending = ref(false);
const generating = ref(false);
const error = ref('');

const canUseAi = computed(() => props.emailableType === 'lead' || props.emailableType === 'client');

const availableVariables = computed(() => [
    { key: 'company_name', value: props.companyName },
    { key: 'contact_name', value: props.recipientName },
    { key: 'sender_name', value: '' },
]);

watch(() => props.show, async (val) => {
    if (!val) return;
    subject.value = '';
    body.value = '';
    selectedTemplate.value = '';
    error.value = '';
    try {
        const res = await axios.get('/email-templates');
        templates.value = res.data.data;
    } catch {
        // templates not critical
    }
});

function selectTemplate(key: string): void {
    selectedTemplate.value = key;
    const tpl = templates.value.find(t => t.key === key);
    if (!tpl) return;
    subject.value = tpl.subject;
    body.value = tpl.body;
}

function variableLabel(key: string): string {
    return `{{${key}}}`;
}

function insertVariable(variable: string): void {
    const textarea = document.querySelector<HTMLTextAreaElement>('#email-body');
    if (!textarea) return;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    body.value = body.value.substring(0, start) + `{{${variable}}}` + body.value.substring(end);
}

function aiEndpoint(action: string): string {
    const type = props.emailableType === 'client' ? 'clients' : 'leads';
    return `/ai/${type}/${props.emailableId}/${action}`;
}

async function generateOutreach(): Promise<void> {
    generating.value = true;
    error.value = '';
    try {
        const res = await axios.post(aiEndpoint('generate-outreach'));
        subject.value = res.data.data.subject;
        body.value = res.data.data.body;
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'Failed to generate email.';
        }
    } finally {
        generating.value = false;
    }
}

async function generateFollowUp(): Promise<void> {
    generating.value = true;
    error.value = '';
    try {
        const res = await axios.post(aiEndpoint('generate-follow-up'));
        subject.value = res.data.data.subject;
        body.value = res.data.data.body;
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'Failed to generate follow-up.';
        }
    } finally {
        generating.value = false;
    }
}

async function send(): Promise<void> {
    if (!subject.value.trim() || !body.value.trim()) return;
    sending.value = true;
    error.value = '';
    try {
        await axios.post(`/${props.emailableType}/${props.emailableId}/emails`, {
            subject: subject.value,
            body: body.value,
            template: selectedTemplate.value || null,
        });
        emit('sent');
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'Failed to send email.';
        }
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800">Compose Email</h3>
                <button type="button" class="text-gray-500 hover:text-gray-600" @click="emit('close')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">To</label>
                <input
                    type="text"
                    :value="recipientEmail"
                    class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600 shadow-sm"
                    disabled
                />
            </div>

            <div v-if="canUseAi" class="mb-4">
                <label class="block text-sm font-medium text-gray-700">AI Generation</label>
                <div class="mt-1 flex gap-2">
                    <button
                        type="button"
                        :disabled="generating"
                        class="rounded-md bg-indigo-500 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="generateOutreach"
                    >
                        {{ generating ? 'Generating...' : 'Generate Outreach' }}
                    </button>
                    <button
                        type="button"
                        :disabled="generating"
                        class="rounded-md bg-indigo-500 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="generateFollowUp"
                    >
                        Generate Follow-Up
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Template</label>
                <select
                    v-model="selectedTemplate"
                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                    @change="selectTemplate(selectedTemplate)"
                >
                    <option value="">— No template —</option>
                    <option v-for="tpl in templates" :key="tpl.key" :value="tpl.key">
                        {{ tpl.name }}
                    </option>
                </select>
            </div>

            <div class="mb-2 flex flex-wrap gap-1">
                <button
                    v-for="v in availableVariables"
                    :key="v.key"
                    type="button"
                    class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-200"
                    @click="insertVariable(v.key)"
                >
                    {{ variableLabel(v.key) }}
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Subject</label>
                <input
                    v-model="subject"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Body</label>
                <textarea
                    id="email-body"
                    v-model="body"
                    rows="10"
                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                ></textarea>
            </div>

            <p v-if="error" class="mb-2 text-sm text-red-600">{{ error }}</p>

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    @click="emit('close')"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    :disabled="sending || !subject.trim() || !body.trim()"
                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="send"
                >
                    {{ sending ? 'Sending...' : 'Send' }}
                </button>
            </div>
        </div>
    </div>
</template>
