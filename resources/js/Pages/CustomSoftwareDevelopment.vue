<template>
  <Head title="Custom Software Development — CoreVisys" />
  
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
              <CodeIcon class="w-4 h-4 mr-2" />
              Premium Service Offerings
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-3xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-white">
              Custom Software Built for Your Business Needs
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-base md:text-xl text-slate-300 mb-8 max-w-2xl mx-auto leading-relaxed">
              We design and develop scalable, secure, and high-performance software tailored to your business operations.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
              <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-base transition-all shadow-lg hover:shadow-blue-500/20 transform hover:-translate-y-0.5 text-center">
                Start Your Project
              </Link>
              <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 font-medium text-base transition-all transform hover:-translate-y-0.5 text-center">
                Get Consultation
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

      <!-- Overview Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC] relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Why Custom Software?
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-slate-900 mb-6 leading-tight">
                Align Technology with Your Specific Workflows
              </h2>
              <p class="text-slate-600 text-lg leading-relaxed mb-6">
                Off-the-shelf software often forces your business to adapt its processes to rigid frameworks. Custom software flipped this paradigm, putting your processes and goals at the absolute core of the technological design.
              </p>
              <p class="text-slate-600 text-lg leading-relaxed">
                At CoreVisys, we construct enterprise-grade software that is engineered for scale, reliability, and ease of use. From optimizing internal logistics to establishing high-availability customer-facing systems, we craft every line of code to solve your specific challenges.
              </p>
            </Reveal>
            
            <Reveal :delay="200">
              <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200 bg-white p-8">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full"></div>
                <h3 class="font-sora font-bold text-xl text-slate-900 mb-6 flex items-center gap-3">
                  <CodeIcon class="w-6 h-6 text-blue-600" />
                  Our Engineering Manifesto
                </h3>
                <ul class="space-y-4">
                  <li class="flex items-start gap-4">
                    <CheckCircleIcon class="w-5 h-5 text-green-500 mt-1 shrink-0" />
                    <div>
                      <h4 class="font-semibold text-slate-900">Scalable & Distributed Design</h4>
                      <p class="text-slate-600 text-sm">Microservices and cloud-first patterns ensuring seamless scaling under heavy workloads.</p>
                    </div>
                  </li>
                  <li class="flex items-start gap-4">
                    <CheckCircleIcon class="w-5 h-5 text-green-500 mt-1 shrink-0" />
                    <div>
                      <h4 class="font-semibold text-slate-900">Type-Safe & Readable Codebases</h4>
                      <p class="text-slate-600 text-sm">We strictly enforce robust documentation, static typing, and modular architectures.</p>
                    </div>
                  </li>
                  <li class="flex items-start gap-4">
                    <CheckCircleIcon class="w-5 h-5 text-green-500 mt-1 shrink-0" />
                    <div>
                      <h4 class="font-semibold text-slate-900">Security-First Paradigm</h4>
                      <p class="text-slate-600 text-sm">Complete compliance with digital frameworks, absolute encryption, and data ownership.</p>
                    </div>
                  </li>
                </ul>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Solutions Section -->
      <section class="py-20 md:py-28 bg-white border-y border-slate-100 relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Solutions We Provide
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Expertly Engineered Applications</h2>
              <p class="text-slate-600 mt-4 text-lg">We deliver custom-built software across all critical operations of modern enterprises.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(sol, idx) in SOLUTIONS" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-[#F8FAFC] border border-slate-100 card-hover-effect group relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                  <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                  <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-600 mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <component :is="sol.icon" class="w-6 h-6" />
                  </div>
                  <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-slate-900">{{ sol.title }}</h3>
                  <p class="text-slate-600 leading-relaxed text-sm relative z-10">{{ sol.desc }}</p>
                </div>
                <div class="mt-6 flex items-center text-blue-600 font-medium text-sm">
                  Enterprise Ready <CheckCircleIcon class="w-4 h-4 ml-1.5 text-blue-500" />
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Interactive Timeline Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Our Process
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">The Software Development Lifecycle</h2>
              <p class="text-slate-600 mt-4 text-lg">Click on the phases below to explore deliverables and timelines.</p>
            </Reveal>
          </div>

          <div class="flex flex-col lg:flex-row gap-12 items-start relative">
            <!-- Timeline Buttons / Steps -->
            <div class="w-full lg:w-1/3 flex flex-col gap-4 sticky top-32 z-10">
              <button 
                v-for="(step, idx) in PROCESS_STEPS" 
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
                        <component :is="PROCESS_STEPS[activeStep].icon" class="w-5 h-5 text-blue-600" />
                        Phase 0{{ activeStep + 1 }}: {{ PROCESS_STEPS[activeStep].title }}
                      </div>
                      
                      <h3 class="text-2xl md:text-3xl font-sora font-extrabold text-slate-900 mb-6">
                        {{ PROCESS_STEPS[activeStep].subheadline }}
                      </h3>
                      
                      <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-8">
                        {{ PROCESS_STEPS[activeStep].description }}
                      </p>
                    </div>

                    <div class="pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Key Deliverables</h4>
                        <ul class="space-y-2">
                          <li 
                            v-for="(del, i) in PROCESS_STEPS[activeStep].deliverables" 
                            :key="i"
                            class="flex items-center gap-2.5 text-slate-700 text-sm font-medium"
                          >
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                            {{ del }}
                          </li>
                        </ul>
                      </div>

                      <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Target Completion</h4>
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                            <CalendarIcon class="w-5 h-5" />
                          </div>
                          <div>
                            <p class="text-slate-900 font-bold text-sm">{{ PROCESS_STEPS[activeStep].duration }}</p>
                            <p class="text-slate-500 text-xs">Project roadmap milestone</p>
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

      <!-- Key Benefits Section -->
      <section class="py-20 md:py-28 bg-white border-t border-slate-100 relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Key Benefits
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Why Modern Enterprises Partner with Us</h2>
              <p class="text-slate-600 mt-4 text-lg">We deliver lasting business outcomes, security, and full technological control.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(benefit, idx) in BENEFITS" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-[#F8FAFC] border border-slate-100 shadow-sm card-hover-effect h-full flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mb-6 shrink-0">
                  <component :is="benefit.icon" class="w-6 h-6" />
                </div>
                <h3 class="font-sora font-bold text-lg text-slate-900 mb-3">{{ benefit.title }}</h3>
                <p class="text-slate-600 text-sm leading-relaxed">{{ benefit.desc }}</p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Tech Stack Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Our Technology Stack
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Built with Industry-Leading Tech</h2>
              <p class="text-slate-600 mt-4 text-lg">We prioritize modern, battle-tested, and secure stacks to engineer solutions that scale.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <Reveal v-for="(stack, idx) in TECH_STACK" :key="idx" :delay="idx * 100">
              <div class="glass-card p-6 rounded-3xl bg-white border border-slate-200 shadow-sm h-full flex flex-col">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                  <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                    <component :is="stack.icon" class="w-5 h-5" />
                  </div>
                  <h3 class="font-sora font-bold text-slate-900 text-base">{{ stack.category }}</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="(tech, i) in stack.techs" 
                    :key="i"
                    class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold"
                  >
                    {{ tech }}
                  </span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section class="py-20 md:py-28 bg-white border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-6">
          <div class="text-center mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                FAQ
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Frequently Asked Questions</h2>
              <p class="text-slate-600 mt-4 text-lg">Clear answers to common questions about our custom development engagements.</p>
            </Reveal>
          </div>

          <div class="space-y-4">
            <Reveal v-for="(faq, index) in FAQ_ITEMS" :key="index" :delay="index * 80">
              <div class="rounded-2xl border border-slate-200 bg-[#F8FAFC] overflow-hidden transition-all duration-300">
                <button 
                  class="w-full p-6 text-left flex justify-between items-center gap-4 focus:outline-none"
                  @click="openFaq = (openFaq === index ? -1 : index)"
                >
                  <span class="font-sora font-bold text-slate-900 text-base md:text-lg">{{ faq.question }}</span>
                  <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-slate-600 shrink-0">
                    <ChevronDownIcon v-if="openFaq !== index" class="w-4 h-4 transition-transform duration-300" />
                    <ChevronUpIcon v-else class="w-4 h-4 transition-transform duration-300" />
                  </div>
                </button>
                <div 
                  v-show="openFaq === index" 
                  class="p-6 pt-0 border-t border-slate-200/50 text-slate-600 text-sm md:text-base leading-relaxed bg-white"
                >
                  {{ faq.answer }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-5xl mx-auto px-6">
          <Reveal>
            <div class="relative bg-slate-950 text-white rounded-3xl p-8 md:p-16 overflow-hidden shadow-2xl border border-slate-800">
              <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-blue-950/20 z-10"></div>
                <div class="absolute inset-0 grid-bg opacity-10"></div>
              </div>

              <div class="relative z-10 max-w-2xl">
                <h2 class="text-3xl md:text-5xl font-sora font-extrabold mb-6 leading-tight">
                  Ready to Accelerate Your Enterprise?
                </h2>
                <p class="text-slate-300 text-base md:text-lg mb-8 leading-relaxed">
                  Partner with CoreVisys to engineering scalable databases, custom integrations, and custom CRM architectures. Set up a free 30-minute discovery call today.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                  <Link :href="route('contact')" class="px-8 py-4 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-center transition-all shadow-lg hover:shadow-blue-500/25">
                    Schedule Strategy Call
                  </Link>
                  <Link :href="route('contact')" class="px-8 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold text-center transition-all">
                    Fill Consultation Form
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
              <li><Link href="/services/custom-software" class="hover:text-blue-400 transition-colors text-blue-400">Custom Software</Link></li>
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
  Cloud as CloudIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeStep = ref(0);
const openFaq = ref(0);

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const SOLUTIONS = [
  { icon: LayersIcon, title: "Enterprise Software", desc: "High-performance, distributed, and fault-tolerant platforms constructed using robust architectures to support millions of daily transactions." },
  { icon: ServerIcon, title: "ERP Systems", desc: "Tailored ERP systems that orchestrate your supply chain, financials, human resources, and operations with full end-to-end transparency." },
  { icon: DatabaseIcon, title: "CRM Systems", desc: "Custom CRM tools crafted specifically for your workflows, empowering your sales, marketing, and support teams to cultivate deeper customer relationships." },
  { icon: ActivityIcon, title: "Business Automation Tools", desc: "Automated processes, task scheduling, API bridges, and smart pipelines that eliminate human error and dramatically reduce overhead." },
  { icon: CodeIcon, title: "Internal Management Systems", desc: "Custom dashboards, inventory tracking, reporting suites, and role-based access control applications tailored to your specific organizational structure." }
];

const PROCESS_STEPS = [
  { 
    title: "Requirement Analysis", 
    subheadline: "Deep Discovery & Technical Alignment Workshops",
    duration: "Week 1 - 2", 
    icon: FileTextIcon,
    description: "We conduct intensive discovery sessions to thoroughly map out your functional specifications, target user personas, technical boundaries, and system goals. This forms the foundation of our engineering architecture.",
    deliverables: ["Detailed System Scope", "User Flow Diagrams", "Technical Feasibility Report"]
  },
  { 
    title: "Planning & Architecture", 
    subheadline: "Database Schema Design & Topology Selection",
    duration: "Week 2 - 3", 
    icon: DatabaseIcon,
    description: "Choosing the optimal tech stack, designing normalized database schemas, specifying third-party API configurations, and mapping out the secure cloud deployment topologies.",
    deliverables: ["Database Schema Models", "API Integration Spec Sheet", "Cloud Topology Diagram"]
  },
  { 
    title: "UI/UX Design", 
    subheadline: "Interactive High-Fidelity Prototypes & Aesthetics",
    duration: "Week 3 - 5", 
    icon: FigmaIcon,
    description: "Crafting beautiful, interactive mockups in Figma that bring your vision to life. We refine user interfaces until they are incredibly intuitive and perfectly align with your brand visual guides.",
    deliverables: ["Figma Mockups & Layouts", "Interactive Prototypes", "Design System Assets"]
  },
  { 
    title: "Development", 
    subheadline: "Writing Clean, Type-Safe, Modular Codebases",
    duration: "Week 5 - 12", 
    icon: TerminalIcon,
    description: "Our engineers build the backend endpoints and frontend layouts. We prioritize writing clean, self-documenting code with comprehensive automated tests and robust integration benchmarks.",
    deliverables: ["Fully-featured Source Code", "API Documentations", "Unit Test Suites"]
  },
  { 
    title: "Testing & QA", 
    subheadline: "Vulnerability Assessments & Performance Tuning",
    duration: "Week 12 - 14", 
    icon: ShieldIcon,
    description: "Rigorous testing protocols ensure reliability. We execute comprehensive regression, stress, load, security vulnerability, and cross-browser responsiveness tests.",
    deliverables: ["QA Completion Reports", "Security Assessment Logs", "Beta Testing Feedback"]
  },
  { 
    title: "Deployment & Support", 
    subheadline: "Zero-Downtime Deployment & Active Monitoring",
    duration: "Week 14+", 
    icon: PlayIcon,
    description: "Deploying your system containerized via Docker on AWS, GCP, or private servers. We configure real-time error reporting and maintain your application with proactive service SLAs.",
    deliverables: ["Live Production Release", "DevOps Pipelines Specs", "SLA Support Documentation"]
  }
];

const BENEFITS = [
  { icon: SparklesIcon, title: "Scalability & Elasticity", desc: "Engineered to scale with your user base, using containerized deployments (Docker, Kubernetes) and caching (Redis)." },
  { icon: LockIcon, title: "Enterprise-Grade Security", desc: "Built-in security controls, role-based access, data encryption (at rest/in transit), and OWASP compliance out of the box." },
  { icon: Share2Icon, title: "Seamless Integration", desc: "Connect effortlessly with external platforms, payment gateways, CRM/ERP systems, and legacy infrastructure via standard RESTful/GraphQL APIs." },
  { icon: KeyIcon, title: "100% Intellectual Property", desc: "We hand over 100% intellectual property ownership of the source code, databases, and deployment configurations." },
  { icon: ClockIcon, title: "Dedicated Support & SLAs", desc: "Enjoy post-deployment peace of mind with our dedicated maintenance plans, 24/7 uptime monitoring, and fast service SLA." }
];

const TECH_STACK = [
  { category: "Frontend Frameworks", icon: CodeIcon, techs: ["Vue.js", "React", "Next.js", "Tailwind CSS", "TypeScript"] },
  { category: "Backend Systems", icon: TerminalIcon, techs: ["Laravel", "Node.js (Express)", "Python (FastAPI)", "PHP 8.2"] },
  { category: "Database & Caching", icon: HardDriveIcon, techs: ["PostgreSQL", "MySQL", "Redis Cache", "MongoDB"] },
  { category: "Cloud & DevOps", icon: CloudIcon, techs: ["AWS Cloud", "Vercel", "Docker Engine", "GitHub Actions CI/CD"] }
];

const FAQ_ITEMS = [
  { 
    question: "How do we start the custom software engagement?", 
    answer: "We begin with a free initial discovery session to understand your business objectives. Following this, we outline a detailed proposal containing a technical roadmap, timeline, and exact pricing options." 
  },
  { 
    question: "How is the project pricing determined?", 
    answer: "Depending on the project scope and client preferences, we offer both fixed-price contracts (for well-defined scopes) and time-and-material engagement models (for agile, evolving projects)." 
  },
  { 
    question: "Who owns the custom software code?", 
    answer: "Once development is complete and final delivery is verified, all intellectual property rights and full source code ownership are permanently transferred to your company." 
  },
  { 
    question: "Do you offer long-term support and maintenance?", 
    answer: "Yes, we offer comprehensive post-launch SLA support plans that cover security updates, system health monitoring, performance tuning, and continuous feature updates." 
  }
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
