<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    submitted: boolean;
    testimonial: { id: string; review_token: string } | null;
}>();

const hoverRating = ref(0);
const selectedRating = ref(0);

const form = useForm({
    rating: 0,
    content: '',
});

function setRating(r: number): void {
    selectedRating.value = r;
    form.rating = r;
}

function submit(): void {
    if (!form.rating) return;
    form.post(`/review/${props.testimonial?.review_token}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Leave a Review" />

        <div v-if="submitted" class="text-center">
            <p class="text-4xl">🎉</p>
            <h2 class="mt-4 text-xl font-semibold text-gray-800">Thank You!</h2>
            <p class="mt-2 text-sm text-gray-600">We really appreciate your feedback.</p>
        </div>

        <div v-else>
            <div class="mb-6 text-center">
                <h2 class="text-xl font-semibold text-gray-800">How was your experience?</h2>
                <p class="mt-1 text-sm text-gray-600">Your feedback helps us improve.</p>
            </div>

            <form @submit.prevent="submit">
                <div class="text-center">
                    <label class="block text-sm font-medium text-gray-700">Rating</label>
                    <div class="mt-2 flex justify-center gap-1">
                        <button
                            v-for="i in 5"
                            :key="i"
                            type="button"
                            @click="setRating(i)"
                            @mouseenter="hoverRating = i"
                            @mouseleave="hoverRating = 0"
                            class="text-3xl transition-colors"
                            :class="(hoverRating || selectedRating) >= i ? 'text-yellow-500' : 'text-gray-300'"
                        >
                            ★
                        </button>
                    </div>
                    <p v-if="form.errors.rating" class="mt-1 text-sm text-red-600">{{ form.errors.rating }}</p>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Care to share more? <span class="text-gray-400">(optional)</span></label>
                    <textarea
                        v-model="form.content"
                        rows="4"
                        class="mt-1 block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-400 focus:ring-indigo-400 sm:text-sm"
                        placeholder="What did you enjoy most?"
                    />
                    <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                </div>

                <div class="mt-6">
                    <button
                        type="submit"
                        :disabled="form.processing || !selectedRating"
                        class="w-full rounded-md bg-gray-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-600 disabled:opacity-50"
                    >
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
