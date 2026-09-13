<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">
            {{ message }}
        </p>
    </div>
</template>
