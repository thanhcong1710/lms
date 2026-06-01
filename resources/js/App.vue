<template>
  <div :class="[theme, 'min-h-screen flex font-sans bg-brand-bg text-brand-text']">
    <!-- Mobile Hamburger Header -->
    <header class="md:hidden w-full bg-brand-card/80 backdrop-blur-md border-b border-brand-border h-16 fixed top-0 left-0 z-40 flex items-center justify-between px-4" v-if="isAuthenticated">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg">
          LMS
        </div>
        <span class="font-bold text-base leading-none">CMS EDU</span>
      </div>
      
      <div class="flex items-center gap-2">
        <!-- Language Switcher Mobile -->
        <button @click="toggleLanguage" class="px-2 py-1 rounded-lg bg-brand-input hover:bg-brand-border font-medium text-xs text-brand-desc hover:text-brand-text transition">
          {{ currentLocale === 'vi' ? 'VI' : 'EN' }}
        </button>
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="p-2 rounded-xl bg-brand-input hover:bg-brand-border transition text-brand-desc hover:text-brand-text">
          <span v-if="theme === 'dark'">☀️</span>
          <span v-else>🌙</span>
        </button>
        <!-- Mobile Menu Toggle -->
        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 rounded-xl bg-brand-input hover:bg-brand-border text-brand-desc hover:text-brand-text">
          🍔
        </button>
      </div>
    </header>

    <!-- Sidebar Overlay for Mobile -->
    <div v-if="isAuthenticated && isMobileMenuOpen" @click="isMobileMenuOpen = false" class="md:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40"></div>

    <!-- Sidebar (Desktop and Mobile Drawer) -->
    <aside :class="[
      isAuthenticated ? 'flex' : 'hidden',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      'w-64 bg-brand-card/90 backdrop-blur-md border-r border-brand-border flex-col justify-between p-5 fixed md:sticky top-0 h-screen z-50 transition-transform duration-300 ease-in-out'
    ]">
      <div>
        <!-- Logo and Close Button (Mobile) -->
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">
              LMS
            </div>
            <div>
              <h1 class="font-bold text-lg leading-none">CMS EDU</h1>
              <span class="text-xs text-indigo-500 font-medium">LMS PORTAL</span>
            </div>
          </div>
          <button @click="isMobileMenuOpen = false" class="md:hidden p-1.5 rounded-lg bg-brand-input hover:bg-brand-border">
            ✕
          </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-3">
          <!-- Always visible dashboard -->
          <div class="mb-2">
            <router-link to="/" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc" active-class="bg-indigo-600/10 text-indigo-500 border-l-4 border-indigo-600 font-medium">
              <span>📊 {{ $t('sidebar.dashboard') }}</span>
            </router-link>
          </div>

          <!-- Group: Center Management -->
          <div v-if="userRole === 'admin' || userRole === 'team_leader'" class="border-t border-brand-border/50 pt-3">
            <button @click="toggleGroup('center')" :class="['w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition rounded-xl mb-1', expandedGroup === 'center' ? 'text-indigo-500 bg-indigo-500/10' : 'text-brand-desc hover:text-brand-text hover:bg-brand-input/50']">
              <div class="flex items-center gap-2.5">
                <span class="text-base">🏢</span>
                <span>{{ $t('sidebar.group_center') }}</span>
              </div>
              <svg :class="{'rotate-180': expandedGroup === 'center'}" class="w-4 h-4 transition-transform duration-300 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div v-show="expandedGroup === 'center'" class="space-y-1 pl-2">
              <router-link v-if="userRole === 'admin'" to="/branches" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.branches') }}</span>
              </router-link>
              <router-link v-if="userRole === 'admin' || userRole === 'team_leader'" to="/teachers" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.teachers') }}</span>
              </router-link>
              <router-link to="/classes" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.classes') }}</span>
              </router-link>
              <router-link to="/students" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.students') }}</span>
              </router-link>
              <router-link to="/contracts" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.contracts') }}</span>
              </router-link>
            </div>
          </div>

          <!-- Group for teacher role: Classes + Students + Contracts only -->
          <div v-if="userRole === 'teacher'" class="border-t border-brand-border/50 pt-3">
            <button @click="toggleGroup('center')" :class="['w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition rounded-xl mb-1', expandedGroup === 'center' ? 'text-indigo-500 bg-indigo-500/10' : 'text-brand-desc hover:text-brand-text hover:bg-brand-input/50']">
              <div class="flex items-center gap-2.5">
                <span class="text-base">🏢</span>
                <span>{{ $t('sidebar.group_center') }}</span>
              </div>
              <svg :class="{'rotate-180': expandedGroup === 'center'}" class="w-4 h-4 transition-transform duration-300 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div v-show="expandedGroup === 'center'" class="space-y-1 pl-2">
              <router-link to="/classes" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.classes') }}</span>
              </router-link>
              <router-link to="/students" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.students') }}</span>
              </router-link>
              <router-link to="/contracts" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.contracts') }}</span>
              </router-link>
            </div>
          </div>

          <!-- Group: UCREA Test Management -->
          <div class="border-t border-brand-border/50 pt-3">
            <button @click="toggleGroup('ucrea')" :class="['w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition rounded-xl mb-1', expandedGroup === 'ucrea' ? 'text-indigo-500 bg-indigo-500/10' : 'text-brand-desc hover:text-brand-text hover:bg-brand-input/50']">
              <div class="flex items-center gap-2.5">
                <span class="text-base">⭐</span>
                <span>{{ $t('sidebar.group_ucrea') }}</span>
              </div>
              <svg :class="{'rotate-180': expandedGroup === 'ucrea'}" class="w-4 h-4 transition-transform duration-300 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div v-show="expandedGroup === 'ucrea'" class="space-y-1 pl-2">
              <router-link to="/ucrea/tests" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.ucrea_tests') }}</span>
              </router-link>
              <router-link to="/ucrea/evaluations" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.ucrea_eval') }}</span>
              </router-link>
            </div>
          </div>

          <!-- Group: IG.BH Test Management -->
          <div class="border-t border-brand-border/50 pt-3">
            <button @click="toggleGroup('igbh')" :class="['w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition rounded-xl mb-1', expandedGroup === 'igbh' ? 'text-indigo-500 bg-indigo-500/10' : 'text-brand-desc hover:text-brand-text hover:bg-brand-input/50']">
              <div class="flex items-center gap-2.5">
                <span class="text-base">📊</span>
                <span>{{ $t('sidebar.group_igbh') }}</span>
              </div>
              <svg :class="{'rotate-180': expandedGroup === 'igbh'}" class="w-4 h-4 transition-transform duration-300 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div v-show="expandedGroup === 'igbh'" class="space-y-1 pl-2">
              <router-link to="/igbh/tests" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.igbh_tests') }}</span>
              </router-link>
              <router-link to="/igbh/evaluations" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.igbh_eval') }}</span>
              </router-link>
              <router-link to="/igbh/weekly" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.igbh_weekly') }}</span>
              </router-link>
              <router-link to="/igbh/summative/evaluations" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.igbh_summative') }}</span>
              </router-link>
            </div>
          </div>

          <!-- Group: System (Admin only) -->
          <div v-if="userRole === 'admin'" class="border-t border-brand-border/50 pt-3">
            <button @click="toggleGroup('system')" :class="['w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold uppercase tracking-wider transition rounded-xl mb-1', expandedGroup === 'system' ? 'text-indigo-500 bg-indigo-500/10' : 'text-brand-desc hover:text-brand-text hover:bg-brand-input/50']">
              <div class="flex items-center gap-2.5">
                <span class="text-base">⚙️</span>
                <span>{{ $t('sidebar.group_system') }}</span>
              </div>
              <svg :class="{'rotate-180': expandedGroup === 'system'}" class="w-4 h-4 transition-transform duration-300 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div v-show="expandedGroup === 'system'" class="space-y-1 pl-2">
              <router-link to="/system/users" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition hover:bg-brand-input hover:text-brand-text text-brand-desc text-sm" active-class="bg-indigo-600/10 text-indigo-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-current opacity-40"></span>
                <span>{{ $t('sidebar.users') }}</span>
              </router-link>
            </div>
          </div>
        </nav>
      </div>

      <!-- User Profile / Logout -->
      <div class="border-t border-brand-border pt-4">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-9 w-9 rounded-full bg-indigo-500/10 flex items-center justify-center font-bold text-indigo-500 border border-indigo-500/20">
            {{ (userName || 'A').charAt(0).toUpperCase() }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold truncate">{{ userName || 'User' }}</p>
            <p class="text-xs text-brand-desc truncate">{{ userRoleLabel }}</p>
          </div>
        </div>
        <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-red-500/20 text-red-500 hover:bg-red-500/10 transition text-sm font-medium">
          {{ $t('header.logout') }}
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 pt-16 md:pt-0">
      <!-- Desktop header -->
      <header v-if="isAuthenticated" class="hidden md:flex h-16 border-b border-brand-border bg-brand-card/50 backdrop-blur-md items-center justify-between px-8 sticky top-0 z-40">
        <div class="text-sm text-brand-desc">{{ $t('header.welcome') }}</div>
        
        <div class="flex items-center gap-6">
          <!-- Language Switcher Desktop -->
          <div class="flex bg-brand-input border border-brand-border rounded-xl p-1">
            <button @click="setLanguage('vi')" :class="['px-3 py-1 rounded-lg text-xs font-semibold transition', currentLocale === 'vi' ? 'bg-indigo-600 text-white shadow-md' : 'text-brand-desc hover:text-brand-text']">VI</button>
            <button @click="setLanguage('en')" :class="['px-3 py-1 rounded-lg text-xs font-semibold transition', currentLocale === 'en' ? 'bg-indigo-600 text-white shadow-md' : 'text-brand-desc hover:text-brand-text']">EN</button>
          </div>

          <!-- Theme Toggle -->
          <button @click="toggleTheme" class="p-2 rounded-xl bg-brand-input hover:bg-brand-border transition text-brand-desc hover:text-brand-text flex items-center justify-center">
            <span v-if="theme === 'dark'">☀️ {{ $t('header.light_mode') }}</span>
            <span v-else>🌙 {{ $t('header.dark_mode') }}</span>
          </button>
          
          <!-- <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs text-green-500 font-medium uppercase tracking-wider">{{ $t('header.system_live') }}</span>
          </div> -->
        </div>
      </header>

      <!-- Router View Wrapper -->
      <div class="flex-1 p-4 md:p-8 overflow-y-auto">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isAuthenticated: false,
      isMobileMenuOpen: false,
      theme: localStorage.getItem('theme') || 'dark',
      currentLocale: localStorage.getItem('locale') || 'vi',
      expandedGroup: 'center',
      userRole: localStorage.getItem('user_role') || 'admin',
      userName: localStorage.getItem('user_name') || 'Admin',
    }
  },
  computed: {
    userRoleLabel() {
      const labels = { admin: 'Admin', team_leader: 'Team Leader', teacher: 'Teacher' };
      return labels[this.userRole] || this.userRole;
    }
  },
  watch: {
    $route() {
      this.checkAuth();
      this.updateExpandedGroup();
    },
    theme: {
      immediate: true,
      handler(newTheme) {
        if (newTheme === 'light') {
          document.documentElement.classList.add('light');
          document.documentElement.classList.remove('dark');
        } else {
          document.documentElement.classList.add('dark');
          document.documentElement.classList.remove('light');
        }
      }
    }
  },
  created() {
    this.checkAuth();
    this.updateExpandedGroup();
  },
  methods: {
    updateExpandedGroup() {
      const path = this.$route.path;
      if (path.includes('/system')) {
        this.expandedGroup = 'system';
      } else if (path.includes('/ucrea')) {
        this.expandedGroup = 'ucrea';
      } else if (path.includes('/igbh')) {
        this.expandedGroup = 'igbh';
      } else if (path !== '/' && path !== '/login') {
        this.expandedGroup = 'center';
      }
    },
    toggleGroup(group) {
      if (this.expandedGroup === group) {
        this.expandedGroup = null;
      } else {
        this.expandedGroup = group;
      }
    },
    checkAuth() {
      this.isAuthenticated = !!localStorage.getItem('token');
      this.userRole = localStorage.getItem('user_role') || 'admin';
      this.userName = localStorage.getItem('user_name') || 'Admin';
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('user_role');
      localStorage.removeItem('user_name');
      localStorage.removeItem('user_branch_id');
      localStorage.removeItem('user_teacher_id');
      this.$router.push('/login');
    },
    toggleTheme() {
      this.theme = this.theme === 'dark' ? 'light' : 'dark';
      localStorage.setItem('theme', this.theme);
    },
    setLanguage(lang) {
      this.currentLocale = lang;
      this.$i18n.locale = lang;
      localStorage.setItem('locale', lang);
    },
    toggleLanguage() {
      const nextLang = this.currentLocale === 'vi' ? 'en' : 'vi';
      this.setLanguage(nextLang);
    }
  }
}
</script>
