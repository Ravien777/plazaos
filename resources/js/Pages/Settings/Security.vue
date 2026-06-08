<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';
import { usePasskeys, type Passkey } from '@/composables/usePasskeys';

const toast = useToast();

const props = defineProps<{
    twoFactor: {
        enabled: boolean;
        confirmed: boolean;
        qrCodeSvg?: string;
        recoveryCodes?: string[];
    };
    passkeys: Passkey[];
}>();

const confirmForm = useForm({ code: '' });
const confirming = ref(false);
const showingRecoveryCodes = ref(false);

const { registerPasskey, deletePasskey } = usePasskeys();

const registering = ref(false);
const passkeyName = ref('');
const showRegisterModal = ref(false);

async function handleRegister() {
    if (!passkeyName.value.trim()) return;
    registering.value = true;
    try {
        await registerPasskey(passkeyName.value.trim());
        showRegisterModal.value = false;
        passkeyName.value = '';
        window.location.reload();
    } catch {
        // error already handled in composable
    } finally {
        registering.value = false;
    }
}

async function handleDelete(passkey: Passkey) {
    if (!window.confirm(`Delete "${passkey.name}"? This action cannot be undone.`)) return;
    await deletePasskey(passkey.id);
    window.location.reload();
}

function enable() {
    confirmForm.post(route('two-factor.enable'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
        onError: () => {
            toast.error('Failed to enable two-factor authentication.');
        },
    });
}

function confirm() {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
            toast.success('Two-factor authentication is now active.');
        },
        onError: () => {
            toast.error('Invalid code. Please try again.');
        },
    });
}

function disable() {
    if (!window.confirm('Disable two-factor authentication? You will lose the extra security layer.')) return;

    confirmForm.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
            toast.success('Two-factor authentication disabled.');
        },
        onError: () => {
            toast.error('Failed to disable two-factor authentication.');
        },
    });
}

function regenerate() {
    confirmForm.post(route('two-factor.regenerate-recovery-codes'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
            toast.success('Recovery codes regenerated.');
        },
        onError: () => {
            toast.error('Failed to regenerate recovery codes.');
        },
    });
}
</script>

<template>
    <Head title="Security" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">
                Security Settings
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl space-y-6">
                <!-- Two-Factor Authentication -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Two-Factor Authentication
                        </h3>
                        <span
                            v-if="twoFactor.confirmed"
                            class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                        >
                            Active
                        </span>
                        <span
                            v-else-if="twoFactor.enabled"
                            class="ml-2 inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800"
                        >
                            Pending confirmation
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-600">
                        Add an extra layer of security to your account by requiring a one-time code from an authenticator app.
                    </p>

                    <!-- Disabled state -->
                    <div v-if="!twoFactor.enabled" class="mt-6">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600"
                            @click="enable"
                        >
                            Enable Two-Factor Authentication
                        </button>
                    </div>

                    <!-- Pending confirmation (show QR + recovery codes) -->
                    <div v-if="twoFactor.enabled && !twoFactor.confirmed && twoFactor.qrCodeSvg" class="mt-6 space-y-6">
                        <div>
                            <p class="mb-3 text-sm font-medium text-gray-700">
                                Scan this QR code with your authenticator app (e.g., Google Authenticator, Authy):
                            </p>
                            <div class="inline-block rounded-lg border bg-white p-4" v-html="twoFactor.qrCodeSvg"></div>
                        </div>

                        <div>
                            <button
                                type="button"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                                @click="showingRecoveryCodes = !showingRecoveryCodes"
                            >
                                {{ showingRecoveryCodes ? 'Hide' : 'Show' }} recovery codes
                            </button>
                            <div v-if="showingRecoveryCodes && twoFactor.recoveryCodes" class="mt-2 space-y-1">
                                <p class="text-xs text-gray-500">Save these codes somewhere safe. Each can only be used once.</p>
                                <ul class="mt-2 space-y-1">
                                    <li
                                        v-for="(code, i) in twoFactor.recoveryCodes"
                                        :key="i"
                                        class="font-mono text-sm text-gray-700"
                                    >
                                        {{ code }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm form -->
                        <div class="max-w-xs">
                            <InputLabel for="code" value="Confirm with code from app" />
                            <div class="mt-1 flex gap-2">
                                <TextInput
                                    id="code"
                                    type="text"
                                    class="block w-full"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    v-model="confirmForm.code"
                                />
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600 disabled:opacity-50"
                                    :disabled="confirmForm.processing"
                                    @click="confirm"
                                >
                                    Confirm
                                </button>
                            </div>
                            <InputError class="mt-2" :message="confirmForm.errors.code" />
                        </div>
                    </div>

                    <!-- Confirmed/Active state -->
                    <div v-if="twoFactor.confirmed" class="mt-6 space-y-4">
                        <div>
                            <button
                                type="button"
                                class="text-sm text-indigo-600 hover:text-indigo-800"
                                @click="showingRecoveryCodes = !showingRecoveryCodes"
                            >
                                {{ showingRecoveryCodes ? 'Hide' : 'Show' }} recovery codes
                            </button>
                            <div v-if="showingRecoveryCodes && twoFactor.recoveryCodes" class="mt-2 space-y-1">
                                <p class="text-xs text-gray-500">Save these codes somewhere safe. Each can only be used once.</p>
                                <ul class="mt-2 space-y-1">
                                    <li
                                        v-for="(code, i) in twoFactor.recoveryCodes"
                                        :key="i"
                                        class="font-mono text-sm text-gray-700"
                                    >
                                        {{ code }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                @click="regenerate"
                            >
                                Regenerate Recovery Codes
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 transition hover:bg-red-50"
                                @click="disable"
                            >
                                Disable
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Passkeys -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Passkeys
                        </h3>
                    </div>

                    <p class="mt-2 text-sm text-gray-600">
                        Use a passkey (Face ID, Touch ID, Windows Hello, or security key) to sign in quickly and securely — no password needed.
                    </p>

                    <div class="mt-6 space-y-4">
                        <div v-if="passkeys.length === 0" class="rounded-md bg-gray-50 p-4 text-sm text-gray-500">
                            You haven't registered any passkeys yet.
                        </div>

                        <div v-for="pk in passkeys" :key="pk.id" class="flex items-center justify-between rounded-md border border-gray-200 p-3">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ pk.name }}</p>
                                    <p class="text-xs text-gray-500">
                                        <template v-if="pk.authenticator">{{ pk.authenticator }} &middot; </template>
                                        Registered {{ pk.created_at }}
                                        <template v-if="pk.last_used_at"> &middot; Last used {{ pk.last_used_at }}</template>
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="text-sm text-red-600 hover:text-red-800"
                                @click="handleDelete(pk)"
                            >
                                Delete
                            </button>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600"
                            @click="showRegisterModal = true"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Register New Passkey
                        </button>
                    </div>

                    <!-- Register passkey modal -->
                    <div
                        v-if="showRegisterModal"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                        @click.self="showRegisterModal = false"
                    >
                        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                            <h4 class="mb-4 text-lg font-semibold text-gray-900">Register a New Passkey</h4>
                            <p class="mb-4 text-sm text-gray-600">
                                Give this passkey a name so you can recognise it later (e.g., "Work Laptop", "iPhone").
                            </p>
                            <InputLabel for="passkey-name" value="Passkey Name" />
                            <TextInput
                                id="passkey-name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="passkeyName"
                                placeholder="My passkey"
                                @keyup.enter="handleRegister"
                                autofocus
                            />
                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                    @click="showRegisterModal = false"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-600 disabled:opacity-50"
                                    :disabled="registering || !passkeyName.trim()"
                                    @click="handleRegister"
                                >
                                    {{ registering ? 'Registering...' : 'Register' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
