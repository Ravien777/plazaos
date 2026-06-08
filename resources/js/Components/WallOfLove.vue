<script setup lang="ts">
import type { Testimonial } from '@/Types';

const props = defineProps<{
    testimonials: Testimonial[];
    removable?: boolean;
}>();

defineEmits<{
    (e: 'remove'): void;
}>();

function starString(rating: number): string {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
}

function copy(text: string | null): void {
    if (text) navigator.clipboard.writeText(text);
}
</script>

<template>
    <div class="group relative overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800">Wall of Love ❤️</h3>
                <button
                    v-if="removable"
                    type="button"
                    class="text-gray-300 opacity-0 transition hover:text-red-500 group-hover:opacity-100"
                    @click="$emit('remove')"
                    title="Remove widget"
                >
                    ✕
                </button>
            </div>
            <div v-if="testimonials.length === 0" class="mt-4 text-sm text-gray-600">No reviews yet.</div>
            <div v-else class="mt-4 space-y-3">
                <div v-for="t in testimonials" :key="t.id" class="rounded-md border border-gray-200 p-3">
                    <p class="text-lg text-yellow-500">{{ starString(t.rating) }}</p>
                    <p v-if="t.content" class="mt-1 text-sm text-gray-700">{{ t.content }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs text-gray-500">— {{ t.client?.company_name ?? 'Anonymous' }}</p>
                        <button
                            v-if="t.content"
                            @click="copy(t.content)"
                            class="rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-200"
                        >
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
