<script setup lang="ts">
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface IntakeFormField {
    id: string;
    label: string;
    field_type: string;
    required: boolean;
    options: string[] | null;
    placeholder: string | null;
    sort_order: number;
}

interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    fields: IntakeFormField[];
}

const props = defineProps<{
    form: IntakeForm;
}>();

const intakeForm = useForm<Record<string, string>>({});

const fileInputs = new Map<string, File>();

function onFileChange(fieldId: string, event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        fileInputs.set(fieldId, file);
    }
}

function toggleMultiSelect(fieldId: string, opt: string, event: Event): void {
    const current: string[] = JSON.parse(intakeForm[fieldId] || '[]');
    const checked = (event.target as HTMLInputElement).checked;
    if (checked) {
        current.push(opt);
    } else {
        const idx = current.indexOf(opt);
        if (idx !== -1) current.splice(idx, 1);
    }
    intakeForm[fieldId] = JSON.stringify(current);
}

function isMultiSelectChecked(fieldId: string, opt: string): boolean {
    const current: string[] = JSON.parse(intakeForm[fieldId] || '[]');
    return current.includes(opt);
}

function submit(): void {
    if (fileInputs.size > 0) {
        intakeForm.transform(() => {
            const data: Record<string, unknown> = {};
            for (const key of Object.keys(intakeForm.data())) {
                data[`fields[${key}]`] = intakeForm[key];
            }
            for (const [fieldId, file] of fileInputs) {
                data[`fields[${fieldId}]`] = file;
            }
            return data;
        });
    }
    intakeForm.post(`/portal/intake-forms/${props.form.id}`);
}
</script>

<template>
    <Head :title="form.title" />

    <PortalLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">{{ form.title }}</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p v-if="form.description" class="mb-6 text-sm text-gray-600">{{ form.description }}</p>

                        <form @submit.prevent="submit">
                            <div v-for="field in props.form.fields" :key="field.id" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ field.label }}
                                    <span v-if="field.required" class="text-red-500">*</span>
                                </label>

                                <!-- Text -->
                                <input
                                    v-if="field.field_type === 'text' || field.field_type === 'email'"
                                    v-model="intakeForm[field.id]"
                                    :type="field.field_type === 'email' ? 'email' : 'text'"
                                    :placeholder="field.placeholder ?? undefined"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />

                                <!-- Textarea -->
                                <textarea
                                    v-else-if="field.field_type === 'textarea'"
                                    v-model="intakeForm[field.id]"
                                    :placeholder="field.placeholder ?? undefined"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />

                                <!-- Select -->
                                <select
                                    v-else-if="field.field_type === 'select'"
                                    v-model="intakeForm[field.id]"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                >
                                    <option value="" disabled>Select...</option>
                                    <option v-for="opt in field.options ?? []" :key="opt" :value="opt">{{ opt }}</option>
                                </select>

                                <!-- Multi Select -->
                                <div v-else-if="field.field_type === 'multi_select'" class="mt-2 space-y-1">
                                    <label v-for="opt in field.options ?? []" :key="opt" class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            :value="opt"
                                            :checked="isMultiSelectChecked(field.id, opt)"
                                            @change="toggleMultiSelect(field.id, opt, $event)"
                                            class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                        />
                                        {{ opt }}
                                    </label>
                                </div>

                                <!-- File -->
                                <input
                                    v-else-if="field.field_type === 'file'"
                                    type="file"
                                    @change="onFileChange(field.id, $event)"
                                    class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white file:transition hover:file:bg-gray-700"
                                />

                                <!-- Checkbox -->
                                <label v-else-if="field.field_type === 'checkbox'" class="mt-1 flex items-center gap-2 text-sm">
                                    <input
                                        v-model="intakeForm[field.id]"
                                        type="checkbox"
                                        :true-value="'1'"
                                        :false-value="'0'"
                                        class="rounded border-gray-200 text-indigo-500 shadow-sm focus:ring-indigo-400"
                                    />
                                    {{ field.placeholder || field.label }}
                                </label>

                                <!-- Date -->
                                <input
                                    v-else-if="field.field_type === 'date'"
                                    v-model="intakeForm[field.id]"
                                    type="date"
                                    class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />

                                <p v-if="intakeForm.errors[field.id]" class="mt-1 text-xs text-red-600">{{ intakeForm.errors[field.id] }}</p>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <Link href="/portal/intake-forms" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="intakeForm.processing"
                                    class="rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-25"
                                >
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
