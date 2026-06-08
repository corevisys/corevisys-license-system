<template>
  <Head title="Mobile App Development — CoreVisys" />
  
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
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-slate-950">
        <!-- Abstract Tech Grid Backdrop -->
        <div class="absolute inset-0 grid-bg opacity-10 z-0"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950 via-slate-950/90 to-[#F8FAFC] z-10"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Content (Left) -->
            <div class="lg:col-span-7 text-left">
              <Reveal :delay="0">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                  <SmartphoneIcon class="w-4 h-4 mr-2" />
                  Mobile App Development
                </div>
              </Reveal>
              
              <Reveal :delay="100">
                <h1 class="text-3xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-white">
                  Powerful Mobile Apps for Android & iOS
                </h1>
              </Reveal>
              
              <Reveal :delay="200">
                <p class="text-base md:text-xl text-slate-300 mb-8 leading-relaxed max-w-xl">
                  We build fast, scalable, and user-friendly mobile applications tailored precisely to your brand and customers. Native components, hybrid simplicity, absolute execution.
                </p>
              </Reveal>
              
              <Reveal :delay="300">
                <div class="flex flex-col sm:flex-row items-center gap-4 mb-8">
                  <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-base transition-all shadow-lg hover:shadow-blue-500/20 transform hover:-translate-y-0.5 text-center">
                    Start Mobile App Project
                  </Link>
                  <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 font-medium text-base transition-all transform hover:-translate-y-0.5 text-center">
                    Contact Us
                  </Link>
                </div>
              </Reveal>

              <Reveal :delay="400">
                <div class="flex flex-wrap items-center gap-6 text-sm text-slate-400 font-medium">
                  <div class="flex items-center gap-2">
                    <CalendarIcon class="w-4 h-4 text-blue-400" />
                    <span>Last Updated: <span class="text-white">May 21, 2026</span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <CheckCircleIcon class="w-4 h-4 text-green-400" />
                    <span>Store Compliance: <span class="text-white">Apple & Google Verified</span></span>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- CSS Styled Device Simulator (Right) -->
            <div class="lg:col-span-5 flex justify-center relative">
              <Reveal :delay="200">
                <!-- Device Glow Background -->
                <div class="absolute -inset-4 bg-blue-500/20 rounded-[50px] blur-2xl -z-10 animate-pulse"></div>
                
                <!-- iPhone Container Frame -->
                <div class="w-[280px] h-[560px] md:w-[310px] md:h-[620px] rounded-[48px] bg-slate-900 p-3 border-4 border-slate-800 shadow-2xl relative">
                  <!-- Dynamic iPhone Notch -->
                  <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-32 h-5 rounded-full bg-slate-950 z-30 flex items-center justify-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-800 mr-8"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#111] absolute right-8"></span>
                  </div>
                  
                  <!-- Screen Container -->
                  <div class="w-full h-full rounded-[38px] bg-slate-950 overflow-hidden relative border border-slate-950 p-4 pt-10 flex flex-col justify-between font-inter text-white">
                    <!-- Screen App UI -->
                    <div class="space-y-4">
                      <!-- Mock App Header -->
                      <div class="flex justify-between items-center pb-2 border-b border-slate-900">
                        <div class="flex items-center gap-1.5">
                          <div class="w-5 h-5 rounded bg-blue-600 flex items-center justify-center"><CodeIcon class="text-white w-3 h-3" /></div>
                          <span class="font-sora font-bold text-xs tracking-tight">CoreVisys App</span>
                        </div>
                        <div class="flex items-center gap-1">
                          <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-ping"></span>
                          <span class="text-[9px] text-slate-500">Live Engine</span>
                        </div>
                      </div>

                      <!-- Mock App Card: Account Overview -->
                      <div class="bg-slate-900/60 border border-slate-800/80 p-3 rounded-2xl">
                        <p class="text-[9px] text-slate-500 uppercase font-bold tracking-wider mb-1">Total Revenue</p>
                        <div class="flex items-baseline gap-2">
                          <span class="text-xl font-bold font-sora">$42,940</span>
                          <span class="text-[9px] text-green-400 font-bold">+18.4%</span>
                        </div>
                        <!-- Mini bar graph simulation -->
                        <div class="flex items-end gap-1 h-8 mt-3">
                          <div class="w-full bg-slate-800 rounded-t h-[40%]"></div>
                          <div class="w-full bg-slate-800 rounded-t h-[60%]"></div>
                          <div class="w-full bg-slate-800 rounded-t h-[50%]"></div>
                          <div class="w-full bg-blue-600 rounded-t h-[85%]"></div>
                          <div class="w-full bg-blue-500 rounded-t h-[95%]"></div>
                        </div>
                      </div>

                      <!-- Mock App Card: Push Event -->
                      <div class="bg-blue-600/10 border border-blue-500/20 p-3 rounded-2xl flex gap-2.5 items-start">
                        <BellIcon class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" />
                        <div>
                          <p class="text-[11px] font-bold text-slate-100">Delivery Dispatched</p>
                          <p class="text-[9px] text-slate-400 leading-normal mt-0.5">Order ORD-9481 left the warehouse in Dhaka.</p>
                        </div>
                      </div>

                      <!-- Active users simulation -->
                      <div class="bg-slate-900/60 border border-slate-800/80 p-3 rounded-2xl flex justify-between items-center">
                        <div class="flex items-center gap-2">
                          <UsersIcon class="w-4 h-4 text-cyan-400" />
                          <span class="text-[10px] text-slate-300">Active Sessions</span>
                        </div>
                        <span class="text-xs font-bold text-white font-sora">1,402</span>
                      </div>
                    </div>

                    <!-- Mock Bottom Navigation Bar -->
                    <div class="flex justify-around items-center border-t border-slate-900 pt-3 text-slate-500 text-[10px]">
                      <div class="flex flex-col items-center gap-0.5 text-blue-400"><SmartphoneIcon class="w-4 h-4" /><span>Home</span></div>
                      <div class="flex flex-col items-center gap-0.5"><ActivityIcon class="w-4 h-4" /><span>Stats</span></div>
                      <div class="flex flex-col items-center gap-0.5"><ShieldIcon class="w-4 h-4" /><span>Secure</span></div>
                    </div>
                  </div>

                  <!-- iPhone Home Indicator Line -->
                  <div class="absolute bottom-5 left-1/2 transform -translate-x-1/2 w-28 h-1 rounded-full bg-slate-800 z-30"></div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- Platforms We Support Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Platforms
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Platforms We Support</h2>
              <p class="text-slate-600 mt-4 text-lg">We deliver custom experiences tailored directly to the strengths of each mobile architecture ecosystem.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <Reveal v-for="(plat, idx) in PLATFORMS" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm card-hover-effect group relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                  <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                  <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-blue-600 mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <component :is="plat.icon" class="w-6 h-6" />
                  </div>
                  <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-slate-900">{{ plat.title }}</h3>
                  <p class="text-slate-600 leading-relaxed text-sm relative z-10">{{ plat.desc }}</p>
                </div>
                <div class="mt-8 pt-4 border-t border-slate-50 flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-widest relative z-10">
                  <span>Engine Blueprint</span>
                  <span class="text-blue-600">{{ plat.tech }}</span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- App Types Grid Section -->
      <section class="py-20 md:py-28 bg-white border-y border-slate-100 relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                App Types
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Custom Built Mobile Products</h2>
              <p class="text-slate-600 mt-4 text-lg">From high-speed checkouts to dense social networks, we program apps structured for exact workflows.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <Reveal v-for="(app, idx) in APP_TYPES" :key="idx" :delay="idx * 80">
              <div class="bg-[#F8FAFC] p-6 rounded-2xl border border-slate-100 hover:border-blue-200 transition-colors shadow-sm h-full flex flex-col justify-between">
                <div>
                  <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 mb-4 shrink-0">
                    <component :is="app.icon" class="w-5 h-5" />
                  </div>
                  <h4 class="font-sora font-bold text-slate-900 text-base mb-2">{{ app.title }}</h4>
                  <p class="text-slate-600 text-xs leading-relaxed">{{ app.desc }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/40 text-[10px] text-blue-600 font-bold uppercase tracking-wider">
                  {{ app.tag }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Mobile Engineering Focus Features Section -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-5">
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                  Features
                </div>
                <h2 class="text-3xl md:text-5xl font-sora font-bold text-slate-900 mb-6 leading-tight">
                  High-Performance Mobile Capabilities Built-In
                </h2>
                <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-6">
                  Today's mobile users demand immediate response times, solid offline stability, and secure transaction gateways. 
                </p>
                <p class="text-slate-600 text-base md:text-lg leading-relaxed">
                  We don't code simple wrapper shells. Our mobile apps leverage native hooks, optimized local caching databases, and secure encrypted sockets to guarantee a top-tier user experience.
                </p>
              </Reveal>
            </div>
            
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
              <Reveal v-for="(feat, idx) in FEATURES" :key="idx" :delay="idx * 100">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm hover:border-blue-300 transition-colors h-full flex flex-col justify-between">
                  <div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-6">
                      <component :is="feat.icon" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold font-sora text-slate-900 mb-3">{{ feat.title }}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">{{ feat.desc }}</p>
                  </div>
                  <div class="mt-6 pt-4 border-t border-slate-50 text-[10px] font-bold text-blue-600 uppercase tracking-widest">
                    {{ feat.highlight }}
                  </div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- App Build Pipeline Timeline -->
      <section class="py-20 md:py-28 bg-white border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Workflow
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">App Build Pipeline</h2>
              <p class="text-slate-600 mt-4 text-lg">A rigorous, step-by-step lifecycle to turn abstract sketches into operational app store deployments.</p>
            </Reveal>
          </div>

          <div class="flex flex-col lg:flex-row gap-12 items-start relative">
            <!-- Timeline Sidebar Buttons -->
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
                  <p :class="['text-xs mt-0.5', activeStep === idx ? 'text-slate-300' : 'text-slate-500']">{{ step.milestone }}</p>
                </div>
              </button>
            </div>

            <!-- Interactive Active Step Details View -->
            <div class="w-full lg:w-2/3">
              <div class="glass-card p-8 md:p-12 rounded-3xl bg-white border border-slate-200 shadow-sm relative overflow-hidden min-h-[400px]">
                <div class="absolute top-0 right-0 w-48 h-48 bg-blue-50/50 rounded-bl-full -mr-12 -mt-12 -z-10"></div>
                
                <transition name="fade" mode="out-in">
                  <div :key="activeStep" class="flex flex-col justify-between h-full">
                    <div>
                      <div class="inline-flex items-center gap-2 text-blue-600 font-bold text-sm tracking-wider uppercase mb-6">
                        <component :is="WORKFLOW_STEPS[activeStep].icon" class="w-5 h-5 text-blue-600" />
                        Stage 0{{ activeStep + 1 }}: {{ WORKFLOW_STEPS[activeStep].title }}
                      </div>
                      
                      <h3 class="text-2xl md:text-3xl font-sora font-extrabold text-slate-900 mb-6">
                        {{ WORKFLOW_STEPS[activeStep].subheadline }}
                      </h3>
                      
                      <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-8">
                        {{ WORKFLOW_STEPS[activeStep].desc }}
                      </p>
                    </div>

                    <div class="pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Activities & Deliverables</h4>
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
                        <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-3">Timeline Target</h4>
                        <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                            <ClockIcon class="w-5 h-5" />
                          </div>
                          <div>
                            <p class="text-slate-900 font-bold text-sm">{{ WORKFLOW_STEPS[activeStep].duration }}</p>
                            <p class="text-slate-500 text-xs">Agile execution track</p>
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

      <!-- Technologies Grid Showcase -->
      <section class="py-20 md:py-28 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Technologies
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold text-slate-900">Mobile Engineering Stack</h2>
              <p class="text-slate-600 mt-4 text-lg">We employ modern compilers and backend libraries to guarantee native speed and fluid modular updates.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <Reveal v-for="(tech, idx) in TECHS" :key="idx" :delay="idx * 100">
              <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm h-full flex flex-col justify-between items-center text-center">
                <div class="w-16 h-16 bg-[#F8FAFC] border border-slate-100 rounded-2xl flex items-center justify-center text-blue-600 mb-4 shadow-inner">
                  <component :is="tech.icon" class="w-8 h-8" />
                </div>
                <div>
                  <h3 class="font-sora font-bold text-slate-900 text-base mb-1">{{ tech.name }}</h3>
                  <p class="text-slate-500 text-xs leading-relaxed">{{ tech.category }}</p>
                </div>
                <div class="mt-4 px-2.5 py-1 rounded bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">
                  {{ tech.version }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Final CTA Section -->
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
                  Turn Your Idea Into a Mobile App
                </h2>
                <p class="text-slate-300 text-base md:text-lg mb-8 leading-relaxed">
                  Ready to sketch out your application navigation mockups or draft a robust cross-platform project scope? Reach out to our engineers today for a deep architectural blueprint session.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                  <Link :href="route('contact')" class="px-8 py-4 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-center transition-all shadow-lg hover:shadow-blue-500/25">
                    Start Mobile App Project
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
              <li><Link href="/services/web-applications" class="hover:text-blue-400 transition-colors">Web Applications</Link></li>
              <li><Link href="/services/mobile-apps" class="hover:text-blue-400 transition-colors text-blue-400 font-semibold">Mobile App Dev</Link></li>
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
  Smartphone as SmartphoneIcon,
  Tablet as TabletIcon,
  Layers as LayersIcon,
  ShoppingBag as ShoppingBagIcon,
  Truck as TruckIcon,
  CalendarDays as CalendarIconDays,
  Users as UsersIcon,
  Bell as BellIcon,
  Activity as ActivityIcon,
  Clock as ClockIcon,
  Lock as LockIcon,
  Search as SearchIcon,
  Figma as FigmaIcon,
  Terminal as TerminalIcon,
  Play as PlayIcon,
  Sparkles as SparklesIcon,
  Cpu as CpuIcon
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

const PLATFORMS = [
  { icon: SmartphoneIcon, title: "Android Apps", desc: "Native engineering leveraging native platform modules to deliver robust, pixel-perfect experiences.", tech: "Kotlin & Java" },
  { icon: TabletIcon, title: "iOS Apps", desc: "Elegant interfaces designed to scale with clean iOS layouts, ensuring strict Apple App Store compliance.", tech: "Swift & SwiftUI" },
  { icon: LayersIcon, title: "Cross-platform Apps", desc: "Unified single-source codebases with native bridges for fast, economical multi-store rollouts.", tech: "Flutter & React Native" }
];

const APP_TYPES = [
  { icon: LayersIcon, title: "Business Apps", desc: "Enterprise operations tracking, unified dashboards, and secure internal tools.", tag: "Enterprise Core", icon: LayersIcon },
  { icon: ShoppingBagIcon, title: "E-commerce Apps", desc: "Fluid custom checkouts, inventory synchronization, and unified payment bridges.", tag: "Instant Scale", icon: ShoppingBagIcon },
  { icon: TruckIcon, title: "Delivery Apps", desc: "Real-time courier GPS maps, auto routes, and immediate status telemetry.", tag: "Logistics Peak", icon: TruckIcon },
  { icon: CalendarIconDays, title: "Booking Apps", desc: "Custom booking grids, slots matching APIs, and immediate email/SMS notifications.", tag: "Dynamic Flow", icon: CalendarIconDays },
  { icon: UsersIcon, title: "Social Apps", desc: "Chat bridges, feed algorithms, secure content delivery, and reactive likes/comments.", tag: "Instant Engaged", icon: UsersIcon }
];

const FEATURES = [
  { icon: BellIcon, title: "Push Notifications", desc: "Vibrant user engagement hooks built via Firebase Cloud Messaging (FCM) to trigger targeted alert loops.", highlight: "Instant Dispatch" },
  { icon: SparklesIcon, title: "Offline Support", desc: "Local SQLite/Hive caching schemas enabling immediate screen load states even inside isolated network environments.", highlight: "Zero-Lag Mode" },
  { icon: ActivityIcon, title: "Real-time Sync", desc: "Optimized WebSockets telemetry matching immediately with central DBs, managing server-state conflicts silently.", highlight: "Sync Perfect" },
  { icon: LockIcon, title: "Secure Authentication", desc: "OAuth 2.0 paradigms, biometric lock bridges (FaceID / Fingerprint), JWT tokens, and strict local keychain encryptions.", highlight: "Vault Grade" }
];

const WORKFLOW_STEPS = [
  { 
    title: "Idea & Scoping", 
    milestone: "Milestone 1", 
    duration: "Week 1", 
    icon: SearchIcon,
    subheadline: "Stakeholder Workshops & Agile Roadmap Alignments",
    desc: "We analyze competitor dynamics, gather complete customer requirements, and coordinate technical architecture specifications to design a blueprint that scales.",
    activities: ["User Persona Scopes", "Flow Charting", "Tech Architecture Mapping"]
  },
  { 
    title: "Design & UX", 
    milestone: "Milestone 2", 
    duration: "Weeks 2 - 3", 
    icon: FigmaIcon,
    subheadline: "High-Fidelity Interactive App Screen Mockups",
    desc: "We build intuitive Figma navigation scopes, interactive click-through device simulations, and strict visual brand assets to match user layouts precisely.",
    activities: ["Navigation Prototypes", "Visual Assets Design", "Store Guidelines Audit"]
  },
  { 
    title: "App Development", 
    milestone: "Milestone 3", 
    duration: "Weeks 3 - 10", 
    icon: TerminalIcon,
    subheadline: "Coding Clean, Highly-Optimized Mobile Codebases",
    desc: "Our engineers build native Swift/Kotlin interfaces or unified cross-platform compilers, integrating secure local storage hooks and external API hubs.",
    activities: ["Local DB Programming", "Core App Build Cycles", "Secure API Integrations"]
  },
  { 
    title: "Device Testing", 
    milestone: "Milestone 4", 
    duration: "Weeks 10 - 12", 
    icon: ShieldIcon,
    subheadline: "Proactive Cross-Device Performance Profiling & QA",
    desc: "Rigorous alpha/beta testing cycles using physical device grids (TestFlight & Firebase App Distribution) to guarantee sub-millisecond paint states and zero memory leaks.",
    activities: ["Physical Device Benchmarking", "Security Integrity Testing", "Beta Testing Feedback Logs"]
  },
  { 
    title: "Store Launch", 
    milestone: "Milestone 5", 
    duration: "Week 12+", 
    icon: PlayIcon,
    subheadline: "Seamless Apple App Store & Google Play Publishing",
    desc: "We organize the metadata requirements, compile signing certificates, secure publishing reviews, and set up live telemetry uptime monitors.",
    activities: ["Play Store Submission", "App Store App Submits", "Post-Launch Alert Logs"]
  }
];

const TECHS = [
  { name: "Flutter", category: "Cross-Platform Framework", version: "v3.22+", icon: CpuIcon },
  { name: "React Native", category: "Hybrid JS Engine", version: "v0.74+", icon: CodeIcon },
  { name: "Kotlin", category: "Native Android Compiler", version: "v2.0+", icon: SmartphoneIcon },
  { name: "Swift", category: "Native iOS Blueprint", version: "v5.10+", icon: TabletIcon },
  { name: "Firebase", category: "Real-time Mobile Cloud", version: "v11.0+", icon: ShieldIcon }
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

/* Timeline transitions */
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
