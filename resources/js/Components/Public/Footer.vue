<script setup>
import { Link } from '@inertiajs/vue3';
import { contact, site } from '@/content/public.js';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-panel-line bg-panel">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 py-12 sm:grid-cols-3">
            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Product</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('pricing')" class="text-[13.5px] text-text-secondary hover:text-amber">Pricing</Link></li>
                    <li><Link :href="route('developers')" class="text-[13.5px] text-text-secondary hover:text-amber">Developers</Link></li>
                    <li><Link :href="route('home')" class="text-[13.5px] text-text-secondary hover:text-amber">Home</Link></li>
                </ul>
            </div>

            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Support</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('contact')" class="text-[13.5px] text-text-secondary hover:text-amber">Contact</Link></li>
                    <li><a :href="`mailto:${contact.supportEmail}`" class="text-[13.5px] text-text-secondary hover:text-amber">{{ contact.supportEmail }}</a></li>
                </ul>
            </div>

            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Legal</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('privacy')" class="text-[13.5px] text-text-secondary hover:text-amber">Privacy</Link></li>
                    <li><Link :href="route('terms')" class="text-[13.5px] text-text-secondary hover:text-amber">Terms</Link></li>
                    <li><Link :href="route('cookies')" class="text-[13.5px] text-text-secondary hover:text-amber">Cookies</Link></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-panel-line">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-mono text-[12px] text-text-muted">© {{ year }} {{ site.name }}. All rights reserved.</p>
                <p class="font-mono text-[12px] text-text-muted">{{ site.tagline }}</p>
            </div>
        </div>
    </footer>
</template>