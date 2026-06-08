<template>
  <Head title="Privacy Policy — CoreVisys" />
  
  <div class="min-h-screen bg-[#F8FAFC] selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden font-inter">
    <!-- Navbar -->
    <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', scrolled ? 'glass-nav py-3 shadow-sm' : 'bg-transparent py-5']">
      <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <Link href="/" class="flex items-center gap-2">
          <img :src="scrolled ? '/storage/logo/Main-Logo.png' : '/storage/logo/Main-Logo-2.png'" alt="CoreVisys" class="h-16 md:h-20 w-auto transition-all duration-300" />
        </Link>

        <div :class="['hidden md:flex items-center space-x-8 text-sm font-medium transition-colors duration-300', scrolled ? 'text-slate-600' : 'text-white/80']">
          <Link href="/" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Home</Link>
          <a href="/#services" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Services</a>
          <a href="/#portfolio" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Portfolio</a>
          <Link href="/company" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Company</Link>
          <Link :href="route('insights')" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" :class="['text-sm font-medium transition-colors duration-300', scrolled ? 'text-slate-600 hover:text-slate-900' : 'text-white/80 hover:text-white']">Sign In</Link>
                    <Link :href="route('contact')" :class="['px-5 py-2.5 rounded-full text-sm font-medium transition-all shadow-md transform hover:-translate-y-0.5', scrolled ? 'bg-slate-900 hover:bg-blue-600 text-white hover:shadow-blue-500/20' : 'bg-white hover:bg-blue-50 text-slate-900 hover:shadow-white/10']">
            Book Consultation
          </Link>
        </div>

        <button :class="['md:hidden transition-colors duration-300', scrolled ? 'text-slate-900' : 'text-white']" @click="isOpen = !isOpen">
          <XIcon v-if="isOpen" />
          <MenuIcon v-else />
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="isOpen" class="absolute top-full left-0 w-full bg-white border-b border-slate-200 shadow-xl py-4 px-6 flex flex-col space-y-4 md:hidden">
        <Link href="/" class="text-slate-600 font-medium">Home</Link>
        <a href="/#services" class="text-slate-600 font-medium">Services</a>
        <a href="/#portfolio" class="text-slate-600 font-medium">Portfolio</a>
        <Link href="/company" class="text-slate-600 font-medium">Company</Link>
        <Link :href="route('insights')" class="text-slate-600 font-medium">Insights</Link>
                <Link :href="route('contact')" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero Section -->
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-28 overflow-hidden bg-slate-950">
        <div class="absolute inset-0 z-0">
          <div class="absolute inset-0 bg-gradient-to-b from-slate-950/90 via-slate-950/95 to-[#F8FAFC] z-10"></div>
        </div>
        
        <div class="absolute inset-0 grid-bg opacity-10 z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
              <ShieldIcon class="w-4 h-4 mr-2" />
              Legal & Compliance
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-3xl md:text-5xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-white">
              Privacy Policy
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-base md:text-lg text-slate-300 mb-8 max-w-2xl mx-auto leading-relaxed">
              How CoreVisys collects, uses, and protects your information.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex items-center justify-center gap-6 text-sm text-slate-400 font-medium">
              <div class="flex items-center gap-2">
                <CalendarIcon class="w-4 h-4" />
                <span>Last Updated: <span class="text-white">May 21, 2026</span></span>
              </div>
              <div class="flex items-center gap-2">
                <CheckCircleIcon class="w-4 h-4 text-green-400" />
                <span>Effective Date: <span class="text-white">May 21, 2026</span></span>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- Main Content Layout -->
      <section class="py-16 md:py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row gap-12 lg:gap-20 relative">
          
          <!-- Sticky Sidebar Navigation -->
          <div class="hidden lg:block lg:w-1/4 shrink-0">
            <div class="sticky top-32 glass-card p-6 rounded-2xl bg-white border-slate-200">
              <h3 class="font-sora font-bold text-slate-900 mb-4 text-sm uppercase tracking-wider">Table of Contents</h3>
              <nav class="space-y-3 text-sm font-medium">
                <a href="#introduction" :class="['block transition-colors', activeSection === 'introduction' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">1. Introduction</a>
                <a href="#information-we-collect" :class="['block transition-colors', activeSection === 'information-we-collect' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">2. Information We Collect</a>
                <a href="#how-we-use-information" :class="['block transition-colors', activeSection === 'how-we-use-information' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">3. How We Use Information</a>
                <a href="#cookies-tracking" :class="['block transition-colors', activeSection === 'cookies-tracking' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">4. Cookies & Tracking Technologies</a>
                <a href="#data-sharing" :class="['block transition-colors', activeSection === 'data-sharing' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">5. Data Sharing & Third Parties</a>
                <a href="#data-retention" :class="['block transition-colors', activeSection === 'data-retention' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">6. Data Retention</a>
                <a href="#data-security" :class="['block transition-colors', activeSection === 'data-security' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">7. Data Security</a>
                <a href="#user-rights" :class="['block transition-colors', activeSection === 'user-rights' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">8. User Rights</a>
                <a href="#international-transfers" :class="['block transition-colors', activeSection === 'international-transfers' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">9. International Data Transfers</a>
                <a href="#childrens-privacy" :class="['block transition-colors', activeSection === 'childrens-privacy' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">10. Children’s Privacy</a>
                <a href="#changes-to-policy" :class="['block transition-colors', activeSection === 'changes-to-policy' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">11. Changes to Privacy Policy</a>
                <a href="#contact-information" :class="['block transition-colors', activeSection === 'contact-information' ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900']">12. Contact Information</a>
              </nav>
            </div>
          </div>

          <!-- Policy Content -->
          <div class="w-full lg:w-3/4">
            <div class="glass-card p-8 md:p-12 rounded-3xl bg-white border border-slate-200 shadow-sm text-slate-700 leading-relaxed text-base policy-content">
              
              <section id="introduction" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">1. Introduction</h2>
                <p class="mb-4">
                  Welcome to CoreVisys ("CoreVisys," "we," "our," or "us"). We are deeply committed to protecting your privacy and ensuring the security of the personal information you share with us.
                </p>
                <p>
                  This Privacy Policy explains how we collect, use, disclose, safeguard, and otherwise process your personal data when you use our website, services, applications, and enterprise software solutions. We adhere strictly to applicable data protection regulations in Bangladesh (such as the Data Protection Act paradigms) and implement global best practices inspired by frameworks like the General Data Protection Regulation (GDPR) to ensure transparency and trust in all our operations.
                </p>
              </section>

              <section id="information-we-collect" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">2. Information We Collect</h2>
                <p class="mb-4">We collect various types of information to provide and improve our services:</p>
                <ul class="list-disc pl-6 space-y-2 mb-4">
                  <li><strong class="text-slate-900">Personal Information:</strong> Includes your name, company name, job title, and other identifiers you provide when creating an account or requesting our services.</li>
                  <li><strong class="text-slate-900">Contact Information:</strong> Email addresses, physical addresses, and phone numbers used for communication and billing.</li>
                  <li><strong class="text-slate-900">Financial Information:</strong> Billing details and payment history. (Note: We use secure third-party payment gateways; we do not store full credit card numbers on our servers).</li>
                  <li><strong class="text-slate-900">Device Information:</strong> Information about the computer, phone, or other devices you use to access our services, including IP addresses, browser types, and operating systems.</li>
                  <li><strong class="text-slate-900">Usage Data:</strong> Information about how you interact with our website and platforms, such as pages visited, time spent, and clickstream data.</li>
                  <li><strong class="text-slate-900">Cookies and Tracking Data:</strong> Data collected via cookies and similar technologies (detailed in Section 4).</li>
                </ul>
              </section>

              <section id="how-we-use-information" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">3. How We Use Information</h2>
                <p class="mb-4">We use the collected information for the following purposes:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-slate-900">Providing Services:</strong> To deliver our enterprise software solutions, process transactions, and manage user accounts and licenses.</li>
                  <li><strong class="text-slate-900">Improving Website Experience:</strong> To analyze usage trends and continually enhance the functionality and aesthetics of our platforms.</li>
                  <li><strong class="text-slate-900">Customer Support:</strong> To respond to your inquiries, provide technical assistance, and resolve issues efficiently.</li>
                  <li><strong class="text-slate-900">Marketing Communications:</strong> To send updates, newsletters, and promotional materials (you may opt out at any time).</li>
                  <li><strong class="text-slate-900">Security & Fraud Prevention:</strong> To detect, prevent, and address technical anomalies, unauthorized access, or fraudulent activities.</li>
                  <li><strong class="text-slate-900">Analytics & Performance Monitoring:</strong> To measure platform performance and gather actionable business intelligence.</li>
                </ul>
              </section>

              <section id="cookies-tracking" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">4. Cookies & Tracking Technologies</h2>
                <p class="mb-4">
                  CoreVisys uses cookies and similar tracking technologies to track activity on our website and hold certain information.
                </p>
                <ul class="list-disc pl-6 space-y-2 mb-4">
                  <li><strong class="text-slate-900">Essential Cookies:</strong> Required to provide you with services available through our website and enable you to use some of its features (e.g., logging in).</li>
                  <li><strong class="text-slate-900">Functional Cookies:</strong> Used to remember the choices you make when using our services, such as remembering your login details or language preferences.</li>
                  <li><strong class="text-slate-900">Analytics Cookies:</strong> Used to track information about traffic to the website and how users use the website.</li>
                </ul>
                <p>
                  You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our services.
                </p>
              </section>

              <section id="data-sharing" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">5. Data Sharing & Third Parties</h2>
                <p class="mb-4">
                  <strong class="text-slate-900">CoreVisys does not sell, rent, or trade your personal data to third parties.</strong> We only share information in the following circumstances:
                </p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-slate-900">Hosting & Infrastructure Providers:</strong> To securely host our platforms and databases.</li>
                  <li><strong class="text-slate-900">Analytics Providers:</strong> To assist us in analyzing how our service is used.</li>
                  <li><strong class="text-slate-900">Payment Gateways:</strong> To securely process payments for licenses and services (e.g., Stripe).</li>
                  <li><strong class="text-slate-900">Legal Obligations:</strong> When required by law, subpoena, or other legal processes, or to protect the rights, property, or safety of CoreVisys, our users, or others.</li>
                </ul>
              </section>

              <section id="data-retention" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">6. Data Retention</h2>
                <p class="mb-4">
                  We will retain your Personal Information only for as long as is necessary for the purposes set out in this Privacy Policy. We will retain and use your information to the extent necessary to comply with our legal obligations (for example, if we are required to retain your data to comply with applicable tax/accounting laws in Bangladesh), resolve disputes, and enforce our legal agreements and policies.
                </p>
                <p>
                  Upon account termination, data is securely archived or deleted in accordance with our data lifecycle policies, unless further retention is legally mandated.
                </p>
              </section>

              <section id="data-security" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">7. Data Security</h2>
                <p class="mb-4">
                  The security of your data is paramount to us. CoreVisys implements robust security measures including:
                </p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-slate-900">Encryption:</strong> Data is encrypted at rest and in transit using modern cryptographic standards (e.g., TLS/SSL).</li>
                  <li><strong class="text-slate-900">Secure Infrastructure:</strong> Deployment on isolated VPCs with strict firewall rules and containerized architecture.</li>
                  <li><strong class="text-slate-900">Access Control:</strong> Strict Role-Based Access Control (RBAC) ensuring only authorized personnel can access sensitive systems.</li>
                  <li><strong class="text-slate-900">Monitoring Systems:</strong> 24/7 proactive monitoring and logging to detect and mitigate potential threats instantly.</li>
                </ul>
              </section>

              <section id="user-rights" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">8. User Rights</h2>
                <p class="mb-4">Depending on your jurisdiction, you may have the following rights regarding your data:</p>
                <ul class="list-disc pl-6 space-y-2">
                  <li><strong class="text-slate-900">Access:</strong> Request a copy of the personal data we hold about you.</li>
                  <li><strong class="text-slate-900">Correction:</strong> Request that we correct inaccurate or incomplete information.</li>
                  <li><strong class="text-slate-900">Deletion (Right to be Forgotten):</strong> Request the erasure of your personal data under certain conditions.</li>
                  <li><strong class="text-slate-900">Withdraw Consent:</strong> Withdraw your consent at any time where we relied on your consent to process your personal information.</li>
                </ul>
                <p class="mt-4">To exercise any of these rights, please contact us at privacy@corevisys.com.</p>
              </section>

              <section id="international-transfers" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">9. International Data Transfers</h2>
                <p>
                  Your information, including Personal Data, may be transferred to — and maintained on — computers and cloud servers located outside of your state, province, country, or other governmental jurisdiction where the data protection laws may differ from those of your jurisdiction. Our primary infrastructure leverages global cloud providers. By providing us with your data, you consent to this transfer, provided that we ensure the receiving entities maintain equivalent levels of data protection.
                </p>
              </section>

              <section id="childrens-privacy" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">10. Children’s Privacy</h2>
                <p>
                  Our Services are designed for enterprises and professionals and are not intended for children under the age of 18 (or the applicable age of majority). We do not knowingly collect personally identifiable information from anyone under the age of 18. If you are a parent or guardian and you are aware that your child has provided us with Personal Data, please contact us.
                </p>
              </section>

              <section id="changes-to-policy" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">11. Changes to Privacy Policy</h2>
                <p>
                  We may update our Privacy Policy from time to time to reflect changes in our practices or for other operational, legal, or regulatory reasons. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Effective Date" at the top. We encourage you to review this Privacy Policy periodically.
                </p>
              </section>

              <section id="contact-information" class="mb-12 scroll-mt-32">
                <h2 class="text-2xl font-sora font-bold text-slate-900 mb-4">12. Contact Information</h2>
                <p class="mb-4">
                  If you have any questions, concerns, or requests regarding this Privacy Policy or our data processing practices, please contact our Data Protection Officer:
                </p>
                <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100">
                  <h4 class="font-sora font-bold text-slate-900 mb-2">CoreVisys</h4>
                  <ul class="space-y-2">
                    <li class="flex items-center gap-3">
                      <MailIcon class="w-4 h-4 text-blue-600" />
                      <a href="mailto:privacy@corevisys.com" class="text-blue-600 hover:underline">privacy@corevisys.com</a>
                    </li>
                    <li class="flex items-center gap-3">
                      <PhoneIcon class="w-4 h-4 text-blue-600" />
                      <span>+880 1234 567890</span>
                    </li>
                    <li class="flex items-start gap-3 mt-1">
                      <MapPinIcon class="w-4 h-4 text-blue-600 mt-1" />
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
    <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-900">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
          <div class="lg:col-span-2">
            <div class="flex items-center gap-2 mb-6">
              <img src="/storage/logo/Main-Logo-2.png" alt="CoreVisys" class="h-16 md:h-20 w-auto" />
            </div>
            <p class="text-sm leading-relaxed mb-6 max-w-xs">
              A premium software development agency building enterprise-grade digital products for modern companies.
            </p>
            <div class="flex space-x-4">
              <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors cursor-pointer"><MailIcon class="w-4 h-4" /></div>
              <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors cursor-pointer"><PhoneIcon class="w-4 h-4" /></div>
              <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors cursor-pointer"><MapPinIcon class="w-4 h-4" /></div>
            </div>
          </div>
          
          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Services</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/services/custom-software" class="hover:text-blue-400 transition-colors">Custom Software</Link></li>
              <li><Link href="/services/web-applications" class="hover:text-blue-400 transition-colors">Web Applications</Link></li>
              <li><Link href="/services/mobile-apps" class="hover:text-blue-400 transition-colors">Mobile App Dev</Link></li>
              <li><Link href="/services/ai-ml" class="hover:text-blue-400 transition-colors">AI & Machine Learning</Link></li>
              <li><Link href="/services/cloud-solutions" class="hover:text-blue-400 transition-colors">Cloud Architecture</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Company</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/" class="hover:text-blue-400 transition-colors">About Us</Link></li>
              <li><a href="/#portfolio" class="hover:text-blue-400 transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-blue-400 transition-colors">Careers</Link></li>
              <li><a href="/#blog" class="hover:text-blue-400 transition-colors">Blog</a></li>
              <li><Link :href="route('contact')" class="hover:text-blue-400 transition-colors">Contact</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Legal</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy-policy" class="hover:text-blue-400 transition-colors text-blue-400">Privacy Policy</Link></li>
              <li><Link href="/terms-of-service" class="hover:text-blue-400 transition-colors">Terms of Service</Link></li>
              <li><Link href="/cookie-policy" class="hover:text-blue-400 transition-colors">Cookie Policy</Link></li>
            </ul>
          </div>
        </div>
        
        <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
          <p>© {{ new Date().getFullYear() }} CoreVisys Technologies. All rights reserved.</p>
          <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
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
  Shield as ShieldIcon,
  Calendar as CalendarIcon,
  CheckCircle as CheckCircleIcon,
  Mail as MailIcon, 
  MapPin as MapPinIcon, 
  Phone as PhoneIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeSection = ref('introduction');

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;

  // Simple scrollspy to update active section in sticky nav
  const sections = [
    'introduction', 'information-we-collect', 'how-we-use-information', 
    'cookies-tracking', 'data-sharing', 'data-retention', 'data-security', 
    'user-rights', 'international-transfers', 'childrens-privacy', 
    'changes-to-policy', 'contact-information'
  ];

  for (const section of sections) {
    const el = document.getElementById(section);
    if (el) {
      const rect = el.getBoundingClientRect();
      // If the top of the section is near the top of viewport (considering sticky nav + spacing)
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
