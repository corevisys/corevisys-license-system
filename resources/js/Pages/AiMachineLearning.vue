<template>
  <Head title="AI & Machine Learning Solutions — CoreVisys" />
  
  <div class="min-h-screen bg-slate-950 text-white selection:bg-purple-500 selection:text-white overflow-x-hidden font-inter">
    <!-- Navbar -->
    <nav :class="['fixed top-0 w-full z-50 transition-all duration-300', scrolled ? 'glass-nav-dark py-3 shadow-lg shadow-purple-950/20' : 'bg-transparent py-5']">
      <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <Link href="/" class="flex items-center gap-2">
          <img src="/storage/logo/Main-Logo-2.png" alt="CoreVisys" class="h-16 md:h-20 w-auto" />
        </Link>

        <div class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
          <Link href="/" class="transition-colors hover:text-purple-400">Home</Link>
          <a href="/#services" class="transition-colors hover:text-purple-400">Services</a>
          <a href="/#portfolio" class="transition-colors hover:text-purple-400">Portfolio</a>
          <Link href="/company" class="transition-colors hover:text-purple-400">Company</Link>
          <Link :href="route('insights')" class="transition-colors hover:text-purple-400">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" class="text-sm font-medium text-slate-400 hover:text-white transition-colors">Sign In</Link>
          <Link :href="route('contact')" class="px-5 py-2.5 rounded-full text-sm font-medium bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-500 hover:to-cyan-400 text-white transition-all shadow-md shadow-purple-500/20 transform hover:-translate-y-0.5">
            Book Consultation
          </Link>
        </div>

        <button class="md:hidden text-white transition-colors" @click="isOpen = !isOpen">
          <XIcon v-if="isOpen" />
          <MenuIcon v-else />
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="isOpen" class="absolute top-full left-0 w-full bg-slate-900 border-b border-slate-800 shadow-2xl py-4 px-6 flex flex-col space-y-4 md:hidden">
        <Link href="/" class="text-slate-300 font-medium">Home</Link>
        <a href="/#services" class="text-slate-300 font-medium">Services</a>
        <a href="/#portfolio" class="text-slate-300 font-medium">Portfolio</a>
        <Link href="/company" class="text-slate-300 font-medium">Company</Link>
        <Link :href="route('insights')" class="text-slate-300 font-medium">Insights</Link>
        <Link :href="route('contact')" class="bg-gradient-to-r from-purple-600 to-cyan-500 text-white px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero Section -->
      <section class="relative pt-32 pb-24 md:pt-48 md:pb-40 overflow-hidden bg-slate-950">
        <!-- Cosmic Neon Backdrop -->
        <div class="absolute inset-0 grid-bg-dark opacity-20 z-0"></div>
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-purple-900/10 blur-[120px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-cyan-900/10 blur-[120px] pointer-events-none z-0"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-20">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Content (Left) -->
            <div class="lg:col-span-7 text-left">
              <Reveal :delay="0">
                <div class="inline-flex items-center px-3.5 py-1 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-300 text-sm font-semibold mb-6 tracking-wide uppercase shadow-inner shadow-purple-500/5">
                  <BrainIcon class="w-4 h-4 mr-2 text-purple-400 animate-pulse" />
                  Artificial Intelligence & ML
                </div>
              </Reveal>
              
              <Reveal :delay="100">
                <h1 class="text-3xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-white">
                  AI & Machine Learning Solutions <br/>
                  <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-500 to-cyan-400">for the Future</span>
                </h1>
              </Reveal>
              
              <Reveal :delay="200">
                <p class="text-base md:text-xl text-slate-300 mb-10 leading-relaxed max-w-xl">
                  We build intelligent systems that automate complex workflows, predict market opportunities, and optimize operations for next-generation enterprises.
                </p>
              </Reveal>
              
              <Reveal :delay="300">
                <div class="flex flex-col sm:flex-row items-center gap-4 mb-10">
                  <a href="#solutions" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-500 hover:to-cyan-400 text-white font-medium text-base transition-all shadow-lg shadow-purple-500/20 transform hover:-translate-y-0.5 text-center">
                    Explore AI Solutions
                  </a>
                  <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 font-medium text-base transition-all transform hover:-translate-y-0.5 text-center">
                    Contact AI Team
                  </Link>
                </div>
              </Reveal>

              <Reveal :delay="400">
                <div class="flex flex-wrap items-center gap-6 text-sm text-slate-400 font-medium border-t border-slate-900 pt-6">
                  <div class="flex items-center gap-2">
                    <CalendarIcon class="w-4 h-4 text-purple-400" />
                    <span>Last Updated: <span class="text-white">May 21, 2026</span></span>
                  </div>
                  <div class="flex items-center gap-2">
                    <CheckCircleIcon class="w-4 h-4 text-cyan-400" />
                    <span>SaaS Standard Compliance: <span class="text-white">Active</span></span>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- Neural Connection Interactive Simulator (Right) -->
            <div class="lg:col-span-5 flex justify-center relative">
              <Reveal :delay="200">
                <div class="absolute -inset-4 bg-purple-500/10 rounded-full blur-3xl -z-10"></div>
                <div class="w-[320px] h-[320px] md:w-[400px] md:h-[400px] relative border border-slate-800 bg-slate-950/40 backdrop-blur-md rounded-3xl p-6 flex flex-col justify-between overflow-hidden shadow-2xl">
                  <!-- Custom Animated SVG Neural Network -->
                  <div class="absolute inset-0 z-0 flex items-center justify-center p-6 opacity-80">
                    <svg viewBox="0 0 200 200" class="w-full h-full text-purple-500/20">
                      <!-- Connections -->
                      <line x1="20" y1="50" x2="80" y2="30" stroke="currentColor" stroke-width="0.8" class="animate-pulse" />
                      <line x1="20" y1="50" x2="80" y2="100" stroke="currentColor" stroke-width="0.8" />
                      <line x1="20" y1="150" x2="80" y2="100" stroke="currentColor" stroke-width="0.8" />
                      <line x1="20" y1="150" x2="80" y2="170" stroke="currentColor" stroke-width="0.8" />
                      
                      <line x1="80" y1="30" x2="140" y2="60" stroke="currentColor" stroke-width="0.8" />
                      <line x1="80" y1="100" x2="140" y2="60" stroke="currentColor" stroke-width="0.8" />
                      <line x1="80" y1="100" x2="140" y2="140" stroke="currentColor" stroke-width="0.8" />
                      <line x1="80" y1="170" x2="140" y2="140" stroke="currentColor" stroke-width="0.8" />
                      
                      <line x1="140" y1="60" x2="180" y2="100" stroke="currentColor" stroke-dasharray="3" stroke-width="1.2" class="animate-pulse text-cyan-400" />
                      <line x1="140" y1="140" x2="180" y2="100" stroke="currentColor" stroke-dasharray="3" stroke-width="1.2" class="animate-pulse text-cyan-400" />

                      <!-- Node Pulses -->
                      <circle cx="20" cy="50" r="4" fill="#a855f7" />
                      <circle cx="20" cy="150" r="4" fill="#a855f7" />
                      <circle cx="80" cy="30" r="4.5" fill="#a855f7" />
                      <circle cx="80" cy="100" r="5" fill="#c084fc" />
                      <circle cx="80" cy="170" r="4.5" fill="#a855f7" />
                      
                      <circle cx="140" cy="60" r="5" fill="#22d3ee" />
                      <circle cx="140" cy="140" r="5" fill="#22d3ee" />
                      <circle cx="180" cy="100" r="6" fill="#06b6d4" />
                      
                      <!-- Path animation pulses -->
                      <circle cx="20" cy="50" r="2.5" fill="#fff" class="animate-ping" />
                      <circle cx="80" cy="100" r="2.5" fill="#fff" class="animate-ping" />
                      <circle cx="180" cy="100" r="3" fill="#fff" class="animate-ping" />
                    </svg>
                  </div>

                  <!-- Overlay Widget Content -->
                  <div class="relative z-10 space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-900">
                      <span class="text-xs font-mono text-purple-400 font-bold">MODEL_TRAINING_MODULE</span>
                      <span class="text-[10px] px-2 py-0.5 rounded bg-purple-500/10 text-purple-300 font-mono">EPOCH: 480/500</span>
                    </div>

                    <div class="space-y-2">
                      <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800/80">
                        <div class="flex justify-between items-center mb-1">
                          <span class="text-[10px] text-slate-400">Current Accuracy</span>
                          <span class="text-xs font-bold text-cyan-400 font-mono">98.42%</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                          <div class="bg-gradient-to-r from-purple-500 to-cyan-400 h-full rounded-full" style="width: 98.42%"></div>
                        </div>
                      </div>

                      <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800/80">
                        <div class="flex justify-between items-center mb-1">
                          <span class="text-[10px] text-slate-400">Val Loss</span>
                          <span class="text-xs font-bold text-pink-500 font-mono">0.0248</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                          <div class="bg-pink-500 h-full rounded-full" style="width: 8%"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Telemetry stream -->
                  <div class="relative z-10 bg-slate-950 border border-slate-900 p-3 rounded-xl font-mono text-[9px] text-slate-400 space-y-1 mt-4 h-24 overflow-y-hidden">
                    <div class="text-purple-400">>> Initializing neural backpropagation...</div>
                    <div class="text-slate-500">>> Loading training weights from database... [OK]</div>
                    <div class="text-slate-500">>> Target function configured. Optimizer: Adam.</div>
                    <div class="text-cyan-400">>> Epoch 478/500 | Loss: 0.0252 | Acc: 98.39%</div>
                    <div class="text-cyan-400">>> Epoch 479/500 | Loss: 0.0250 | Acc: 98.41%</div>
                    <div class="text-purple-300">>> Epoch 480/500 | Loss: 0.0248 | Acc: 98.42%</div>
                  </div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- AI Solutions Section -->
      <section id="solutions" class="py-24 bg-slate-950 relative border-y border-slate-900">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[70%] h-[70%] bg-purple-900/5 rounded-full blur-[160px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10">
          <div class="text-center max-w-2xl mx-auto mb-20">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                Solutions Portfolio
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-white mb-6 leading-tight">
                AI Solutions We Provide
              </h2>
              <p class="text-slate-400 text-lg leading-relaxed">
                Leverage modern intelligent algorithms tailored specifically to solve core organizational challenges and streamline your operational productivity.
              </p>
            </Reveal>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <Reveal v-for="(solution, idx) in SOLUTIONS" :key="idx" :delay="idx * 100">
              <div class="p-8 rounded-3xl bg-slate-900/40 border border-slate-800/80 card-hover-neon group relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                  <!-- Tech background pattern inside card -->
                  <div class="absolute -right-8 -top-8 w-24 h-24 bg-purple-600/5 rounded-full blur-2xl group-hover:bg-purple-600/15 transition-all"></div>
                  
                  <div class="w-14 h-14 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-purple-400 mb-6 relative z-10 group-hover:border-purple-500 group-hover:text-purple-300 transition-colors duration-300">
                    <component :is="solution.icon" class="w-6 h-6" />
                  </div>
                  
                  <h3 class="text-xl font-sora font-bold mb-3 relative z-10 text-white group-hover:text-purple-300 transition-colors">
                    {{ solution.title }}
                  </h3>
                  
                  <p class="text-slate-400 leading-relaxed text-sm relative z-10 mb-6">
                    {{ solution.desc }}
                  </p>
                </div>
                
                <div class="flex items-center text-purple-400 font-medium text-sm border-t border-slate-800/50 pt-4 relative z-10">
                  <span>Explore Capabilities</span>
                  <ArrowRightIcon class="w-4 h-4 ml-1.5 transition-transform group-hover:translate-x-1 text-purple-400" />
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Use Cases Showcase Section -->
      <section class="py-24 bg-slate-950 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                Real-World Impact
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-white mb-6">
                Targeted AI Use Cases
              </h2>
              <p class="text-slate-400 text-lg leading-relaxed">
                Explore custom integrations optimized for high-growth sectors, ensuring complete security and measurable productivity.
              </p>
            </Reveal>
          </div>

          <!-- Use Case Tabbed Matrix -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Controls -->
            <div class="lg:col-span-5 space-y-4">
              <Reveal v-for="(uc, idx) in USE_CASES" :key="idx" :delay="idx * 50">
                <button 
                  @click="activeUseCase = idx" 
                  :class="[
                    'w-full text-left p-6 rounded-2xl transition-all duration-300 border flex items-center justify-between group',
                    activeUseCase === idx 
                      ? 'bg-slate-900 border-purple-500/60 shadow-lg shadow-purple-950/20' 
                      : 'bg-transparent border-slate-900 hover:border-slate-800'
                  ]"
                >
                  <div class="flex items-center gap-4">
                    <div :class="[
                      'w-10 h-10 rounded-xl flex items-center justify-center transition-colors',
                      activeUseCase === idx ? 'bg-purple-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'
                    ]">
                      <component :is="uc.icon" class="w-5 h-5" />
                    </div>
                    <div>
                      <h4 class="font-sora font-bold text-sm text-white group-hover:text-purple-300 transition-colors">{{ uc.title }}</h4>
                      <p class="text-xs text-slate-500 mt-0.5 leading-normal max-w-xs">{{ uc.sub }}</p>
                    </div>
                  </div>
                  <ChevronRightIcon :class="['w-4 h-4 transition-all', activeUseCase === idx ? 'text-purple-400 translate-x-1' : 'text-slate-600']" />
                </button>
              </Reveal>
            </div>

            <!-- Right Detail Panel -->
            <div class="lg:col-span-7">
              <Reveal :delay="200">
                <div class="p-8 rounded-3xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-md relative overflow-hidden min-h-[420px] flex flex-col justify-between">
                  <!-- Flow gradient -->
                  <div class="absolute -right-24 -bottom-24 w-60 h-60 bg-cyan-500/5 rounded-full blur-3xl"></div>
                  
                  <div>
                    <div class="flex justify-between items-center mb-6 border-b border-slate-800 pb-4">
                      <span class="text-xs font-mono text-purple-400 font-bold uppercase tracking-wider">DEPLOYMENT METRIC</span>
                      <span class="text-xs text-slate-500">Latency: <span class="text-cyan-400 font-mono">14ms</span></span>
                    </div>

                    <h3 class="text-2xl font-sora font-extrabold text-white mb-4">
                      {{ USE_CASES[activeUseCase].title }}
                    </h3>
                    
                    <p class="text-slate-300 text-sm leading-relaxed mb-6">
                      {{ USE_CASES[activeUseCase].desc }}
                    </p>

                    <!-- Interactive Diagram Mockup -->
                    <div class="bg-slate-950 border border-slate-900 p-5 rounded-2xl space-y-4">
                      <p class="text-[10px] text-slate-500 uppercase font-mono tracking-widest">Process Flow Logic</p>
                      
                      <div class="flex items-center justify-between text-xs font-mono text-slate-400 gap-2">
                        <div class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-center w-full">
                          <p class="text-[8px] text-slate-500">INPUT DATA</p>
                          <p class="font-bold text-white text-[10px]">Unstructured</p>
                        </div>
                        
                        <div class="text-purple-400 shrink-0 animate-pulse">&rarr;</div>
                        
                        <div class="px-3 py-2 bg-purple-950/20 border border-purple-500/30 rounded-lg text-center w-full">
                          <p class="text-[8px] text-purple-400">EMBEDDING / LLM</p>
                          <p class="font-bold text-purple-300 text-[10px]">CoreVisys AI</p>
                        </div>
                        
                        <div class="text-cyan-400 shrink-0 animate-pulse">&rarr;</div>
                        
                        <div class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-lg text-center w-full">
                          <p class="text-[8px] text-slate-500">OUTPUT DATA</p>
                          <p class="font-bold text-cyan-400 text-[10px]">Automation</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-8 pt-6 border-t border-slate-800 flex justify-between items-center text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full bg-green-500"></span>
                      <span>Operational Efficiency Increase</span>
                    </div>
                    <span class="text-white font-bold font-sora text-sm bg-purple-500/10 px-3 py-1 rounded-full text-purple-300">
                      {{ USE_CASES[activeUseCase].efficiency }}
                    </span>
                  </div>
                </div>
              </Reveal>
            </div>
          </div>
        </div>
      </section>

      <!-- ML Pipeline Section -->
      <section class="py-24 bg-slate-950 relative border-t border-slate-900 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="text-center max-w-2xl mx-auto mb-16">
            <Reveal>
              <div class="inline-flex items-center px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                Workflow
              </div>
              <h2 class="text-3xl md:text-5xl font-sora font-bold text-white mb-6">
                Our Development Pipeline
              </h2>
              <p class="text-slate-400 text-lg leading-relaxed">
                From structural raw data analysis to high-performance hyperparameter tuning, we run our operations through a highly optimized sequence.
              </p>
            </Reveal>
          </div>

          <!-- Pipeline Interactive Section -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Timeline details -->
            <div class="lg:col-span-6 space-y-6">
              <Reveal v-for="(phase, idx) in PIPELINE" :key="idx" :delay="idx * 100">
                <div 
                  @click="activePhase = idx"
                  :class="[
                    'p-6 rounded-2xl cursor-pointer transition-all border flex gap-5 items-start',
                    activePhase === idx 
                      ? 'bg-slate-900 border-purple-500/60 shadow-lg shadow-purple-950/20' 
                      : 'bg-slate-900/30 border-slate-900/50 hover:border-slate-800'
                  ]"
                >
                  <div :class="[
                    'w-10 h-10 rounded-xl shrink-0 flex items-center justify-center font-bold text-sm font-sora transition-colors',
                    activePhase === idx ? 'bg-purple-600 text-white' : 'bg-slate-950 text-slate-500 border border-slate-800'
                  ]">
                    0{{ idx + 1 }}
                  </div>
                  <div>
                    <h3 class="font-sora font-bold text-base text-white transition-colors group-hover:text-purple-300">
                      {{ phase.title }}
                    </h3>
                    <p class="text-sm text-slate-400 mt-2 leading-relaxed">
                      {{ phase.desc }}
                    </p>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- Animated Virtual Sandbox Console -->
            <div class="lg:col-span-6">
              <Reveal :delay="200">
                <div class="bg-slate-950 border border-slate-900 p-6 rounded-3xl font-mono relative overflow-hidden shadow-2xl">
                  <!-- Neon accents -->
                  <div class="absolute -right-16 -top-16 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl"></div>
                  
                  <div class="flex justify-between items-center border-b border-slate-900 pb-4 mb-4 text-xs">
                    <div class="flex items-center gap-1.5">
                      <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                      <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                      <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                      <span class="text-[10px] text-slate-500 ml-2">COREVISYS_PIPELINE.py</span>
                    </div>
                    <span class="text-[9px] px-2 py-0.5 bg-slate-900 text-green-400 rounded">STATUS: RUNNING</span>
                  </div>

                  <div class="h-80 overflow-y-auto space-y-3 text-[10px] leading-relaxed select-none scrollbar-none">
                    <p class="text-slate-500"># Initializing workflow sequence for phase {{ activePhase + 1 }}...</p>
                    <p class="text-purple-400 font-bold">>>> RUNNING: {{ PIPELINE[activePhase].title.toUpperCase() }}</p>
                    
                    <div v-if="activePhase === 0" class="space-y-1">
                      <p class="text-slate-400">>>> Fetching unstructured files from cloud datasets...</p>
                      <p class="text-slate-400">>>> Found: 1,480,920 records. Formats: CSV, JSON, PNG.</p>
                      <p class="text-slate-400">>>> Cleaning null variables and duplicates...</p>
                      <p class="text-green-400">>>> Data Preprocessing Complete: Accuracy rate 100%.</p>
                      <p class="text-cyan-400">>>> Splitting data: Train (80%), Test (10%), Val (10%).</p>
                    </div>

                    <div v-if="activePhase === 1" class="space-y-1">
                      <p class="text-slate-400">>>> Loading architecture: PyTorch Deep Learning Model.</p>
                      <p class="text-slate-400">>>> Weights initialized randomly. GPU clusters connected.</p>
                      <p class="text-slate-400">>>> Starting training loop... Epoch 100/500</p>
                      <p class="text-cyan-400">>>> Epoch 200/500 | Loss: 0.1245 | Accuracy: 92.18%</p>
                      <p class="text-cyan-400">>>> Epoch 400/500 | Loss: 0.0410 | Accuracy: 97.45%</p>
                      <p class="text-green-400">>>> Training Complete: Final Accuracy: 98.42%</p>
                    </div>

                    <div v-if="activePhase === 2" class="space-y-1">
                      <p class="text-slate-400">>>> Initializing rigorous QA unit evaluation...</p>
                      <p class="text-slate-400">>>> Testing dataset loaded (148,092 entries)...</p>
                      <p class="text-slate-400">>>> Cross-validating model weights under extreme edge queries...</p>
                      <p class="text-cyan-400">>>> Precision Rate: 98.24% | Recall Rate: 98.60%</p>
                      <p class="text-green-400">>>> All evaluation benchmarks passed successfully.</p>
                    </div>

                    <div v-if="activePhase === 3" class="space-y-1">
                      <p class="text-slate-400">>>> Packaging final compiled model parameters into Docker...</p>
                      <p class="text-slate-400">>>> Orchestrating Docker containers to AWS EKS clusters...</p>
                      <p class="text-slate-400">>>> Setting up high-performance CDN & API endpoints...</p>
                      <p class="text-cyan-400">>>> Deployment API Health check: Code 200 [OK]</p>
                      <p class="text-green-400">>>> Service status changed to LIVE.</p>
                    </div>

                    <div v-if="activePhase === 4" class="space-y-1">
                      <p class="text-slate-400">>>> Establishing automated server telemetry metrics...</p>
                      <p class="text-slate-400">>>> Monitoring real-time inference latency and hardware load...</p>
                      <p class="text-cyan-400">>>> Triggering automated feedback loop retraining module...</p>
                      <p class="text-green-400">>>> Core model weights optimized under continuous analytics.</p>
                    </div>

                    <div class="mt-4 p-2 bg-slate-900 border border-slate-800 rounded font-mono text-[9px] text-slate-300">
                      <div class="flex justify-between items-center mb-1">
                        <span>Process Stream Accuracy:</span>
                        <span class="text-green-400 font-bold">99.88%</span>
                      </div>
                      <div class="w-full bg-slate-950 h-1 rounded overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-cyan-400 h-full rounded" style="width: 100%"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- Technology Stack Section -->
      <section class="py-24 bg-slate-950 relative border-t border-slate-900 overflow-hidden">
        <div class="absolute inset-0 grid-bg-dark opacity-10 z-0"></div>
        <div class="max-w-7xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-sm font-semibold mb-6 tracking-wide uppercase">
              Technology Stack
            </div>
            <h2 class="text-3xl md:text-5xl font-sora font-bold text-white mb-6">
              Our Core Technologies
            </h2>
            <p class="text-slate-400 text-lg leading-relaxed max-w-2xl mx-auto mb-16">
              We leverage modern platforms and industry-standard machine learning stacks to ensure reliable, high-performance execution.
            </p>
          </Reveal>

          <div class="grid grid-cols-2 md:grid-cols-5 gap-8 max-w-4xl mx-auto">
            <Reveal v-for="(tech, idx) in TECHS" :key="idx" :delay="idx * 100">
              <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/80 backdrop-blur hover:border-purple-500/50 hover:shadow-lg hover:shadow-purple-950/20 transition-all flex flex-col items-center justify-center h-36">
                <!-- SVG styled icons -->
                <div class="w-12 h-12 flex items-center justify-center text-purple-400 mb-3 font-bold group-hover:text-purple-300">
                  <component :is="tech.icon" class="w-8 h-8" />
                </div>
                <div class="text-sm font-sora font-semibold text-white">{{ tech.name }}</div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <!-- Benefits Section -->
      <section class="py-24 bg-slate-950 relative border-t border-slate-900 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-5 text-left">
              <Reveal>
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-sm font-semibold mb-6 tracking-wide uppercase">
                  Business Advantage
                </div>
                <h2 class="text-3xl md:text-5xl font-sora font-bold text-white mb-6 leading-tight">
                  Proven Business Benefits
                </h2>
                <p class="text-slate-400 text-lg leading-relaxed mb-8">
                  Implement custom models that scale your operations, streamline manual bottlenecks, and empower structural decision-making speeds.
                </p>
              </Reveal>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-6">
              <Reveal v-for="(benefit, idx) in BENEFITS" :key="idx" :delay="idx * 100">
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/80 backdrop-blur hover:border-purple-500/50 transition-all flex gap-4 items-start">
                  <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center shrink-0 text-purple-400">
                    <CheckCircleIcon class="w-5 h-5 text-purple-400" />
                  </div>
                  <div>
                    <h4 class="font-sora font-bold text-sm text-white mb-2">{{ benefit.title }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">{{ benefit.desc }}</p>
                  </div>
                </div>
              </Reveal>
            </div>

          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="py-24 bg-slate-950 relative">
        <div class="max-w-6xl mx-auto px-6 relative">
          <!-- Ambient glowing backgrounds -->
          <div class="absolute inset-0 bg-gradient-to-r from-purple-600/10 to-cyan-500/10 rounded-[40px] blur-xl"></div>
          
          <div class="relative rounded-[40px] bg-slate-900/60 border border-slate-800 p-8 md:p-16 text-center overflow-hidden z-10">
            <!-- Grid backdrop -->
            <div class="absolute inset-0 grid-bg-dark opacity-10 pointer-events-none"></div>
            
            <Reveal>
              <h2 class="text-3xl md:text-5xl font-sora font-extrabold text-white mb-6">
                Build Your AI Solution Today
              </h2>
              <p class="text-slate-300 text-base md:text-lg mb-10 max-w-2xl mx-auto leading-relaxed">
                Connect with our expert machine learning engineers to structure custom models, fine-tune models, or automate complex business operations.
              </p>
              
              <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-purple-600 to-cyan-500 hover:from-purple-500 hover:to-cyan-400 text-white font-bold rounded-full transition-all shadow-lg shadow-purple-600/30 transform hover:-translate-y-0.5">
                  Book Architecture Session
                </Link>
                <Link :href="route('contact')" class="w-full sm:w-auto px-8 py-4 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-white font-semibold rounded-full transition-all transform hover:-translate-y-0.5">
                  Request AI Audit
                </Link>
              </div>
            </Reveal>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-900 py-16">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
          
          <div class="space-y-4">
            <div class="flex items-center gap-2">
              <img src="/storage/logo/Main-Logo-2.png" alt="CoreVisys" class="h-16 md:h-20 w-auto" />
            </div>
            <p class="text-sm leading-relaxed">
              Engineering futuristic enterprise software, intelligent machine learning algorithms, and high-performance cross-platform platforms.
            </p>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Services</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/services/custom-software" class="hover:text-purple-400 transition-colors">Custom Software</Link></li>
              <li><Link href="/services/web-applications" class="hover:text-purple-400 transition-colors">Web Applications</Link></li>
              <li><Link href="/services/mobile-apps" class="hover:text-purple-400 transition-colors">Mobile App Dev</Link></li>
              <li><Link href="/services/ai-ml" class="text-purple-400 font-semibold transition-colors">AI & Machine Learning</Link></li>
              <li><Link href="/services/cloud-solutions" class="hover:text-purple-400 transition-colors">Cloud Architecture</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Company</h4>
            <ul class="space-y-3 text-sm">
              <li><a href="#" class="hover:text-purple-400 transition-colors">About Us</a></li>
              <li><a href="#" class="hover:text-purple-400 transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-purple-400 transition-colors">Careers</Link></li>
              <li><Link :href="route('contact')" class="hover:text-purple-400 transition-colors">Contact</Link></li>
            </ul>
          </div>

          <div>
            <h4 class="text-white font-bold mb-4 font-sora">Legal</h4>
            <ul class="space-y-3 text-sm">
              <li><Link href="/privacy-policy" class="hover:text-purple-400 transition-colors">Privacy Policy</Link></li>
              <li><Link href="/terms-of-service" class="hover:text-purple-400 transition-colors">Terms of Service</Link></li>
              <li><Link href="/cookie-policy" class="hover:text-purple-400 transition-colors">Cookie Policy</Link></li>
            </ul>
          </div>

        </div>

        <div class="pt-8 border-t border-slate-900 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
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
  Cpu as CpuIcon, 
  Brain as BrainIcon, 
  Bot as BotIcon, 
  TrendingUp as TrendingUpIcon, 
  Workflow as WorkflowIcon, 
  Terminal as TerminalIcon, 
  Code as CodeIcon,
  Shield as ShieldIcon,
  Activity as ActivityIcon,
  Users as UsersIcon,
  CheckCircle as CheckCircleIcon,
  Calendar as CalendarIcon,
  ArrowRight as ArrowRightIcon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);
const activeUseCase = ref(0);
const activePhase = ref(0);

const SOLUTIONS = [
  { icon: BotIcon, title: "Chatbots & Assistants", desc: "Fine-tuned generative AI chat agents and contextual co-pilots that learn company intelligence dynamically." },
  { icon: TrendingUpIcon, title: "Predictive Analytics", desc: "Robust data regressions that process multi-variable historical timelines to forecast and prevent market bottlenecks." },
  { icon: CpuIcon, title: "Recommendation Engines", desc: "Highly tailored user metrics processing that serves precise content, product cataloging, and personal feeds." },
  { icon: WorkflowIcon, title: "Automation Systems", desc: "Intelligent background scripts and automated agents that perform human-like data indexing and workflows." },
  { icon: TerminalIcon, title: "NLP Solutions", desc: "Advanced semantic search, document entity classification, and contextual summarization models." }
];

const USE_CASES = [
  { icon: WorkflowIcon, title: "Business Automation", sub: "Speed up core logic paths", desc: "Automate complex clerical data routing, contract analysis, and report generation using local LLM inference engines securely.", efficiency: "+78% Time Saved" },
  { icon: UsersIcon, title: "Customer Support AI", sub: "Instant precise responses", desc: "Serve hundreds of immediate, accurate customer questions using a secure retrieval-augmented generation (RAG) system.", efficiency: "+92% CSAT Score" },
  { icon: ActivityIcon, title: "Data Analysis", sub: "Uncover structural insights", desc: "Generate instant business trends and data forecasts from complex CSV/SQL databases using automated model testing.", efficiency: "+64% Faster Insights" },
  { icon: ShieldIcon, title: "Fraud Detection", sub: "Prevent malicious vectors", desc: "Flag abnormal transaction pathways or identity verification vectors instantly using real-time automated regression models.", efficiency: "-99.9% Fraud Rate" }
];

const PIPELINE = [
  { title: "Data Processing", desc: "Clean and pre-process raw unstructured customer datasets, establishing solid train, validation, and test arrays." },
  { title: "Model Training", desc: "Implement state-of-the-art weights and hyperparameter optimizations using GPU-accelerated clusters." },
  { title: "Testing & Validation", desc: "Validate model execution under extreme edge queries, validating robust recall, precision, and alignment metrics." },
  { title: "Cloud Deployment", desc: "Publish fully containerized models to secure cloud clusters with highly scalable API routing configurations." },
  { title: "Continuous Optimization", desc: "Monitor telemetry queries in real-time, executing continuous feedback loop optimizations." }
];

const TECHS = [
  { icon: CodeIcon, name: "Python" },
  { icon: CpuIcon, name: "TensorFlow" },
  { icon: BrainIcon, name: "PyTorch" },
  { icon: BotIcon, name: "OpenAI APIs" },
  { icon: TerminalIcon, name: "Scikit-Learn" }
];

const BENEFITS = [
  { title: "Faster Decision Making", desc: "Model business scenarios instantly to take decisive, data-driven operational paths with zero delay." },
  { title: "Reduced Manual Work", desc: "Delegate heavy data-entry, clerical tasks, and routine administrative loops to intelligent agents." },
  { title: "Smart Automation", desc: "Leverage self-improving workflows that adapt to new context queries and optimize productivity." },
  { title: "Absolute Security", desc: "Run state-of-the-art secure local integrations ensuring sensitive customer and database security." }
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
.grid-bg-dark {
  background-size: 45px 45px;
  background-image: linear-gradient(to right, rgba(168, 85, 247, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(168, 85, 247, 0.05) 1px, transparent 1px);
  mask-image: radial-gradient(circle at center, black 60%, transparent 100%);
  -webkit-mask-image: radial-gradient(circle at center, black 60%, transparent 100%);
}

.glass-nav-dark {
  background: rgba(9, 13, 26, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(168, 85, 247, 0.1);
}

.card-hover-neon {
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.card-hover-neon:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 30px -10px rgba(168, 85, 247, 0.25);
  border-color: rgba(168, 85, 247, 0.4);
}

.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
