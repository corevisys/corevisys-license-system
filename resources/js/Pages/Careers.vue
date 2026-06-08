<template>
  <Head title="Careers at CoreVisys — Join Our Team" />
  
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
          <Link href="/insights" :class="['transition-colors', scrolled ? 'hover:text-blue-600' : 'hover:text-white']">Insights</Link>
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
        <Link href="/insights" class="text-slate-600 font-medium">Insights</Link>
                <Link :href="route('contact')" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- 1. Hero Section -->
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-slate-950 min-h-[85vh] flex items-center">
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 w-full text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-8 tracking-wide uppercase">
              Join Our Team
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-white max-w-4xl mx-auto">
              Build the Future With <br/>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">CoreVisys</span>
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-lg md:text-xl text-slate-300 mb-10 leading-relaxed max-w-2xl mx-auto">
              Join our passionate team of engineers and designers to work on innovative software solutions that make a real-world impact.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <a href="#positions" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-bold transition-all shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 transform hover:-translate-y-1">
                View Open Positions <ArrowDownIcon class="w-5 h-5" />
              </a>
              <a href="#apply" class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-bold transition-all flex items-center justify-center backdrop-blur-sm transform hover:-translate-y-1">
                Apply Now
              </a>
            </div>
          </Reveal>
        </div>
        
        <!-- Decorative Background Elements -->
        <div class="absolute top-1/4 -left-64 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 -right-64 w-[600px] h-[600px] bg-cyan-600/10 rounded-full blur-[150px] pointer-events-none"></div>
      </section>

      <!-- 2. Why Join Us -->
      <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="text-center max-w-3xl mx-auto mb-16">
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900 mb-6">Why Join Us?</h2>
              <p class="text-lg text-slate-600">We invest heavily in our people because we know that great software is built by happy, healthy, and supported teams.</p>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(benefit, i) in benefits" :key="i" :delay="i * 100">
              <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-blue-200 transition-colors h-full card-hover-effect">
                <div class="w-14 h-14 bg-white text-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-sm relative z-10 border border-slate-100 group-hover:scale-110 transition-transform duration-300">
                  <component :is="benefit.icon" class="w-7 h-7" />
                </div>
                <h3 class="text-xl font-bold font-sora text-slate-900 mb-3 relative z-10">{{ benefit.title }}</h3>
                <p class="text-slate-600 relative z-10 text-sm leading-relaxed">{{ benefit.desc }}</p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 3. Company Culture -->
      <section class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                  Our Culture
                </div>
                <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900 mb-8 leading-tight">
                  A place where innovation meets collaboration
                </h2>
              </Reveal>
              
              <div class="space-y-6">
                <Reveal :delay="100">
                  <div class="flex gap-4">
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0 mt-1">
                      <CheckIcon class="w-4 h-4" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 text-lg mb-1">Engineering Excellence</h4>
                      <p class="text-slate-600 text-sm leading-relaxed">We write clean, testable code. You will be surrounded by senior engineers who value code reviews and architectural discussions.</p>
                    </div>
                  </div>
                </Reveal>
                <Reveal :delay="200">
                  <div class="flex gap-4">
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0 mt-1">
                      <CheckIcon class="w-4 h-4" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 text-lg mb-1">Diversity & Inclusion</h4>
                      <p class="text-slate-600 text-sm leading-relaxed">We believe diverse teams build better products. We are an equal-opportunity employer fostering an inclusive environment.</p>
                    </div>
                  </div>
                </Reveal>
                <Reveal :delay="300">
                  <div class="flex gap-4">
                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0 mt-1">
                      <CheckIcon class="w-4 h-4" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 text-lg mb-1">Continuous Growth</h4>
                      <p class="text-slate-600 text-sm leading-relaxed">Get access to premium courses, tech conferences, and dedicated time for R&D projects.</p>
                    </div>
                  </div>
                </Reveal>
              </div>
            </div>
            
            <Reveal :delay="200" class="relative">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-4 pt-12">
                  <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=500&q=80" alt="Team Collaboration" class="rounded-2xl shadow-lg w-full h-48 object-cover" />
                  <img src="https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=500&q=80" alt="Meeting" class="rounded-2xl shadow-lg w-full h-64 object-cover" />
                </div>
                <div class="space-y-4">
                  <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=500&q=80" alt="Workshop" class="rounded-2xl shadow-lg w-full h-64 object-cover" />
                  <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?auto=format&fit=crop&w=500&q=80" alt="Office Culture" class="rounded-2xl shadow-lg w-full h-48 object-cover" />
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 4. Open Positions -->
      <section id="positions" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
              <div>
                <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900 mb-4">Open Positions</h2>
                <p class="text-slate-600 text-lg">Find your perfect role and apply today.</p>
              </div>
              <div class="flex gap-2">
                <button class="px-4 py-2 rounded-full bg-blue-50 text-blue-600 font-medium text-sm">All</button>
                <button class="px-4 py-2 rounded-full bg-slate-50 text-slate-600 font-medium text-sm hover:bg-slate-100">Engineering</button>
                <button class="px-4 py-2 rounded-full bg-slate-50 text-slate-600 font-medium text-sm hover:bg-slate-100">Design</button>
              </div>
            </div>
          </Reveal>
          
          <div class="space-y-4">
            <Reveal v-for="(job, index) in jobs" :key="index" :delay="index * 50">
              <div class="group bg-slate-50 border border-slate-100 rounded-2xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-xl hover:border-blue-200 transition-all duration-300">
                <div class="flex-grow">
                  <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-xs font-bold text-slate-600 shadow-sm">{{ job.department }}</span>
                    <span class="flex items-center text-xs font-medium text-slate-500"><MapPinIcon class="w-3 h-3 mr-1" /> {{ job.location }}</span>
                    <span class="flex items-center text-xs font-medium text-slate-500"><ClockIcon class="w-3 h-3 mr-1" /> {{ job.type }}</span>
                  </div>
                  <h3 class="text-2xl font-sora font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">{{ job.title }}</h3>
                  <p class="text-sm text-slate-600 max-w-3xl leading-relaxed">{{ job.desc }}</p>
                </div>
                
                <div class="flex-shrink-0 flex flex-col items-end gap-3">
                  <span class="text-sm font-semibold text-slate-500">{{ job.experience }}</span>
                  <button class="px-8 py-3 bg-slate-900 hover:bg-blue-600 text-white rounded-full font-semibold transition-all shadow-md flex items-center gap-2 group-hover:-translate-y-1">
                    Apply <ArrowRightIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 5. Hiring Process -->
      <section class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-900/40 via-slate-900 to-slate-900"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <Reveal>
            <div class="text-center mb-20">
              <h2 class="text-3xl md:text-4xl font-sora font-bold mb-4">Our Hiring Process</h2>
              <p class="text-slate-400">Transparent, fast, and respectful of your time.</p>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
            <Reveal v-for="(step, idx) in processSteps" :key="idx" :delay="idx * 100">
              <div class="relative flex flex-col items-center text-center">
                <!-- Connector Line -->
                <div v-if="idx < processSteps.length - 1" class="hidden md:block absolute top-8 left-[60%] w-full h-[2px] bg-slate-700"></div>
                
                <div class="w-16 h-16 rounded-2xl bg-slate-800 border-2 border-blue-500/30 flex items-center justify-center text-blue-400 text-xl font-bold font-sora mb-6 relative z-10 shadow-lg shadow-blue-900/50">
                  {{ idx + 1 }}
                </div>
                <h4 class="text-lg font-bold font-sora text-white mb-3">{{ step.title }}</h4>
                <p class="text-sm text-slate-400 leading-relaxed">{{ step.desc }}</p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 6. Employee Testimonials -->
      <section class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-16">
              <h2 class="text-3xl font-sora font-bold text-slate-900 mb-4">Hear From Our Team</h2>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Reveal v-for="(test, idx) in testimonials" :key="idx" :delay="idx * 100">
              <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden h-full">
                <QuoteIcon class="w-12 h-12 text-slate-100 absolute top-6 right-6" />
                <p class="text-slate-600 relative z-10 mb-8 italic">"{{ test.quote }}"</p>
                <div class="flex items-center gap-4 border-t border-slate-50 pt-6">
                  <img :src="test.img" :alt="test.name" class="w-12 h-12 rounded-full object-cover shadow-sm" />
                  <div>
                    <h4 class="font-bold text-slate-900 text-sm">{{ test.name }}</h4>
                    <p class="text-xs text-blue-600 font-medium">{{ test.role }}</p>
                  </div>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 7. FAQ Section -->
      <section class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-16">
              <h2 class="text-3xl font-sora font-bold text-slate-900 mb-4">Frequently Asked Questions</h2>
            </div>
          </Reveal>
          
          <div class="space-y-4">
            <Reveal v-for="(faq, idx) in faqs" :key="idx" :delay="idx * 50">
              <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 transition-colors">
                <button 
                  @click="toggleFaq(idx)" 
                  class="w-full px-6 py-5 flex items-center justify-between font-bold text-slate-900 text-left focus:outline-none hover:bg-slate-100 transition-colors"
                >
                  {{ faq.question }}
                  <ChevronDownIcon 
                    :class="['w-5 h-5 text-slate-400 transition-transform duration-300', openFaq === idx ? 'rotate-180 text-blue-600' : '']" 
                  />
                </button>
                <div 
                  v-show="openFaq === idx" 
                  class="px-6 pb-5 text-slate-600 text-sm leading-relaxed"
                >
                  {{ faq.answer }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 8. Final CTA -->
      <section id="apply" class="py-24 bg-blue-600 text-white relative overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-10"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/10 blur-3xl rounded-full pointer-events-none"></div>
        
        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 font-sora">
              Ready to Start Your Journey?
            </h2>
            <p class="text-lg text-blue-100 mb-10 max-w-2xl mx-auto">
              Whether you're an experienced professional or a recent graduate, we want to hear from you. Check our open roles or reach out to our team.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <a href="#positions" class="px-8 py-4 bg-white hover:bg-slate-50 text-blue-600 rounded-full font-bold text-base transition-all shadow-xl transform hover:-translate-y-0.5">
                Apply Now
              </a>
              <button class="px-8 py-4 bg-blue-700/50 hover:bg-blue-700 border border-blue-500/50 text-white rounded-full font-bold text-base transition-all backdrop-blur-sm">
                Contact HR
              </button>
            </div>
          </Reveal>
        </div>
      </section>
    </main>

    <!-- 9. Footer -->
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
              <li><Link href="/company" class="hover:text-blue-400 transition-colors">About Us</Link></li>
              <li><Link href="/case-study" class="hover:text-blue-400 transition-colors">Case Studies</Link></li>
              <li><Link :href="route('careers')" class="hover:text-blue-400 transition-colors text-blue-500 font-semibold">Careers</Link></li>
              <li><Link href="/insights" class="hover:text-blue-400 transition-colors">Blog</Link></li>
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
  ArrowDown as ArrowDownIcon,
  ArrowRight as ArrowRightIcon,
  MapPin as MapPinIcon,
  Clock as ClockIcon,
  Briefcase as BriefcaseIcon,
  Heart as HeartIcon,
  Globe as GlobeIcon,
  Coffee as CoffeeIcon,
  Users as UsersIcon,
  BookOpen as BookOpenIcon,
  Check as CheckIcon,
  Quote as QuoteIcon,
  ChevronDown as ChevronDownIcon,
  Mail as MailIcon,
  Phone as PhoneIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);

const openFaq = ref(0);
const toggleFaq = (index) => {
  openFaq.value = openFaq.value === index ? -1 : index;
};

const benefits = [
  { icon: GlobeIcon, title: "Flexible Work Environment", desc: "Work from our modern office or remotely from anywhere in the world. We value results over hours." },
  { icon: BriefcaseIcon, title: "Competitive Salary", desc: "Top-tier compensation packages with performance bonuses, equity options, and yearly reviews." },
  { icon: BookOpenIcon, title: "Learning & Growth", desc: "Generous budget for courses, certifications, and attending global tech conferences." },
  { icon: HeartIcon, title: "Health & Wellness", desc: "Comprehensive premium health insurance, mental wellness programs, and gym memberships." },
  { icon: CoffeeIcon, title: "Modern Equipment", desc: "Choose your own gear. MacBook Pros, ultra-wide monitors, and ergonomic home-office setups." },
  { icon: UsersIcon, title: "Friendly Team Culture", desc: "Regular team retreats, hackathons, and collaborative environment free of corporate bureaucracy." }
];

const jobs = [
  { title: "Senior Frontend Developer", department: "Engineering", location: "Remote / Hybrid", type: "Full-time", experience: "5+ Years", desc: "Looking for an expert in Vue.js and React to architect scalable SPAs and lead our frontend initiatives." },
  { title: "Backend Engineer (Node/Laravel)", department: "Engineering", location: "Remote", type: "Full-time", experience: "3+ Years", desc: "Build resilient microservices and RESTful APIs supporting high-traffic enterprise applications." },
  { title: "UI/UX Product Designer", department: "Design", location: "New York / Hybrid", type: "Full-time", experience: "4+ Years", desc: "Design intuitive, premium user interfaces and lead the product discovery process from wireframes to high-fidelity." },
  { title: "DevOps Engineer", department: "Engineering", location: "Remote", type: "Full-time", experience: "4+ Years", desc: "Manage AWS infrastructure, optimize CI/CD pipelines, and ensure high availability across environments." },
  { title: "AI/ML Engineer", department: "Engineering", location: "Remote", type: "Full-time", experience: "3+ Years", desc: "Integrate Large Language Models and build custom automated workflows for our SaaS products." },
  { title: "Technical Project Manager", department: "Product", location: "London / Remote", type: "Full-time", experience: "5+ Years", desc: "Lead agile squads, manage client expectations, and ensure successful delivery of complex software projects." }
];

const processSteps = [
  { title: "Application Review", desc: "Our team reviews your resume and portfolio within 48 hours." },
  { title: "HR Interview", desc: "A 30-minute introductory call to discuss culture fit and your career goals." },
  { title: "Technical Assessment", desc: "A practical take-home challenge or live pair-programming session." },
  { title: "Final Interview", desc: "Meet with the founders and your future team lead." },
  { title: "Offer & Onboarding", desc: "We make an offer. Once accepted, your gear is shipped and onboarding begins!" }
];

const testimonials = [
  { name: "Elena Rodriguez", role: "Senior Frontend Engineer", quote: "Joining CoreVisys was the best career move I've made. The engineering culture here is unparalleled, and I'm given the autonomy to make real architectural decisions.", img: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" },
  { name: "James Chen", role: "DevOps Lead", quote: "What I love most is the lack of bureaucracy. If you have a good idea for improving our infrastructure, you can implement it. The trust leadership puts in the team is amazing.", img: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" },
  { name: "Sarah Jenkins", role: "UI/UX Designer", quote: "As a designer, it's rare to work with developers who care this much about the micro-interactions. We truly work as a unified team to build premium products.", img: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80" }
];

const faqs = [
  { question: "Is CoreVisys fully remote?", answer: "We operate on a remote-first model. You can work entirely from home from anywhere in the world, or if you prefer, work from our hub offices in New York and London." },
  { question: "What is the typical hiring timeline?", answer: "We move fast. The entire process from application to offer typically takes 2-3 weeks, depending on your availability for interviews." },
  { question: "Do you offer internships?", answer: "Yes! We run a 3-month paid summer internship program for rising college seniors and recent bootcamp graduates. Applications open in March." },
  { question: "What tech stack do you use?", answer: "Our modern stack primarily consists of Vue.js, React, Laravel, Node.js, Tailwind CSS, PostgreSQL, and AWS infrastructure." },
  { question: "Can I apply for multiple roles?", answer: "Yes, you can apply for multiple roles if your skill set aligns with them. However, we recommend focusing on the one role that best matches your expertise." }
];

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
.font-inter { font-family: 'Inter', sans-serif; }
.font-sora { font-family: 'Sora', sans-serif; }

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

.glass-nav {
  background: rgba(248, 250, 252, 0.85);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(15, 23, 42, 0.05);
}

.card-hover-effect {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card-hover-effect:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
  border-color: rgba(37, 99, 235, 0.2);
}
</style>
