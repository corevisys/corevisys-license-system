<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import { site } from '@/content/public.js';

defineProps({
    message: { type: String, default: '' },
});

const links = [
    { label: 'Pricing', name: 'pricing' },
    { label: 'Developers', name: 'developers' },
    { label: 'Contact', name: 'contact' },
];
</script>

<template>
    <PublicLayout title="Page not found" :description="'The page you requested could not be found on ' + site.name + '.'">
        <section class="mx-auto flex max-w-6xl flex-col items-start px-5 py-20">
            <SectionHeading
                eyebrow="404"
                title="We could not find that page"
                :description="message || 'The link may be outdated, or the product you are looking for is no longer available.'"
            />

            <div class="mt-8 flex flex-wrap gap-3">
                <Link
                    :href="route('home')"
                    class="inline-flex items-center rounded-lg border border-transparent bg-amber px-[18px] py-[11px] text-[14.5px] font-medium text-[#1A1305] hover:bg-amber-hover"
                >Back to home</Link>
                <Link
                    :href="route('pricing')"
                    class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-primary hover:border-amber-dim"
                >See pricing</Link>
            </div>

            <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-2">
                <li v-for="link in links" :key="link.name">
                    <Link :href="route(link.name)" class="text-[13.5px] text-text-secondary hover:text-amber">
                        {{ link.label }}
                    </Link>
                </li>
            </ul>
        </section>
    </PublicLayout>
</template>