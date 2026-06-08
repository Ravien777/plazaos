<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps<{
    title: string;
    canLogin?: boolean;
    canRegister?: boolean;
}>();

const scrolled = ref(false);

onMounted(() => {
    const onScroll = () => {
        scrolled.value = window.scrollY > 20;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen flex-col bg-white">
        <header
            class="fixed top-0 z-50 w-full transition-all duration-300"
            :class="scrolled ? 'border-b border-gray-200/80 bg-white/80 backdrop-blur-lg' : 'bg-transparent'"
        >
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2">
                    <svg viewBox="0 0 316 316" class="h-8 w-8 fill-current text-indigo-500" xmlns="http://www.w3.org/2000/svg">
                        <line x1="158" y1="70" x2="248" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <line x1="158" y1="70" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <line x1="248" y1="186" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                        <circle cx="158" cy="70" r="28" fill="currentColor" />
                        <circle cx="248" cy="186" r="28" fill="currentColor" />
                        <circle cx="68" cy="186" r="28" fill="currentColor" />
                    </svg>
                    <span class="text-xl font-bold text-gray-800">PlazaOS</span>
                </Link>

                <nav class="hidden items-center gap-8 sm:flex">
                    <Link
                        :href="route('features')"
                        class="text-sm font-medium text-gray-600 transition hover:text-indigo-500"
                        :class="{ 'text-indigo-500': $page.component === 'Features' }"
                    >
                        Features
                    </Link>
                    <Link
                        :href="route('about')"
                        class="text-sm font-medium text-gray-600 transition hover:text-indigo-500"
                        :class="{ 'text-indigo-500': $page.component === 'About' }"
                    >
                        About
                    </Link>
                </nav>

                <nav v-if="canLogin" class="flex items-center gap-4">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-700 transition hover:text-gray-800"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-gray-200">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg viewBox="0 0 316 316" class="h-5 w-5 fill-current text-indigo-500" xmlns="http://www.w3.org/2000/svg">
                            <line x1="158" y1="70" x2="248" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                            <line x1="158" y1="70" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                            <line x1="248" y1="186" x2="68" y2="186" stroke="currentColor" stroke-width="12" stroke-linecap="round" />
                            <circle cx="158" cy="70" r="28" fill="currentColor" />
                            <circle cx="248" cy="186" r="28" fill="currentColor" />
                            <circle cx="68" cy="186" r="28" fill="currentColor" />
                        </svg>
                        PlazaOS
                    </div>
                    <nav class="flex gap-6 text-sm text-gray-500">
                        <Link :href="route('features')" class="transition hover:text-gray-700">Features</Link>
                        <Link :href="route('about')" class="transition hover:text-gray-700">About</Link>
                    </nav>
                    <p class="text-sm text-gray-500">
                        &copy; {{ new Date().getFullYear() }} PlazaOS. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
