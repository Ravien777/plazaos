<script setup lang="ts">
defineProps<{
    team: { name: string };
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-stone-50">
        <div class="mx-4 w-full max-w-md text-center">
            <div class="rounded-lg bg-white p-8 shadow-lg">
                <h1 class="text-2xl font-bold text-stone-800">
                    Invitation Expired
                </h1>
                <p class="mt-4 text-stone-600">
                    The invitation to join <strong>{{ team.name }}</strong> has expired.
                    Ask your team owner to send a new invitation.
                </p>
                <a
                    :href="route('login')"
                    class="mt-6 inline-block rounded-md bg-stone-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-600"
                >
                    Go to Login
                </a>
            </div>
        </div>
    </div>
</template>
