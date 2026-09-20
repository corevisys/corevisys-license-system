<script setup>
import { computed, reactive, ref } from 'vue';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import FAQ from '@/Components/public/FAQ.vue';
import Alert from '@/Components/UI/Alert.vue';
import { contact, faqs, site } from '@/content/public.js';

const form = reactive({
    name: '',
    email: '',
    topic: 'Sales',
    message: '',
});

const topics = ['Sales', 'Support', 'Billing', 'Security'];

const sentHint = ref(false);

// The form is intentionally non-submitting: it composes an email instead of
// posting to the server, so no message is stored or processed by the app.
const mailtoHref = computed(() => {
    const subject = encodeURIComponent(`[${form.topic}] Message from ${form.name || 'a visitor'}`);
    const body = encodeURIComponent(
        `${form.message}\n\n—\n${form.name}\n${form.email}`,
    );
    return `mailto:${contact.salesEmail}?subject=${subject}&body=${body}`;
});

const openEmail = () => {
    sentHint.value = true;
    window.location.href = mailtoHref.value;
};

const details = computed(() => [
    { label: 'Sales', value: contact.salesEmail, href: `mailto:${contact.salesEmail}` },
    { label: 'Support', value: contact.supportEmail, href: `mailto:${contact.supportEmail}` },
    { label: 'Phone', value: contact.phone, href: null },
    { label: 'Address', value: contact.address, href: null },
]);

const fieldClass =
    'w-full rounded-lg border border-panel-line bg-panel px-3 py-2.5 text-[14px] text-text-primary placeholder:text-text-muted transition-colors focus:border-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary/25';
</script>

<template>
    <PublicLayout title="Contact" :description="'Contact the ' + site.name + ' team.'">
        <!-- Header -->
        <section class="relative overflow-hidden border-b border-panel-line">
            <span class="bg-grid pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" />
            <span class="hero-glow pointer-events-none absolute -top-24 right-1/3 h-72 w-72 rounded-full" aria-hidden="true" />

            <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-16 sm:pt-20">
                <SectionHeading
                    eyebrow="Contact"
                    title="Talk to the team"
                    description="Questions about licensing, billing, or integration? Send us a message and we will get back to you."
                />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-16">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_1fr]">
                <!-- Form card -->
                <div class="rounded-3xl border border-panel-line bg-panel p-6 sm:p-8">
                    <Alert variant="success" class="mb-6">
                        This form does not submit to our servers. Completing it opens your
                        email client with the details pre-filled — nothing is stored here.
                    </Alert>

                    <form class="space-y-4" @submit.prevent="openEmail">
                        <div>
                            <label for="contact-name" class="mb-1.5 block font-mono text-[11.5px] uppercase tracking-wide text-text-secondary">Name</label>
                            <input
                                id="contact-name"
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                :class="fieldClass"
                                placeholder="Your name"
                            />
                        </div>

                        <div>
                            <label for="contact-email" class="mb-1.5 block font-mono text-[11.5px] uppercase tracking-wide text-text-secondary">Email</label>
                            <input
                                id="contact-email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                :class="fieldClass"
                                placeholder="you@company.com"
                            />
                        </div>

                        <div>
                            <label for="contact-topic" class="mb-1.5 block font-mono text-[11.5px] uppercase tracking-wide text-text-secondary">Topic</label>
                            <select
                                id="contact-topic"
                                v-model="form.topic"
                                :class="fieldClass"
                            >
                                <option v-for="topic in topics" :key="topic" :value="topic">{{ topic }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="contact-message" class="mb-1.5 block font-mono text-[11.5px] uppercase tracking-wide text-text-secondary">Message</label>
                            <textarea
                                id="contact-message"
                                v-model="form.message"
                                rows="5"
                                :class="[fieldClass, 'resize-y']"
                                placeholder="How can we help?"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-transparent bg-brand-primary px-[18px] py-[11px] text-[14.5px] font-medium text-bg-dark transition-transform hover:scale-[1.02] motion-reduce:hover:scale-100"
                        >Open email with this message</button>

                        <p v-if="sentHint" class="font-mono text-[12px] text-text-secondary">
                            If your email client did not open, write to
                            <a :href="`mailto:${contact.salesEmail}`" class="text-brand-primary hover:underline">{{ contact.salesEmail }}</a>.
                        </p>
                    </form>
                </div>

                <!-- Info cards -->
                <div class="space-y-4">
                    <div class="card-lift rounded-2xl border border-panel-line bg-panel p-6">
                        <h3 class="text-base font-semibold text-text-primary">Direct contacts</h3>
                        <dl class="mt-4 space-y-3">
                            <div v-for="detail in details" :key="detail.label" class="border-t border-panel-line pt-3 first-of-type:border-t-0 first-of-type:pt-0">
                                <dt class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ detail.label }}</dt>
                                <dd class="mt-1 text-[13.5px] text-text-primary">
                                    <a v-if="detail.href" :href="detail.href" class="text-brand-primary hover:underline">{{ detail.value }}</a>
                                    <span v-else>{{ detail.value }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="card-lift rounded-2xl border border-panel-line bg-panel p-6">
                        <h3 class="text-base font-semibold text-text-primary">Support hours</h3>
                        <p class="mt-2 text-sm text-text-secondary">{{ contact.supportHours }}</p>
                        <p class="mt-1 text-sm text-text-secondary">Typical response time: {{ contact.responseTime }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <SectionHeading eyebrow="FAQ" title="Before you write" align="center" />
                <div class="mx-auto mt-10 max-w-3xl">
                    <FAQ :items="faqs.slice(0, 4)" />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>