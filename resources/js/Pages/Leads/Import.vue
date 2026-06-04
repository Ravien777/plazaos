<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import type { ImportPreview, LeadImport } from '@/Types';

const props = defineProps<{
    import?: LeadImport;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const step = ref<'upload' | 'map' | 'progress' | 'done'>('upload');
const file = ref<File | null>(null);
const preview = ref<ImportPreview | null>(null);
const importRecord = ref<LeadImport | null>(props.import ?? null);
const mapping = ref<Record<string, string>>({});
const duplicateStrategy = ref<'skip' | 'update'>('skip');
const uploadError = ref('');

const leadFields = [
    { value: '', label: '— Skip —' },
    { value: 'company_name', label: 'Company Name' },
    { value: 'contact_name', label: 'Contact Name' },
    { value: 'email', label: 'Email' },
    { value: 'phone', label: 'Phone' },
    { value: 'website', label: 'Website' },
    { value: 'industry', label: 'Industry' },
    { value: 'city', label: 'City' },
    { value: 'country', label: 'Country' },
    { value: 'source', label: 'Source' },
    { value: 'status', label: 'Status' },
    { value: 'notes', label: 'Notes' },
    { value: 'last_contacted_at', label: 'Last Contacted' },
];

function autoMap(headers: string[]): void {
    const map: Record<string, string> = {};
    for (const h of headers) {
        const key = h.toLowerCase().replace(/[\s_-]+/g, '_');
        const found = leadFields.find(f => f.value && (f.value === key || key.includes(f.value) || f.value.includes(key)));
        map[h] = found?.value ?? '';
    }
    mapping.value = map;
}

async function uploadFile(): Promise<void> {
    if (!file.value) return;
    uploadError.value = '';
    const form = new FormData();
    form.append('file', file.value);
    try {
        const res = await axios.post<ImportPreview>('/leads/import/preview', form);
        preview.value = res.data;
        autoMap(res.data.headers);
        step.value = 'map';
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            uploadError.value = e.response.data.message;
        } else {
            uploadError.value = 'Upload failed. Make sure the file is a valid CSV.';
        }
    }
}

async function startImport(): Promise<void> {
    if (!file.value || !preview.value) return;
    const form = new FormData();
    form.append('file', file.value);
    form.append('column_mapping', JSON.stringify(mapping.value));
    form.append('duplicate_strategy', duplicateStrategy.value);
    try {
        const res = await axios.post<LeadImport>('/leads/import', form);
        importRecord.value = res.data;
        step.value = 'progress';
        pollProgress(res.data.id);
    } catch (e: unknown) {
        if (axios.isAxiosError(e) && e.response?.data?.message) {
            uploadError.value = e.response.data.message;
        } else {
            uploadError.value = 'Import failed to start.';
        }
    }
}

function pollProgress(id: string): void {
    const interval = setInterval(async () => {
        try {
            const res = await axios.get<LeadImport>(`/leads/import/${id}/progress`);
            importRecord.value = res.data;
            if (res.data.status === 'completed' || res.data.status === 'failed') {
                clearInterval(interval);
                step.value = 'done';
            }
        } catch {
            clearInterval(interval);
        }
    }, 2000);
}

const progressPercent = computed(() => {
    const r = importRecord.value;
    if (!r || r.total_rows === 0) return 0;
    return Math.round(((r.processed + r.failed) / r.total_rows) * 100);
});

function reset(): void {
    step.value = 'upload';
    file.value = null;
    preview.value = null;
    importRecord.value = null;
    mapping.value = {};
    uploadError.value = '';
}

function goToLeads(): void {
    router.visit('/leads');
}
</script>

<template>
    <Head title="Import Leads" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Import Leads" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Step 1: Upload -->
                        <div v-if="step === 'upload'">
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Upload CSV File</h3>
                            <p class="mb-4 text-sm text-gray-600">Upload a CSV file with your leads. The first row must contain column headers.</p>
                            <div
                                class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-200 p-12 hover:border-indigo-400"
                                @click="fileInput?.click()"
                                @dragover.prevent
                                @drop.prevent="file = ($event.dataTransfer?.files[0] ?? null) as File | null"
                            >
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-600">
                                        <span v-if="!file">Drag and drop a CSV file here, or click to browse</span>
                                        <span v-else class="font-medium text-indigo-500">{{ file.name }}</span>
                                    </p>
                                    <p v-if="!file" class="mt-1 text-xs text-gray-600">CSV files only, max 256MB</p>
                                </div>
                            </div>
                            <input ref="fileInput" type="file" accept=".csv,.txt" class="hidden" @change="file = (($event.target as HTMLInputElement).files?.[0]) ?? null" />
                            <p v-if="uploadError" class="mt-2 text-sm text-red-600">{{ uploadError }}</p>
                            <div class="mt-4 flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    @click="goToLeads"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    :disabled="!file"
                                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="uploadFile"
                                >
                                    Preview & Map Columns
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Map Columns -->
                        <div v-else-if="step === 'map' && preview">
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Map Columns</h3>
                            <p class="mb-4 text-sm text-gray-600">Map each CSV column to the corresponding lead field. Choose "— Skip —" for columns you don't want to import.</p>

                            <div class="mb-4 flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="radio" v-model="duplicateStrategy" value="skip" />
                                    Skip duplicates
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="radio" v-model="duplicateStrategy" value="update" />
                                    Update existing
                                </label>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">CSV Column</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Lead Field</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Sample Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="header in preview.headers" :key="header">
                                            <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-gray-800">{{ header }}</td>
                                            <td class="px-3 py-3">
                                                <select
                                                    v-model="mapping[header]"
                                                    class="w-full rounded-md border-gray-200 text-sm shadow-sm focus:border-indigo-400 focus:ring-indigo-400"
                                                >
                                                    <option v-for="f in leadFields" :key="f.value" :value="f.value">{{ f.label }}</option>
                                                </select>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-600">
                                                {{ preview.sample_rows[0]?.[header] ?? '' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    @click="reset"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600"
                                    @click="startImport"
                                >
                                    Start Import
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Progress -->
                        <div v-else-if="step === 'progress' && importRecord">
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Importing...</h3>
                            <div class="mb-2 flex justify-between text-sm text-gray-600">
                                <span>{{ importRecord.processed }} processed, {{ importRecord.failed }} failed</span>
                                <span>{{ progressPercent }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                <div
                                    class="h-full rounded-full bg-indigo-500 transition-all duration-500"
                                    :style="{ width: `${progressPercent}%` }"
                                ></div>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">Processing {{ importRecord.total_rows }} rows...</p>
                        </div>

                        <!-- Step 4: Done -->
                        <div v-else-if="step === 'done' && importRecord">
                            <h3 class="mb-4 text-lg font-medium text-gray-800">Import Complete</h3>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-gray-800">{{ importRecord.total_rows }}</p>
                                        <p class="text-sm text-gray-600">Total Rows</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-green-600">{{ importRecord.processed }}</p>
                                        <p class="text-sm text-gray-600">Imported</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold" :class="importRecord.failed > 0 ? 'text-red-600' : 'text-green-600'">
                                            {{ importRecord.failed }}
                                        </p>
                                        <p class="text-sm text-gray-600">Failed</p>
                                    </div>
                                </div>
                                <div v-if="importRecord.errors && importRecord.errors.length > 0" class="mt-4">
                                    <h4 class="mb-2 text-sm font-medium text-red-800">Errors</h4>
                                    <ul class="max-h-40 space-y-1 overflow-y-auto">
                                        <li v-for="(err, i) in importRecord.errors" :key="i" class="text-xs text-red-600">{{ err }}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                    @click="reset"
                                >
                                    Import Another
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600"
                                    @click="goToLeads"
                                >
                                    View Leads
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
