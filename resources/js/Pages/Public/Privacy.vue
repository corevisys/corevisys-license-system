<script setup>
import PublicLayout from '@/Components/public/PublicLayout.vue';
import LegalLayout from '@/Components/public/LegalLayout.vue';
import { legal } from '@/content/public.js';

const doc = legal.privacy;
</script>

<template>
    <PublicLayout :title="doc.title">
        <LegalLayout
            :title="doc.title"
            :updated="doc.updated"
            :intro="doc.intro"
            :sections="doc.sections"
        />
    </PublicLayout>
</template>