<template>
  <Head title="CoreVisys — Enterprise Software Agency" />
  
  <div class="min-h-screen bg-[#F8FAFC] selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden font-inter">
    <!-- Navbar -->
    <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', scrolled ? 'glass-nav py-3 shadow-sm' : 'bg-transparent py-5']">
      <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
          <img :src="scrolled ? '/storage/logo/Main-Logo.png' : '/storage/logo/Main-Logo-2.png'" alt="CoreVisys" class="h-16 md:h-20 w-auto transition-all duration-300" />
        </div>

        <div :class="['hidden md:flex items-center space-x-8 text-sm font-medium transition-colors duration-300', scrolled ? 'text-slate-600' : 'text-white/80']">
          <a href="#" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Home</a>
          <a href="#services" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Services</a>
          <a href="#portfolio" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Portfolio</a>
          <Link :href="route('company')" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Company</Link>
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
        <a href="#" class="text-slate-600 font-medium">Home</a>
        <a href="#services" class="text-slate-600 font-medium">Services</a>
        <a href="#portfolio" class="text-slate-600 font-medium">Portfolio</a>
        <Link :href="route('company')" class="text-slate-600 font-medium">Company</Link>
        <Link :href="route('insights')" class="text-slate-600 font-medium">Insights</Link>
        <Link :href="route('contact')" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero -->
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-slate-950">
        <!-- Developer GIF Background -->
        <div class="absolute inset-0 z-0">
          <img src="https://media.giphy.com/media/L1R1tvI9svkIWwpVYr/giphy.gif" alt="Developer Background" class="w-full h-full object-cover opacity-95 mix-blend-luminosity grayscale" />
          <div class="absolute inset-0 bg-gradient-to-b from-slate-950/40 via-slate-950/80 to-[#F8FAFC] z-10"></div>
        </div>
        
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
              <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
              Top Rated Software Agency
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-3xl md:text-4xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-white">
              Building Scalable Digital Products for <br /><span class="gradient-text">Global Businesses</span>
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl mx-auto leading-relaxed">
              We engineer enterprise-grade software, intuitive mobile apps, and robust cloud solutions that empower ambitious companies to scale faster and innovate smarter.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <Link :href="route('contact')" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-medium text-base transition-all shadow-lg shadow-blue-600/20 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Start Your Project <ArrowRightIcon class="w-4 h-4" />
              </Link>
              <Link :href="route('case-study')" class="w-full sm:w-auto px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-medium text-base transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2 backdrop-blur-sm">
                View Case Studies
              </Link>
            </div>
          </Reveal>

          <Reveal :delay="400">
            <div class="mt-16 text-sm font-medium text-slate-400 uppercase tracking-widest mb-6">
              Trusted by innovative teams worldwide
            </div>
            
            <div class="w-full max-w-5xl mx-auto overflow-hidden relative glass-card-dark py-6 rounded-2xl">
              <div class="absolute left-0 top-0 w-16 h-full bg-gradient-to-r from-slate-950/80 to-transparent z-10"></div>
              <div class="absolute right-0 top-0 w-16 h-full bg-gradient-to-l from-slate-950/80 to-transparent z-10"></div>
              <div class="flex w-[200%] animate-marquee">
                <div v-for="set in 2" :key="set" class="flex justify-around w-full items-center">
                  <span v-for="(logo, i) in ['Acme Corp', 'GlobalTech', 'CoreVisys', 'Stark Ind.', 'Wayne Ent.', 'CyberDyne']" :key="`${set}-${i}`" class="text-xl font-sora font-bold text-slate-400 opacity-60">
                    {{ logo }}
                  </span>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- Services Section -->
      <section id="services" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                <span class="w-2 h-2 rounded-full bg-blue-600 mr-2 animate-pulse"></span>
                Our Expertise
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold mb-6 text-slate-900">End-to-End Development Solutions</h2>
              <p class="text-slate-600 text-lg">We transform complex ideas into elegant, highly functional digital solutions tailored to your specific industry needs.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(service, index) in SERVICES" :key="index" :delay="index * 100">
              <component :is="service.link ? Link : 'div'" :href="service.link" class="p-8 rounded-3xl bg-[#F8FAFC] border border-slate-100 card-hover-effect group relative overflow-hidden h-full block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-600 mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                  <component :is="service.icon" class="w-6 h-6" />
                </div>
                <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-slate-900">{{ service.title }}</h3>
                <p class="text-slate-600 leading-relaxed relative z-10">{{ service.desc }}</p>
                <div class="mt-6 flex items-center text-blue-600 font-medium text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-[-10px] group-hover:translate-x-0 duration-300">
                  Learn more <ChevronRightIcon class="w-4 h-4 ml-1" />
                </div>
              </component>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Stats Section -->
      <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-10"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-10 md:gap-6 text-center">
            <Reveal v-for="(stat, index) in STATS" :key="index" :delay="index * 100">
              <div class="flex flex-col items-center">
                <div class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 mb-2 font-sora">
                  {{ stat.value }}
                </div>
                <div class="text-slate-400 font-medium tracking-wide text-sm uppercase">
                  {{ stat.label }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Portfolio Section -->
      <section id="portfolio" class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                <span class="w-2 h-2 rounded-full bg-blue-600 mr-2 animate-pulse"></span>
                Case Studies
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold mb-4 text-slate-900">Featured Work</h2>
              <p class="text-slate-600 max-w-xl">Explore how we've helped ambitious companies scale their operations and disrupt their industries.</p>
            </Reveal>
            
            <Reveal :delay="200">
              <div class="flex flex-wrap gap-2">
                <button 
                  v-for="f in filters" 
                  :key="f"
                  @click="filter = f"
                  :class="[
                    'px-4 py-2 rounded-full text-sm font-medium transition-all',
                    filter === f ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300'
                  ]"
                >
                  {{ f }}
                </button>
              </div>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(project, index) in filteredProjects" :key="`${project.title}-${index}`" :delay="index * 100">
              <Link :href="route('case-study.show', project.slug)" class="block group rounded-3xl overflow-hidden bg-white border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="relative h-60 overflow-hidden">
                  <div class="absolute inset-0 bg-slate-900/10 group-hover:bg-transparent transition-colors z-10"></div>
                  <img 
                    :src="project.img" 
                    :alt="project.title" 
                    class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                  />
                  <div class="absolute top-4 left-4 z-20">
                    <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-bold text-slate-800">
                      {{ project.category }}
                    </span>
                  </div>
                </div>
                <div class="p-6">
                  <h3 class="text-xl font-sora font-bold mb-3 group-hover:text-blue-600 transition-colors text-slate-900">{{ project.title }}</h3>
                  <div class="flex flex-wrap gap-2 mb-4">
                    <span v-for="tag in project.tags" :key="tag" class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-xs font-medium">
                      {{ tag }}
                    </span>
                  </div>
                  <div class="flex items-center text-blue-600 font-medium text-sm">
                    View Case Study <ArrowRightIcon class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" />
                  </div>
                </div>
              </Link>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Process and FAQ -->
      <section class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Why Choose Us / Process -->
            <div>
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                  <span class="w-2 h-2 rounded-full bg-blue-600 mr-2 animate-pulse"></span>
                  The Process
                </div>
                <h2 class="text-3xl md:text-4xl font-sora font-bold mb-6 text-slate-900">How We Build Silicon Valley Standard Products</h2>
                
                <div class="space-y-8 mt-10 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                  <div v-for="(step, idx) in PROCESS" :key="idx" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-blue-100 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                      <CheckCircleIcon class="w-5 h-5" />
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-xl border border-slate-100 bg-slate-50 shadow-sm group-hover:shadow-md transition-shadow">
                      <h4 class="font-bold text-slate-900 mb-1 font-sora">{{ step.title }}</h4>
                      <p class="text-sm text-slate-600">{{ step.desc }}</p>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- FAQ -->
            <div>
              <Reveal :delay="200">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                  <span class="w-2 h-2 rounded-full bg-blue-600 mr-2 animate-pulse"></span>
                  FAQ
                </div>
                <h2 class="text-3xl md:text-4xl font-sora font-bold mb-6 text-slate-900">Common Questions</h2>
                
                <div class="space-y-4 mt-10">
                  <div 
                    v-for="(faq, index) in FAQS" 
                    :key="index" 
                    :class="['border border-slate-200 rounded-2xl overflow-hidden transition-all duration-300', openFaq === index ? 'bg-slate-50 shadow-sm' : 'bg-white']"
                  >
                    <button 
                      class="w-full px-6 py-5 text-left font-semibold flex justify-between items-center focus:outline-none text-slate-900 font-sora"
                      @click="openFaq = openFaq === index ? null : index"
                    >
                      {{ faq.q }}
                      <ChevronRightIcon 
                        :class="['w-5 h-5 text-slate-400 transition-transform duration-300', openFaq === index ? 'rotate-90 text-blue-600' : '']" 
                      />
                    </button>
                    <div 
                      :class="['px-6 overflow-hidden transition-all duration-300', openFaq === index ? 'max-h-40 pb-5 opacity-100' : 'max-h-0 opacity-0']"
                    >
                      <p class="text-slate-600 text-sm leading-relaxed">{{ faq.a }}</p>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-slate-900"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-full bg-blue-600/20 blur-3xl rounded-full pointer-events-none"></div>
        
        <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-4xl md:text-6xl font-extrabold text-white mb-6 font-sora">
              Ready to build something <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">extraordinary?</span>
            </h2>
            <p class="text-xl text-slate-400 mb-10 max-w-2xl mx-auto">
              Partner with us to transform your vision into a scalable, high-performance digital reality. Get a free technical consultation today.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <button class="px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-full font-bold text-lg transition-all shadow-xl shadow-blue-500/20 transform hover:-translate-y-1">
                Book a Free Strategy Call
              </button>
              <button class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-bold text-lg transition-all backdrop-blur-sm">
                Contact Sales
              </button>
            </div>
          </Reveal>
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
              <li><Link :href="route('company')" class="hover:text-blue-400 transition-colors">About Us</Link></li>
              <li><Link :href="route('case-study')" class="hover:text-blue-400 transition-colors">Case Studies</Link></li>
              <li><Link :href="route('careers')" class="hover:text-blue-400 transition-colors">Careers</Link></li>
              <li><Link :href="route('insights')" class="hover:text-blue-400 transition-colors">Blog</Link></li>
              <li><Link :href="route('contact')" class="hover:text-blue-400 transition-colors">Contact</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Legal</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy-policy" class="hover:text-blue-400 transition-colors">Privacy Policy</Link></li>
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
    
    <!-- Floating WhatsApp / Chat Button -->
    <button class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:bg-blue-700 hover:scale-110 transition-all z-50">
      <MailIcon class="w-6 h-6" />
    </button>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Reveal from '@/Components/Reveal.vue';
import { 
  Menu as MenuIcon, 
  X as XIcon, 
  ChevronRight as ChevronRightIcon, 
  Code as CodeIcon, 
  Smartphone as SmartphoneIcon, 
  Globe as GlobeIcon, 
  Cpu as CpuIcon, 
  Cloud as CloudIcon, 
  ArrowRight as ArrowRightIcon, 
  CheckCircle as CheckCircleIcon, 
  Layers as LayersIcon, 
  Mail as MailIcon, 
  MapPin as MapPinIcon, 
  Phone as PhoneIcon 
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const openFaq = ref(0);
const filter = ref('All');

const filters = ['All', 'SaaS', 'Mobile Apps', 'Web Apps', 'ERP', 'AI Systems'];

const SERVICES = [
  { icon: CodeIcon, title: "Custom Software", desc: "Enterprise-grade scalable systems built to solve complex business challenges with modern architectures.", link: "/services/custom-software" },
  { icon: SmartphoneIcon, title: "Mobile Apps", desc: "High-performance iOS and Android applications with native-like experiences using React Native and Flutter.", link: "/services/mobile-apps" },
  { icon: GlobeIcon, title: "Web Platforms", desc: "Modern, responsive, and SEO-optimized web applications utilizing Next.js, React, and robust APIs.", link: "/services/web-applications" },
  { icon: CpuIcon, title: "AI Automation", desc: "Integrate LLMs, machine learning models, and automated workflows to supercharge productivity.", link: "/services/ai-ml" },
  { icon: LayersIcon, title: "ERP Solutions", desc: "Custom Enterprise Resource Planning software to unify your operations, finance, and HR seamlessly." },
  { icon: CloudIcon, title: "Cloud Infrastructure", desc: "Secure, scalable cloud deployments and DevOps pipelines on AWS, Google Cloud, and Azure.", link: "/services/cloud-solutions" }
];

const PORTFOLIO = [
  { slug: "corevisys-analytics", category: "SaaS", title: "CoreVisys Analytics", img: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80", tags: ["Next.js", "Python", "AWS"] },
  { slug: "finflow-banking-app", category: "Mobile Apps", title: "FinFlow Banking App", img: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80", tags: ["React Native", "Node.js"] },
  { slug: "global-logistics-pro", category: "ERP", title: "Global Logistics Pro", img: "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80", tags: ["Laravel", "Vue", "PostgreSQL"] },
  { slug: "healthtech-portal", category: "Web Apps", title: "HealthTech Portal", img: "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80", tags: ["React", "TypeScript"] },
  { slug: "autocrm-ai", category: "AI Systems", title: "AutoCRM AI", img: "https://images.unsplash.com/photo-1535223289827-42f1e9919769?auto=format&fit=crop&w=800&q=80", tags: ["OpenAI", "Python", "Vue"] },
  { slug: "cloudsync-workspace", category: "SaaS", title: "CloudSync Workspace", img: "https://images.unsplash.com/photo-1600132806370-bf17e65e942f?auto=format&fit=crop&w=800&q=80", tags: ["Vue", "Tailwind", "Supabase"] }
];

const STATS = [
  { value: "200+", label: "Projects Delivered" },
  { value: "50+", label: "Enterprise Clients" },
  { value: "99%", label: "Client Retention" },
  { value: "10+", label: "Years Experience" }
];

const PROCESS = [
  { title: "Discovery & Architecture", desc: "We map out user journeys, design database schemas, and establish technical architecture." },
  { title: "Agile Sprints & UI/UX", desc: "Rapid prototyping and continuous deployment cycles using modern Vue/Inertia frameworks." },
  { title: "QA & Cloud Deployment", desc: "Rigorous testing, containerization with Docker, and automated CI/CD pipelines via Vercel/AWS." }
];

const FAQS = [
  { q: "What is your typical project timeline?", a: "Project timelines vary depending on scope and complexity. A standard MVP typically takes 8-12 weeks, while enterprise solutions can take 4-6 months." },
  { q: "Do you provide post-launch support?", a: "Yes, we offer comprehensive SLA-based maintenance and support packages to ensure your software remains secure, updated, and optimized." },
  { q: "What technology stack do you use?", a: "We primarily utilize Vue.js/Inertia for frontend, Node.js/Laravel for backend, and deploy on AWS or Vercel. We select the best tool for your specific requirements." },
  { q: "How do you ensure data security?", a: "We follow industry best practices including ISO 27001 standards, end-to-end encryption, regular penetration testing, and robust CI/CD security checks." }
];

const filteredProjects = computed(() => {
  return filter.value === 'All' 
    ? PORTFOLIO 
    : PORTFOLIO.filter(p => p.category === filter.value);
});

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;
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

.hero-gradient {
  background: radial-gradient(circle at 50% -20%, rgba(37, 99, 235, 0.15), rgba(248, 250, 252, 0) 60%);
}

.grid-bg {
  background-size: 40px 40px;
  background-image: linear-gradient(to right, rgba(15, 23, 42, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(15, 23, 42, 0.05) 1px, transparent 1px);
  mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
  -webkit-mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
}

.glass-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.glass-card-dark {
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.glass-nav {
  background: rgba(248, 250, 252, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(15, 23, 42, 0.05);
}

.animate-marquee {
  animation: marquee 35s linear infinite;
}

@keyframes marquee {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-50%); }
}

.card-hover-effect {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card-hover-effect:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
  border-color: rgba(37, 99, 235, 0.2);
}

.gradient-text {
  background: linear-gradient(135deg, #2563EB 0%, #06B6D4 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
