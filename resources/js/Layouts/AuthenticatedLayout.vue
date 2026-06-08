<script setup>
import { computed, ref, onBeforeMount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const isSidebarOpen = ref(true);
const page = usePage();
const user = computed(() => page.props.auth.user);

const { currentTheme, themes, initTheme, switchTheme } = useTheme();

onBeforeMount(() => {
    initTheme();
});

const navItems = computed(() => {
    const items = [];

    if (user.value.role === 'admin') {
        items.push({ name: 'Dashboard', href: route('admin.dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' });
        items.push({ name: 'Analytics', href: route('admin.analytics'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' });
        items.push({ name: 'Orders', href: route('admin.orders'), icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' });
        items.push({ name: 'Products', href: route('admin.products'), icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' });
        items.push({ name: 'Admin Settings', href: route('admin.settings'), icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' });
        items.push({ name: 'Licenses', href: route('admin.licenses'), icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' });
    } else {
        items.push({ name: 'Dashboard', href: route('dashboard'), icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' });
        items.push({ name: 'Analytics', href: route('analytics'), icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' });
        items.push({ name: 'Licenses', href: route('licenses'), icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' });
        items.push({ name: 'Orders', href: route('orders'), icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' });
        items.push({ name: 'Account Settings', href: route('profile.edit'), icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' });
    }

    return items;
});

const isProfileDropdownOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-bg-dark flex font-sans text-text-secondary">
        <!-- Sidebar -->
        <aside 
            class="acrylic border-r border-white/5 transition-all duration-300 ease-in-out sticky top-0 h-screen overflow-y-auto z-20"
            :class="isSidebarOpen ? 'w-72' : 'w-20'"
        >
            <div class="p-8 flex items-center gap-4">
                <div class="w-10 h-10 bg-gradient-to-br from-brand-teal to-brand-blue rounded-2xl shadow-lg shadow-brand-teal/20 shrink-0 flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <h1 v-if="isSidebarOpen" class="font-black text-xl text-adaptive tracking-tighter uppercase truncate">CoreVi<span class="text-brand-teal">SYS</span></h1>
            </div>

            <nav class="mt-8 px-6 space-y-1">
                <Link 
                    v-for="item in navItems" 
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all group"
                    :class="$page.url === item.href 
                        ? 'bg-brand-teal/10 text-brand-teal shadow-glow-teal' 
                        : 'text-text-muted hover:bg-white/5 hover:text-adaptive'"
                >
                    <svg class="w-6 h-6 shrink-0 transition-colors" :class="$page.url === item.href ? 'text-brand-teal' : 'text-text-muted group-hover:text-text-secondary'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                    </svg>
                    <span v-if="isSidebarOpen" class="font-semibold text-sm tracking-tight truncate">
                        {{ item.name }}
                    </span>
                    <div v-if="$page.url === item.href && isSidebarOpen" class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-teal shadow-[0_0_8px_rgba(102,217,201,0.8)]"></div>
                </Link>
            </nav>

            <!-- Bottom Profile / Upgrade Banner Placeholder -->
            <div v-if="isSidebarOpen" class="absolute bottom-8 left-6 right-6 p-6 glass rounded-3xl border border-white/5">
                <p class="text-[10px] font-black text-brand-blue uppercase tracking-widest mb-2 text-center">Unlimited Access</p>
                <p class="text-[11px] text-text-muted text-center mb-4 leading-tight italic">Upgrade to Enterprise for dedicated support.</p>
                <button class="w-full py-2 bg-gradient-to-r from-brand-teal to-brand-blue text-slate-900 text-xs font-black rounded-xl shadow-lg hover:scale-[1.02] active:scale-95 transition-all">Upgrade Now</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 relative bg-bg-dark">
            <!-- Topbar -->
            <header class="h-20 acrylic flex items-center justify-between px-10 sticky top-0 z-10 border-b border-white/5">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2.5 hover:bg-white/5 rounded-xl text-text-muted transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-6">
                    <!-- Global Search Placeholder -->
                    <div class="hidden md:flex items-center px-4 py-2 glass rounded-2xl w-64 group focus-within:ring-2 focus-within:ring-brand-teal/20 transition-all">
                        <svg class="w-4 h-4 text-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" placeholder="Search anything..." class="bg-transparent border-none text-xs text-text-secondary focus:outline-none w-full placeholder:text-text-muted" />
                    </div>

                    <div class="flex items-center gap-4 relative">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-adaptive leading-none mb-1">{{ $page.props.auth.user.name }}</p>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-teal">{{ $page.props.auth.user.role || 'Member' }}</span>
                        </div>
                        
                        <div class="relative">
                            <button @click="isProfileDropdownOpen = !isProfileDropdownOpen" class="group flex items-center gap-2">
                                <div class="w-10 h-10 glass rounded-2xl p-0.5 overflow-hidden transition-all group-hover:border-brand-teal/50">
                                     <div class="w-full h-full bg-white/5 rounded-[14px]"></div>
                                </div>
                                <svg class="w-4 h-4 text-text-muted transition-transform" :class="{ 'rotate-180': isProfileDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Dropdown -->
                            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                <div v-if="isProfileDropdownOpen" class="absolute right-0 mt-4 w-56 acrylic rounded-3xl shadow-2xl border border-white/5 py-3 z-30">
                                    <div class="px-5 py-2 mb-2 border-b border-white/5">
                                        <p class="text-xs text-text-muted font-medium italic">Logged in as</p>
                                        <p class="text-xs font-bold text-adaptive truncate">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                    
                                    <!-- Theme Selection (Mini) -->
                                    <div class="px-5 py-2 border-b border-white/5">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-2">Theme</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            <button v-for="t in themes" :key="t.id"
                                                @click="switchTheme(t.id)"
                                                :title="t.name"
                                                class="w-8 h-8 rounded-full border-2 transition-all flex items-center justify-center"
                                                :class="currentTheme === t.id ? 'border-brand-teal scale-110 shadow-glow-teal' : 'border-slate-700 hover:border-slate-500'"
                                                :style="{
                                                    backgroundColor: t.id === 'light-modern' ? '#f8fafc' : 
                                                                     t.id === 'solarized-dark' ? '#002b36' : 
                                                                     t.id === 'tokyo-night' ? '#1a1b26' : '#0f111a'
                                                }"
                                            >
                                                <svg v-if="currentTheme === t.id" class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <Link :href="route('profile.edit')" class="flex items-center px-5 py-2 text-sm text-text-secondary hover:bg-white/5 hover:text-brand-teal transition-colors font-semibold">
                                        Account Settings
                                    </Link>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left flex items-center px-5 py-2 text-sm text-rose-400 hover:bg-rose-500/10 transition-colors font-semibold">
                                        Sign Out
                                    </Link>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-10 max-w-7xl mx-auto">
                <slot />
            </div>
        </main>
    </div>
</template>
