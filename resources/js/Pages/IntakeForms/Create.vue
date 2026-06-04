<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface FieldType {
    value: string;
    label: string;
}

interface Field {
    id?: string;
    label: string;
    field_type: string;
    required: boolean;
    options: string[];
    placeholder: string;
    sort_order: number;
}

const props = defineProps<{
    fieldTypes: FieldType[];
}>();

const form = useForm({
    title: '',
    description: '',
    is_active: true,
    fields: [] as Field[],
});

function addField(): void {
    form.fields.push({
        label: '',
        field_type: 'text',
        required: false,
        options: [],
        placeholder: '',
        sort_order: form.fields.length,
    });
}

function removeField(index: number): void {
    form.fields.splice(index, 1);
    form.fields.forEach((f, i) => (f.sort_order = i));
}

function moveField(index: number, direction: 'up' | 'down'): void {
    const target = direction === 'up' ? index - 1 : index + 1;
    if (target < 0 || target >= form.fields.length) return;
    [form.fields[index], form.fields[target]] = [form.fields[target], form.fields[index]];
    form.fields.forEach((f, i) => (f.sort_order = i));
}

function submit(): void {
    form.post('/intake-forms');
}

const fieldTypeSupportsOptions = (type: string) => ['select', 'multi_select'].includes(type);
</script>

<template>
    <Head title="New Intake Form" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="New Intake Form" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm" />
                                <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input v-model="form.is_active" type="checkbox" class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400" />
                                    Active
                                </label>
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-gray-700">Form Fields</label>
                                    <button type="button" @click="addField" class="rounded-md bg-gray-700 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-600">
                                        Add Field
                                    </button>
                                </div>
                                <p v-if="form.errors['fields.0.label']" class="mt-1 text-xs text-red-600">{{ form.errors['fields.0.label'] }}</p>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(field, index) in form.fields" :key="index" class="rounded-lg border border-gray-200 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-xs font-medium text-gray-600">Field {{ index + 1 }}</span>
                                        <div class="flex gap-1">
                                            <button type="button" @click="moveField(index, 'up')" :disabled="index === 0" class="rounded px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 disabled:opacity-30">&uarr;</button>
                                            <button type="button" @click="moveField(index, 'down')" :disabled="index === form.fields.length - 1" class="rounded px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 disabled:opacity-30">&darr;</button>
                                            <button type="button" @click="removeField(index)" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50">Remove</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Label</label>
                                            <input v-model="field.label" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-xs" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Type</label>
                                            <select v-model="field.field_type" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-xs">
                                                <option v-for="ft in fieldTypes" :key="ft.value" :value="ft.value">{{ ft.label }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="block text-xs font-medium text-gray-600">Placeholder</label>
                                        <input v-model="field.placeholder" type="text" class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-xs" />
                                    </div>

                                    <div v-if="fieldTypeSupportsOptions(field.field_type)" class="mt-3">
                                        <label class="block text-xs font-medium text-gray-600">Options (one per line)</label>
                                        <textarea
                                            :value="field.options.join('\n')"
                                            @input="field.options = ($event.target as HTMLTextAreaElement).value.split('\n').filter(Boolean)"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-xs"
                                        />
                                    </div>

                                    <div class="mt-3">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                            <input v-model="field.required" type="checkbox" class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400" />
                                            Required
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.fields.length === 0" class="mb-4 rounded-lg border-2 border-dashed border-gray-200 p-8 text-center text-sm text-gray-600">
                                No fields yet. Click "Add Field" to start building your form.
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <Link href="/intake-forms" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
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
