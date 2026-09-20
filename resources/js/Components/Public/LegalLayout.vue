<script setup>
import { computed } from 'vue';
import PageToc from '@/Components/public/PageToc.vue';

const props = defineProps({
    title: { type: String, required: true },
    updated: { type: String, default: '' },
    intro: { type: String, default: '' },
    sections: { type: Array, default: () => [] },
});

const toc = computed(() =>
    props.sections.map((section, index) => ({
        id: `section-${index + 1}`,
        label: section.heading,
    })),
);

// The stored copy uses "[placeholder]" markers until the legal text is finalised.
const isDraft = computed(() => {
    if (props.updated === '[placeholder]') return true;
    return props.sections.some((section) => String(section.body).includes('[placeholder]'));
});
</script>

<template>
    <div class="mx-auto max-w-5xl px-5 py-14">
        <div class="max-w-3xl">
            <p class="mb-3 font-mono text-xs uppercase tracking-widest text-brand-primary">Legal</p>
            <h1 class="text-3xl font-semibold tracking-tight text-text-primary sm:text-4xl">{{ title }}</h1>
            <p v-if="updated" class="mt-3 font-mono text-[12px] text-text-secondary">Last updated: {{ updated }}</p>
            <p v-if="intro" class="mt-4 text-base leading-relaxed text-text-secondary">{{ intro }}</p>

            <div
                v-if="isDraft"
                class="mt-6 flex items-start gap-3 rounded-2xl border border-brand-primary/40 bg-brand-primary/10 p-4"
                role="note"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                <p class="text-sm leading-relaxed text-text-secondary">
                    <span class="font-semibold text-text-primary">Draft.</span>
                    This document is a working draft. Items marked
                    <span class="font-mono text-[12px] text-brand-primary">[placeholder]</span>
                    are not final and will be completed before publication.
                </p>
            </div>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[220px_1fr]">
            <aside v-if="sections.length" class="hidden lg:block">
                <PageToc :items="toc" />
            </aside>

            <div class="min-w-0 max-w-3xl space-y-8">
                <section
                    v-for="(section, index) in sections"
                    :key="index"
                    :id="`section-${index + 1}`"
                    class="scroll-mt-24"
                >
                    <h2 class="text-base font-semibold text-text-primary">
                        <a :href="`#section-${index + 1}`" class="group inline-flex items-baseline gap-2">
                            <span>{{ index + 1 }}. {{ section.heading }}</span>
                            <span class="font-mono text-[12px] text-text-muted opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true">#</span>
                        </a>
                    </h2>
                    <p class="mt-2 text-base leading-relaxed text-text-secondary">{{ section.body }}</p>
                </section>
            </div>
        </div>
    </div>
</template>