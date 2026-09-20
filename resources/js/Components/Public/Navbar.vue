<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { nav, site } from '@/content/public.js';
import { useTheme } from '@/Composables/useTheme.js';

const page = usePage();
const { currentTheme, themes, switchTheme } = useTheme();

const isOpen = ref(false);
const themeOpen = ref(false);

const isAuthed = computed(() => Boolean(page.props.auth?.user));
const ctaHref = computed(() => (isAuthed.value ? route('dashboard') : route('register')));
const ctaLabel = computed(() => (isAuthed.value ? 'Dashboard' : 'Get started'));

const currentPath = computed(() => (page.url || '/').split('?')[0]);

const isActive = (name) => {
    const path = route(name, undefined, false);
    if (path === '/') return currentPath.value === '/';
    return currentPath.value === path || currentPath.value.startsWith(`${path}/`);
};

const swatch = (id) => ({
    terminal: '#100e0c',
    'light-modern': '#f8fafc',
    'solarized-dark': '#002b36',
    'tokyo-night': '#1a1b26',
    'dark-modern': '#0f172a',
}[id] || '#0f172a');
</script>

<template>
    <header class="acrylic sticky top-0 z-40 border-b border-panel-line">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5">
            <Link href="/" class="flex items-center gap-2.5" aria-label="CoreVisys home">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-primary">
                    <svg class="h-5 w-5 text-bg-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </span>
                <span class="text-sm font-semibold uppercase tracking-tight text-text-primary">Corevi<span class="text-brand-primary">SYS</span></span>
            </Link>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Primary">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="relative rounded-lg px-3 py-2 text-[13.5px] transition-colors"
                    :class="isActive(item.route)
                        ? 'text-text-primary'
                        : 'text-text-secondary hover:bg-panel-2 hover:text-text-primary'"
                >
                    {{ item.label }}
                    <span
                        v-if="isActive(item.route)"
                        class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-brand-primary"
                        aria-hidden="true"
                    />
                </Link>
            </nav>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-panel-line text-text-muted transition-colors hover:border-brand-primary/40 hover:text-brand-primary"
                        aria-label="Change theme"
                        :aria-expanded="themeOpen"
                        @click="themeOpen = !themeOpen"
                    >
                        <span class="h-4 w-4 rounded-full border border-panel-line" :style="{ backgroundColor: swatch(currentTheme) }" aria-hidden="true" />
                    </button>

                    <div v-if="themeOpen" class="absolute right-0 z-50 mt-2 w-60 rounded-xl border border-panel-line bg-panel p-3 shadow-soft-md">
                        <p class="mb-2 font-mono text-[10px] uppercase tracking-wide text-text-muted">Theme</p>
                        <div class="grid grid-cols-5 gap-2">
                            <button
                                v-for="t in themes"
                                :key="t.id"
                                type="button"
                                :title="t.name"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border transition-colors"
                                :class="currentTheme === t.id ? 'border-brand-primary text-brand-primary' : 'border-panel-line text-text-muted hover:border-brand-primary/40'"
                                :style="{ backgroundColor: swatch(t.id) }"
                                @click="switchTheme(t.id)"
                            >
                                <svg v-if="currentTheme === t.id" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <Link
                    v-if="!isAuthed"
                    :href="route('login')"
                    class="hidden rounded-lg px-3 py-2 text-[13.5px] text-text-secondary transition-colors hover:text-text-primary md:inline-flex"
                >Login</Link>

                <Link
                    :href="ctaHref"
                    class="inline-flex items-center rounded-lg border border-transparent bg-brand-primary px-4 py-2 text-[13.5px] font-medium text-bg-dark transition-transform hover:scale-[1.03] motion-reduce:hover:scale-100"
                >{{ ctaLabel }}</Link>

                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-panel-line text-text-secondary transition-colors hover:text-brand-primary md:hidden"
                    aria-label="Toggle menu"
                    :aria-expanded="isOpen"
                    @click="isOpen = !isOpen"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>

        <div v-if="isOpen" class="border-t border-panel-line bg-panel md:hidden">
            <nav class="mx-auto max-w-6xl px-5 py-3" aria-label="Mobile">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="block rounded-lg px-3 py-2.5 text-sm transition-colors"
                    :class="isActive(item.route)
                        ? 'bg-panel-2 text-brand-primary'
                        : 'text-text-secondary hover:bg-panel-2 hover:text-text-primary'"
                    @click="isOpen = false"
                >{{ item.label }}</Link>
                <Link
                    v-if="!isAuthed"
                    :href="route('login')"
                    class="block rounded-lg px-3 py-2.5 text-sm text-text-secondary transition-colors hover:bg-panel-2 hover:text-text-primary"
                    @click="isOpen = false"
                >Login</Link>
            </nav>
        </div>
    </header>
</template>
