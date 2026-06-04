<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { EmailTemplate } from '@/Types';

const props = defineProps<{
    template: EmailTemplate;
}>();

const form = useForm({
    key: props.template.key,
    name: props.template.name,
    subject: props.template.subject,
    body: props.template.body,
    variables: [...(props.template.variables ?? [])],
});

const newVariable = ref('');

function addVariable(): void {
    const val = newVariable.value.trim();
    if (!val) return;
    if (form.variables.includes(val)) return;
    form.variables.push(val);
    newVariable.value = '';
}

function removeVariable(index: number): void {
    form.variables.splice(index, 1);
}

function submit(): void {
    form.put(route('templates.update', props.template.id));
}
</script>

<template>
    <Head title="Edit Template" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Edit Email Template" />
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="p-6" @submit.prevent="submit">
                        <div class="mb-6">
                            <InputLabel for="key" value="Key" />
                            <TextInput id="key" v-model="form.key" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.key" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="name" value="Name" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="subject" value="Subject" />
                            <TextInput id="subject" v-model="form.subject" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.subject" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel for="body" value="Body" />
                            <textarea
                                id="body"
                                v-model="form.body"
                                rows="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                            <InputError :message="form.errors.body" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <InputLabel value="Variables" />
                            <div class="mt-1 flex flex-wrap gap-2">
                                <span
                                    v-for="(v, i) in form.variables"
                                    :key="i"
                                    class="inline-flex items-center rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700"
                                >
                                    {{ v }}
                                    <button type="button" class="ml-1 text-indigo-500 hover:text-indigo-700" @click="removeVariable(i)">&times;</button>
                                </span>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <input
                                    v-model="newVariable"
                                    type="text"
                                    class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="variable_name"
                                    @keydown.enter.prevent="addVariable"
                                />
                                <button
                                    type="button"
                                    class="rounded-md bg-gray-100 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200"
                                    @click="addVariable"
                                >
                                    Add
                                </button>
                            </div>
                            <InputError :message="form.errors.variables" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link :href="route('templates.index')" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Update Template
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
