<template>
  <Head title="Terms of Service — CoreVisys" />

  <div class="min-h-screen bg-[var(--panel-2)] selection:bg-amber/20 selection:text-text-primary overflow-x-hidden font-inter">
    <!-- Navbar -->
    <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', scrolled ? 'glass-nav py-3 shadow-sm' : 'bg-transparent py-5']">
      <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <Link href="/" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-amber flex items-center justify-center">
            <CodeIcon class="text-text-primary w-5 h-5" />
          </div>
          <span :class="['font-sora font-bold text-xl tracking-tight transition-colors duration-300', scrolled ? 'text-text-primary' : 'text-text-primary']">CoreVisys</span>
        </Link>

        <div :class="['hidden md:flex items-center space-x-8 text-sm font-medium transition-colors duration-300', scrolled ? 'text-text-secondary' : 'text-text-secondary']">
          <Link href="/" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Home</Link>
          <a href="/#services" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Services</a>
          <a href="/#portfolio" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Portfolio</a>
          <Link href="/company" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Company</Link>
          <Link :href="route('insights')" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" :class="['text-sm font-medium transition-colors duration-300', scrolled ? 'text-text-secondary hover:text-text-primary' : 'text-text-secondary hover:text-text-primary']">Sign In</Link>
                    <Link :href="route('contact')" :class="['px-5 py-2.5 rounded-full text-sm font-medium transition-all shadow-md transform hover:-translate-y-0.5', scrolled ? 'bg-bg-dark hover:bg-amber text-text-primary hover:shadow-amber/20' : 'bg-panel hover:bg-amber/10 text-text-primary hover:shadow-panel-line']">
            Book Consultation
          </Link>
        </div>

        <button :class="['md:hidden transition-colors duration-300', scrolled ? 'text-text-primary' : 'text-text-primary']" @click="isOpen = !isOpen">
          <XIcon v-if="isOpen" />
          <MenuIcon v-else />
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="isOpen" class="absolute top-full left-0 w-full bg-panel border-b border-panel-line shadow-xl py-4 px-6 flex flex-col space-y-4 md:hidden">
        <Link href="/" class="text-text-secondary font-medium">Home</Link>
        <a href="/#services" class="text-text-secondary font-medium">Services</a>
        <a href="/#portfolio" class="text-text-secondary font-medium">Portfolio</a>
        <Link href="/company" class="text-text-secondary font-medium">Company</Link>
        <Link :href="route('insights')" class="text-text-secondary font-medium">Insights</Link>
                <Link :href="route('contact')" class="bg-amber text-text-primary px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero Section -->
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-28 overflow-hidden bg-bg-dark">
        <div class="absolute inset-0 z-0">
          <div class="absolute inset-0 bg-gradient-to-b from-bg-dark/90 via-bg-dark/95 to-[var(--panel-2)] z-10"></div>
        </div>

        <div class="absolute inset-0 grid-bg opacity-10 z-0"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-6 tracking-wide uppercase">
              <ScaleIcon class="w-4 h-4 mr-2" />
              Legal & Compliance
            </div>
          </Reveal>

          <Reveal :delay="100">
            <h1 class="text-3xl md:text-5xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-text-primary">
              Terms of Service
            </h1>
          </Reveal>

          <Reveal :delay="200">
            <p class="text-base md:text-lg text-text-secondary mb-8 max-w-2xl mx-auto leading-relaxed">
              Terms and conditions governing the use of CoreVisys services and website.
            </p>
          </Reveal>

          <Reveal :delay="300">
            <div class="flex items-center justify-center gap-6 text-sm text-text-secondary font-medium">
              <div class="flex items-center gap-2">
                <CalendarIcon class="w-4 h-4" />
                <span>Last Updated: <span class="text-text-primary">May 21, 2026</span></span>
              </div>
              <div class="flex items-center gap-2">
                <CheckCircleIcon class="w-4 h-4 text-green-400" />
                <span>Effective Date: <span class="text-text-primary">May 21, 2026</span></span>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- Main Content Layout -->
      <section class="py-16 md:py-24 bg-[var(--panel-2)]">
        <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row gap-12 lg:gap-20 relative">

          <!-- Sticky Sidebar Navigation -->
          <div class="hidden lg:block lg:w-1/4 shrink-0">
            <div class="sticky top-32 glass-card p-6 rounded-2xl bg-panel border-panel-line">
              <h3 class="font-sora font-bold text-text-primary mb-4 text-sm uppercase tracking-wider">Table of Contents</h3>
              <nav class="space-y-3 text-sm font-medium max-h-[70vh] overflow-y-auto pr-2 custom-scrollbar">
                <a href="#acceptance" :class="['block transition-colors', activeSection === 'acceptance' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">1. Acceptance of Terms</a>
                <a href="#services-overview" :class="['block transition-colors', activeSection === 'services-overview' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">2. Services Overview</a>
                <a href="#user-responsibilities" :class="['block transition-colors', activeSection === 'user-responsibilities' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">3. User Responsibilities</a>
                <a href="#intellectual-property" :class="['block transition-colors', activeSection === 'intellectual-property' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">4. Intellectual Property Rights</a>
                <a href="#payments-billing" :class="['block transition-colors', activeSection === 'payments-billing' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">5. Payments & Billing</a>
                <a href="#confidentiality" :class="['block transition-colors', activeSection === 'confidentiality' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">6. Confidentiality</a>
                <a href="#service-availability" :class="['block transition-colors', activeSection === 'service-availability' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">7. Service Availability</a>
                <a href="#limitation-liability" :class="['block transition-colors', activeSection === 'limitation-liability' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">8. Limitation of Liability</a>
                <a href="#termination" :class="['block transition-colors', activeSection === 'termination' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">9. Termination</a>
                <a href="#third-party-services" :class="['block transition-colors', activeSection === 'third-party-services' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">10. Third-Party Services</a>
                <a href="#governing-law" :class="['block transition-colors', activeSection === 'governing-law' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">11. Governing Law</a>
                <a href="#dispute-resolution" :class="['block transition-colors', activeSection === 'dispute-resolution' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">12. Dispute Resolution</a>
                <a href="#changes-to-terms" :class="['block transition-colors', activeSection === 'changes-to-terms' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">13. Changes to Terms</a>
                <a href="#contact-information" :class="['block transition-colors', activeSection === 'contact-information' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">14. Contact Information</a>
              </nav>
            </div>
          </div>

          <!-- Policy Content -->
          <div class="w-full lg:w-3/4">
            <div class="glass-card p-8 md:p-12 rounded-3xl bg-panel border border-panel-line shadow-sm text-text-secondary leading-relaxed text-base policy-content">

              <section id="acceptance" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">1. Acceptance of Terms</h2>
                <p>
                  By accessing, browsing, or utilizing the CoreVisys website, platforms, SaaS solutions, or engaging our software development services, you explicitly agree to be bound by these Terms of Service. If you do not agree with any part of these terms, you are prohibited from using our website and services.
                </p>
              </section>

              <section id="services-overview" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">2. Services Overview</h2>
                <p class="mb-4">CoreVisys is a technology solutions provider. Our primary offerings include:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Software Development Services:</strong> Custom enterprise software, web applications, and mobile app engineering.</li>
                  <li><strong class="text-text-primary">Consulting Services:</strong> Cloud architecture design, DevOps structuring, and technology strategy.</li>
                  <li><strong class="text-text-primary">SaaS Solutions:</strong> Access to proprietary software products developed and maintained by CoreVisys via recurring subscriptions.</li>
                  <li><strong class="text-text-primary">Technology Support Services:</strong> Maintenance, SLA-backed support, and security patching for digital infrastructures.</li>
                </ul>
              </section>

              <section id="user-responsibilities" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">3. User Responsibilities</h2>
                <p class="mb-4">As a user of CoreVisys platforms or a client of our services, you must:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Provide Accurate Information:</strong> Ensure all registration and billing information is truthful and current.</li>
                  <li><strong class="text-text-primary">Use Services Legally:</strong> Comply with all applicable local, state, national, and international laws, particularly the Information and Communication Technology (ICT) Act and Digital Security Act of Bangladesh.</li>
                  <li><strong class="text-text-primary">Avoid Harmful Activities:</strong> Refrain from uploading malware, engaging in unauthorized penetration testing without permission, or attempting to breach our server security.</li>
                  <li><strong class="text-text-primary">Respect Intellectual Property:</strong> Not reverse-engineer, duplicate, or distribute proprietary software without explicit licensing agreements.</li>
                </ul>
              </section>

              <section id="intellectual-property" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">4. Intellectual Property Rights</h2>
                <p class="mb-4">
                  All rights, titles, and interests in and to the CoreVisys platform, including but not limited to:
                </p>
                <ul class="list-disc pl-6 space-y-2 mb-4">
                  <li>Website content, text, and marketing copy</li>
                  <li>Branding, trademarks, and logos</li>
                  <li>Proprietary code, APIs, and algorithms</li>
                  <li>UI/UX designs and graphic assets</li>
                  <li>SaaS solutions and off-the-shelf software</li>
                </ul>
                <p>
                  ...are the exclusive property of CoreVisys. Custom software development clients will receive intellectual property rights only to the extent explicitly defined and transferred within their specific Master Services Agreement (MSA) upon full payment.
                </p>
              </section>

              <section id="payments-billing" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">5. Payments & Billing</h2>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Pricing Terms:</strong> Prices for SaaS products are listed on our platform. Custom services are billed according to a signed Statement of Work (SOW).</li>
                  <li><strong class="text-text-primary">Payment Timelines:</strong> SaaS subscriptions are billed in advance. Custom projects typically require milestone payments as outlined in the MSA. Invoices are due upon receipt unless otherwise stated.</li>
                  <li><strong class="text-text-primary">Project Milestone Payments:</strong> Development work requires approval and payment at distinct phases (e.g., Design, MVP, Final Delivery). Failure to pay milestone invoices may result in project suspension.</li>
                  <li><strong class="text-text-primary">Refund Policies:</strong> Except when required by law or explicitly stated in a service agreement, payments are non-refundable. Service credits may be offered at our discretion for SLA breaches.</li>
                </ul>
              </section>

              <section id="confidentiality" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">6. Confidentiality</h2>
                <p>
                  CoreVisys is committed to maintaining the highest level of confidentiality regarding client business data, trade secrets, and user information. Both CoreVisys and the client agree not to disclose confidential information to any third party without prior written consent, except as required by law.
                </p>
              </section>

              <section id="service-availability" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">7. Service Availability</h2>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Maintenance Windows:</strong> We routinely perform system maintenance to ensure optimal performance. We aim to schedule these during low-traffic periods and provide advance notice.</li>
                  <li><strong class="text-text-primary">Downtime Possibilities:</strong> While we strive for 99.9% uptime, we do not guarantee uninterrupted service. Unforeseen technical issues or Force Majeure events may cause temporary outages.</li>
                  <li><strong class="text-text-primary">Third-Party Dependencies:</strong> Our services rely on third-party cloud infrastructure (e.g., AWS, DigitalOcean). We are not liable for downtime caused directly by upstream providers.</li>
                </ul>
              </section>

              <section id="limitation-liability" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">8. Limitation of Liability</h2>
                <p class="mb-4">To the maximum extent permitted by applicable law, CoreVisys shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li>Loss of profits, revenue, or business opportunities.</li>
                  <li>Data loss risks or corruption of data.</li>
                  <li>Service interruptions or procurement of substitute services.</li>
                  <li>Damages resulting from the use of integrated third-party tools.</li>
                </ul>
                <p class="mt-4">
                  In no event shall CoreVisys's aggregate liability exceed the total amount paid by you to CoreVisys for the specific service in the six (6) months preceding the claim.
                </p>
              </section>

              <section id="termination" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">9. Termination</h2>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Account Suspension:</strong> We reserve the right to suspend access to our SaaS platforms immediately without notice if we suspect illegal activity, fraud, or a violation of these Terms.</li>
                  <li><strong class="text-text-primary">Service Termination:</strong> Either party may terminate custom service agreements based on the conditions defined in their specific MSA.</li>
                  <li><strong class="text-text-primary">Breach of Agreement:</strong> In the event of a material breach, the non-breaching party may terminate the agreement if the breach remains uncured for 30 days after written notice.</li>
                </ul>
              </section>

              <section id="third-party-services" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">10. Third-Party Services</h2>
                <p>
                  Our applications and services may integrate with third-party tools, including but not limited to cloud hosting providers, payment gateways (e.g., Stripe), and analytics platforms (e.g., Google Analytics). Your use of these integrated features is subject to the terms and privacy policies of those respective third parties. CoreVisys is not responsible for the practices of any third-party services.
                </p>
              </section>

              <section id="governing-law" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">11. Governing Law</h2>
                <p>
                  These Terms of Service and any separate agreements whereby we provide you services shall be governed by and construed in accordance with the laws of the People's Republic of Bangladesh, without regard to its conflict of law provisions.
                </p>
              </section>

              <section id="dispute-resolution" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">12. Dispute Resolution</h2>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Negotiation:</strong> In the event of a dispute, parties agree to attempt to resolve the issue amicably through good-faith negotiation for at least thirty (30) days.</li>
                  <li><strong class="text-text-primary">Arbitration/Jurisdiction:</strong> If negotiation fails, disputes shall be subject to the exclusive jurisdiction of the competent courts located in Dhaka, Bangladesh, or submitted to binding arbitration in Dhaka under the Arbitration Act, 2001 of Bangladesh.</li>
                </ul>
              </section>

              <section id="changes-to-terms" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">13. Changes to Terms</h2>
                <p>
                  We reserve the right, at our sole discretion, to modify or replace these Terms at any time. For material changes, we will provide at least 30 days' notice prior to any new terms taking effect via email or platform notification. What constitutes a material change will be determined at our sole discretion. Your continued use of the service after changes become effective constitutes acceptance of the new Terms.
                </p>
              </section>

              <section id="contact-information" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">14. Contact Information</h2>
                <p class="mb-4">
                  For any questions regarding these Terms of Service, please contact our legal department:
                </p>
                <div class="bg-[var(--panel-2)] p-6 rounded-2xl border border-panel-line">
                  <h4 class="font-sora font-bold text-text-primary mb-2">CoreVisys</h4>
                  <ul class="space-y-2">
                    <li class="flex items-center gap-3">
                      <MailIcon class="w-4 h-4 text-amber" />
                      <a href="mailto:legal@corevisys.com" class="text-amber hover:underline">legal@corevisys.com</a>
                    </li>
                    <li class="flex items-center gap-3">
                      <PhoneIcon class="w-4 h-4 text-amber" />
                      <span>+880 1234 567890</span>
                    </li>
                    <li class="flex items-start gap-3 mt-1">
                      <MapPinIcon class="w-4 h-4 text-amber mt-1" />
                      <span>Dhaka, Bangladesh<br/>(Headquarters)</span>
                    </li>
                  </ul>
                </div>
              </section>

            </div>
          </div>
        </div>
      </section>

    </main>

    <!-- Footer -->
    <footer class="bg-bg-dark text-text-secondary py-16 border-t border-panel-line">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
          <div class="lg:col-span-2">
            <div class="flex items-center gap-2 mb-6">
              <div class="w-8 h-8 rounded-lg bg-amber flex items-center justify-center">
                <CodeIcon class="text-text-primary w-5 h-5" />
              </div>
              <span class="font-sora font-bold text-xl tracking-tight text-text-primary">CoreVisys</span>
            </div>
            <p class="text-sm leading-relaxed mb-6 max-w-xs">
              A premium software development agency building enterprise-grade digital products for modern companies.
            </p>
            <div class="flex space-x-4">
              <div class="w-10 h-10 rounded-full bg-panel-2/50 flex items-center justify-center hover:bg-amber hover:text-text-primary transition-colors cursor-pointer"><MailIcon class="w-4 h-4" /></div>
              <div class="w-10 h-10 rounded-full bg-panel-2/50 flex items-center justify-center hover:bg-amber hover:text-text-primary transition-colors cursor-pointer"><PhoneIcon class="w-4 h-4" /></div>
              <div class="w-10 h-10 rounded-full bg-panel-2/50 flex items-center justify-center hover:bg-amber hover:text-text-primary transition-colors cursor-pointer"><MapPinIcon class="w-4 h-4" /></div>
            </div>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Services</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/services/custom-software" class="hover:text-amber transition-colors">Custom Software</Link></li>
              <li><Link href="/services/web-applications" class="hover:text-amber transition-colors">Web Applications</Link></li>
              <li><Link href="/services/mobile-apps" class="hover:text-amber transition-colors">Mobile App Dev</Link></li>
              <li><Link href="/services/ai-ml" class="hover:text-amber transition-colors">AI & Machine Learning</Link></li>
              <li><Link href="/services/cloud-solutions" class="hover:text-amber transition-colors">Cloud Architecture</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Company</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/" class="hover:text-amber transition-colors">About Us</Link></li>
              <li><a href="/#portfolio" class="hover:text-amber transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-amber transition-colors">Careers</Link></li>
              <li><a href="/#blog" class="hover:text-amber transition-colors">Blog</a></li>
              <li><Link :href="route('contact')" class="hover:text-amber transition-colors">Contact</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Legal</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy-policy" class="hover:text-amber transition-colors">Privacy Policy</Link></li>
              <li><Link href="/terms-of-service" class="hover:text-amber transition-colors text-amber">Terms of Service</Link></li>
              <li><Link href="/cookie-policy" class="hover:text-amber transition-colors">Cookie Policy</Link></li>
            </ul>
          </div>
        </div>

        <div class="pt-8 border-t border-panel-line flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
          <p>© {{ new Date().getFullYear() }} CoreVisys Technologies. All rights reserved.</p>
          <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-teal"></span>
            </span>
            All systems operational
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import Reveal from '@/Components/Reveal.vue';
import {
  Menu as MenuIcon,
  X as XIcon,
  Code as CodeIcon,
  Scale as ScaleIcon,
  Calendar as CalendarIcon,
  CheckCircle as CheckCircleIcon,
  Mail as MailIcon,
  MapPin as MapPinIcon,
  Phone as PhoneIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeSection = ref('acceptance');

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;

  const sections = [
    'acceptance', 'services-overview', 'user-responsibilities',
    'intellectual-property', 'payments-billing', 'confidentiality', 'service-availability',
    'limitation-liability', 'termination', 'third-party-services',
    'governing-law', 'dispute-resolution', 'changes-to-terms', 'contact-information'
  ];

  for (const section of sections) {
    const el = document.getElementById(section);
    if (el) {
      const rect = el.getBoundingClientRect();
      if (rect.top <= 150 && rect.bottom >= 150) {
        activeSection.value = section;
        break;
      }
    }
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Sora:wght@400;600;700;800&display=swap');

.font-inter {
  font-family: 'Inter', sans-serif;
}

.font-sora {
  font-family: 'Sora', sans-serif;
}

.grid-bg {
  background-size: 40px 40px;
  background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
  mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
  -webkit-mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
}

.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.glass-nav {
  background: rgba(248, 250, 252, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(15, 23, 42, 0.05);
}

.policy-content h2 {
  scroll-margin-top: 100px;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.5);
}
</style>
