<template>
  <Head title="Web Application Development — CoreVisys" />
  
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
        <!-- Developer GIF Background -->
        <div class="absolute inset-0 z-0">
          <img src="https://media.giphy.com/media/L1R1tvI9svkIWwpVYr/giphy.gif" alt="Developer Background" class="w-full h-full object-cover opacity-95 mix-blend-luminosity grayscale" />
          <div class="absolute inset-0 bg-gradient-to-b from-slate-950/40 via-slate-950/80 to-[#F8FAFC] z-10"></div>
        </div>
        
        <div class="absolute inset-0 grid-bg opacity-15 z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
              <GlobeIcon class="w-4 h-4 mr-2" />
              Web Application Development
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-3xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-white">
              High-Performance Web Applications That Scale
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-base md:text-xl text-slate-300 mb-8 max-w-2xl mx-auto leading-relaxed">
              We build modern web apps that are fast, secure, and user-friendly. Tailored precisely to your workflows and customer needs.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
              <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-base transition-all shadow-lg hover:shadow-blue-500/20 transform hover:-translate-y-0.5 text-center">
                Build Your Web App
              </Link>
              <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 font-medium text-base transition-all transform hover:-translate-y-0.5 text-center">
                Talk to Experts
              </Link>
            </div>
          </Reveal>

          <Reveal :delay="400">
            <div class="flex items-center justify-center gap-6 text-sm text-slate-400 font-medium">
              <div class="flex items-center gap-2">
                <CalendarIcon class="w-4 h-4" />
                <span>Last Updated: <span class="text-white">May 21, 2026</span></span>
              </div>
              <div class="flex items-center gap-2">
                <CheckCircleIcon class="w-4 h-4 text-green-400" />
                <span>Bangladesh Compliance: <span class="text-white">Certified</span></span>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- What We Build Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC] relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                What We Build
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Custom Built Web Products</h2>
              <p class="text-slate-600 mt-4 text-lg">We deliver custom-built web applications that streamline operations and delight users.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(item, idx) in WHAT_WE_BUILD" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm card-hover-effect group relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                  <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                  <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-blue-600 mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <component :is="item.icon" class="w-6 h-6" />
                  </div>
                  <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-slate-900">{{ item.title }}</h3>
                  <p class="text-slate-600 leading-relaxed text-sm relative z-10">{{ item.desc }}</p>
                </div>
                <div class="mt-6 flex items-center text-blue-600 font-medium text-sm">
                  Explore Offering <ArrowRightIcon class="w-4 h-4 ml-1.5 transition-transform group-hover:translate-x-1" />
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Features Focus Section -->
      <section class="py-20 md:py-28 bg-white border-y border-slate-100 relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Features We Focus On
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-slate-900 mb-6 leading-tight">
                Engineering Modern Capabilities For High-Growth Operations
              </h2>
              <p class="text-slate-600 text-lg leading-relaxed mb-6">
                Web applications require rigorous architectural standards to ensure fast response times, safe transactions, and highly available access interfaces across the globe.
              </p>
              <p class="text-slate-600 text-lg leading-relaxed">
                We design and build customized web architectures focusing on robust reliability. Whether optimizing page-load paint speeds for thousands of organic visits or securing API bridges for cross-platform dashboards, our engineering checklist is comprehensive.
              </p>
            </Reveal>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <Reveal v-for="(feat, idx) in FEATURES" :key="idx" :delay="idx * 80">
                <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-blue-200 transition-colors shadow-sm">
                  <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mb-4 shrink-0">
                    <component :is="feat.icon" class="w-5 h-5" />
                  </div>
                  <h4 class="font-sora font-bold text-slate-900 text-base mb-2">{{ feat.title }}</h4>
                  <p class="text-slate-600 text-xs leading-relaxed">{{ feat.desc }}</p>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- Development Workflow Timeline -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Workflow
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Our Development Workflow</h2>
              <p class="text-slate-600 mt-4 text-lg">Rigorous milestones to guarantee clean deployment and high alignment.</p>
            </Reveal>
          </div>

          <div class="flex flex-col lg:flex-row gap-12 items-start relative">
            <!-- Timeline Buttons / Steps -->
            <div class="w-full lg:w-1/3 flex flex-col gap-4 sticky top-32 z-10">
              <button 
                v-for="(step, idx) in WORKFLOW_STEPS" 
                :key="idx"
                @click="activeStep = idx"
                :class="[
                  'p-5 rounded-2xl border text-left transition-all flex items-center gap-4 shadow-sm',
                  activeStep === idx 
                    ? 'bg-slate-900 border-slate-900 text-white transform -translate-y-1 shadow-lg' 
                    : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50'
                ]"
              >
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0', activeStep === idx ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600']">
                  0{{ idx + 1 }}
                </div>
                <div>
                  <h4 class="font-bold font-sora text-sm md:text-base leading-snug">{{ step.title }}</h4>
                  <p :class="['text-xs mt-0.5', activeStep === idx ? 'text-slate-300' : 'text-slate-500']">{{ step.duration }}</p>
                </div>
              </button>
            </div>

            <!-- Active Step Showcase -->
            <div class="w-full lg:w-2/3">
              <div class="glass-card p-8 md:p-12 rounded-3xl bg-white border border-slate-200 shadow-sm relative overflow-hidden min-h-[400px]">
                <div class="absolute top-0 right-0 w-48 h-48 bg-blue-50/50 rounded-bl-full -mr-12 -mt-12 -z-10"></div>
                
                <transition name="fade" mode="out-in">
                  <div :key="activeStep" class="flex flex-col justify-between h-full">
                    <div>
                      <div class="inline-flex items-center gap-2 text-blue-600 font-bold text-sm tracking-wider uppercase mb-6">
                        <component :is="WORKFLOW_STEPS[activeStep].icon" class="w-5 h-5 text-blue-600" />
                        Milestone 0{{ activeStep + 1 }}: {{ WORKFLOW_STEPS[activeStep].title }}
                      </div>
                      
                      <h3 class="text-2xl md:text-3xl font-sora font-extrabold text-slate-900 mb-6">
                        {{ WORKFLOW_STEPS[activeStep].subheadline }}
                      </h3>
                      
                      <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-8">
                        {{ WORKFLOW_STEPS[activeStep].description }}
                      </p>
                    </div>

                    <div class="pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Core Activities</h4>
                        <ul class="space-y-2">
                          <li 
                            v-for="(act, i) in WORKFLOW_STEPS[activeStep].activities" 
                            :key="i"
                            class="flex items-center gap-2.5 text-slate-700 text-sm font-medium"
                          >
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                            {{ act }}
                          </li>
                        </ul>
                      </div>

                      <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Milestone Duration</h4>
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                            <ClockIcon class="w-5 h-5" />
                          </div>
                          <div>
                            <p class="text-slate-900 font-bold text-sm">{{ WORKFLOW_STEPS[activeStep].duration }}</p>
                            <p class="text-slate-500 text-xs">Standard release roadmap</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Tech Stack Section -->
      <section class="py-20 md:py-28 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Tech Stack
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Our Core Tech Stack</h2>
              <p class="text-slate-600 mt-4 text-lg">We employ modern, highly efficient technologies ensuring modular code and rapid scale.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(stack, idx) in TECH_STACK" :key="idx" :delay="idx * 100">
              <div class="glass-card p-8 rounded-3xl bg-[#F8FAFC] border border-slate-200 shadow-sm h-full flex flex-col justify-between">
                <div>
                  <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200/60">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600">
                      <component :is="stack.icon" class="w-6 h-6" />
                    </div>
                    <h3 class="font-sora font-bold text-slate-900 text-base">{{ stack.category }}</h3>
                  </div>
                  <p class="text-slate-600 text-xs mb-6 leading-relaxed">{{ stack.desc }}</p>
                </div>
                <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-200/60">
                  <span 
                    v-for="(tech, i) in stack.techs" 
                    :key="i"
                    class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold shadow-sm"
                  >
                    {{ tech }}
                  </span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Performance Benefits Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Benefits
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Performance Benefits Built-In</h2>
              <p class="text-slate-600 mt-4 text-lg">We don't treat speed and scalability as afterthoughts. They are built into every build phase.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Reveal v-for="(benefit, idx) in PERFORMANCE_BENEFITS" :key="idx" :delay="idx * 100">
              <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group hover:border-blue-300 transition-colors h-full flex flex-col justify-between">
                <div>
                  <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-6">
                    <component :is="benefit.icon" class="w-6 h-6" />
                  </div>
                  <h3 class="text-xl font-bold font-sora text-slate-900 mb-3">{{ benefit.title }}</h3>
                  <p class="text-slate-600 text-sm leading-relaxed">{{ benefit.desc }}</p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-50 text-xs font-bold text-blue-600 uppercase tracking-widest">
                  {{ benefit.tag }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="py-20 md:py-28 bg-white">
        <div class="max-w-5xl mx-auto px-6">
          <Reveal>
            <div class="relative bg-slate-950 text-white rounded-3xl p-8 md:p-16 overflow-hidden shadow-2xl border border-slate-800">
              <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-blue-950/20 z-10"></div>
                <div class="absolute inset-0 grid-bg opacity-10"></div>
              </div>

              <div class="relative z-10 max-w-2xl">
                <h2 class="text-3xl md:text-5xl font-sora font-extrabold mb-6 leading-tight">
                  Let’s Build Your Web Application
                </h2>
                <p class="text-slate-300 text-base md:text-lg mb-8 leading-relaxed">
                  Have a web platform concept or ready to overhaul your current frontend system? Get in touch with our tech team today for a complete architecture blueprint consultation.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                  <Link :href="route('contact')" class="px-8 py-4 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-center transition-all shadow-lg hover:shadow-blue-500/25">
                    Build Your Web App
                  </Link>
                  <Link :href="route('contact')" class="px-8 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold text-center transition-all">
                    Talk to Experts
                  </Link>
                </div>
              </div>
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
              <li><Link href="/services/web-applications" class="hover:text-blue-400 transition-colors text-blue-400 font-semibold">Web Applications</Link></li>
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
  Shield as ShieldIcon,
  Calendar as CalendarIcon,
  CheckCircle as CheckCircleIcon,
  Mail as MailIcon, 
  MapPin as MapPinIcon, 
  Phone as PhoneIcon,
  Database as DatabaseIcon,
  Server as ServerIcon,
  Activity as ActivityIcon,
  ChevronDown as ChevronDownIcon,
  ChevronUp as ChevronUpIcon,
  Layers as LayersIcon,
  Smartphone as SmartphoneIcon,
  Cpu as CpuIcon,
  FileText as FileTextIcon,
  Figma as FigmaIcon,
  Play as PlayIcon,
  Clock as ClockIcon,
  Lock as LockIcon,
  Share2 as Share2Icon,
  Key as KeyIcon,
  Sparkles as SparklesIcon,
  Terminal as TerminalIcon,
  HardDrive as HardDriveIcon,
  Cloud as CloudIcon,
  Globe as GlobeIcon,
  Layout as LayoutIcon,
  ShoppingBag as ShoppingBagIcon,
  CalendarDays as CalendarDaysIcon,
  Search as SearchIcon,
  ArrowRight as ArrowRightIcon,
  Zap as ZapIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeStep = ref(0);

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const WHAT_WE_BUILD = [
  { icon: LayersIcon, title: "SaaS Platforms", desc: "Multi-tenant subscriptions platforms constructed using modern Next.js frontends and bulletproof databases." },
  { icon: ActivityIcon, title: "Dashboards", desc: "Real-time analytics tools mapping highly dense telemetry data into beautiful visualizations and UI grids." },
  { icon: ShoppingBagIcon, title: "E-commerce Systems", desc: "Elastic transaction backends, tailored product inventory systems, and localized checkout flows built to convert." },
  { icon: LayoutIcon, title: "Admin Panels", desc: "Enterprise operational controls with rigid role-based accessibility, logging, and audit records." },
  { icon: CalendarDaysIcon, title: "Booking Systems", desc: "Custom scheduling algorithms, calendar syncing APIs, and automated confirmation workflows." }
];

const FEATURES = [
  { icon: ZapIcon, title: "Speed Optimization", desc: "Static site generation, Redis query caching, and image CDNs for sub-second load benchmarks." },
  { icon: ShieldIcon, title: "Enterprise Security", desc: "OWASP compliance, OAuth secure session workflows, JWT token logic, and robust database sanitization." },
  { icon: SmartphoneIcon, title: "Responsive UI", desc: "Clean and dynamic layouts crafted meticulously using fluid CSS styles that load perfectly on all screens." },
  { icon: KeyIcon, title: "API Integration", desc: "Secure RESTful/GraphQL interfaces to orchestrate connections with external products, payment gateways, and CRM/ERP engines." },
  { icon: ActivityIcon, title: "Real-time Data", desc: "Websockets connectivity delivering immediate dashboard state modifications without page reloads." }
];

const WORKFLOW_STEPS = [
  { 
    title: "Idea Validation", 
    subheadline: "Scope Strategy Workshops & Core Alignment",
    duration: "Week 1", 
    icon: SearchIcon,
    description: "We work directly with your stakeholders to outline core functional criteria, review competitor telemetry, and translate business scope into concrete technical architectures.",
    activities: ["Stakeholder Workshops", "Competitor Review", "High-level Scope Sheets"]
  },
  { 
    title: "Wireframing & UX", 
    subheadline: "Designing High-Fidelity UI Scopes",
    duration: "Week 2 - 3", 
    icon: LayoutIcon,
    description: "Creating comprehensive, interactive wireframes that maps navigation pathways and button placements to streamline client workflows.",
    activities: ["Navigation Mapping", "Wireframe Blueprints", "Accessibility Audits"]
  },
  { 
    title: "UI/UX Design", 
    subheadline: "Premium Visual System Construction",
    duration: "Week 3 - 5", 
    icon: FigmaIcon,
    description: "Building breathtaking Figma layouts with micro-animations and cohesive design tokens that perfectly mesh with your core identity.",
    activities: ["Figma Layouts", "Interactive Design Tokens", "Design System Assets"]
  },
  { 
    title: "Full-stack Development", 
    subheadline: "Writing Clean, Optimized, and Scalable Source Code",
    duration: "Week 5 - 12", 
    icon: TerminalIcon,
    description: "Our developers construct database models, secure endpoints, and build responsive frontend layouts using Vue, React, or Next.js.",
    activities: ["Modular Databases Design", "Secure Endpoint Logic", "Responsive Frontend Views"]
  },
  { 
    title: "Deployment & QA", 
    subheadline: "Production Launch & Continuous Performance Monitoring",
    duration: "Week 12+", 
    icon: PlayIcon,
    description: "Deploying your containerized web application via modern CI/CD pipelines to AWS, Vercel, or custom private VPC servers.",
    activities: ["Automated CI/CD Workflows", "Rigorous Penetration QA", "Proactive Server Uptime Alerting"]
  }
];

const TECH_STACK = [
  { category: "React & Next.js", icon: CodeIcon, desc: "We utilize React and Next.js to craft exceptionally responsive client-side layout grids, dynamic server-side rendering logic, and lightning-fast page loading.", techs: ["React.js", "Next.js", "TypeScript", "Tailwind CSS"] },
  { category: "Node.js & Laravel", icon: TerminalIcon, desc: "Powering backends with Laravel and Node.js to create bulletproof RESTful controllers, safe route controllers, and clean SQL database mappings.", techs: ["Laravel 10", "Node.js", "Express.js", "Eloquent ORM"] },
  { category: "PostgreSQL & MongoDB", icon: HardDriveIcon, desc: "We utilize industry-grade database systems designed for high integrity transactions, optimized indexing, and robust data storage schemas.", techs: ["PostgreSQL", "MongoDB", "Redis Cache", "Mongoose"] }
];

const PERFORMANCE_BENEFITS = [
  { icon: ZapIcon, title: "Fast Load Times", desc: "Sub-second LCP (Largest Contentful Paint) targets using localized edge caching, server-side render methods, and assets compression protocols.", tag: "Google PageSpeed Core Web Vitals Approved" },
  { icon: GlobeIcon, title: "SEO Optimized Apps", desc: "Automatic robots crawling tags, fully dynamic sitemaps, structured schema data scripts, and lightning-fast server side render logic.", tag: "Organically Driven Search Rankings" },
  { icon: CloudIcon, title: "Scalable Architecture", desc: "Modular, distributed application layouts ready for effortless clustering behind elastic load balancers (AWS Elastic Beanstalk / ECS).", tag: "Horizontally Scalable Deployment Paths" }
];
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

.card-hover-effect {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.card-hover-effect:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
  border-color: rgba(37, 99, 235, 0.2);
}

/* Timeline animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
