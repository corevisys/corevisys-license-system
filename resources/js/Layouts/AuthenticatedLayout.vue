<script setup>
import { computed, onBeforeMount, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';
import Button from '@/Components/UI/Button.vue';

const isSidebarOpen = ref(true);
const isProfileDropdownOpen = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const { currentTheme, themes, initTheme, switchTheme } = useTheme();

onBeforeMount(initTheme);

const navItems = computed(() => user.value.role === 'admin'
    ? [
        { name: 'Dashboard', href: route('admin.dashboard'), icon: 'M3 12l2-2 7-7 7 7M5 10v10h4v-6h6v6h4V10' },
        { name: 'Analytics', href: route('admin.analytics'), icon: 'M4 19v-6h4v6m4 0V5h4v14m4 0V9h-4v10' },
        { name: 'Orders', href: route('admin.orders'), icon: 'M6 4h12v16H6zM9 8h6m-6 4h6m-6 4h4' },
        { name: 'Products', href: route('admin.products'), icon: 'm20 7-8-4-8 4 8 4 8-4Zm-16 0v10l8 4 8-4V7M12 11v10' },
        { name: 'Admin Settings', href: route('admin.settings'), icon: 'M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0-12v2m0 13v2m8.5-8.5h-2m-13 0h-2m14.5-6-1.4 1.4m-10.2 10.2L4.6 19m14.8 0-1.4-1.4M5.9 5.9 4.6 4.6' },
        { name: 'Licenses', href: route('admin.licenses'), icon: 'M6 3h8l4 4v14H6zM9 12h6m-6 4h6' },
    ]
    : [
        { name: 'Dashboard', href: route('dashboard'), icon: 'M3 12l2-2 7-7 7 7M5 10v10h4v-6h6v6h4V10' },
        { name: 'Analytics', href: route('analytics'), icon: 'M4 19v-6h4v6m4 0V5h4v14m4 0V9h-4v10' },
        { name: 'Licenses', href: route('licenses'), icon: 'M6 3h8l4 4v14H6zM9 12h6m-6 4h6' },
        { name: 'Store', href: route('store'), icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5m1.6 8-2.3 2.3A1 1 0 0 0 5.4 17H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-10 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z' },
        { name: 'Orders', href: route('orders'), icon: 'M8 7V5a4 4 0 0 1 8 0v2m-11 0h14l1 13H4L5 7Zm0 4h14' },
        { name: 'Account Settings', href: route('profile.edit'), icon: 'M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0-12v2m0 13v2m8.5-8.5h-2m-13 0h-2m14.5-6-1.4 1.4m-10.2 10.2L4.6 19m14.8 0-1.4-1.4M5.9 5.9 4.6 4.6' },
    ]);
</script>

<template>
    <div class="min-h-screen bg-bg-dark flex font-sans text-text-secondary">
        <aside class="bg-panel border-r border-panel-line transition-all duration-300 ease-in-out sticky top-0 h-screen overflow-y-auto z-20" :class="isSidebarOpen ? 'w-72' : 'w-20'">
            <div class="p-8 flex items-center gap-4">
                <div class="w-10 h-10 bg-amber rounded-lg shrink-0 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#1A1305]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h1 v-if="isSidebarOpen" class="font-semibold text-xl text-text-primary tracking-tight uppercase truncate">CoreVi<span class="text-amber">SYS</span></h1>
            </div>

            <nav class="mt-4 px-4 space-y-1">
                <Link v-for="item in navItems" :key="item.name" :href="item.href" class="flex items-center gap-3 px-3 py-3 rounded-lg border-l-2 transition-colors" :class="$page.url === item.href ? 'bg-panel-2 text-amber border-amber' : 'text-text-secondary border-transparent hover:bg-panel-2 hover:text-amber'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" /></svg>
                    <span v-if="isSidebarOpen" class="font-medium text-sm truncate">{{ item.name }}</span>
                </Link>
            </nav>

            <div v-if="isSidebarOpen" class="absolute bottom-6 left-4 right-4 p-4 bg-panel-2 border border-panel-line rounded-lg">
                <p class="text-[11px] font-mono text-amber uppercase tracking-wide mb-2">Unlimited Access</p>
                <p class="text-xs text-text-muted mb-4 leading-relaxed">Upgrade to Enterprise for dedicated support.</p>
                <Button class="w-full" type="button">Upgrade now</Button>
            </div>
        </aside>

        <main class="flex-1 min-w-0 relative bg-bg-dark">
            <header class="h-20 bg-panel border-b border-panel-line flex items-center justify-between px-6 sm:px-10 sticky top-0 z-10">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 rounded-lg text-text-secondary hover:bg-panel-2 hover:text-amber transition-colors" aria-label="Toggle sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>

                <div class="flex items-center gap-4 sm:gap-6">
                    <div class="hidden md:flex items-center px-3 py-2 bg-panel-2 border border-panel-line rounded-lg w-64 focus-within:border-amber-dim transition-colors">
                        <svg class="w-4 h-4 text-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" placeholder="Search anything..." class="bg-transparent border-none text-xs text-text-primary focus:outline-none w-full placeholder:text-text-muted" />
                    </div>

                    <div class="flex items-center gap-3 relative">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-text-primary leading-none mb-1">{{ $page.props.auth.user.name }}</p>
                            <span class="text-[10px] font-mono uppercase tracking-wide text-amber">{{ $page.props.auth.user.role || 'Member' }}</span>
                        </div>

                        <div class="relative">
                            <button @click="isProfileDropdownOpen = !isProfileDropdownOpen" class="group flex items-center gap-2" aria-label="Open profile menu">
                                <div class="w-10 h-10 bg-panel-2 border border-panel-line rounded-lg flex items-center justify-center text-text-muted group-hover:border-amber-dim group-hover:text-amber transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A9 9 0 1118.88 17.804M15 11a3 3 0 10-6 0 3 3 0 006 0zm2.5 7.5a6.5 6.5 0 00-11 0" /></svg>
                                </div>
                                <svg class="w-4 h-4 text-text-muted transition-transform" :class="{ 'rotate-180': isProfileDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div v-if="isProfileDropdownOpen" class="absolute right-0 mt-3 w-60 bg-panel border border-panel-line rounded-lg py-2 z-30">
                                <div class="px-4 py-2 mb-2 border-b border-panel-line">
                                    <p class="text-[11px] font-mono text-text-muted">Logged in as</p>
                                    <p class="text-xs font-medium text-text-primary truncate">{{ $page.props.auth.user.email }}</p>
                                </div>
                                <div class="px-4 py-2 border-b border-panel-line">
                                    <p class="text-[10px] font-mono uppercase tracking-wide text-text-muted mb-2">Theme</p>
                                    <div class="grid grid-cols-5 gap-2">
                                        <button v-for="t in themes" :key="t.id" @click="switchTheme(t.id)" :title="t.name" class="w-8 h-8 rounded-lg border flex items-center justify-center transition-colors" :class="currentTheme === t.id ? 'border-amber text-amber' : 'border-panel-line text-text-muted hover:border-amber-dim'" :style="{ backgroundColor: t.id === 'terminal' ? '#100e0c' : t.id === 'light-modern' ? '#f8fafc' : t.id === 'solarized-dark' ? '#002b36' : t.id === 'tokyo-night' ? '#1a1b26' : '#0f172a' }">
                                            <svg v-if="currentTheme === t.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-text-secondary hover:bg-panel-2 hover:text-amber transition-colors">Account settings</Link>
                                <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center px-4 py-2 text-sm text-danger hover:bg-danger/8 transition-colors">Sign out</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-10 max-w-7xl mx-auto"><slot /></div>
        </main>
    </div>
</template>
