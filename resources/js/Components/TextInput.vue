<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all shadow-lg shadow-black/5"
        v-model="model"
        ref="input"
    />
</template>
