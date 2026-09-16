<template>
  <Head title="Contact Us — CoreVisys" />

  <div class="min-h-screen bg-panel-2 selection:bg-amber/20 selection:text-text-primary overflow-x-hidden font-inter">
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
          <Link href="/insights" :class="['transition-colors', scrolled ? 'hover:text-amber' : 'hover:text-text-primary']">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" :class="['text-sm font-medium transition-colors duration-300', scrolled ? 'text-text-secondary hover:text-text-primary' : 'text-text-secondary hover:text-text-primary']">Sign In</Link>
          <button class="px-5 py-2.5 rounded-full text-sm font-medium transition-all shadow-md transform hover:-translate-y-0.5 bg-amber hover:bg-amber/100 text-text-primary shadow-amber/20">
            Let's Talk
          </button>
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
        <Link href="/insights" class="text-text-secondary font-medium">Insights</Link>
        <button class="bg-amber text-text-primary px-5 py-2.5 rounded-lg text-sm font-medium w-full mt-2">
          Let's Talk
        </button>
      </div>
    </nav>

    <main>
      <!-- 1. Hero Section -->
      <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden bg-bg-dark">
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-20 w-full text-center">
          <Reveal :delay="0">
            <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-8 tracking-wide uppercase">
              Get In Touch
            </div>
          </Reveal>

          <Reveal :delay="100">
            <h1 class="text-4xl md:text-5xl lg:text-7xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-text-primary max-w-4xl mx-auto">
              Let's Talk About <br/>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber to-teal">Your Project</span>
            </h1>
          </Reveal>

          <Reveal :delay="200">
            <p class="text-lg md:text-xl text-text-secondary mb-10 leading-relaxed max-w-2xl mx-auto">
              We help businesses build scalable software solutions, modern web applications, and premium digital experiences.
            </p>
          </Reveal>

          <Reveal :delay="300">
            <a href="#form" class="inline-flex items-center justify-center px-8 py-4 bg-amber hover:bg-amber-hover text-text-primary rounded-full font-bold transition-all shadow-lg shadow-amber/20 gap-2 transform hover:-translate-y-1">
              Schedule Consultation <ArrowDownIcon class="w-5 h-5" />
            </a>
          </Reveal>
        </div>

        <div class="absolute top-1/2 -left-32 w-96 h-96 bg-amber/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 -right-32 w-[500px] h-[500px] bg-teal/10 rounded-full blur-[120px] pointer-events-none"></div>
      </section>

      <!-- 2. Contact Information Cards -->
      <section class="py-16 -mt-10 relative z-30">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Reveal v-for="(info, i) in contactInfo" :key="i" :delay="i * 100">
              <div class="bg-panel p-8 rounded-3xl border border-panel-line shadow-xl shadow-panel-line flex flex-col items-center text-center group hover:-translate-y-2 transition-all duration-300 h-full">
                <div class="w-14 h-14 bg-amber/10 text-amber rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 group-hover:bg-amber group-hover:text-text-primary transition-all duration-300">
                  <component :is="info.icon" class="w-6 h-6" />
                </div>
                <h3 class="font-bold font-sora text-text-primary mb-2">{{ info.title }}</h3>
                <p class="text-text-secondary text-sm font-medium">{{ info.details }}</p>
                <p v-if="info.subdetails" class="text-text-secondary text-xs mt-1">{{ info.subdetails }}</p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 3. Form & Services Section -->
      <section id="form" class="py-24 bg-panel-2">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">

            <!-- Left Column: Services Inquiry -->
            <div class="lg:col-span-5 space-y-12">
              <Reveal>
                <div>
                  <h2 class="text-3xl md:text-4xl font-sora font-bold text-text-primary mb-6">How can we help?</h2>
                  <p class="text-text-secondary leading-relaxed text-lg mb-8">
                    Select the services you're interested in, and tell us a bit about your project. Our experts will get back to you within 24 hours.
                  </p>

                  <div class="flex flex-wrap gap-3">
                    <button
                      v-for="service in services"
                      :key="service"
                      @click="toggleService(service)"
                      :class="[
                        'px-4 py-2.5 rounded-full text-sm font-medium transition-all duration-300 border shadow-sm',
                        selectedServices.includes(service)
                          ? 'bg-amber border-amber text-text-primary shadow-md transform scale-105'
                          : 'bg-panel border-panel-line text-text-secondary hover:border-panel-line hover:bg-panel-2'
                      ]"
                    >
                      {{ service }}
                      <CheckIcon v-if="selectedServices.includes(service)" class="inline-block w-4 h-4 ml-1 -mt-0.5" />
                    </button>
                  </div>
                </div>
              </Reveal>

              <Reveal :delay="200">
                <div class="bg-amber text-text-primary p-8 rounded-3xl relative overflow-hidden">
                  <div class="absolute -right-10 -top-10 w-40 h-40 bg-panel-2/60 rounded-full blur-2xl"></div>
                  <div class="relative z-10">
                    <h3 class="font-bold font-sora text-xl mb-4">Direct Contact</h3>
                    <p class="text-amber/70 text-sm mb-6">Need immediate assistance? Skip the form and reach out directly.</p>
                    <div class="space-y-4 text-sm font-medium">
                      <a href="mailto:hello@corevisys.com" class="flex items-center gap-3 hover:text-amber/80 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-panel-2 flex items-center justify-center shrink-0"><MailIcon class="w-4 h-4" /></div>
                        hello@corevisys.com
                      </a>
                      <a href="tel:+18005550199" class="flex items-center gap-3 hover:text-amber/80 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-panel-2 flex items-center justify-center shrink-0"><PhoneIcon class="w-4 h-4" /></div>
                        +1 (800) 555-0199
                      </a>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- Right Column: Contact Form -->
            <div class="lg:col-span-7">
              <Reveal :delay="100">
                <div class="bg-panel p-8 md:p-12 rounded-3xl border border-panel-line shadow-xl shadow-panel-line">
                  <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Name -->
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-text-secondary">Full Name <span class="text-danger">*</span></label>
                        <div class="relative">
                          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary">
                            <UserIcon class="w-5 h-5" />
                          </div>
                          <input
                            v-model="form.name"
                            type="text"
                            required
                            :class="['w-full pl-11 pr-4 py-3 rounded-xl border bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none', errors.name ? 'border-danger' : 'border-panel-line']"
                            placeholder="John Doe"
                          >
                        </div>
                        <p v-if="errors.name" class="text-xs text-danger">{{ errors.name }}</p>
                      </div>

                      <!-- Email -->
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-text-secondary">Email Address <span class="text-danger">*</span></label>
                        <div class="relative">
                          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary">
                            <MailIcon class="w-5 h-5" />
                          </div>
                          <input
                            v-model="form.email"
                            type="email"
                            required
                            :class="['w-full pl-11 pr-4 py-3 rounded-xl border bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none', errors.email ? 'border-danger' : 'border-panel-line']"
                            placeholder="john@company.com"
                          >
                        </div>
                        <p v-if="errors.email" class="text-xs text-danger">{{ errors.email }}</p>
                      </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Company -->
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-text-secondary">Company Name</label>
                        <div class="relative">
                          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary">
                            <BriefcaseIcon class="w-5 h-5" />
                          </div>
                          <input
                            v-model="form.company"
                            type="text"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-panel-line bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none"
                            placeholder="Acme Corp"
                          >
                        </div>
                      </div>

                      <!-- Phone -->
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-text-secondary">Phone Number</label>
                        <div class="relative">
                          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary">
                            <PhoneIcon class="w-5 h-5" />
                          </div>
                          <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-panel-line bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none"
                            placeholder="+1 (555) 000-0000"
                          >
                        </div>
                      </div>
                    </div>

                    <!-- Budget Range -->
                    <div class="space-y-2">
                      <label class="text-sm font-bold text-text-secondary">Project Budget</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary">
                          <DollarSignIcon class="w-5 h-5" />
                        </div>
                        <select
                          v-model="form.budget"
                          class="w-full pl-11 pr-10 py-3 rounded-xl border border-panel-line bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none appearance-none"
                        >
                          <option value="" disabled>Select a range</option>
                          <option value="under_10k">Under $10,000</option>
                          <option value="10k_50k">$10,000 - $50,000</option>
                          <option value="50k_100k">$50,000 - $100,000</option>
                          <option value="over_100k">$100,000+</option>
                          <option value="not_sure">Not sure yet</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-text-muted">
                          <ChevronDownIcon class="w-5 h-5" />
                        </div>
                      </div>
                    </div>

                    <!-- Message -->
                    <div class="space-y-2">
                      <label class="text-sm font-bold text-text-secondary">Project Details <span class="text-danger">*</span></label>
                      <textarea
                        v-model="form.message"
                        required
                        rows="4"
                        :class="['w-full p-4 rounded-xl border bg-panel-2 text-text-primary focus:bg-panel focus:ring-2 focus:ring-amber focus:border-transparent transition-all outline-none resize-none', errors.message ? 'border-danger' : 'border-panel-line']"
                        placeholder="Tell us about your project goals, timeline, and requirements..."
                      ></textarea>
                      <p v-if="errors.message" class="text-xs text-danger">{{ errors.message }}</p>
                    </div>

                    <!-- Submit -->
                    <button
                      type="submit"
                      :disabled="isSubmitting || isSuccess"
                      class="w-full py-4 rounded-xl font-bold text-text-primary transition-all shadow-lg flex items-center justify-center gap-2"
                      :class="[
                        isSuccess ? 'bg-teal hover:bg-teal shadow-teal/20' : 'bg-bg-dark hover:bg-amber shadow-amber/20',
                        isSubmitting ? 'opacity-70 cursor-wait' : ''
                      ]"
                    >
                      <template v-if="isSubmitting">
                        <Loader2Icon class="w-5 h-5 animate-spin" /> Sending...
                      </template>
                      <template v-else-if="isSuccess">
                        <CheckCircleIcon class="w-5 h-5" /> Message Sent Successfully
                      </template>
                      <template v-else>
                        Send Message <SendIcon class="w-5 h-5" />
                      </template>
                    </button>

                    <p class="text-center text-xs text-text-muted mt-4">
                      By submitting this form, you agree to our <a href="#" class="text-amber hover:underline">Privacy Policy</a>.
                    </p>
                  </form>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- 4. Map & Office Location -->
      <section class="py-24 bg-panel relative">
        <div class="max-w-7xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-12">
              <h2 class="text-3xl font-sora font-bold text-text-primary mb-4">Visit Our Headquarters</h2>
              <p class="text-text-secondary">Located in the heart of the tech district.</p>
            </div>
          </Reveal>

          <Reveal :delay="100">
            <div class="rounded-3xl overflow-hidden border border-panel-line shadow-sm relative h-[400px] md:h-[500px] group">
              <!-- Dummy Map Image to act as embed -->
              <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=1200&q=80" alt="Map Location" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700" />
              <div class="absolute inset-0 bg-bg-dark/10 pointer-events-none"></div>

              <!-- Map Card Overlay -->
              <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 bg-panel p-6 md:p-8 rounded-2xl shadow-2xl max-w-sm border border-panel-line">
                <div class="w-12 h-12 bg-amber/15 text-amber rounded-full flex items-center justify-center mb-4">
                  <MapPinIcon class="w-6 h-6" />
                </div>
                <h3 class="font-bold font-sora text-text-primary text-xl mb-2">CoreVisys HQ</h3>
                <p class="text-text-secondary text-sm mb-6 leading-relaxed">
                  125 Tech Innovation Blvd,<br/>
                  Suite 400<br/>
                  San Francisco, CA 94105
                </p>
                <button class="w-full py-3 bg-bg-dark hover:bg-amber text-text-primary rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                  Get Directions <NavigationIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <!-- 5. FAQ Section -->
      <section class="py-24 bg-panel-2">
        <div class="max-w-4xl mx-auto px-6">
          <Reveal>
            <div class="text-center mb-16">
              <h2 class="text-3xl font-sora font-bold text-text-primary mb-4">Frequently Asked Questions</h2>
            </div>
          </Reveal>

          <div class="space-y-4">
            <Reveal v-for="(faq, idx) in faqs" :key="idx" :delay="idx * 50">
              <div class="border border-panel-line rounded-2xl overflow-hidden bg-panel shadow-sm transition-colors">
                <button
                  @click="toggleFaq(idx)"
                  class="w-full px-6 py-5 flex items-center justify-between font-bold text-text-primary text-left focus:outline-none hover:bg-panel-2 transition-colors"
                >
                  {{ faq.question }}
                  <ChevronDownIcon
                    :class="['w-5 h-5 text-text-secondary transition-transform duration-300', openFaq === idx ? 'rotate-180 text-amber' : '']"
                  />
                </button>
                <div
                  v-show="openFaq === idx"
                  class="px-6 pb-5 text-text-secondary text-sm leading-relaxed"
                >
                  {{ faq.answer }}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- 6. Social Media -->
      <section class="py-16 bg-panel border-t border-panel-line">
        <div class="max-w-7xl mx-auto px-6 text-center">
          <p class="text-sm font-bold text-text-secondary uppercase tracking-widest mb-8">Connect With Us</p>
          <div class="flex items-center justify-center gap-6">
            <a href="#" class="w-14 h-14 rounded-full bg-panel-2 border border-panel-line flex items-center justify-center text-text-secondary hover:bg-amber hover:border-amber hover:text-text-primary transition-all transform hover:-translate-y-1 shadow-sm">
              <LinkedinIcon class="w-6 h-6" />
            </a>
            <a href="#" class="w-14 h-14 rounded-full bg-panel-2 border border-panel-line flex items-center justify-center text-text-secondary hover:bg-bg-dark hover:border-panel-line hover:text-text-primary transition-all transform hover:-translate-y-1 shadow-sm">
              <TwitterIcon class="w-6 h-6" />
            </a>
            <a href="#" class="w-14 h-14 rounded-full bg-panel-2 border border-panel-line flex items-center justify-center text-text-secondary hover:bg-amber hover:border-amber hover:text-text-primary transition-all transform hover:-translate-y-1 shadow-sm">
              <FacebookIcon class="w-6 h-6" />
            </a>
            <a href="#" class="w-14 h-14 rounded-full bg-panel-2 border border-panel-line flex items-center justify-center text-text-secondary hover:bg-amber hover:border-amber hover:text-text-primary transition-all transform hover:-translate-y-1 shadow-sm">
              <InstagramIcon class="w-6 h-6" />
            </a>
            <a href="#" class="w-14 h-14 rounded-full bg-panel-2 border border-panel-line flex items-center justify-center text-text-secondary hover:bg-bg-dark hover:border-panel-line hover:text-text-primary transition-all transform hover:-translate-y-1 shadow-sm">
              <GithubIcon class="w-6 h-6" />
            </a>
          </div>
        </div>
      </section>

      <!-- 7. Final CTA -->
      <section class="py-24 bg-bg-dark text-text-primary relative overflow-hidden">
        <div class="absolute inset-0 bg-amber/10 grid-bg"></div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-2xl h-1/2 bg-amber/30 blur-[100px] pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 font-sora">
              Ready to Build Something <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber to-teal">Amazing?</span>
            </h2>
            <p class="text-lg text-text-secondary mb-10 max-w-2xl mx-auto">
              Skip the queue and book a direct meeting with our technical leadership team to discuss your architecture.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
              <button class="px-8 py-4 bg-amber hover:bg-amber/100 text-text-primary rounded-full font-bold text-base transition-all shadow-xl shadow-amber/20 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <CalendarIcon class="w-5 h-5" /> Book a Meeting
              </button>
            </div>
          </Reveal>
        </div>
      </section>
    </main>

    <!-- 8. Footer -->
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
              <li><Link href="/company" class="hover:text-amber transition-colors">About Us</Link></li>
              <li><Link href="/case-study" class="hover:text-amber transition-colors">Case Studies</Link></li>
              <li><Link :href="route('careers')" class="hover:text-amber transition-colors">Careers</Link></li>
              <li><Link href="/insights" class="hover:text-amber transition-colors">Blog</Link></li>
              <li><Link :href="route('contact')" class="hover:text-amber transition-colors text-amber font-semibold">Contact</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Legal</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy-policy" class="hover:text-amber transition-colors">Privacy Policy</Link></li>
              <li><Link href="/terms-of-service" class="hover:text-amber transition-colors">Terms of Service</Link></li>
              <li><Link href="/cookie-policy" class="hover:text-amber transition-colors">Cookie Policy</Link></li>
            </ul>
          </div>
        </div>

        <div class="pt-8 border-t border-panel-line flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
          <p>© {{ new Date().getFullYear() }} CoreVisys Technologies. All rights reserved.</p>
          <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal/40 opacity-75"></span>
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
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import Reveal from '@/Components/Reveal.vue';
import {
  Menu as MenuIcon,
  X as XIcon,
  Code as CodeIcon,
  ArrowDown as ArrowDownIcon,
  Mail as MailIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  Clock as ClockIcon,
  Check as CheckIcon,
  User as UserIcon,
  Briefcase as BriefcaseIcon,
  DollarSign as DollarSignIcon,
  ChevronDown as ChevronDownIcon,
  Send as SendIcon,
  Loader2 as Loader2Icon,
  CheckCircle as CheckCircleIcon,
  Navigation as NavigationIcon,
  Calendar as CalendarIcon,
  Linkedin as LinkedinIcon,
  Twitter as TwitterIcon,
  Facebook as FacebookIcon,
  Instagram as InstagramIcon,
  Github as GithubIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);

const contactInfo = [
  { icon: MailIcon, title: "Email Us", details: "hello@corevisys.com", subdetails: "Support: help@corevisys.com" },
  { icon: PhoneIcon, title: "Call Us", details: "+1 (800) 555-0199", subdetails: "Mon-Fri from 9am to 6pm PST" },
  { icon: MapPinIcon, title: "Visit Us", details: "125 Tech Innovation Blvd", subdetails: "San Francisco, CA 94105" },
  { icon: ClockIcon, title: "Business Hours", details: "09:00 AM - 06:00 PM", subdetails: "Weekend: Closed" }
];

const services = [
  "Web Development", "Custom Software", "SaaS Development",
  "AI & Automation", "Cloud Solutions", "UI/UX Design", "Other"
];

const selectedServices = ref([]);
const toggleService = (service) => {
  if (selectedServices.value.includes(service)) {
    selectedServices.value = selectedServices.value.filter(s => s !== service);
  } else {
    selectedServices.value.push(service);
  }
};

const form = reactive({
  name: '',
  email: '',
  company: '',
  phone: '',
  budget: '',
  message: ''
});

const errors = reactive({
  name: '',
  email: '',
  message: ''
});

const isSubmitting = ref(false);
const isSuccess = ref(false);

const submitForm = () => {
  // Reset errors
  errors.name = '';
  errors.email = '';
  errors.message = '';
  let isValid = true;

  if (!form.name) { errors.name = 'Name is required'; isValid = false; }
  if (!form.email) { errors.email = 'Email is required'; isValid = false; }
  else if (!/^\S+@\S+\.\S+$/.test(form.email)) { errors.email = 'Invalid email format'; isValid = false; }
  if (!form.message) { errors.message = 'Please provide project details'; isValid = false; }

  if (!isValid) return;

  isSubmitting.value = true;

  // Simulate API call
  setTimeout(() => {
    isSubmitting.value = false;
    isSuccess.value = true;

    // Reset form after success
    setTimeout(() => {
      form.name = '';
      form.email = '';
      form.company = '';
      form.phone = '';
      form.budget = '';
      form.message = '';
      selectedServices.value = [];
      isSuccess.value = false;
    }, 4000);
  }, 1500);
};

const openFaq = ref(0);
const toggleFaq = (index) => {
  openFaq.value = openFaq.value === index ? -1 : index;
};

const faqs = [
  { question: "What is your typical project timeline?", answer: "Project timelines vary greatly depending on scope. A standard web application usually takes 2-4 months from discovery to launch, while complex enterprise software can take 6-12 months." },
  { question: "How does your pricing model work?", answer: "We offer both fixed-price contracts for well-defined scopes and time-and-materials (hourly) billing for agile projects where scope may evolve." },
  { question: "Do you offer post-launch support and maintenance?", answer: "Absolutely. We offer dedicated SLA support packages to ensure your software remains updated, secure, and performs optimally after deployment." },
  { question: "What happens during the initial consultation?", answer: "During our first meeting, we'll discuss your business goals, technical requirements, budget, and timeline. We aim to understand if we are the right technical partner for your needs." }
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
