<script setup>
defineProps({
    title: { type: String, required: true },
    updated: { type: String, default: '' },
    intro: { type: String, default: '' },
    sections: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="mx-auto max-w-3xl px-5 py-14">
        <p class="mb-3 font-mono text-[11.5px] uppercase tracking-[0.18em] text-amber">Legal</p>
        <h1 class="text-3xl font-semibold tracking-tight text-text-primary sm:text-4xl">{{ title }}</h1>
        <p v-if="updated" class="mt-3 font-mono text-[12px] text-text-muted">Last updated: {{ updated }}</p>
        <p v-if="intro" class="mt-4 text-sm leading-relaxed text-text-secondary sm:text-[15px]">{{ intro }}</p>

        <nav
            v-if="sections.length"
            aria-label="On this page"
            class="mt-8 rounded-[10px] border border-panel-line bg-panel-2 p-5"
        >
            <p class="mb-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted">On this page</p>
            <ul class="grid gap-1.5 sm:grid-cols-2">
                <li v-for="(section, index) in sections" :key="index">
                    <a :href="`#section-${index + 1}`" class="text-[13.5px] text-text-secondary hover:text-amber">{{ section.heading }}</a>
                </li>
            </ul>
        </nav>

        <div class="mt-10 space-y-8">
            <section
                v-for="(section, index) in sections"
                :key="index"
                :id="`section-${index + 1}`"
                class="scroll-mt-24"
            >
                <h2 class="text-base font-semibold text-text-primary">{{ index + 1 }}. {{ section.heading }}</h2>
                <p class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ section.body }}</p>
            </section>
        </div>
    </div>
</template>