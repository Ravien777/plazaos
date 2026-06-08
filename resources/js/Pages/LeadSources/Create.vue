<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    sourceTypes: { value: string; label: string }[];
}>();

const form = useForm({
    name: '',
    type: '',
    is_active: true,
    frequency: 'manual',
    config: '',
});

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            config: data.config ? JSON.parse(data.config as string) : null,
        }))
        .post('/lead-sources');
}
</script>

<template>
    <Head title="New Lead Source" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="New Lead Source" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-2xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select
                                    v-model="form.type"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                >
                                    <option value="" disabled>Select type...</option>
                                    <option v-for="t in sourceTypes" :key="t.value" :value="t.value">
                                        {{ t.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                    />
                                    Active
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                                <select
                                    v-model="form.frequency"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                >
                                    <option value="manual">Manual</option>
                                    <option value="hourly">Hourly</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                                <p v-if="form.errors.frequency" class="mt-1 text-xs text-red-600">{{ form.errors.frequency }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Configuration (JSON)</label>
                                <textarea
                                    v-model="form.config"
                                    rows="5"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                    placeholder='{"url": "https://example.com/leads.json"}'
                                ></textarea>
                                <p v-if="form.errors.config" class="mt-1 text-xs text-red-600">{{ form.errors.config }}</p>
                            </div>

                            <div class="flex justify-end gap-3">
                                <Link
                                    href="/lead-sources"
                                    class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-25"
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
