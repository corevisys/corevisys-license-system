<template>
  <Head title="Insights & Ideas — CoreVisys" />

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
          <Link href="/insights" :class="['transition-colors text-amber font-semibold']">Insights</Link>
        </div>

        <div class="hidden md:flex items-center space-x-4">
          <Link :href="route('login')" :class="['text-sm font-medium transition-colors duration-300', scrolled ? 'text-text-secondary hover:text-text-primary' : 'text-text-secondary hover:text-text-primary']">Sign In</Link>
                    <Link :href="route('contact')" :class="['px-5 py-2.5 rounded-full text-sm font-medium transition-all shadow-md transform hover:-translate-y-0.5', scrolled ? 'bg-bg-dark hover:bg-amber text-text-primary hover:shadow-amber/20' : 'bg-panel hover:bg-amber/10 text-text-primary hover:shadow-panel-line']">
            Book Consultation
          </Link>
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
        <Link href="/insights" class="text-amber font-semibold">Insights</Link>
                <Link :href="route('contact')" class="bg-amber text-text-primary px-5 py-2.5 rounded-lg text-sm font-medium text-center w-full mt-2">
          Book Consultation
        </Link>
      </div>
    </nav>

    <main>
      <!-- Hero / Header Section -->
      <section class="relative pt-32 pb-16 md:pt-40 md:pb-24 overflow-hidden bg-bg-dark">
        <div class="absolute inset-0 grid-bg opacity-20 z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full hero-gradient pointer-events-none z-0"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-20 text-center">
          <Reveal :delay="0">
            <h1 class="text-4xl md:text-6xl font-sora font-extrabold tracking-tight mb-6 leading-tight text-text-primary">
              Insights & <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber to-teal">Ideas</span>
            </h1>
          </Reveal>

          <Reveal :delay="100">
            <p class="text-base md:text-lg text-text-secondary mb-10 max-w-2xl mx-auto leading-relaxed">
              Latest technology trends, software engineering solutions, AI insights, and business innovation articles curated by our experts.
            </p>
          </Reveal>

          <!-- Search Bar -->
          <Reveal :delay="200">
            <div class="max-w-xl mx-auto relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-text-secondary group-focus-within:text-amber transition-colors">
                <SearchIcon class="w-5 h-5" />
              </div>
              <input
                v-model="searchQuery"
                type="text"
                class="w-full pl-12 pr-4 py-4 rounded-full bg-panel-2/60 border border-panel-line text-text-primary placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-amber focus:border-transparent  transition-all shadow-xl"
                placeholder="Search articles, topics, or keywords..."
              >
            </div>
          </Reveal>
        </div>
      </section>

      <!-- Category Filter Sticky Bar -->
      <div class="sticky top-[72px] z-40 bg-panel-2/90  border-b border-panel-line py-4 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 overflow-x-auto hide-scrollbar">
          <div class="flex space-x-2 md:space-x-4 min-w-max">
            <button
              v-for="cat in categories"
              :key="cat"
              @click="activeCategory = cat"
              :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-all duration-300',
                activeCategory === cat
                  ? 'bg-bg-dark text-text-primary shadow-md'
                  : 'bg-panel text-text-secondary border border-panel-line hover:bg-panel-2 hover:text-text-primary hover:border-panel-line'
              ]"
            >
              {{ cat }}
            </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-6 py-16">

        <!-- Featured Article -->
        <Reveal :delay="0" v-if="activeCategory === 'All' && !searchQuery">
          <div class="mb-20">
            <div class="group relative rounded-3xl overflow-hidden bg-panel border border-panel-line shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col md:flex-row h-full">
              <!-- Image side -->
              <div class="w-full md:w-1/2 relative overflow-hidden min-h-[300px]">
                <img :src="featuredArticle.img" :alt="featuredArticle.title" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" />
                <div class="absolute inset-0 bg-gradient-to-t from-bg-dark/60 to-transparent opacity-80 md:hidden"></div>
                <div class="absolute top-4 left-4">
                  <span class="px-3 py-1 bg-amber/90  text-text-primary text-xs font-bold uppercase tracking-wider rounded-full">Featured</span>
                </div>
              </div>

              <!-- Content side -->
              <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center relative bg-panel">
                <div class="flex items-center gap-3 text-sm text-amber font-semibold mb-4 tracking-wide uppercase">
                  <span>{{ featuredArticle.category }}</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-panel-line"></span>
                  <span class="text-text-muted flex items-center gap-1"><ClockIcon class="w-4 h-4" /> {{ featuredArticle.readTime }}</span>
                </div>

                <h2 class="text-2xl md:text-3xl lg:text-4xl font-sora font-bold text-text-primary mb-6 leading-tight group-hover:text-amber transition-colors duration-300">
                  {{ featuredArticle.title }}
                </h2>

                <p class="text-text-secondary mb-8 leading-relaxed text-base md:text-lg line-clamp-3">
                  {{ featuredArticle.desc }}
                </p>

                <div class="flex items-center justify-between mt-auto pt-6 border-t border-panel-line">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-panel-line flex items-center justify-center text-text-muted font-bold uppercase border border-panel-line shadow-sm">
                      {{ featuredArticle.author.charAt(0) }}
                    </div>
                    <div>
                      <div class="text-sm font-bold text-text-primary">{{ featuredArticle.author }}</div>
                      <div class="text-xs text-text-muted">{{ featuredArticle.date }}</div>
                    </div>
                  </div>
                  <button class="flex items-center gap-2 text-amber font-semibold group-hover:gap-3 transition-all">
                    Read Article <ArrowRightIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Reveal>

        <!-- Articles Grid & Sidebar -->
        <div class="flex flex-col lg:flex-row gap-12">

          <!-- Main Content (Articles Grid) -->
          <div class="w-full lg:w-3/4">
            <div class="flex items-center justify-between mb-8">
              <h3 class="text-2xl font-sora font-bold text-text-primary">
                {{ searchQuery ? 'Search Results' : 'Latest Articles' }}
              </h3>
              <span class="text-sm font-medium text-text-muted">{{ filteredArticles.length }} Articles</span>
            </div>

            <div v-if="filteredArticles.length === 0" class="py-20 text-center bg-panel rounded-3xl border border-panel-line shadow-sm">
              <div class="w-16 h-16 mx-auto bg-panel-2 rounded-full flex items-center justify-center text-text-secondary mb-4">
                <SearchIcon class="w-8 h-8" />
              </div>
              <h3 class="text-xl font-sora font-bold text-text-primary mb-2">No articles found</h3>
              <p class="text-text-muted">We couldn't find any articles matching your search query.</p>
              <button @click="searchQuery = ''; activeCategory = 'All'" class="mt-6 px-6 py-2.5 bg-bg-dark text-text-primary rounded-full font-medium text-sm hover:bg-amber transition-colors">
                Clear Filters
              </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <Reveal v-for="(article, index) in paginatedArticles" :key="article.id" :delay="(index % 2) * 100">
                <div class="group bg-panel rounded-3xl overflow-hidden border border-panel-line shadow-sm hover:shadow-xl transition-all duration-300 h-full flex flex-col card-hover-effect">
                  <div class="relative h-56 overflow-hidden">
                    <img :src="article.img" :alt="article.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    <div class="absolute top-4 left-4">
                      <span class="px-3 py-1 bg-panel/90  text-text-primary text-xs font-bold rounded-full shadow-sm">{{ article.category }}</span>
                    </div>
                  </div>

                  <div class="p-6 flex-grow flex flex-col">
                    <div class="flex items-center gap-3 text-xs text-text-muted font-medium mb-3 uppercase tracking-wide">
                      <span>{{ article.date }}</span>
                      <span class="w-1 h-1 rounded-full bg-panel-line"></span>
                      <span class="flex items-center gap-1"><ClockIcon class="w-3 h-3" /> {{ article.readTime }}</span>
                    </div>

                    <h3 class="text-xl font-sora font-bold text-text-primary mb-3 group-hover:text-amber transition-colors line-clamp-2">
                      {{ article.title }}
                    </h3>

                    <p class="text-text-secondary mb-6 text-sm leading-relaxed line-clamp-3">
                      {{ article.desc }}
                    </p>

                    <div class="mt-auto flex items-center justify-between border-t border-panel-line pt-4">
                      <div class="flex gap-2">
                        <span v-for="tag in article.tags" :key="tag" class="text-xs text-text-muted bg-panel-2 px-2 py-1 rounded-md font-medium">#{{ tag }}</span>
                      </div>
                      <button class="text-amber font-medium text-sm flex items-center group-hover:underline">
                        Read <ArrowRightIcon class="w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" />
                      </button>
                    </div>
                  </div>
                </div>
              </Reveal>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="mt-16 flex justify-center items-center gap-2">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="w-10 h-10 rounded-full flex items-center justify-center border border-panel-line text-text-secondary hover:bg-panel-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronLeftIcon class="w-5 h-5" />
              </button>

              <button
                v-for="page in totalPages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center font-medium transition-colors',
                  currentPage === page ? 'bg-amber text-text-primary shadow-md' : 'text-text-secondary hover:bg-panel-2'
                ]"
              >
                {{ page }}
              </button>

              <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="w-10 h-10 rounded-full flex items-center justify-center border border-panel-line text-text-secondary hover:bg-panel-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronRightIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="w-full lg:w-1/4 space-y-10">
            <!-- Trending Topics -->
            <div class="bg-panel p-6 rounded-3xl border border-panel-line shadow-sm">
              <h4 class="font-sora font-bold text-text-primary text-lg mb-4 flex items-center gap-2">
                <TrendingUpIcon class="w-5 h-5 text-amber" /> Trending Topics
              </h4>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="tag in trendingTags"
                  :key="tag"
                  @click="searchQuery = tag; activeCategory = 'All'"
                  class="px-3 py-1.5 bg-panel-2 border border-panel-line text-text-secondary rounded-full text-xs font-medium hover:bg-amber/10 hover:text-amber hover:border-amber/30 transition-colors"
                >
                  {{ tag }}
                </button>
              </div>
            </div>

            <!-- Newsletter Subscription -->
            <div class="bg-bg-dark p-8 rounded-3xl shadow-xl relative overflow-hidden group">
              <div class="absolute inset-0 bg-gradient-to-br from-amber/20 to-teal/20 opacity-50 z-0"></div>
              <div class="absolute -right-10 -top-10 w-32 h-32 bg-amber/100/30 blur-2xl rounded-full group-hover:bg-amber/40 transition-colors z-0"></div>

              <div class="relative z-10">
                <div class="w-12 h-12 bg-panel-2/60  rounded-2xl flex items-center justify-center text-text-primary mb-6">
                  <MailIcon class="w-6 h-6" />
                </div>
                <h4 class="font-sora font-bold text-text-primary text-xl mb-2">Stay Updated</h4>
                <p class="text-text-secondary text-sm mb-6 leading-relaxed">
                  Get the latest tech insights, software architecture patterns, and news delivered directly to your inbox.
                </p>

                <form @submit.prevent="handleSubscribe" class="space-y-3">
                  <div class="relative">
                    <input
                      v-model="emailInput"
                      type="email"
                      required
                      class="w-full bg-panel-2/60 border border-panel-line rounded-xl px-4 py-3 text-text-primary placeholder-text-secondary text-sm focus:outline-none focus:ring-2 focus:ring-amber transition-all"
                      placeholder="Your email address"
                    >
                  </div>
                  <button
                    type="submit"
                    :disabled="isSubscribing"
                    class="w-full bg-amber hover:bg-amber/100 text-text-primary rounded-xl px-4 py-3 text-sm font-bold shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-70"
                  >
                    <span v-if="!isSubscribing && !subscribed">Subscribe Now</span>
                    <span v-else-if="isSubscribing" class="flex items-center gap-2"><Loader2Icon class="w-4 h-4 animate-spin" /> Processing...</span>
                    <span v-else class="flex items-center gap-2"><CheckCircleIcon class="w-4 h-4" /> Subscribed!</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Call To Action -->
      <section class="py-20 bg-amber text-text-primary relative overflow-hidden">
        <div class="absolute inset-0 grid-bg opacity-10"></div>
        <div class="absolute top-1/2 -translate-y-1/2 right-0 w-96 h-96 bg-panel-2/60 blur-3xl rounded-full pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-6 relative z-10 text-center">
          <Reveal>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-6 font-sora">
              Need Custom Software Solutions?
            </h2>
            <p class="text-lg text-amber/70 mb-10 max-w-2xl mx-auto">
              Ready to bring your next big idea to life? Our expert engineering team is ready to help you scale and innovate.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <Link :href="route('contact')" class="px-8 py-4 bg-panel hover:bg-panel-2 text-amber rounded-full font-bold text-base transition-all shadow-xl transform hover:-translate-y-0.5 text-center">
                Book Consultation
              </Link>
              <Link href="/company#contact" class="px-8 py-4 bg-amber/50 hover:bg-amber-hover border border-amber/50 text-text-primary rounded-full font-bold text-base transition-all ">
                Contact Us
              </Link>
            </div>
          </Reveal>
        </div>
      </section>
    </main>

    <!-- Footer -->
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
              <li><Link href="/" class="hover:text-amber transition-colors">About Us</Link></li>
              <li><a href="/#portfolio" class="hover:text-amber transition-colors">Case Studies</a></li>
              <li><Link :href="route('careers')" class="hover:text-amber transition-colors">Careers</Link></li>
              <li><Link href="/insights" class="hover:text-amber transition-colors">Blog</Link></li>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import Reveal from '@/Components/Reveal.vue';
import {
  Menu as MenuIcon,
  X as XIcon,
  Code as CodeIcon,
  Search as SearchIcon,
  Clock as ClockIcon,
  ArrowRight as ArrowRightIcon,
  ChevronLeft as ChevronLeftIcon,
  ChevronRight as ChevronRightIcon,
  TrendingUp as TrendingUpIcon,
  Mail as MailIcon,
  CheckCircle as CheckCircleIcon,
  Phone as PhoneIcon,
  MapPin as MapPinIcon,
  Loader2 as Loader2Icon
} from 'lucide-vue-next';

const isOpen = ref(false);
const scrolled = ref(false);

const searchQuery = ref('');
const activeCategory = ref('All');
const categories = ['All', 'Web Development', 'AI & Automation', 'Cyber Security', 'SaaS', 'Cloud Computing', 'Business Technology'];

const currentPage = ref(1);
const itemsPerPage = 6;

const emailInput = ref('');
const isSubscribing = ref(false);
const subscribed = ref(false);

const trendingTags = ['React', 'Vue', 'Laravel', 'DevOps', 'Security', 'LLM', 'Startups', 'UI/UX'];

const featuredArticle = ref({
  title: 'Architecting for Scale: How We Built a Global Cloud Platform',
  category: 'Cloud Computing',
  author: 'Sarah Connor',
  date: 'Oct 15, 2026',
  readTime: '12 min read',
  desc: 'A comprehensive engineering breakdown of the microservices architecture, automated deployments, and load balancing strategies that power scalable platforms.',
  img: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80'
});

const articles = ref([
  { id: 1, title: 'The Future of Web Development in 2026', category: 'Web Development', date: 'Oct 12, 2026', readTime: '5 min read', desc: 'Explore the new standards in modern frontend development, edge computing, and server-side rendering.', img: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80', tags: ['Frontend', 'Vue'] },
  { id: 2, title: 'Generative AI for Enterprise Automation', category: 'AI & Automation', date: 'Oct 08, 2026', readTime: '8 min read', desc: 'How large language models are transforming back-office workflows and reducing manual data entry for enterprise companies.', img: 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=600&q=80', tags: ['AI', 'LLM'] },
  { id: 3, title: 'Zero-Trust Architecture Basics', category: 'Cyber Security', date: 'Sep 28, 2026', readTime: '6 min read', desc: 'Understanding the principles of zero-trust security for cloud-native applications and why VPNs are no longer enough.', img: 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=600&q=80', tags: ['Security', 'Cloud'] },
  { id: 4, title: 'SaaS Metrics You Must Track', category: 'SaaS', date: 'Sep 15, 2026', readTime: '7 min read', desc: 'A deep dive into MRR, churn rate, and CAC to build a sustainable, profitable SaaS business model.', img: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80', tags: ['Metrics', 'Startup'] },
  { id: 5, title: 'Kubernetes vs Serverless Architecture', category: 'Cloud Computing', date: 'Sep 02, 2026', readTime: '9 min read', desc: 'Which infrastructure strategy is right for your next scalable web application? A technical comparison.', img: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=600&q=80', tags: ['DevOps', 'Architecture'] },
  { id: 6, title: 'Digital Transformation Frameworks', category: 'Business Technology', date: 'Aug 21, 2026', readTime: '4 min read', desc: 'Navigating the challenges of legacy modernization in traditional enterprise environments effectively.', img: 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80', tags: ['Transformation', 'Enterprise'] },
  { id: 7, title: 'Building Accessible UI Components', category: 'Web Development', date: 'Aug 10, 2026', readTime: '6 min read', desc: 'Step-by-step guide to ensuring your web applications are fully accessible and compliant with WCAG standards.', img: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80', tags: ['UI/UX', 'Accessibility'] },
  { id: 8, title: 'Ransomware Prevention Strategies', category: 'Cyber Security', date: 'Jul 28, 2026', readTime: '10 min read', desc: 'The most effective modern strategies to defend your enterprise network against sophisticated ransomware attacks.', img: 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&w=600&q=80', tags: ['Security', 'Enterprise'] },
]);

// Reset to page 1 when filters change
watch([searchQuery, activeCategory], () => {
  currentPage.value = 1;
});

const filteredArticles = computed(() => {
  return articles.value.filter(article => {
    const matchCategory = activeCategory.value === 'All' || article.category === activeCategory.value;
    const matchSearch = article.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        article.desc.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        article.tags.some(tag => tag.toLowerCase().includes(searchQuery.value.toLowerCase()));
    return matchCategory && matchSearch;
  });
});

const totalPages = computed(() => Math.ceil(filteredArticles.value.length / itemsPerPage));

const paginatedArticles = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredArticles.value.slice(start, end);
});

const handleSubscribe = () => {
  if (!emailInput.value) return;
  isSubscribing.value = true;

  // Simulate API call
  setTimeout(() => {
    isSubscribing.value = false;
    subscribed.value = true;
    emailInput.value = '';

    // Reset success message after 3 seconds
    setTimeout(() => {
      subscribed.value = false;
    }, 3000);
  }, 1000);
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
/* Same global utility classes from Company.vue and Welcome.vue */
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

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
