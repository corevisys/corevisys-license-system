<template>
  <Head title="Company — CoreVisys Enterprise Software Agency" />
  
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
          <Link href="/company" :class="['transition-colors text-blue-500 font-semibold']">Company</Link>
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
        <Link href="/company" class="text-blue-600 font-semibold">Company</Link>
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
          <div class="absolute inset-0 bg-gradient-to-b from-slate-950/45 via-slate-950/85 to-[#F8FAFC] z-10"></div>
        </div>
        
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-semibold mb-6 tracking-wide uppercase">
              <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
              About CoreVisys
            </div>
          </Reveal>
          
          <Reveal :delay="100">
            <h1 class="text-3xl md:text-5xl font-sora font-extrabold tracking-tight mb-6 leading-tight max-w-4xl mx-auto text-white">
              Innovative Software Solutions <br /><span class="gradient-text">for Modern Businesses</span>
            </h1>
          </Reveal>
          
          <Reveal :delay="200">
            <p class="text-base md:text-lg text-slate-300 mb-10 max-w-2xl mx-auto leading-relaxed">
              CoreVisys is an enterprise-grade technology and software solutions partner. We help ambitious global companies engineer secure, scalable, and cutting-edge software systems.
            </p>
          </Reveal>
          
          <Reveal :delay="300">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
              <button class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-medium text-base transition-all shadow-lg shadow-blue-600/20 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                Get Started <ArrowRightIcon class="w-4 h-4" />
              </button>
              <a href="#contact" class="w-full sm:w-auto px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-medium text-base transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2 backdrop-blur-sm">
                Contact Us
              </a>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- About Company Overview & Cards -->
      <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-20">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Who We Are
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold mb-6 text-slate-900 leading-tight">We Engineer the Future of Enterprise Solutions</h2>
              <p class="text-slate-600 text-lg mb-6 leading-relaxed">
                CoreVisys was founded on the principle that modern software should be robust, beautifully designed, and built to scale. We act as a technology accelerator for enterprise clients, turning ambitious strategies into functioning ecosystems.
              </p>
              <p class="text-slate-600 text-lg leading-relaxed">
                Whether migrating legacy infrastructure to the cloud, building responsive SaaS platforms, or automating complex database workflows with machine learning models, our team delivers with surgical precision.
              </p>
            </Reveal>
            <Reveal :delay="200" class="relative">
              <div class="absolute inset-0 bg-blue-600/10 blur-3xl rounded-3xl -z-10"></div>
              <div class="glass-card p-10 rounded-3xl border border-slate-200 shadow-xl bg-slate-50/50">
                <div class="flex items-center gap-4 mb-6">
                  <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center font-bold text-xl">
                    1
                  </div>
                  <h3 class="text-xl font-sora font-bold text-slate-950">Enterprise Software Partner</h3>
                </div>
                <ul class="space-y-4">
                  <li class="flex gap-3">
                    <CheckCircleIcon class="text-blue-600 w-5 h-5 shrink-0 mt-1" />
                    <span class="text-slate-700">Dedicated engineering teams prioritizing robust architecture.</span>
                  </li>
                  <li class="flex gap-3">
                    <CheckCircleIcon class="text-blue-600 w-5 h-5 shrink-0 mt-1" />
                    <span class="text-slate-700">Clean, ISO-compliant workflows and testing standards.</span>
                  </li>
                  <li class="flex gap-3">
                    <CheckCircleIcon class="text-blue-600 w-5 h-5 shrink-0 mt-1" />
                    <span class="text-slate-700">Aesthetic responsive dashboards and high-speed portals.</span>
                  </li>
                </ul>
              </div>
            </Reveal>
          </div>

          <!-- Mission & Vision -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
            <Reveal :delay="0">
              <div class="p-8 md:p-10 rounded-3xl bg-[#F8FAFC] border border-slate-100 card-hover-effect h-full">
                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-600 mb-6">
                  <SparklesIcon class="w-6 h-6" />
                </div>
                <h3 class="text-2xl font-sora font-bold text-slate-900 mb-4">Our Mission</h3>
                <p class="text-slate-600 leading-relaxed">
                  To empower global businesses by providing robust, state-of-the-art custom software and cloud engineering services. We bridge the gap between complex engineering challenges and seamless, premium user experiences.
                </p>
              </div>
            </Reveal>
            
            <Reveal :delay="200">
              <div class="p-8 md:p-10 rounded-3xl bg-[#F8FAFC] border border-slate-100 card-hover-effect h-full">
                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-600 mb-6">
                  <GlobeIcon class="w-6 h-6" />
                </div>
                <h3 class="text-2xl font-sora font-bold text-slate-900 mb-4">Our Vision</h3>
                <p class="text-slate-600 leading-relaxed">
                  To become the world's most trusted partner for premium digital product development. We envision a future where organizations can deploy cloud infrastructures, custom portals, and automated models effortlessly.
                </p>
              </div>
            </Reveal>
          </div>

          <!-- Core Values -->
          <div>
            <div class="text-center max-w-2xl mx-auto mb-16">
              <Reveal>
                <h3 class="text-3xl font-sora font-bold text-slate-900">Our Core Values</h3>
                <p class="text-slate-600 mt-4">The foundational rules that guide every single line of code we write and project we deliver.</p>
              </Reveal>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
              <Reveal v-for="(v, i) in VALUES" :key="i" :delay="i * 100">
                <div class="p-6 rounded-2xl bg-white border border-slate-100 text-center shadow-sm card-hover-effect">
                  <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <component :is="v.icon" class="w-5 h-5" />
                  </div>
                  <h4 class="font-sora font-bold text-lg text-slate-900 mb-2">{{ v.title }}</h4>
                  <p class="text-slate-600 text-sm leading-relaxed">{{ v.desc }}</p>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- Services Overview -->
      <section class="py-24 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Solutions We Offer
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold mb-6 text-slate-900">Expert Services Designed to Scale</h2>
              <p class="text-slate-600 text-lg">We deliver premium end-to-end technical execution across standard and custom technological areas.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(s, index) in SERVICES" :key="index" :delay="index * 100">
              <component :is="s.link ? Link : 'div'" :href="s.link" class="p-8 rounded-3xl bg-white border border-slate-100 shadow-sm card-hover-effect group relative overflow-hidden h-full block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 rounded-2xl bg-[#F8FAFC] shadow-sm flex items-center justify-center text-blue-600 mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                  <component :is="s.icon" class="w-6 h-6" />
                </div>
                <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-slate-900">{{ s.title }}</h3>
                <p class="text-slate-600 leading-relaxed relative z-10 text-sm">{{ s.desc }}</p>
                <div class="mt-6 flex items-center text-blue-600 font-medium text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-x-[-10px] group-hover:translate-x-0 duration-300">
                  Learn more <ChevronRightIcon class="w-4 h-4 ml-1" />
                </div>
              </component>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Why Choose Us / Animated Stats -->
      <section class="py-20 bg-slate-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-10"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <h2 class="text-3xl font-sora font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">Why Modern Enterprises Partner with Us</h2>
              <p class="text-slate-400 mt-4">Proven records of scaling operations, ensuring robust architecture, and delivering visual excellence.</p>
            </Reveal>
          </div>
          
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 md:gap-6 text-center">
            <Reveal v-for="(stat, index) in STATS" :key="index" :delay="index * 100">
              <div class="flex flex-col items-center">
                <div class="text-4xl md:text-5xl font-extrabold text-white mb-2 font-sora flex items-center gap-1 justify-center">
                  {{ stat.value }}
                </div>
                <div class="text-blue-400 font-bold text-sm tracking-wider uppercase mb-1">
                  {{ stat.label }}
                </div>
                <div class="text-slate-400 text-xs max-w-[200px] leading-relaxed">
                  {{ stat.desc }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Team Section -->
      <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                The Architects
              </div>
              <h2 class="text-3xl md:text-4xl font-sora font-bold mb-6 text-slate-900">Meet Our Leadership Team</h2>
              <p class="text-slate-600 text-lg">A dynamic group of engineers, designers, and systems architects passionate about technical excellence.</p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <Reveal v-for="(member, idx) in TEAM" :key="idx" :delay="idx * 100">
              <div class="group rounded-3xl overflow-hidden bg-slate-50 border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col h-full text-center">
                <div class="relative h-64 overflow-hidden bg-slate-200">
                  <img :src="member.img" :alt="member.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-6 gap-3 z-20">
                    <a :href="member.linkedin" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center hover:bg-blue-600 transition-colors">
                      <LinkedinIcon class="w-4 h-4" />
                    </a>
                    <a :href="member.twitter" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center hover:bg-blue-600 transition-colors">
                      <TwitterIcon class="w-4 h-4" />
                    </a>
                    <a :href="member.github" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center hover:bg-blue-600 transition-colors">
                      <GithubIcon class="w-4 h-4" />
                    </a>
                  </div>
                </div>
                <div class="p-6 flex-grow flex flex-col justify-center">
                  <h3 class="font-sora font-bold text-slate-900 text-lg mb-1">{{ member.name }}</h3>
                  <p class="text-blue-600 text-sm font-medium">{{ member.role }}</p>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Client Testimonials Slider -->
      <section class="py-24 bg-[#F8FAFC] overflow-hidden">
        <div class="max-w-5xl mx-auto px-6 relative">
          <div class="text-center mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-sm font-semibold mb-6 tracking-wide uppercase">
                Success Stories
              </div>
              <h2 class="text-3xl font-sora font-bold text-slate-900">What Our Partners Say</h2>
            </Reveal>
          </div>

          <!-- Slider Container -->
          <div class="relative glass-card p-8 md:p-12 rounded-3xl border border-slate-200/60 shadow-xl bg-white min-h-[300px] flex flex-col justify-between">
            <div class="absolute top-6 left-6 text-blue-100 -z-10">
              <QuoteIcon class="w-20 h-20 opacity-40" />
            </div>

            <!-- Slides with fade transitions -->
            <div class="relative z-10">
              <transition name="fade" mode="out-in">
                <div :key="activeTestimonial" class="space-y-6">
                  <p class="text-lg md:text-xl text-slate-700 italic leading-relaxed font-medium">
                    "{{ TESTIMONIALS[activeTestimonial].quote }}"
                  </p>
                  <div class="flex items-center gap-4">
                    <img :src="TESTIMONIALS[activeTestimonial].img" :alt="TESTIMONIALS[activeTestimonial].author" class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm" />
                    <div>
                      <h4 class="font-sora font-bold text-slate-950">{{ TESTIMONIALS[activeTestimonial].author }}</h4>
                      <p class="text-xs text-slate-500 font-medium">{{ TESTIMONIALS[activeTestimonial].role }}</p>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <!-- Controls -->
            <div class="flex justify-end gap-3 mt-8 relative z-20">
              <button 
                @click="prevTestimonial" 
                class="w-10 h-10 rounded-full border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 hover:text-blue-600 transition-all focus:outline-none"
              >
                ←
              </button>
              <button 
                @click="nextTestimonial" 
                class="w-10 h-10 rounded-full border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 hover:text-blue-600 transition-all focus:outline-none"
              >
                →
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Call To Action -->
      <section id="contact" class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-slate-900"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-full bg-blue-600/20 blur-3xl rounded-full pointer-events-none"></div>
        
        <div class="max-w-5xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6 font-sora">
              Let’s Build Something <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">Amazing Together</span>
            </h2>
            <p class="text-lg text-slate-400 mb-10 max-w-2xl mx-auto">
              Partner with CoreVisys to engineer high-performance platforms, systems automation, or custom cloud pipelines. Get in touch for a technical consultation.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <button class="px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white rounded-full font-bold text-base transition-all shadow-xl shadow-blue-500/20 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <CalendarIcon class="w-5 h-5" /> Schedule Meeting
              </button>
              <button class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-full font-bold text-base transition-all backdrop-blur-sm">
                Contact Us
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
  ChevronRight as ChevronRightIcon, 
  Code as CodeIcon, 
  Globe as GlobeIcon, 
  Cpu as CpuIcon, 
  Cloud as CloudIcon, 
  ArrowRight as ArrowRightIcon, 
  CheckCircle as CheckCircleIcon, 
  Layers as LayersIcon, 
  Mail as MailIcon, 
  MapPin as MapPinIcon, 
  Phone as PhoneIcon,
  Shield as ShieldIcon,
  Users as UsersIcon,
  Award as AwardIcon,
  Sparkles as SparklesIcon,
  Calendar as CalendarIcon,
  Linkedin as LinkedinIcon,
  Twitter as TwitterIcon,
  Github as GithubIcon,
  Quote as QuoteIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeTestimonial = ref(0);

const VALUES = [
  { icon: SparklesIcon, title: "Innovation", desc: "Constantly pushing boundaries to deliver state-of-the-art technological solutions." },
  { icon: ShieldIcon, title: "Integrity", desc: "Building trust with clients through absolute transparency and strict compliance standards." },
  { icon: UsersIcon, title: "Collaboration", desc: "Partnering closely to coordinate technical scope with client goals seamlessly." },
  { icon: AwardIcon, title: "Excellence", desc: "Setting the gold standard for clean architecture, clean code, and responsiveness." }
];

const SERVICES = [
  { icon: GlobeIcon, title: "Web Development", desc: "Clean frontend systems built with React or Vue integrated with high-performance APIs.", link: "/services/web-applications" },
  { icon: CodeIcon, title: "Custom Software", desc: "Bespoke database architectures, portals, and custom CRM systems tailored to your workflows.", link: "/services/custom-software" },
  { icon: CloudIcon, title: "Cloud Solutions", desc: "Scalable VPCs, containerized deployments, Kubernetes orchestration, and optimized pipelines." },
  { icon: CpuIcon, title: "AI & Automation", desc: "Generative AI API connections, smart automated parsing models, and operations workflows." },
  { icon: LayersIcon, title: "UI/UX Design", desc: "Interactive wireframes and high-fidelity mockups emphasizing dynamic visual aesthetics." },
  { icon: ShieldIcon, title: "Cyber Security", desc: "Rigorous vulnerability patching, encryption at rest, secure authorization, and penetration testing." }
];

const STATS = [
  { value: "200+", label: "Projects Completed", desc: "Delivered scalable solutions globally." },
  { value: "99%", label: "Happy Clients", desc: "Exceptional rate of satisfaction." },
  { value: "10+", label: "Years Experience", desc: "Decade of industry leadership." },
  { value: "24/7", label: "Support Availability", desc: "Reliable maintenance round the clock." }
];

const TEAM = [
  { name: "Monir Zaman", role: "Founder & CEO", img: "https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=300&q=80", linkedin: "#", twitter: "#", github: "#" },
  { name: "Sarah Connor", role: "Chief Technology Officer", img: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80", linkedin: "#", twitter: "#", github: "#" },
  { name: "Alex Rivera", role: "Head of Product Design", img: "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=300&q=80", linkedin: "#", twitter: "#", github: "#" },
  { name: "Dr. Sofia Patel", role: "Lead Systems Architect", img: "https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=300&q=80", linkedin: "#", twitter: "#", github: "#" }
];

const TESTIMONIALS = [
  { quote: "CoreVisys built our custom enterprise software in record time. The quality of code and responsiveness of their team exceeded our expectations.", author: "Sarah Jenkins", role: "CTO, FinTech Labs", img: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" },
  { quote: "Their AI and automation solutions completely transformed our operations. We saw a 40% increase in efficiency within just two months.", author: "Michael Chen", role: "VP of Operations, LogiCorp", img: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80" },
  { quote: "CoreVisys is more than a vendor; they are a strategic partner. Their cloud migration expertise saved us hundreds of thousands in hosting fees.", author: "Amanda Ross", role: "Founder, CloudSync", img: "https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=150&q=80" }
];

const nextTestimonial = () => {
  activeTestimonial.value = (activeTestimonial.value + 1) % TESTIMONIALS.length;
};

const prevTestimonial = () => {
  activeTestimonial.value = (activeTestimonial.value - 1 + TESTIMONIALS.length) % TESTIMONIALS.length;
};

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

/* Testimonial slider fade animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
