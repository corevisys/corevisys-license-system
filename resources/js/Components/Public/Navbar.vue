<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Code2, Menu, X } from 'lucide-vue-next';
import Button from '@/Components/UI/Button.vue';

const isOpen = ref(false);
const page = usePage();
const navItems = [
    { name: 'Home', href: '/', active: () => page.url === '/' },
    { name: 'Services', href: '/#services', active: () => page.url.startsWith('/services') },
    { name: 'Portfolio', href: '/#portfolio', active: () => page.url.startsWith('/case-study') },
    { name: 'Company', href: route('company'), active: () => route().current('company') },
    { name: 'Insights', href: route('insights'), active: () => route().current('insights') },
];
const activeNavItems = computed(() => navItems.map((item) => ({ ...item, active: item.active() })));
const visitContact = () => router.visit(route('contact'));
</script>

<template>
    <nav class="sticky top-0 z-50 bg-panel border-b border-panel-line">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between gap-6">
            <Link href="/" class="flex items-center gap-2 text-text-primary">
                <span class="w-8 h-8 rounded-lg bg-amber text-[#1A1305] flex items-center justify-center"><Code2 class="w-4 h-4" /></span>
                <span class="font-semibold tracking-tight">CoreVisys</span>
            </Link>
            <div class="hidden md:flex items-center gap-6">
                <Link v-for="item in activeNavItems" :key="item.name" :href="item.href" class="text-sm transition-colors" :class="item.active ? 'text-amber' : 'text-text-secondary hover:text-amber'">{{ item.name }}</Link>
            </div>
            <div class="hidden md:flex items-center gap-4">
                <Link :href="route('login')" class="text-sm text-text-secondary hover:text-amber transition-colors">Sign in</Link>
                <Button type="button" @click="visitContact">Book consultation</Button>
            </div>
            <button class="md:hidden p-2 text-text-secondary hover:text-amber transition-colors" @click="isOpen = !isOpen" aria-label="Toggle navigation">
                <X v-if="isOpen" class="w-5 h-5" />
                <Menu v-else class="w-5 h-5" />
            </button>
        </div>
        <div v-if="isOpen" class="md:hidden border-t border-panel-line bg-panel px-6 py-4 space-y-4">
            <Link v-for="item in activeNavItems" :key="item.name" :href="item.href" class="block text-sm" :class="item.active ? 'text-amber' : 'text-text-secondary hover:text-amber'" @click="isOpen = false">{{ item.name }}</Link>
            <Link :href="route('login')" class="block text-sm text-text-secondary hover:text-amber" @click="isOpen = false">Sign in</Link>
            <Button class="w-full" type="button" @click="visitContact">Book consultation</Button>
        </div>
    </nav>
</template>
