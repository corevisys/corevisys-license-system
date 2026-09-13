<script setup>
import { onBeforeMount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import { Code as CodeIcon } from 'lucide-vue-next';

const { initTheme } = useTheme();

onBeforeMount(() => {
    initTheme();
});
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-bg-dark font-sans text-text-primary">
        <div class="mb-8 flex flex-col items-center">
            <Link href="/" class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-600 flex items-center justify-center rounded-2xl shadow-lg shadow-brand-teal/20">
                    <CodeIcon class="text-white w-6 h-6" />
                </div>
            </Link>
            <h1 class="mt-4 text-2xl font-black text-text-primary tracking-tight">Corevisys</h1>
        </div>

        <div class="w-full sm:max-w-md acrylic shadow-soft-md rounded-3xl p-8">
            <slot />
        </div>
        
        <p class="mt-8 text-sm text-text-muted">Secure • Enterprise • Automated</p>
    </div>
</template>
