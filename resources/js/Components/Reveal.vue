<template>
  <div
    ref="target"
    :style="{
      opacity: isVisible ? 1 : 0,
      transform: getTransform(),
      transition: `opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)`,
      transitionDelay: `${delay}ms`
    }"
  >
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  delay: {
    type: Number,
    default: 0
  },
  direction: {
    type: String,
    default: 'up'
  }
});

const target = ref(null);
const isVisible = ref(false);

let observer = null;

onMounted(() => {
  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true;
        if (target.value) observer.unobserve(target.value);
      }
    },
    { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
  );

  if (target.value) observer.observe(target.value);
});

onUnmounted(() => {
  if (observer && target.value) {
    observer.unobserve(target.value);
  }
});

const getTransform = () => {
  if (isVisible.value) return 'translate(0, 0)';
  switch (props.direction) {
    case 'up': return 'translateY(40px)';
    case 'down': return 'translateY(-40px)';
    case 'left': return 'translateX(40px)';
    case 'right': return 'translateX(-40px)';
    default: return 'translateY(40px)';
  }
};
</script>
