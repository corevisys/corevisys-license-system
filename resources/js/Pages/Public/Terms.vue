<script setup>
import PublicLayout from '@/Components/Public/PublicLayout.vue';
import LegalLayout from '@/Components/Public/LegalLayout.vue';
import { legal } from '@/content/public.js';

const doc = legal.terms;
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