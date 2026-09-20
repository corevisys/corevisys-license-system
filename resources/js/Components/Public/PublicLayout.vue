<script setup>
import { onBeforeMount } from 'vue';
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/public/Navbar.vue';
import Footer from '@/Components/public/Footer.vue';
import { useTheme } from '@/Composables/useTheme.js';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});

const { initTheme } = useTheme();
onBeforeMount(initTheme);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-bg-dark font-sans text-text-primary">
        <Head :title="title">
            <meta v-if="description" name="description" :content="description" />
        </Head>

        <Navbar />

        <main class="flex-1">
            <slot />
        </main>

        <Footer />
    </div>
</template>