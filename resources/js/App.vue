<template>
  <div class="min-h-screen bg-[#070b13] text-gray-100 flex font-sans">
    <!-- Sidebar -->
    <aside v-if="isAuthenticated" class="w-64 bg-[#0d1527]/80 backdrop-blur-md border-r border-gray-800 flex flex-col justify-between p-4">
      <div>
        <!-- Logo -->
        <div class="flex items-center gap-3 px-2 py-4 mb-6">
          <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">
            LMS
          </div>
          <div>
            <h1 class="font-bold text-lg leading-none bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">CMS EDU</h1>
            <span class="text-xs text-indigo-400 font-medium">LMS PORTAL CLONE</span>
          </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5">
          <router-link to="/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Dashboard</span>
          </router-link>
          <router-link to="/branches" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Branches</span>
          </router-link>
          <router-link to="/teachers" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Teachers</span>
          </router-link>
          <router-link to="/classes" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Classes</span>
          </router-link>
          <router-link to="/students" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Students</span>
          </router-link>
          <router-link to="/contracts" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-150 hover:bg-gray-800/50 hover:text-white" active-class="bg-indigo-600/20 text-indigo-400 border-l-4 border-indigo-600 font-medium">
            <span>Contracts</span>
          </router-link>
        </nav>
      </div>

      <!-- User Profile / Logout -->
      <div class="border-t border-gray-800 pt-4">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-indigo-500/20 flex items-center justify-center font-bold text-indigo-400 border border-indigo-500/30">
              U
            </div>
            <div>
              <p class="text-sm font-semibold text-white">Administrator</p>
              <p class="text-xs text-gray-400">admin@example.com</p>
            </div>
          </div>
        </div>
        <button @click="logout" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 transition duration-150 text-sm font-medium">
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0">
      <!-- Top header -->
      <header v-if="isAuthenticated" class="h-16 border-b border-gray-800 bg-[#070b13]/50 backdrop-blur-md flex items-center justify-between px-8 sticky top-0 z-40">
        <div class="text-sm text-gray-400">Welcome to CMS EDU LMS Management Portal</div>
        <div class="flex items-center gap-4">
          <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
          <span class="text-xs text-green-400 font-medium uppercase tracking-wider">System Live</span>
        </div>
      </header>

      <!-- Router View Wrapper -->
      <div class="flex-1 p-8 overflow-y-auto">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isAuthenticated: false
    }
  },
  watch: {
    $route() {
      this.checkAuth();
    }
  },
  created() {
    this.checkAuth();
  },
  methods: {
    checkAuth() {
      this.isAuthenticated = !!localStorage.getItem('token');
    },
    logout() {
      localStorage.removeItem('token');
      this.$router.push('/login');
    }
  }
}
</script>
