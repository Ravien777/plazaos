<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    errors?: Record<string, string>;
}>();

const usingRecovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function submit() {
    form.post(route('two-factor.login'));
}
</script>

<template>
    <GuestLayout>
        <Head title="Two-Factor Authentication" />

        <div class="mb-4 text-sm text-gray-600">
            <template v-if="!usingRecovery">
                Please enter the authentication code from your authenticator app.
            </template>
            <template v-else>
                Please enter one of your recovery codes.
            </template>
        </div>

        <form @submit.prevent="submit">
            <div v-if="!usingRecovery">
                <InputLabel for="code" value="Authentication code" />
                <TextInput
                    id="code"
                    type="text"
                    class="mt-1 block w-full"
                    inputmode="numeric"
                    autofocus
                    autocomplete="one-time-code"
                    v-model="form.code"
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Recovery code" />
                <TextInput
                    id="recovery_code"
                    type="text"
                    class="mt-1 block w-full"
                    autofocus
                    autocomplete="off"
                    v-model="form.recovery_code"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    class="text-sm text-gray-600 underline hover:text-gray-900"
                    @click="usingRecovery = !usingRecovery"
                >
                    <template v-if="!usingRecovery">
                        Use a recovery code
                    </template>
                    <template v-else>
                        Use an authentication code
                    </template>
                </button>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirm
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
