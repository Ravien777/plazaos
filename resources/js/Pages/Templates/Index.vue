<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import type { EmailTemplate } from '@/Types';

const props = defineProps<{
    templates: EmailTemplate[];
}>();

function destroy(id: string): void {
    if (confirm('Delete this template?')) {
        router.delete(`/templates/${id}`);
    }
}
</script>

<template>
    <Head title="Email Templates" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader title="Email Templates">
                <template #actions>
                    <Link
                        :href="route('templates.create')"
                        class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-600"
                    >
                        New Template
                    </Link>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="templates.length === 0" class="py-6 text-center text-sm text-gray-600">
                            No templates yet.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Key</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-600">Subject</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="tpl in templates" :key="tpl.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-600">{{ tpl.key }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">{{ tpl.name }}</td>
                                        <td class="max-w-md truncate px-6 py-4 text-sm text-gray-700">{{ tpl.subject }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                            <Link
                                                :href="`/templates/${tpl.id}/edit`"
                                                class="mr-3 text-indigo-500 hover:text-indigo-600"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                class="text-red-600 hover:text-red-900"
                                                @click="destroy(tpl.id!)"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
