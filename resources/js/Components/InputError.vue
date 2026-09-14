<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-danger">
            {{ message }}
        </p>
    </div>
</template>
