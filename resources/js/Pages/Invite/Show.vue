<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    token: string;
    team: { name: string };
    email: string;
}>();

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post(route('invitation.accept', props.token));
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-stone-50">
        <div class="mx-4 w-full max-w-md">
            <div class="rounded-lg bg-white p-8 shadow-lg">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-stone-800">
                        Join {{ team.name }}
                    </h1>
                    <p class="mt-2 text-stone-600">
                        You've been invited to join <strong>{{ team.name }}</strong> on PlazaOS.
                        Create your account to get started.
                    </p>
                    <p class="mt-1 text-xs text-stone-500">
                        Invited as {{ email }}
                    </p>
                </div>

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-stone-700">Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-stone-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                            />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full rounded-md border-stone-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                            />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-stone-700">
                                Confirm Password
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full rounded-md border-stone-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                            />
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex w-full justify-center rounded-md bg-stone-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-600 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Creating Account...' : 'Accept & Join' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
