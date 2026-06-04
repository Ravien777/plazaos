<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    error?: string;
}>();
</script>

<template>
    <GuestLayout>
        <Head title="Client Portal" />

        <div class="mb-6 text-center">
            <h2 class="text-xl font-semibold text-gray-800">Client Portal</h2>
            <p class="mt-1 text-sm text-gray-600">Access your project information and documents</p>
        </div>

        <div v-if="error" class="mb-4 text-sm font-medium text-red-600">
            {{ error }}
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="rounded-md border border-gray-200 bg-gray-50 p-6 text-center">
            <p class="text-sm text-gray-700">
                You've been sent a magic link to access your portal.
            </p>
            <p class="mt-2 text-sm text-gray-600">
                Check your email or contact your project manager for a new link.
            </p>
        </div>
    </GuestLayout>
</template>
