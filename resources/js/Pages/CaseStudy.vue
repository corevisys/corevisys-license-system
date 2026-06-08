<template>
  <Head :title="`Case Study: ${caseStudy.title} — CoreVisys`" />
  
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
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-slate-950 min-h-[90vh] flex items-center">
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 w-full">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
              <Reveal :delay="0">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                  Featured Case Study
                </div>
              </Reveal>
              
              <Reveal :delay="100">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-white">
                  {{ caseStudy.title }}
                </h1>
              </Reveal>
              
              <Reveal :delay="200">
                <p class="text-lg text-slate-300 mb-10 leading-relaxed">
                  {{ caseStudy.description }}
                </p>
              </Reveal>
              
              <Reveal :delay="300">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-10 border-t border-slate-800 pt-8">
                  <div>
                    <div class="text-slate-400 text-sm font-medium mb-1">Industry</div>
                    <div class="text-white font-semibold">{{ caseStudy.industry }}</div>
                  </div>
                  <div>
                    <div class="text-slate-400 text-sm font-medium mb-1">Duration</div>
                    <div class="text-white font-semibold">{{ caseStudy.duration }}</div>
                  </div>
                  <div>
                    <div class="text-slate-400 text-sm font-medium mb-1">Services</div>
                    <div class="text-white font-semibold">{{ caseStudy.services }}</div>
                  </div>
                </div>
              </Reveal>

              <Reveal :delay="400">
                <div class="flex flex-col sm:flex-row gap-4">
                  <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-medium transition-all shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2">
                    Visit Live Project <ArrowUpRightIcon class="w-4 h-4" />
                  </button>
                  <a href="#contact" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-medium transition-all flex items-center justify-center backdrop-blur-sm">
                    Start Your Project
                  </a>
                </div>
              </Reveal>
            </div>
            
            <Reveal :delay="300" class="hidden lg:block relative">
              <div class="absolute inset-0 bg-blue-500/20 blur-3xl rounded-full"></div>
              <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-slate-700/50 bg-slate-900 transform rotate-2 hover:rotate-0 transition-transform duration-700">
                <div class="h-8 bg-slate-800 flex items-center px-4 gap-2 border-b border-slate-700">
                  <div class="w-3 h-3 rounded-full bg-red-500"></div>
                  <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                  <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <img :src="caseStudy.gallery[0].src" alt="Dashboard Preview" class="w-full h-auto opacity-90 hover:opacity-100 transition-opacity" />
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 2. Project Overview -->
      <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-12">
            <div class="md:col-span-4">
              <div class="sticky top-32">
                <Reveal>
                  <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900 mb-4">Project Overview</h2>
                  <p class="text-slate-500 leading-relaxed">Discover the core objectives and business drivers behind the OmniCloud Architecture transformation.</p>
                </Reveal>
              </div>
            </div>
            <div class="md:col-span-8 space-y-12">
              <Reveal :delay="100">
                <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100">
                  <h3 class="text-xl font-bold font-sora text-slate-900 mb-4 flex items-center gap-3">
                    <BuildingIcon class="w-6 h-6 text-blue-600" /> Client Background
                  </h3>
                  <p class="text-slate-600 leading-relaxed">
                    {{ caseStudy.description }}
                  </p>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. The Problem -->
      <section class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="text-center max-w-3xl mx-auto mb-16">
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900 mb-6">The Challenge</h2>
              <p class="text-lg text-slate-600">The existing system was bottlenecking business growth. We identified three critical failure points in their legacy architecture.</p>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Reveal v-for="(problem, i) in caseStudy.problems" :key="i" :delay="i * 100">
              <div class="bg-white p-8 rounded-3xl border border-red-100 shadow-sm relative overflow-hidden group hover:border-red-300 transition-colors h-full">
                <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mb-6 relative z-10">
                  <ShieldAlertIcon class="w-6 h-6" />
                </div>
                <h3 class="text-xl font-bold font-sora text-slate-900 mb-3 relative z-10">{{ problem.title }}</h3>
                <p class="text-slate-600 relative z-10 text-sm leading-relaxed">{{ problem.desc }}</p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 4. Our Solution -->
      <section class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute right-0 top-0 w-1/2 h-full bg-blue-900/20 blur-3xl rounded-full"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <Reveal>
              <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-slate-700/50">
                <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80" alt="Solution Architecture" class="w-full h-auto object-cover opacity-80" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
              </div>
            </Reveal>
            
            <div>
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/20 border border-blue-500/30 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                  Our Approach
                </div>
                <h2 class="text-3xl md:text-4xl font-sora font-bold mb-8">Architecting for Scale & Security</h2>
                
                <div class="space-y-8">
                  <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400">
                      <CloudIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <h4 class="text-xl font-bold mb-2">Cloud-Native Microservices</h4>
                      <p class="text-slate-400 text-sm leading-relaxed">We decoupled the monolith into 40+ independent microservices deployed via Kubernetes, allowing isolated scaling for high-demand financial processing algorithms.</p>
                    </div>
                  </div>
                  <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400">
                      <ShieldIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <h4 class="text-xl font-bold mb-2">Zero-Trust Security Layer</h4>
                      <p class="text-slate-400 text-sm leading-relaxed">Implemented end-to-end encryption, OAuth2 secure flows, and automated threat detection mechanisms to ensure regulatory compliance.</p>
                    </div>
                  </div>
                  <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 bg-blue-600/20 rounded-xl flex items-center justify-center text-blue-400">
                      <LayersIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <h4 class="text-xl font-bold mb-2">Modern Vue/React Frontend</h4>
                      <p class="text-slate-400 text-sm leading-relaxed">Completely overhauled the user interface with a lightning-fast SPA architecture, reducing dashboard load times from 8 seconds to 1.2 seconds.</p>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. Development Timeline -->
      <section class="py-24 bg-white relative">
        <div class="max-w-4xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-16">
              <h2 class="text-3xl font-sora font-bold text-slate-900 mb-4">Development Process</h2>
              <p class="text-slate-600">A rigorous 8-month agile execution plan.</p>
            </div>
          </Reveal>
          
          <div class="relative space-y-12 before:absolute before:inset-0 before:ml-4 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-slate-200">
            <Reveal v-for="(step, idx) in caseStudy.timeline" :key="idx" :delay="idx * 100">
              <div :class="['relative flex flex-col md:flex-row items-center w-full', idx % 2 === 0 ? 'md:justify-start' : 'md:justify-end']">
                <!-- Node -->
                <div class="absolute left-4 md:left-1/2 md:-translate-x-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-blue-600 border-4 border-white shadow-sm z-10 box-content"></div>
                
                <!-- Content Card -->
                <div :class="['w-full pl-12 md:w-[45%] md:pl-0', idx % 2 === 0 ? 'md:pr-12 md:text-right' : 'md:pl-12 md:text-left']">
                  <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 shadow-sm card-hover-effect hover:border-blue-200">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-2 block">{{ step.phase }}</span>
                    <h4 class="text-lg font-bold font-sora text-slate-900 mb-2">{{ step.title }}</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ step.desc }}</p>
                  </div>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 7. Key Features -->
      <section class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="mb-16">
              <h2 class="text-3xl font-sora font-bold text-slate-900 mb-4">Key Capabilities Delivered</h2>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <Reveal v-for="(feature, idx) in caseStudy.features" :key="idx" :delay="idx * 50">
              <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center text-center group hover:bg-blue-600 hover:text-white transition-all duration-300 h-full">
                <CheckCircleIcon class="w-8 h-8 text-blue-600 mb-4 group-hover:text-white transition-colors" />
                <h4 class="font-bold text-slate-900 group-hover:text-white transition-colors text-sm">{{ feature.title }}</h4>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 8. Results & Impact -->
      <section class="py-24 bg-blue-600 text-white relative overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-10"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <Reveal>
            <div class="text-center max-w-2xl mx-auto mb-16">
              <h2 class="text-3xl font-sora font-bold mb-4">Business Impact</h2>
              <p class="text-blue-100">Measurable results achieved within 3 months post-deployment.</p>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-4 text-center">
            <Reveal v-for="(stat, idx) in caseStudy.results" :key="idx" :delay="idx * 100">
              <div class="p-6 bg-white/10 backdrop-blur rounded-3xl border border-white/20 hover:bg-white/20 transition-colors">
                <div class="text-4xl md:text-5xl font-extrabold font-sora mb-2">{{ stat.value }}</div>
                <div class="text-sm font-bold uppercase tracking-wider text-blue-200">{{ stat.label }}</div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 9. Gallery / Screenshots -->
      <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-16">
              <h2 class="text-3xl font-sora font-bold text-slate-900">Project Showcase</h2>
            </div>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Reveal v-for="(img, idx) in caseStudy.gallery" :key="idx" :delay="idx * 100">
              <div class="relative group rounded-2xl overflow-hidden shadow-sm border border-slate-100 h-64">
                <img :src="img.src" :alt="img.alt" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out" />
                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
                  <span class="text-white font-bold font-sora border border-white/40 px-4 py-2 rounded-full">{{ img.alt }}</span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 10. Testimonial -->
      <section class="py-20 bg-[#F8FAFC]">
        <div class="max-w-4xl mx-auto px-6">
          <Reveal>
            <div class="bg-white p-10 md:p-14 rounded-3xl border border-slate-200 shadow-xl relative overflow-hidden">
              <div class="absolute top-0 left-0 w-2 h-full bg-blue-600"></div>
              <QuoteIcon class="w-16 h-16 text-slate-100 absolute top-8 right-8 z-0" />
              
              <div class="relative z-10">
                <p class="text-xl md:text-2xl text-slate-700 font-medium italic leading-relaxed mb-8">
                  "CoreVisys didn't just rebuild our software; they revolutionized our operational capacity. The transition to the new microservices architecture was flawless, and the performance gains have directly impacted our bottom line. An absolutely world-class engineering team."
                </p>
                <div class="flex items-center gap-4">
                  <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=150&q=80" alt="Client" class="w-14 h-14 rounded-full object-cover shadow-sm border-2 border-white" />
                  <div>
                    <h4 class="font-sora font-bold text-slate-900">David Reynolds</h4>
                    <p class="text-sm text-slate-500 font-medium">Chief Operating Officer, OmniCloud Financials</p>
                  </div>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- 11. Related Case Studies -->
      <section class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <h2 class="text-3xl font-sora font-bold text-slate-900 mb-12">More Success Stories</h2>
          </Reveal>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Reveal v-for="(study, idx) in related" :key="idx" :delay="idx * 100">
              <Link :href="route('case-study.show', study.slug)" class="block group h-full">
                <div class="bg-slate-50 rounded-3xl overflow-hidden border border-slate-100 shadow-sm card-hover-effect h-full flex flex-col">
                  <div class="h-48 overflow-hidden relative">
                    <img :src="study.img" :alt="study.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-slate-900">{{ study.industry }}</div>
                  </div>
                  <div class="p-6 flex-grow flex flex-col justify-between">
                    <div>
                      <h3 class="text-xl font-sora font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">{{ study.title }}</h3>
                      <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ study.desc }}</p>
                    </div>
                    <div class="text-blue-600 font-semibold text-sm flex items-center group-hover:underline">
                      View Study <ArrowRightIcon class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" />
                    </div>
                  </div>
                </div>
              </Link>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 12. Final CTA -->
      <section id="contact" class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-blue-900/10 grid-bg"></div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-2xl h-1/2 bg-blue-600/30 blur-[100px] pointer-events-none"></div>
        
        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 font-sora">
              Ready to Build Your Next <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">Digital Solution?</span>
            </h2>
            <p class="text-lg text-slate-300 mb-10 max-w-2xl mx-auto">
              Our engineering team is ready to analyze your challenges and architect a scalable, robust, and beautiful software solution.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <button class="px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-full font-bold text-base transition-all shadow-xl shadow-blue-500/20 transform hover:-translate-y-0.5">
                Schedule Consultation
              </button>
              <button class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-bold text-base transition-all backdrop-blur-sm">
                Contact Us
              </button>
            </div>
          </Reveal>
        </div>
      </section>
    </main>

    <!-- 13. Footer -->
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
              <li><Link href="/insights" class="hover:text-blue-400 transition-colors">Blog</Link></li>
              <li><a href="/#portfolio" class="hover:text-blue-400 transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-blue-400 transition-colors">Careers</Link></li>
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
  Menu as MenuIcon, X as XIcon, Code as CodeIcon,
  ArrowUpRight as ArrowUpRightIcon, Building as BuildingIcon, Target as TargetIcon,
  ServerCrash as ServerCrashIcon, Database as DatabaseIcon, ShieldAlert as ShieldAlertIcon,
  Cloud as CloudIcon, Shield as ShieldIcon, Layers as LayersIcon,
  Smartphone as SmartphoneIcon, Activity as ActivityIcon, Lock as LockIcon,
  Zap as ZapIcon, Key as KeyIcon, CheckCircle as CheckCircleIcon,
  Quote as QuoteIcon, ArrowRight as ArrowRightIcon, Mail as MailIcon,
  Phone as PhoneIcon, MapPin as MapPinIcon
} from 'lucide-vue-next';

const props = defineProps({
  caseStudy: Object
});

const isOpen = ref(false);
const scrolled = ref(false);

const related = [
  { slug: "healthtech-portal", title: "HealthTech Portal", industry: "Healthcare", desc: "A HIPAA-compliant patient management platform connecting doctors and patients via telemedicine and secure messaging.", img: "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=500&q=80" },
  { slug: "global-logistics-pro", title: "Global Logistics Pro", industry: "Logistics", desc: "A comprehensive enterprise resource planning system tailored for global supply chain and logistics management.", img: "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=500&q=80" },
  { slug: "cloudsync-workspace", title: "CloudSync Workspace", industry: "Productivity", desc: "A unified collaborative workspace offering real-time document editing, project management, and team communication.", img: "https://images.unsplash.com/photo-1600132806370-bf17e65e942f?auto=format&fit=crop&w=500&q=80" }
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
