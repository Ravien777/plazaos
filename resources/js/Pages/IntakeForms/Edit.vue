<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface FieldType {
    value: string;
    label: string;
}

interface Field {
    id: string | null;
    label: string;
    field_type: string;
    required: boolean;
    options: string[];
    placeholder: string;
    sort_order: number;
}

interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    is_active: boolean;
    fields: Field[];
}

const props = defineProps<{
    form: IntakeForm;
    fieldTypes: FieldType[];
}>();

const editForm = useForm({
    title: props.form.title,
    description: props.form.description ?? '',
    is_active: props.form.is_active,
    fields: (props.form.fields ?? []).map((f) => ({
        id: f.id ?? null,
        label: f.label,
        field_type: f.field_type,
        required: f.required,
        options: f.options ?? [],
        placeholder: f.placeholder ?? '',
        sort_order: f.sort_order,
    })),
});

function addField(): void {
    editForm.fields.push({
        id: null,
        label: '',
        field_type: 'text',
        required: false,
        options: [],
        placeholder: '',
        sort_order: editForm.fields.length,
    });
}

function removeField(index: number): void {
    editForm.fields.splice(index, 1);
    editForm.fields.forEach((f, i) => (f.sort_order = i));
}

function moveField(index: number, direction: 'up' | 'down'): void {
    const target = direction === 'up' ? index - 1 : index + 1;
    if (target < 0 || target >= editForm.fields.length) return;
    [editForm.fields[index], editForm.fields[target]] = [editForm.fields[target], editForm.fields[index]];
    editForm.fields.forEach((f, i) => (f.sort_order = i));
}

function submit(): void {
    editForm.put(`/intake-forms/${props.form.id}`);
}

const fieldTypeSupportsOptions = (type: string) => ['select', 'multi_select'].includes(type);
</script>

<template>
    <Head :title="`Edit: ${form.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader :title="`Edit: ${form.title}`" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="editForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                <p v-if="editForm.errors.title" class="mt-1 text-xs text-red-600">{{ editForm.errors.title }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="editForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                <p v-if="editForm.errors.description" class="mt-1 text-xs text-red-600">{{ editForm.errors.description }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input v-model="editForm.is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    Active
                                </label>
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-gray-700">Form Fields</label>
                                    <button type="button" @click="addField" class="rounded-md bg-gray-800 px-3 py-1 text-xs font-semibold text-white hover:bg-gray-700">
                                        Add Field
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(field, index) in editForm.fields" :key="field.id ?? `new-${index}`" class="rounded-lg border border-gray-200 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-xs font-medium text-gray-500">Field {{ index + 1 }}</span>
                                        <div class="flex gap-1">
                                            <button type="button" @click="moveField(index, 'up')" :disabled="index === 0" class="rounded px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 disabled:opacity-30">&uarr;</button>
                                            <button type="button" @click="moveField(index, 'down')" :disabled="index === editForm.fields.length - 1" class="rounded px-2 py-1 text-xs text-gray-500 hover:bg-gray-100 disabled:opacity-30">&darr;</button>
                                            <button type="button" @click="removeField(index)" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50">Remove</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Label</label>
                                            <input v-model="field.label" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Type</label>
                                            <select v-model="field.field_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs">
                                                <option v-for="ft in fieldTypes" :key="ft.value" :value="ft.value">{{ ft.label }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="block text-xs font-medium text-gray-600">Placeholder</label>
                                        <input v-model="field.placeholder" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs" />
                                    </div>

                                    <div v-if="fieldTypeSupportsOptions(field.field_type)" class="mt-3">
                                        <label class="block text-xs font-medium text-gray-600">Options (one per line)</label>
                                        <textarea
                                            :value="field.options.join('\n')"
                                            @input="field.options = ($event.target as HTMLTextAreaElement).value.split('\n').filter(Boolean)"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs"
                                        />
                                    </div>

                                    <div class="mt-3">
                                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600">
                                            <input v-model="field.required" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                            Required
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div v-if="editForm.fields.length === 0" class="mb-4 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                                No fields yet. Click "Add Field" to start building your form.
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <Link href="/intake-forms" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="editForm.processing"
                                    class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 disabled:opacity-25"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
