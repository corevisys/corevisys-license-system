<template>
  <Head title="Cookie Policy — CoreVisys" />

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
              <CookieIcon class="w-4 h-4 mr-2" />
              Privacy & Compliance
            </div>
          </Reveal>

          <Reveal :delay="100">
            <h1 class="text-3xl md:text-5xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-text-primary">
              Cookie Policy
            </h1>
          </Reveal>

          <Reveal :delay="200">
            <p class="text-base md:text-lg text-text-secondary mb-8 max-w-2xl mx-auto leading-relaxed">
              How CoreVisys uses cookies and tracking technologies.
            </p>
          </Reveal>

          <Reveal :delay="300">
            <div class="flex items-center justify-center gap-6 text-sm text-text-secondary font-medium">
              <div class="flex items-center gap-2">
                <CalendarIcon class="w-4 h-4" />
                <span>Last Updated: <span class="text-text-primary">May 21, 2026</span></span>
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
              <nav class="space-y-3 text-sm font-medium">
                <a href="#what-are-cookies" :class="['block transition-colors', activeSection === 'what-are-cookies' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">1. What Are Cookies?</a>
                <a href="#types-of-cookies" :class="['block transition-colors', activeSection === 'types-of-cookies' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">2. Types of Cookies We Use</a>
                <a href="#why-we-use-cookies" :class="['block transition-colors', activeSection === 'why-we-use-cookies' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">3. Why We Use Cookies</a>
                <a href="#third-party-cookies" :class="['block transition-colors', activeSection === 'third-party-cookies' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">4. Third-Party Cookies</a>
                <a href="#managing-cookies" :class="['block transition-colors', activeSection === 'managing-cookies' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">5. Managing Cookies</a>
                <a href="#data-protection" :class="['block transition-colors', activeSection === 'data-protection' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">6. Data Protection</a>
                <a href="#policy-updates" :class="['block transition-colors', activeSection === 'policy-updates' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">7. Policy Updates</a>
                <a href="#contact-information" :class="['block transition-colors', activeSection === 'contact-information' ? 'text-amber' : 'text-text-muted hover:text-text-primary']">8. Contact Information</a>
              </nav>
            </div>
          </div>

          <!-- Policy Content -->
          <div class="w-full lg:w-3/4">
            <div class="glass-card p-8 md:p-12 rounded-3xl bg-panel border border-panel-line shadow-sm text-text-secondary leading-relaxed text-base policy-content">

              <section id="what-are-cookies" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">1. What Are Cookies?</h2>
                <p>
                  Cookies are small text files that are placed on your computer, smartphone, or other electronic devices when you visit our website. They act as a memory for a web page, allowing the server to recognize your device across different visits and remember your actions or preferences (such as login details, language, font size, and other display preferences) over a period of time. This means you don't have to keep re-entering them whenever you come back to the site or browse from one page to another.
                </p>
              </section>

              <section id="types-of-cookies" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">2. Types of Cookies We Use</h2>
                <p class="mb-4">We categorize the cookies we use into the following groups based on their purpose:</p>
                <ul class="list-disc pl-6 space-y-4">
                  <li>
                    <strong class="text-text-primary">Essential Cookies:</strong>
                    These cookies are strictly necessary to provide you with the core services available through our website and to use some of its features. For example, they allow you to securely log in to secure areas of our website. Without these cookies, the services that you have asked for cannot be provided.
                  </li>
                  <li>
                    <strong class="text-text-primary">Performance Cookies:</strong>
                    These cookies collect information about how you use a website, like which pages you visited and which links you clicked on. None of this information can be used to identify you. It is all aggregated and, therefore, anonymized. Their sole purpose is to improve website functions.
                  </li>
                  <li>
                    <strong class="text-text-primary">Analytics Cookies:</strong>
                    These cookies allow us to track analytics by counting visits and traffic sources so we can measure and improve the performance of our site. They help us to know which pages are the most and least popular and see how visitors move around the site.
                  </li>
                  <li>
                    <strong class="text-text-primary">Functional Cookies:</strong>
                    These are used to recognize you when you return to our website. This enables us to personalize our content for you and remember your preferences (for example, your choice of language or region).
                  </li>
                  <li>
                    <strong class="text-text-primary">Marketing Cookies:</strong>
                    These cookies track your online activity to help advertisers deliver more relevant advertising or to limit how many times you see an ad. We use them carefully to evaluate our marketing campaigns.
                  </li>
                </ul>
              </section>

              <section id="why-we-use-cookies" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">3. Why We Use Cookies</h2>
                <p class="mb-4">CoreVisys utilizes cookies and tracking technologies to ensure our platforms run seamlessly and securely. The main reasons include:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Website Functionality:</strong> Ensuring forms, navigation, and user dashboards operate without technical glitches.</li>
                  <li><strong class="text-text-primary">Performance Monitoring:</strong> Identifying loading bottlenecks to optimize our high-speed SaaS architectures.</li>
                  <li><strong class="text-text-primary">User Experience Improvement:</strong> Remembering theme preferences, localization choices, and user states.</li>
                  <li><strong class="text-text-primary">Analytics:</strong> Gathering anonymized data to understand user interaction patterns.</li>
                  <li><strong class="text-text-primary">Security:</strong> Preventing CSRF attacks, authenticating sessions, and mitigating fraudulent activities.</li>
                </ul>
              </section>

              <section id="third-party-cookies" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">4. Third-Party Cookies</h2>
                <p class="mb-4">
                  In some special cases, we also use cookies provided by trusted third parties. The following section details which third-party cookies you might encounter through this site:
                </p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Google Analytics:</strong> We use Google Analytics to help us understand how you use the site and ways that we can improve your experience. These cookies may track things such as how long you spend on the site and the pages that you visit.</li>
                  <li><strong class="text-text-primary">Cloudflare:</strong> Used to maintain security and optimize network latency for delivering our platform reliably across the globe.</li>
                  <li><strong class="text-text-primary">Payment Gateways:</strong> Providers like Stripe use cookies to verify device identities for secure transaction processing.</li>
                  <li><strong class="text-text-primary">Embedded Content Providers:</strong> Sometimes we embed content from social media platforms or video hosts, which may set their own cookies.</li>
                </ul>
              </section>

              <section id="managing-cookies" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">5. Managing Cookies</h2>
                <p class="mb-4">
                  You have the right to decide whether to accept or reject non-essential cookies. You can set or amend your web browser controls to accept or refuse cookies.
                </p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-text-primary">Browser Controls:</strong> Most modern web browsers allow you to manage your cookie settings. You can usually find these settings in the "Options" or "Preferences" menu of your browser.</li>
                  <li><strong class="text-text-primary">Disabling Cookies:</strong> If you choose to reject cookies, you may still use our website, though your access to some functionality and areas of our website may be restricted.</li>
                  <li><strong class="text-text-primary">Consent Preferences:</strong> You can manage your initial consent via the cookie banner displayed upon your first visit.</li>
                </ul>
              </section>

              <section id="data-protection" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">6. Data Protection</h2>
                <p>
                  Any personal data that we collect from the application of cookies is processed in accordance with our <a href="/privacy-policy" class="text-amber hover:underline">Privacy Policy</a>. We ensure secure handling of cookie-related information, adhering to local privacy laws in Bangladesh and maintaining a GDPR-style transparency approach globally.
                </p>
              </section>

              <section id="policy-updates" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">7. Policy Updates</h2>
                <p>
                  We may update this Cookie Policy from time to time in order to reflect changes to the cookies we use or for other operational, legal, or regulatory reasons. Please therefore re-visit this Cookie Policy regularly to stay informed about our use of cookies and related technologies. The date at the top of this policy indicates when it was last updated.
                </p>
              </section>

              <section id="contact-information" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-text-primary mb-4">8. Contact Information</h2>
                <p class="mb-4">
                  If you have any questions about our use of cookies or other technologies, please contact us at:
                </p>
                <div class="bg-[var(--panel-2)] p-6 rounded-2xl border border-panel-line">
                  <h4 class="font-sora font-bold text-text-primary mb-2">CoreVisys</h4>
                  <ul class="space-y-2">
                    <li class="flex items-center gap-3">
                      <MailIcon class="w-4 h-4 text-amber" />
                      <a href="mailto:support@corevisys.com" class="text-amber hover:underline">support@corevisys.com</a>
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
              <li><Link href="/terms-of-service" class="hover:text-amber transition-colors">Terms of Service</Link></li>
              <li><Link href="/cookie-policy" class="hover:text-amber transition-colors text-amber">Cookie Policy</Link></li>
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
  Cookie as CookieIcon,
  Calendar as CalendarIcon,
  Mail as MailIcon,
  MapPin as MapPinIcon,
  Phone as PhoneIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeSection = ref('what-are-cookies');

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;

  const sections = [
    'what-are-cookies', 'types-of-cookies', 'why-we-use-cookies',
    'third-party-cookies', 'managing-cookies', 'data-protection',
    'policy-updates', 'contact-information'
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
</style>
