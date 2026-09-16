<template>
  <Head title="Cloud Architecture & Solutions — CoreVisys" />

  <div class="min-h-screen bg-bg-dark text-text-primary selection:bg-amber/100 selection:text-text-primary overflow-x-hidden font-inter">
    <!-- Navbar -->
    <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', scrolled ? 'glass-nav-dark py-3 shadow-lg shadow-amber/20' : 'bg-transparent py-5']">
      <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <Link href="/" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber to-teal flex items-center justify-center shadow-lg shadow-amber/30">
            <CloudIcon class="text-text-primary w-5 h-5 animate-pulse" />
          </div>
          <span class="font-sora font-bold text-xl tracking-tight text-text-primary">CoreVisys</span>
        </Link>

        <div class="hidden md:flex items-center space-x-8 text-sm font-medium text-text-secondary">
          <Link href="/" class="transition-colors hover:text-amber">Home</Link>
          <a href="/#services" class="transition-colors hover:text-amber">Services</a>
          <a href="/#portfolio" class="transition-colors hover:text-amber">Portfolio</a>
          <Link href="/company" class="transition-colors hover:text-amber">Company</Link>
          <Link :href="route('insights')" class="transition-colors hover:text-amber">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" class="text-sm font-medium text-text-secondary hover:text-text-primary transition-colors">Sign In</Link>
          <Link :href="route('contact')" class="px-5 py-2.5 rounded-full text-sm font-medium bg-gradient-to-r from-amber to-teal hover:from-amber hover:to-teal text-text-primary transition-all shadow-md shadow-amber/20 transform hover:-translate-y-0.5">
            Book Consultation
          </Link>
        </div>

        <button class="md:hidden text-text-primary transition-colors" @click="isOpen = !isOpen">
          <XIcon v-if="isOpen" />
          <MenuIcon v-else />
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="isOpen" class="absolute top-full left-0 w-full bg-bg-dark border-b border-panel-line shadow-2xl py-4 px-6 flex flex-col space-y-4 md:hidden">
        <Link href="/" class="text-text-secondary font-medium">Home</Link>
        <a href="/#services" class="text-text-secondary font-medium">Services</a>
        <a href="/#portfolio" class="text-text-secondary font-medium">Portfolio</a>
        <Link href="/company" class="text-text-secondary font-medium">Company</Link>
        <Link :href="route('insights')" class="text-text-secondary font-medium">Insights</Link>
        <Link :href="route('contact')" class="bg-gradient-to-r from-amber to-teal text-text-primary px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero Section -->
      <section class="relative pt-32 pb-24 md:pt-48 md:pb-40 overflow-hidden bg-bg-dark">
        <div class="absolute inset-0 grid-bg opacity-15 z-0"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-amber/10 blur-[120px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-teal/10 blur-[120px] pointer-events-none z-0"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-20">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Hero Content (Left) -->
            <div class="lg:col-span-7 text-left">
              <Reveal :delay="0">
                <div class="inline-flex items-center px-3.5 py-1 rounded-full bg-amber/10 border border-amber/30 text-amber text-sm font-semibold mb-6 tracking-wide uppercase shadow-inner shadow-amber/10">
                  <CpuIcon class="w-4 h-4 mr-2 text-amber" />
                  Enterprise Cloud Operations
                </div>
              </Reveal>

              <Reveal :delay="100">
                <h1 class="text-3xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-text-primary">
                  Scalable Cloud Architecture <br/>
                  <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber via-teal to-teal">for Modern Businesses</span>
                </h1>
              </Reveal>

              <Reveal :delay="200">
                <p class="text-base md:text-xl text-text-secondary mb-10 leading-relaxed max-w-xl">
                  We design secure, scalable, and cost-efficient cloud systems optimized for high throughput, absolute resilience, and streamlined DevOps automation.
                </p>
              </Reveal>

              <Reveal :delay="300">
                <div class="flex flex-col sm:flex-row items-center gap-4 mb-10">
                  <a href="#topology" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-gradient-to-r from-amber to-teal hover:from-amber hover:to-teal text-text-primary font-medium text-base transition-all shadow-lg shadow-amber/20 transform hover:-translate-y-0.5 text-center">
                    Design Your Cloud Infrastructure
                  </a>
                  <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-bg-dark hover:bg-panel-2 text-text-secondary border border-panel-line font-medium text-base transition-all transform hover:-translate-y-0.5 text-center">
                    Talk to Experts
                  </Link>
                </div>
              </Reveal>

              <Reveal :delay="400">
                <div class="flex flex-wrap items-center gap-6 text-sm text-text-secondary font-medium border-t border-panel-line pt-6">
                  <div class="flex items-center gap-2">
                    <CalendarIcon class="w-4 h-4 text-amber" />
                    <span>Updated: <span class="text-text-primary">May 21, 2026</span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <CheckCircleIcon class="w-4 h-4 text-teal" />
                    <span>Uptime compliance: <span class="text-text-primary">99.99% SLA</span></span>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- Custom Cloud Infrastructure Topology Interactive Diagram (Right) -->
            <div class="lg:col-span-5 flex justify-center relative">
              <Reveal :delay="200">
                <div class="absolute -inset-4 bg-amber/10 rounded-full blur-3xl -z-10"></div>
                <div class="w-[320px] h-[420px] md:w-[400px] md:h-[440px] relative border border-panel-line bg-bg-dark/40  rounded-3xl p-6 flex flex-col justify-between overflow-hidden shadow-2xl">

                  <div class="flex justify-between items-center pb-2 border-b border-panel-line">
                    <span class="text-xs font-mono text-amber font-bold">VPC_TOPOLOGY_SIMULATOR</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-teal/10 text-green-300 font-mono">LIVE TELEMETRY</span>
                  </div>

                  <!-- Live Slider to trigger Auto-scaling Server additions! -->
                  <div class="my-4 p-3 bg-bg-dark/50 rounded-xl border border-panel-line relative z-10">
                    <div class="flex justify-between text-[10px] text-text-secondary mb-1 font-mono">
                      <span>Simulated User Load</span>
                      <span class="text-amber font-bold">{{ userLoad }} req/sec</span>
                    </div>
                    <input
                      type="range"
                      min="50"
                      max="1000"
                      step="50"
                      v-model="userLoad"
                      class="w-full h-1 bg-panel-2 rounded-lg appearance-none cursor-pointer accent-blue-500"
                    />
                    <div class="flex justify-between text-[9px] text-text-muted font-mono mt-1">
                      <span>Low Load (Idle)</span>
                      <span>High (Autoscale Trigger)</span>
                    </div>
                  </div>

                  <!-- Interactive SVG Cloud Diagram -->
                  <div class="relative w-full h-44 z-0 flex items-center justify-center">
                    <svg viewBox="0 0 200 120" class="w-full h-full text-amber/20">
                      <!-- Grid guidelines -->
                      <line x1="20" y1="60" x2="60" y2="60" stroke="currentColor" stroke-dasharray="2" stroke-width="0.5" />

                      <!-- LB Links -->
                      <line x1="60" y1="60" x2="110" y2="30" stroke="currentColor" stroke-width="0.8" :class="{'text-teal stroke-2': userLoad > 100}" />
                      <line x1="60" y1="60" x2="110" y2="60" stroke="currentColor" stroke-width="0.8" :class="{'text-teal stroke-2': userLoad > 100}" />
                      <line x1="60" y1="60" x2="110" y2="90" stroke="currentColor" stroke-width="0.8" :class="{'text-teal stroke-2': userLoad > 500}" v-if="userLoad > 400" />

                      <!-- Database Links -->
                      <line x1="130" y1="30" x2="175" y2="60" stroke="currentColor" stroke-width="0.5" />
                      <line x1="130" y1="60" x2="175" y2="60" stroke="currentColor" stroke-width="0.5" />
                      <line x1="130" y1="90" x2="175" y2="60" stroke="currentColor" stroke-width="0.5" v-if="userLoad > 400" />

                      <!-- Node Points & Circles -->
                      <!-- Internet Users Node -->
                      <circle cx="20" cy="60" r="5" fill="var(--amber)" class="animate-pulse" />

                      <!-- Load Balancer Node -->
                      <circle cx="60" cy="60" r="7" fill="var(--amber-hover)" />
                      <path d="M57 60h6M60 57v6" stroke="#fff" stroke-width="1.2" />

                      <!-- Autoscaling Server Node A -->
                      <rect x="110" y="20" width="20" height="15" rx="3" fill="var(--panel-2)" stroke="var(--amber)" stroke-width="1" />
                      <text x="114" y="30" fill="#fff" font-size="8" font-family="monospace">EC2</text>
                      <circle cx="120" cy="15" r="2.5" :fill="userLoad > 100 ? 'var(--teal)' : 'var(--text-primary)'" />

                      <!-- Autoscaling Server Node B -->
                      <rect x="110" y="52" width="20" height="15" rx="3" fill="var(--panel-2)" stroke="var(--amber)" stroke-width="1" />
                      <text x="114" y="62" fill="#fff" font-size="8" font-family="monospace">EC2</text>
                      <circle cx="120" cy="47" r="2.5" :fill="userLoad > 200 ? 'var(--teal)' : 'var(--text-primary)'" />

                      <!-- Autoscaling Server Node C (Dynamic additions!) -->
                      <g v-if="userLoad > 400">
                        <rect x="110" y="83" width="20" height="15" rx="3" fill="var(--panel-2)" stroke="var(--teal)" stroke-width="1" class="animate-pulse" />
                        <text x="114" y="93" fill="#fff" font-size="8" font-family="monospace" class="font-bold">EC2</text>
                        <circle cx="120" cy="78" r="2.5" fill="var(--teal)" class="animate-ping" />
                      </g>

                      <!-- PostgreSQL Database Node -->
                      <circle cx="175" cy="60" r="8" fill="var(--bg-primary)" stroke="var(--teal)" stroke-width="1.5" />
                      <text x="171" y="63" fill="var(--teal)" font-size="9" font-family="sans-serif" font-weight="bold">DB</text>
                    </svg>
                  </div>

                  <!-- Simulated Status Console -->
                  <div class="relative z-10 bg-bg-dark border border-panel-line p-3 rounded-xl font-mono text-[9px] text-text-secondary space-y-1 mt-4 h-24 overflow-y-hidden">
                    <div class="text-amber">>> Connection stream: 10.0.0.1 -> ALB_PORT_80</div>
                    <div class="text-text-muted">>> Target group healthchecks ... [ALL PASS]</div>
                    <div class="text-text-secondary">>> Current active targets: <span class="text-text-primary">{{ activeServersCount }} Nodes</span></div>
                    <div class="text-green-400 font-bold" v-if="userLoad > 400">>> [ALERT] High threshold met! Scaling out Node C... [LIVE]</div>
                    <div class="text-teal" v-else>>> Telemetry status stable. Resource optimization active.</div>
                    <div class="text-text-muted">>> Idle waste percentage: <span class="text-green-400 font-bold">{{ idleWastePercent }}%</span></div>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- Cloud Services Section -->
      <section id="services" class="py-24 bg-bg-dark relative border-y border-panel-line">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[70%] h-[70%] bg-amber/10 rounded-full blur-[160px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="text-center max-w-2xl mx-auto mb-20">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-6 tracking-wide uppercase">
                Solutions Stack
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-text-primary mb-6 leading-tight">
                Cloud Services We Provide
              </h2>
              <p class="text-text-secondary text-lg leading-relaxed">
                Leverage secure, highly automated cloud architectures designed strictly to optimize runtime performance and operational scalability.
              </p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(service, idx) in CLOUD_SERVICES" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-bg-dark/40 border border-panel-line card-hover-neon group relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                  <div class="absolute -right-8 -top-8 w-24 h-24 bg-amber/5 rounded-full blur-2xl group-hover:bg-amber/15 transition-all"></div>

                  <div class="w-14 h-14 rounded-2xl bg-bg-dark border border-panel-line flex items-center justify-center text-amber mb-6 relative z-10 group-hover:border-amber group-hover:text-amber transition-colors duration-300">
                    <component :is="service.icon" class="w-6 h-6" />
                  </div>

                  <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-text-primary group-hover:text-amber transition-colors">
                    {{ service.title }}
                  </h3>

                  <p class="text-text-secondary leading-relaxed text-sm relative z-10 mb-6">
                    {{ service.desc }}
                  </p>
                </div>

                <div class="flex items-center text-amber font-medium text-sm border-t border-panel-line pt-4 relative z-10">
                  <span>Explore Offerings</span>
                  <ArrowRightIcon class="w-4 h-4 ml-1.5 transition-transform group-hover:translate-x-1 text-amber" />
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Supported Platforms Section -->
      <section class="py-24 bg-bg-dark relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-teal/10 border border-teal/20 text-teal text-sm font-semibold mb-6 tracking-wide uppercase">
                Multi-Cloud Native
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-text-primary mb-6">
                Supported Cloud Platforms
              </h2>
              <p class="text-text-secondary text-lg leading-relaxed">
                We engineer scalable setups across top-tier infrastructure hosts ensuring complete vendor independence.
              </p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <Reveal v-for="(platform, idx) in PLATFORMS" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-bg-dark/40 border border-panel-line  hover:border-amber/50 hover:shadow-lg hover:shadow-amber/20 transition-all flex flex-col justify-between min-h-[300px]">
                <div>
                  <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-bg-dark flex items-center justify-center text-amber border border-panel-line">
                      <component :is="platform.icon" class="w-7 h-7" />
                    </div>
                    <div>
                      <h3 class="text-xl font-sora font-bold text-text-primary">{{ platform.name }}</h3>
                      <p class="text-xs text-text-muted">{{ platform.tagline }}</p>
                    </div>
                  </div>

                  <p class="text-text-secondary text-sm leading-relaxed mb-6">
                    {{ platform.desc }}
                  </p>
                </div>

                <div class="border-t border-panel-line pt-4 flex justify-between items-center text-xs">
                  <span class="text-text-muted">Core Integrations</span>
                  <span class="text-amber font-mono font-semibold">{{ platform.integrations }}</span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Architecture Focus / Pillars Panel with Dynamic Savings Calculator -->
      <section id="topology" class="py-24 bg-bg-dark border-t border-panel-line relative">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Info Cards -->
            <div class="lg:col-span-6 space-y-6">
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-6 tracking-wide uppercase">
                  Engineering Principles
                </div>
                <h2 class="text-3xl md:text-5xl font-sora font-bold text-text-primary mb-6 leading-tight">
                  Architecture Core Pillars
                </h2>
                <p class="text-text-secondary text-lg leading-relaxed mb-8">
                  We don't build standard server clusters. Our architectures conform strictly to enterprise operational reliability.
                </p>
              </Reveal>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <Reveal v-for="(pillar, idx) in CORE_PILLARS" :key="idx" :delay="idx * 80">
                  <div class="bg-bg-dark/40 p-6 rounded-2xl border border-panel-line hover:border-amber/40 transition-colors shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-amber/20 flex items-center justify-center text-amber border border-panel-line mb-4 shrink-0">
                      <component :is="pillar.icon" class="w-5 h-5" />
                    </div>
                    <h4 class="font-sora font-bold text-text-primary text-base mb-2">{{ pillar.title }}</h4>
                    <p class="text-text-secondary text-xs leading-relaxed">{{ pillar.desc }}</p>
                  </div>
                </Reveal>
              </div>
            </div>

            <!-- Right Interactive Estimator Panel -->
            <div class="lg:col-span-6">
              <Reveal :delay="200">
                <div class="p-8 rounded-3xl bg-bg-dark/60 border border-panel-line  relative overflow-hidden flex flex-col justify-between shadow-2xl">
                  <div class="absolute -right-24 -bottom-24 w-60 h-60 bg-teal/5 rounded-full blur-3xl"></div>

                  <div>
                    <div class="flex justify-between items-center mb-6 border-b border-panel-line pb-4">
                      <span class="text-xs font-mono text-amber font-bold uppercase tracking-wider">COST OPTIMIZATION ESTIMATOR</span>
                      <span class="text-[10px] px-2 py-0.5 bg-teal/10 text-green-300 rounded font-mono">45% Avg Reduction</span>
                    </div>

                    <p class="text-text-secondary text-sm leading-relaxed mb-8">
                      Find out how much infrastructure spend is lost to idle, over-provisioned nodes and configure automated resource trimming.
                    </p>

                    <!-- Interactive sliders -->
                    <div class="space-y-6 bg-bg-dark p-6 border border-panel-line rounded-2xl">
                      <div>
                        <div class="flex justify-between text-xs text-text-secondary mb-2 font-mono">
                          <span>Monthly Cloud Spend</span>
                          <span class="text-text-primary font-bold font-sora text-sm">${{ monthlySpend.toLocaleString() }}</span>
                        </div>
                        <input
                          type="range"
                          min="1000"
                          max="100000"
                          step="1000"
                          v-model="monthlySpend"
                          class="w-full h-1 bg-panel-2 rounded-lg appearance-none cursor-pointer accent-blue-500"
                        />
                      </div>

                      <div>
                        <div class="flex justify-between text-xs text-text-secondary mb-2 font-mono">
                          <span>Idle / Waste Capacity</span>
                          <span class="text-text-primary font-bold font-sora text-sm">{{ wastePercent }}%</span>
                        </div>
                        <input
                          type="range"
                          min="10"
                          max="80"
                          step="5"
                          v-model="wastePercent"
                          class="w-full h-1 bg-panel-2 rounded-lg appearance-none cursor-pointer accent-blue-500"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="mt-8 pt-6 border-t border-panel-line flex justify-between items-center text-xs text-text-secondary">
                    <div>
                      <p class="text-text-muted uppercase tracking-widest text-[9px] font-mono">Estimated Savings</p>
                      <p class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-teal font-sora mt-1">
                        ${{ estimatedSavings.toLocaleString() }}/year
                      </p>
                    </div>
                    <Link :href="route('contact')" class="px-5 py-3 rounded-full bg-amber hover:bg-amber-hover text-text-primary font-semibold text-xs transition-all shadow-md transform hover:-translate-y-0.5">
                      Reclaim Budget
                    </Link>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- DevOps Pipeline & CLI Terminal Showcase Section -->
      <section class="py-24 bg-bg-dark relative border-t border-panel-line overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-6 tracking-wide uppercase">
                Infrastructure as Code
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-text-primary mb-6">
                Modern DevOps Pipelines
              </h2>
              <p class="text-text-secondary text-lg leading-relaxed">
                Orchestrate modular container builds, continuous integrations, and automated IAC definitions securely.
              </p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Controls / DevOps Tools selection -->
            <div class="lg:col-span-5 space-y-4">
              <Reveal v-for="(tool, idx) in DEVOPS_TOOLS" :key="idx" :delay="idx * 100">
                <button
                  @click="selectDevopsTool(idx)"
                  :class="[
                    'w-full text-left p-5 rounded-2xl transition-all duration-300 border flex items-center justify-between group',
                    activeTool === idx
                      ? 'bg-bg-dark border-amber/60 shadow-lg shadow-amber/20'
                      : 'bg-transparent border-panel-line hover:border-panel-line'
                  ]"
                >
                  <div class="flex items-center gap-4">
                    <div :class="[
                      'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 transition-colors',
                      activeTool === idx ? 'bg-amber text-text-primary' : 'bg-bg-dark text-text-muted border border-panel-line'
                    ]">
                      0{{ idx + 1 }}
                    </div>
                    <div>
                      <h4 class="font-bold font-sora text-sm text-text-primary group-hover:text-amber transition-colors">{{ tool.name }}</h4>
                      <p class="text-xs text-text-muted mt-0.5">{{ tool.tech }}</p>
                    </div>
                  </div>
                  <ChevronRightIcon :class="['w-4 h-4 transition-all', activeTool === idx ? 'text-amber translate-x-1' : 'text-text-secondary']" />
                </button>
              </Reveal>
            </div>

            <!-- Right CLI Interactive Virtual Terminal -->
            <div class="lg:col-span-7">
              <Reveal :delay="200">
                <div class="bg-bg-dark border border-panel-line p-6 rounded-3xl font-mono relative overflow-hidden shadow-2xl">
                  <div class="absolute -right-16 -top-16 w-32 h-32 bg-amber/10 rounded-full blur-2xl"></div>

                  <div class="flex justify-between items-center border-b border-panel-line pb-4 mb-4 text-xs">
                    <div class="flex items-center gap-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-danger/100"></span>
                      <span class="w-2.5 h-2.5 rounded-full bg-amber"></span>
                      <span class="w-2.5 h-2.5 rounded-full bg-teal"></span>
                      <span class="text-[10px] text-text-muted ml-2">{{ DEVOPS_TOOLS[activeTool].fileName }}</span>
                    </div>
                    <button
                      @click="runTerminalSimulation"
                      :disabled="simulating"
                      class="text-[10px] px-3 py-1 rounded bg-amber hover:bg-amber-hover disabled:bg-panel-line disabled:opacity-80 text-text-primary font-semibold transition-all shadow-md shadow-amber/10"
                    >
                      {{ simulating ? 'PROVISIONING...' : 'RUN PIPELINE' }}
                    </button>
                  </div>

                  <!-- Command line execution simulation output -->
                  <div ref="terminalBox" class="h-80 overflow-y-auto space-y-3 text-[10px] leading-relaxed select-none scrollbar-none">
                    <p class="text-text-muted"># Click "RUN PIPELINE" to trigger virtual infrastructure provisioning script...</p>
                    <p class="text-amber font-bold">>>> COREVISYS DEVOPS ORCHESTRATION GATEWAY</p>

                    <div v-for="(log, lIdx) in consoleLogs" :key="lIdx" class="space-y-0.5">
                      <p :class="log.color">{{ log.text }}</p>
                    </div>

                    <div v-if="simulating" class="text-text-secondary animate-pulse">Running process step...</div>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- Key Benefits Section -->
      <section class="py-24 bg-bg-dark relative border-t border-panel-line overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <div class="lg:col-span-5 text-left">
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-amber/10 border border-amber/20 text-amber text-sm font-semibold mb-6 tracking-wide uppercase">
                  Enterprise Value
                </div>
                <h2 class="text-3xl md:text-5xl font-sora font-bold text-text-primary mb-6 leading-tight">
                  Core Performance Benefits
                </h2>
                <p class="text-text-secondary text-lg leading-relaxed mb-8">
                  Achieve absolute peace of mind with clean automation patterns designed to reduce waste and eliminate runtime failure points.
                </p>
              </Reveal>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-6">
              <Reveal v-for="(benefit, idx) in BENEFITS" :key="idx" :delay="idx * 100">
                <div class="bg-bg-dark/40 p-8 rounded-3xl border border-panel-line hover:border-amber/30 transition-colors h-full flex flex-col justify-between">
                  <div>
                    <div class="w-12 h-12 bg-amber/20 text-amber border border-panel-line rounded-xl flex items-center justify-center mb-6">
                      <component :is="benefit.icon" class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold font-sora text-text-primary mb-3">{{ benefit.title }}</h3>
                    <p class="text-text-secondary text-xs leading-relaxed">{{ benefit.desc }}</p>
                  </div>
                  <div class="mt-6 pt-4 border-t border-panel-line text-[10px] font-bold text-amber uppercase tracking-widest">
                    {{ benefit.tag }}
                  </div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="py-24 bg-bg-dark relative border-t border-panel-line">
        <div class="max-w-6xl mx-auto px-6 relative">
          <div class="absolute inset-0 bg-gradient-to-r from-amber/10 to-teal/10 rounded-[40px] blur-xl"></div>

          <div class="relative rounded-[40px] bg-bg-dark/60 border border-panel-line p-8 md:p-16 text-center overflow-hidden z-10">
            <div class="absolute inset-0 grid-bg opacity-10 pointer-events-none"></div>

            <Reveal>
              <h2 class="text-3xl md:text-5xl font-sora font-extrabold text-text-primary mb-6">
                Build Your Cloud Infrastructure with Us
              </h2>
              <p class="text-text-secondary text-base md:text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
                Connect with our certified solution architects to structure highly resilient servers, migrate legacy workloads, or completely automate provisioning.
              </p>

              <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-amber to-teal hover:from-amber hover:to-teal text-text-primary font-bold rounded-full transition-all shadow-lg shadow-amber/30 transform hover:-translate-y-0.5">
                  Design Your Cloud Infrastructure
                </Link>
                <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-4 bg-bg-dark hover:bg-bg-dark border border-panel-line text-text-primary font-semibold rounded-full transition-all transform hover:-translate-y-0.5">
                  Book Free Cost Audit
                </Link>
              </div>
            </Reveal>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-bg-dark text-text-secondary border-t border-panel-line py-16">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">

          <div class="space-y-4">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber to-teal flex items-center justify-center shadow-md">
                <CloudIcon class="text-text-primary w-5 h-5" />
              </div>
              <span class="font-sora font-bold text-xl text-text-primary tracking-tight">CoreVisys</span>
            </div>
            <p class="text-sm leading-relaxed">
              Engineering futuristic enterprise software, intelligent machine learning systems, and resilient cloud architectures.
            </p>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Services</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/services/custom-software" class="hover:text-amber transition-colors">Custom Software</Link></li>
              <li><Link href="/services/web-applications" class="hover:text-amber transition-colors">Web Applications</Link></li>
              <li><Link href="/services/mobile-apps" class="hover:text-amber transition-colors">Mobile App Dev</Link></li>
              <li><Link href="/services/ai-ml" class="hover:text-amber transition-colors">AI & Machine Learning</Link></li>
              <li><Link href="/services/cloud-solutions" class="text-amber font-semibold transition-colors">Cloud Architecture</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-text-primary font-bold mb-4 font-sora">Company</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/" class="hover:text-amber transition-colors">About Us</Link></li>
              <li><a href="/#portfolio" class="hover:text-amber transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-amber transition-colors">Careers</Link></li>
              <li><Link :href="route('contact')" class="hover:text-amber transition-colors">Contact</Link></li>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import Reveal from '@/Components/Reveal.vue';
import {
  Menu as MenuIcon,
  X as XIcon,
  ChevronRight as ChevronRightIcon,
  Cpu as CpuIcon,
  Database as DatabaseIcon,
  Server as ServerIcon,
  Activity as ActivityIcon,
  CheckCircle as CheckCircleIcon,
  Calendar as CalendarIcon,
  ArrowRight as ArrowRightIcon,
  Cloud as CloudIcon,
  Globe as GlobeIcon,
  Terminal as TerminalIcon,
  Shield as ShieldIcon,
  Zap as ZapIcon,
  Play as PlayIcon,
  Coins as CoinsIcon,
  Clock as ClockIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);

const handleScroll = () => {
  scrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Interactive Topology variables
const userLoad = ref(150);

const activeServersCount = computed(() => {
  if (userLoad.value <= 100) return 1;
  if (userLoad.value <= 400) return 2;
  return 3;
});

const idleWastePercent = computed(() => {
  // Higher active load translates to higher utilization (less idle waste)
  const baseUtilization = Math.min(Math.round(userLoad.value / 10), 95);
  return 100 - baseUtilization;
});

// Cloud Estimator variables
const monthlySpend = ref(8000);
const wastePercent = ref(35);

const estimatedSavings = computed(() => {
  // Cloud savings = monthly spend * idle resources percentage * 0.45 optimization index * 12 months
  return Math.round(monthlySpend.value * (wastePercent.value / 100) * 0.45 * 12);
});

// Cloud Services offerings list
const CLOUD_SERVICES = [
  { icon: GlobeIcon, title: "Cloud Migration", desc: "Rigorous planning and risk-free execution translating legacy backends into secure, cost-optimized modern cloud VPC clusters." },
  { icon: ServerIcon, title: "Infrastructure Setup", desc: "Custom virtual cloud environments (VPCs, Subnets, Security Groups) built entirely via production Infrastructure as Code." },
  { icon: CpuIcon, title: "Serverless Architecture", desc: "Unlock scalable micro-services via AWS Lambda or Google Cloud Functions to eliminate server idling costs entirely." },
  { icon: TerminalIcon, title: "DevOps Automation", desc: "Continuous integration pipelines matching container builds with zero-downtime blue/green deployment workflows." },
  { icon: ActivityIcon, title: "Load Balancing", desc: "Highly redundant Application Load Balancer setups routing incoming connection telemetry safely across multiple zones." }
];

// Supported platforms list
const PLATFORMS = [
  { icon: CloudIcon, name: "Amazon Web Services", tagline: "Industry Gold Standard", desc: "Comprehensive configurations across ECS/EKS clusters, redundant RDS clusters, secure IAM boundaries, and Elastic Load Balancing.", integrations: "EC2, EKS, RDS, S3" },
  { icon: GlobeIcon, name: "Google Cloud Platform", tagline: "Advanced Telemetry", desc: "Modern micro-services orchestrations running entirely via Google Kubernetes Engine (GKE), secure Firebase endpoints, and cloud BigQuery datasets.", integrations: "GKE, Compute Engine, BigQuery" },
  { icon: ServerIcon, name: "Microsoft Azure", tagline: "Enterprise Ecosystem Integration", desc: "Seamless corporate setups leveraging hybrid AD syncing, robust Azure Kubernetes Service containers, and resilient SQL servers.", integrations: "AKS, Azure VMs, CosmoDB" }
];

// Architecture Core Pillars
const CORE_PILLARS = [
  { icon: ActivityIcon, title: "High Availability", desc: "Multi-zone target routing with automated replication layers to handle zone failures cleanly." },
  { icon: CpuIcon, title: "Elastic Scalability", desc: "Resource thresholds triggers scaling horizontal compute nodes instantly under load changes." },
  { icon: ShieldIcon, title: "Rigid VPC Security", desc: "Isolated logical boundaries, safe private subnet bridges, secure NAT gateways, and strict IAM definitions." },
  { icon: CoinsIcon, title: "Cost Optimization", desc: "Eliminating compute waste by matching automated scaling, auto-idling configs, and storage tierings." }
];

// Key Benefits
const BENEFITS = [
  { icon: CoinsIcon, title: "Reduced Infrastructure Cost", desc: "Trim compute budgets by automated resource scaling, idling cleanups, and storage optimizations.", tag: "Average 45% Trimmed" },
  { icon: ZapIcon, title: "High Performance", desc: "Global edge deliveries and robust load distributions guarantee lightning fast API paint benchmarks.", tag: "99.99% Network Speed" },
  { icon: ServerIcon, title: "Scalable Systems", desc: "Build structures ready for exponential customer flows behind highly reliable horizontal clusters.", tag: "Seamless Elastic Capacity" }
];

// DevOps interactive tool simulator variables
const activeTool = ref(0);
const simulating = ref(false);
const consoleLogs = ref([]);
const terminalBox = ref(null);

const DEVOPS_TOOLS = [
  {
    name: "Infrastructure as Code",
    tech: "Terraform Configurations",
    fileName: "main.tf",
    logs: [
      { text: ">> Initializing Terraform CLI wrapper...", color: "text-text-muted" },
      { text: ">> Executing: terraform init", color: "text-amber" },
      { text: ">> Finding provider plugins ... HashiCorp AWS v5.4.0 [OK]", color: "text-text-secondary" },
      { text: ">> Executing: terraform plan", color: "text-amber" },
      { text: ">> Planned resources: +1 VPC, +2 Subnets, +1 ALB, +3 EC2 TargetGroups", color: "text-yellow-400" },
      { text: ">> Executing: terraform apply -auto-approve", color: "text-amber" },
      { text: ">> aws_vpc.main_vpc: Creating...", color: "text-text-secondary" },
      { text: ">> aws_alb.public_balancer: Creating...", color: "text-text-secondary" },
      { text: ">> aws_instance.worker_node[0]: Provisioning static IP...", color: "text-text-secondary" },
      { text: ">> Apply complete! Resources: 7 added, 0 changed, 0 destroyed.", color: "text-green-400 font-bold" }
    ]
  },
  {
    name: "Containerization",
    tech: "Docker Containerization",
    fileName: "Dockerfile",
    logs: [
      { text: ">> Initializing Docker compiler daemon...", color: "text-text-muted" },
      { text: ">> Executing: docker build -t corevisys-app:v2.4 .", color: "text-amber" },
      { text: ">> Step 1/5 : FROM node:20-alpine ... Pulled successfully [OK]", color: "text-text-secondary" },
      { text: ">> Step 2/5 : COPY package.json ./ ... Cached", color: "text-text-secondary" },
      { text: ">> Step 3/5 : RUN npm install --omit=dev ... Installed 324 packages", color: "text-text-secondary" },
      { text: ">> Step 4/5 : COPY . . ... [COMPILING ASSETS]", color: "text-yellow-400" },
      { text: ">> Step 5/5 : EXPOSE 3000 ... Layer mapping complete.", color: "text-text-secondary" },
      { text: ">> Exporting container image layers to Docker Hub...", color: "text-text-secondary" },
      { text: ">> Docker image built: corevisys-app:v2.4 [SUCCESS]", color: "text-green-400 font-bold" }
    ]
  },
  {
    name: "Orchestration Clusters",
    tech: "Kubernetes Deployments",
    fileName: "deployment.yaml",
    logs: [
      { text: ">> Connecting to Kubernetes API controller via kubectl...", color: "text-text-muted" },
      { text: ">> Executing: kubectl apply -f deployment.yaml", color: "text-amber" },
      { text: ">> deployment.apps/corevisys-web created.", color: "text-text-secondary" },
      { text: ">> service/corevisys-balancer-svc created.", color: "text-text-secondary" },
      { text: ">> Executing: kubectl get pods -n production", color: "text-amber" },
      { text: ">> pod/corevisys-web-74b8ff-n8f9a [STATUS: PENDING]", color: "text-yellow-400" },
      { text: ">> pod/corevisys-web-74b8ff-n8f9a [STATUS: RUNNING]", color: "text-text-secondary" },
      { text: ">> pod/corevisys-web-74b8ff-x7s3k [STATUS: RUNNING]", color: "text-text-secondary" },
      { text: ">> Triggering automated rollouts: 2 healthy replicas active.", color: "text-green-400 font-bold" }
    ]
  },
  {
    name: "Continuous Integration",
    tech: "CI/CD Pipeline Auditing",
    fileName: "ci-pipeline.yml",
    logs: [
      { text: ">> GitHub Actions runner triggered by branch commit [main]...", color: "text-text-muted" },
      { text: ">> RUN: Install dependencies & run syntax audits", color: "text-amber" },
      { text: ">> NPM security vulnerability scanners ... 0 critical issues found.", color: "text-text-secondary" },
      { text: ">> RUN: Run unit testing specs ... 142/142 tests passed.", color: "text-text-secondary" },
      { text: ">> RUN: Execute SonarQube quality analysis gate", color: "text-amber" },
      { text: ">> Code Quality Rating: A | Security Rating: A+ [PASSED]", color: "text-green-400 font-bold" },
      { text: ">> RUN: Deploy container layers to production EKS cluster", color: "text-amber" },
      { text: ">> Continuous deployment workflow executed successfully.", color: "text-green-400 font-bold" }
    ]
  }
];

const selectDevopsTool = (idx) => {
  if (simulating.value) return;
  activeTool.value = idx;
  consoleLogs.value = [];
};

const runTerminalSimulation = async () => {
  if (simulating.value) return;
  simulating.value = true;
  consoleLogs.value = [];

  const targetLogs = DEVOPS_TOOLS[activeTool.value].logs;

  for (let i = 0; i < targetLogs.length; i++) {
    await new Promise((resolve) => setTimeout(resolve, 350));
    consoleLogs.value.push(targetLogs[i]);
    await nextTick();
    if (terminalBox.value) {
      terminalBox.value.scrollTop = terminalBox.value.scrollHeight;
    }
  }

  simulating.value = false;
};
</script>

<style>
.grid-bg {
  background-size: 45px 45px;
  background-image: linear-gradient(to right, rgba(59, 130, 246, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(59, 130, 246, 0.05) 1px, transparent 1px);
  mask-image: radial-gradient(circle at center, black 65%, transparent 100%);
  -webkit-mask-image: radial-gradient(circle at center, black 65%, transparent 100%);
}

.glass-nav-dark {
  background: rgba(9, 13, 26, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(59, 130, 246, 0.1);
}

.card-hover-neon {
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.card-hover-neon:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 30px -10px rgba(59, 130, 246, 0.25);
  border-color: rgba(59, 130, 246, 0.4);
}

.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
