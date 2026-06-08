<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useConfirm } from '@/composables/useConfirm';
import type { DocumentRecord } from '@/Types';

const { confirm } = useConfirm();

const props = defineProps<{
    documentableType: string;
    documentableId: string;
}>();

const documents = ref<DocumentRecord[]>([]);
const uploading = ref(false);
const uploadError = ref('');

async function fetchDocuments(): Promise<void> {
    try {
        const res = await axios.get(route('documents.index', { documentableType: props.documentableType, documentable: props.documentableId }));
        documents.value = res.data.documents ?? [];
    } catch {
        documents.value = [];
    }
}

async function uploadFile(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    uploading.value = true;
    uploadError.value = '';

    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('documentable_type', props.documentableType);
    formData.append('documentable_id', props.documentableId);

    try {
        await axios.post('/documents', formData);
        input.value = '';
        await fetchDocuments();
    } catch (err: unknown) {
        if (axios.isAxiosError(err) && err.response?.data?.errors?.file) {
            uploadError.value = err.response.data.errors.file.join(', ');
        } else {
            uploadError.value = 'Upload failed.';
        }
    } finally {
        uploading.value = false;
    }
}

async function deleteDocument(doc: DocumentRecord): Promise<void> {
    if (!await confirm({ title: 'Delete document?', message: `Delete "${doc.name}"?` })) return;

    try {
        await axios.delete(`/documents/${doc.id}`);
        documents.value = documents.value.filter((d) => d.id !== doc.id);
    } catch {
        // ignore
    }
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

onMounted(fetchDocuments);
</script>

<template>
    <div>
        <h3 class="mb-4 text-lg font-medium text-gray-800">Documents</h3>

        <div class="mb-4">
            <label class="cursor-pointer rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">
                {{ uploading ? 'Uploading...' : 'Upload File' }}
                <input
                    type="file"
                    class="hidden"
                    :disabled="uploading"
                    @change="uploadFile"
                />
            </label>
            <p v-if="uploadError" class="mt-1 text-sm text-red-600">{{ uploadError }}</p>
        </div>

        <div v-if="documents.length === 0" class="text-sm text-gray-600">
            No documents uploaded yet.
        </div>

        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="doc in documents"
                :key="doc.id"
                class="flex items-center justify-between py-2"
            >
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                    <a
                        :href="doc.signed_download_url"
                        class="text-sm font-medium text-indigo-500 hover:text-indigo-600"
                    >
                            {{ doc.name }}
                        </a>
                        <p class="text-xs text-gray-600">{{ formatSize(doc.size) }}</p>
                    </div>
                </div>
                <button
                    class="text-sm text-red-600 hover:text-red-900"
                    @click="deleteDocument(doc)"
                >
                    Delete
                </button>
            </li>
        </ul>
    </div>
</template>
