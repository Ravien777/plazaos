<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    preferences: Record<string, boolean | string | null>;
}>();

const slackEnabled = ref(props.preferences.slack_enabled === true);
const digestEnabled = ref(props.preferences.digest_enabled === true);
const digestTime = ref((props.preferences.digest_time as string) ?? '08:00');
const saving = ref(false);

watch(slackEnabled, save);
watch(digestEnabled, save);
watch(digestTime, save);

function save(): void {
    saving.value = true;
    router.put(route('settings.notifications'), {
        slack_enabled: slackEnabled.value,
        digest_enabled: digestEnabled.value,
        digest_time: digestEnabled.value ? digestTime.value : null,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
}
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-700">Notifications</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-8 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg sm:p-8">
                    <div class="space-y-8">
                        <!-- Slack -->
                        <div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Slack Notifications</h3>
                                    <p class="mt-1 text-sm text-gray-500">Send notifications to your Slack workspace.</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" v-model="slackEnabled" class="peer sr-only" />
                                    <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-blue-600 peer-checked:after:translate-x-full"></div>
                                </label>
                            </div>
                        </div>

                        <hr class="border-gray-200" />

                        <!-- Email Digest -->
                        <div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Daily Email Summary</h3>
                                    <p class="mt-1 text-sm text-gray-500">Receive a daily summary of activity at 8 AM.</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" v-model="digestEnabled" class="peer sr-only" />
                                    <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-blue-600 peer-checked:after:translate-x-full"></div>
                                </label>
                            </div>

                            <div v-if="digestEnabled" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Delivery Time</label>
                                <input
                                    v-model="digestTime"
                                    type="time"
                                    class="mt-1 block w-full max-w-xs rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <p v-if="saving" class="mt-4 text-right text-sm text-gray-400">Saving…</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
