<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = withDefaults(defineProps<{
  delay?: number;
  duration?: number;
  distance?: string;
}>(), {
  delay: 0,
  duration: 700,
  distance: '2rem',
});

const el = ref<HTMLElement | null>(null);
const isVisible = ref(false);

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true;
        observer.unobserve(entry.target);
      }
    },
    { threshold: 0.1 },
  );

  if (el.value) observer.observe(el.value);
});
</script>

<template>
  <div
    ref="el"
    class="scroll-reveal"
    :class="{ 'scroll-reveal-visible': isVisible }"
    :style="{
      transitionDuration: `${duration}ms`,
      transitionDelay: `${delay}ms`,
      transform: isVisible ? 'translateY(0)' : `translateY(${distance})`,
    }"
  >
    <slot />
  </div>
</template>
