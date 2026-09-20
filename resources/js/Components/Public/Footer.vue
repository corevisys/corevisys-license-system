<script setup>
import { Link } from '@inertiajs/vue3';
import { contact, site } from '@/content/public.js';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="relative border-t border-panel-line bg-panel">
        <span class="glow-line absolute inset-x-0 top-0 h-px" aria-hidden="true" />

        <div class="mx-auto grid max-w-6xl gap-10 px-5 py-14 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <Link href="/" class="flex items-center gap-2.5" aria-label="CoreVisys home">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary">
                        <svg class="h-5 w-5 text-bg-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </span>
                    <span class="text-sm font-semibold uppercase tracking-tight text-text-primary">Corevi<span class="text-brand-primary">SYS</span></span>
                </Link>
                <p class="mt-4 max-w-xs text-[13.5px] leading-relaxed text-text-secondary">{{ site.tagline }}</p>
            </div>

            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">Product</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('pricing')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Pricing</Link></li>
                    <li><Link :href="route('developers')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Developers</Link></li>
                    <li><Link :href="route('home')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Home</Link></li>
                </ul>
            </div>

            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">Support</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('contact')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Contact</Link></li>
                    <li><a :href="`mailto:${contact.supportEmail}`" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">{{ contact.supportEmail }}</a></li>
                </ul>
            </div>

            <div>
                <p class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">Legal</p>
                <ul class="mt-4 space-y-2">
                    <li><Link :href="route('privacy')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Privacy</Link></li>
                    <li><Link :href="route('terms')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Terms</Link></li>
                    <li><Link :href="route('cookies')" class="text-[13.5px] text-text-secondary transition-colors hover:text-brand-primary">Cookies</Link></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-panel-line">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-mono text-[12px] text-text-muted">© {{ year }} {{ site.name }}. All rights reserved.</p>
                <p class="font-mono text-[12px] text-text-muted">{{ site.product }}</p>
            </div>
        </div>
    </footer>
</template>
