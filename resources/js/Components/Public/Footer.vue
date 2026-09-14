<script setup>
import { Link } from '@inertiajs/vue3';
import { Code2, Mail, MapPin, Phone } from 'lucide-vue-next';

const serviceLinks = [
    { label: 'Custom Software', href: '/services/custom-software' },
    { label: 'Web Applications', href: '/services/web-applications' },
    { label: 'Mobile App Dev', href: '/services/mobile-apps' },
    { label: 'AI & Machine Learning', href: '/services/ai-ml' },
    { label: 'Cloud Architecture', href: '/services/cloud-solutions' },
];
const companyLinks = [
    { label: 'About Us', href: route('company') }, { label: 'Case Studies', href: route('case-study') }, { label: 'Careers', href: route('careers') }, { label: 'Insights', href: route('insights') }, { label: 'Contact', href: route('contact') },
];
const legalLinks = [
    { label: 'Privacy Policy', href: route('privacy-policy') }, { label: 'Terms of Service', href: route('terms-of-service') }, { label: 'Cookie Policy', href: route('cookie-policy') },
];
</script>

<template>
    <footer class="bg-panel border-t border-panel-line text-text-secondary">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12">
                <div class="lg:col-span-2">
                    <Link href="/" class="flex items-center gap-2 mb-5 text-text-primary"><span class="w-8 h-8 rounded-lg bg-amber text-[#1A1305] flex items-center justify-center"><Code2 class="w-4 h-4" /></span><span class="font-semibold tracking-tight">CoreVisys</span></Link>
                    <p class="text-sm leading-relaxed max-w-xs">A premium software development agency building enterprise-grade digital products for modern companies.</p>
                    <div class="flex gap-3 mt-6">
                        <a href="mailto:hello@corevisys.com" class="w-9 h-9 rounded-lg border border-panel-line flex items-center justify-center hover:border-amber hover:text-amber transition-colors" aria-label="Email CoreVisys"><Mail class="w-4 h-4" /></a>
                        <a href="tel:+880000000000" class="w-9 h-9 rounded-lg border border-panel-line flex items-center justify-center hover:border-amber hover:text-amber transition-colors" aria-label="Call CoreVisys"><Phone class="w-4 h-4" /></a>
                        <Link :href="route('contact')" class="w-9 h-9 rounded-lg border border-panel-line flex items-center justify-center hover:border-amber hover:text-amber transition-colors" aria-label="Contact CoreVisys"><MapPin class="w-4 h-4" /></Link>
                    </div>
                </div>
                <div><h2 class="font-mono text-[11.5px] uppercase tracking-wide text-text-primary mb-4">Services</h2><ul class="space-y-3 text-sm"><li v-for="item in serviceLinks" :key="item.label"><Link :href="item.href" class="hover:text-amber transition-colors">{{ item.label }}</Link></li></ul></div>
                <div><h2 class="font-mono text-[11.5px] uppercase tracking-wide text-text-primary mb-4">Company</h2><ul class="space-y-3 text-sm"><li v-for="item in companyLinks" :key="item.label"><Link :href="item.href" class="hover:text-amber transition-colors">{{ item.label }}</Link></li></ul></div>
                <div><h2 class="font-mono text-[11.5px] uppercase tracking-wide text-text-primary mb-4">Legal</h2><ul class="space-y-3 text-sm"><li v-for="item in legalLinks" :key="item.label"><Link :href="item.href" class="hover:text-amber transition-colors">{{ item.label }}</Link></li></ul></div>
            </div>
            <div class="pt-6 border-t border-panel-line flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-text-muted">
                <p>© {{ new Date().getFullYear() }} CoreVisys Technologies. All rights reserved.</p>
                <p class="inline-flex items-center gap-2 font-mono"><span class="w-2 h-2 rounded-full bg-teal" />All systems operational</p>
            </div>
        </div>
    </footer>
</template>
