<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    name: '',
});

function submit(): void {
    form.post(route('team.store'));
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-stone-50">
        <div class="mx-4 w-full max-w-md">
            <div class="rounded-lg bg-white p-8 shadow-lg">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-stone-800">
                        Welcome to PlazaOS!
                    </h1>
                    <p class="mt-2 text-stone-600">
                        Let's set up your team to get started.
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <label for="name" class="block text-sm font-medium text-stone-700">
                            What's your team called?
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Acme Corp"
                            class="mt-1 block w-full rounded-md border-stone-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full justify-center rounded-md bg-stone-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-600 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Team' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
