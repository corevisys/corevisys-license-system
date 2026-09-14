<script setup>
import { onBeforeMount } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Code as CodeIcon } from 'lucide-vue-next';
import { useTheme } from '@/Composables/useTheme';
import Card from '@/Components/UI/Card.vue';

const { initTheme } = useTheme();

onBeforeMount(initTheme);
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-bg-dark font-sans text-text-primary">
        <div class="mb-8 flex flex-col items-center">
            <Link href="/" class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber flex items-center justify-center rounded-lg">
                    <CodeIcon class="text-[#1A1305] w-6 h-6" />
                </div>
            </Link>
            <h1 class="mt-4 text-2xl font-semibold text-text-primary tracking-tight">Corevisys</h1>
        </div>

        <Card class="w-full sm:max-w-md">
            <slot />
        </Card>

        <p class="mt-8 font-mono text-[11.5px] text-text-muted">Secure · Enterprise · Automated</p>
    </div>
</template>
