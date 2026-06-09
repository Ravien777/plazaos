<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import type { Client } from '@/Types';

const clients = ref<Client[]>([]);
const templates = ref<any[]>([]);
const selectedTemplate = ref('');
const templatePreview = ref<any>(null);
const loadingPreview = ref(false);

const hasTasks = computed(() => (templatePreview.value?.tasks?.length ?? 0) > 0);

onMounted(async () => {
    try {
        const [clientsRes, templatesRes] = await Promise.all([
            axios.get('/api/clients'),
            axios.get('/api/project-templates'),
        ]);
        clients.value = clientsRes.data;
        templates.value = templatesRes.data;
    } catch {
        clients.value = [];
        templates.value = [];
    }
});

const form = useForm({
    client_id: '',
    name: '',
    description: '',
    status: 'discovery',
    budget: '',
    start_date: '',
    due_date: '',
    template_id: '',
});

async function onTemplateChange(): Promise<void> {
    if (!selectedTemplate.value) {
        templatePreview.value = null;
        form.template_id = '';
        return;
    }
    loadingPreview.value = true;
    form.template_id = selectedTemplate.value;
    try {
        const res = await axios.get(`/api/project-templates/${selectedTemplate.value}/preview`);
        templatePreview.value = res.data;
    } catch {
        templatePreview.value = null;
    } finally {
        loadingPreview.value = false;
    }
}

function submit(): void {
    form.post('/projects');
}
</script>

<template>
    <Head title="Create Project" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Create Project" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl">
                <div v-if="templates.length > 0" class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700">Start from Template</label>
                        <select
                            v-model="selectedTemplate"
                            class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                            @change="onTemplateChange"
                        >
                            <option value="">— No template —</option>
                            <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                                {{ tpl.template_name }} ({{ tpl.tasks_count }} tasks)
                            </option>
                        </select>

                        <div v-if="loadingPreview" class="mt-4 space-y-2">
                            <div v-for="n in 3" :key="n" class="h-4 animate-pulse rounded bg-gray-100" />
                        </div>

                        <div v-else-if="templatePreview" class="mt-4">
                            <p v-if="templatePreview.description" class="text-sm text-gray-600">{{ templatePreview.description }}</p>
                            <div v-if="hasTasks" class="mt-3">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tasks in this template</h4>
                                <ul class="mt-2 space-y-1">
                                    <li
                                        v-for="(task, idx) in templatePreview.tasks"
                                        :key="idx"
                                        class="flex items-center gap-2 text-sm text-gray-700"
                                    >
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        {{ task.title }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Client</label>
                                    <select
                                        v-model="form.client_id"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    >
                                        <option value="">Select a client</option>
                                        <option v-for="c in clients" :key="c.id" :value="c.id">
                                            {{ c.company_name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Project Name</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    >
                                        <option value="discovery">Discovery</option>
                                        <option value="design">Design</option>
                                        <option value="development">Development</option>
                                        <option value="testing">Testing</option>
                                        <option value="launch">Launch</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Budget</label>
                                    <input
                                        v-model="form.budget"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                                    <input
                                        v-model="form.due_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />
                            </div>
                            <div class="flex justify-end gap-3">
                                <Link
                                    href="/projects"
                                    class="inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600 disabled:opacity-25"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
