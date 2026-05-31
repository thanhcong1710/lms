<template>
  <div class="min-h-screen flex text-brand-text font-sans selection:bg-indigo-500/30">
    <!-- Left: Branding & Graphic (hidden on small screens) -->
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-950 items-center justify-center relative overflow-hidden">
      <!-- Decorative background elements -->
      <div class="absolute top-0 left-0 w-full h-full opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMiIgZmlsbD0iI2ZmZiIvPjwvc3ZnPg==')]"></div>
      <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-indigo-500 blur-3xl opacity-30 mix-blend-screen animate-pulse"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-purple-500 blur-3xl opacity-30 mix-blend-screen animate-pulse" style="animation-delay: 2s;"></div>

      <div class="relative z-10 p-16 max-w-2xl text-white">
        <div class="h-20 w-20 rounded-2xl bg-gradient-to-tr from-white to-white/70 flex items-center justify-center font-bold text-indigo-900 shadow-2xl mb-8 text-3xl">
          CMS
        </div>
        <h1 class="text-5xl font-extrabold mb-6 leading-tight tracking-tight">
          Empowering the <br>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-yellow-500">Next Generation</span>
        </h1>
        <p class="text-indigo-200 text-lg leading-relaxed mb-12 max-w-lg">
          LMS Portal for comprehensive center management. Seamlessly manage teachers, classes, students, and curriculum all in one place.
        </p>
        
        <div class="grid grid-cols-2 gap-6 border-t border-white/10 pt-8">
          <div>
            <div class="text-3xl font-bold text-yellow-400 mb-1">50+</div>
            <div class="text-indigo-200 text-sm">Active Branches</div>
          </div>
          <div>
            <div class="text-3xl font-bold text-yellow-400 mb-1">10k+</div>
            <div class="text-indigo-200 text-sm">Happy Students</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-brand-bg relative">
      <!-- Decorative circle for dark mode right side -->
      <div class="absolute top-1/4 -right-20 w-72 h-72 rounded-full bg-indigo-600/10 blur-3xl"></div>
      
      <div class="w-full max-w-md relative z-10">
        <!-- Logo for mobile only -->
        <div class="lg:hidden flex justify-center mb-8">
           <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-xl text-2xl">
            CMS
          </div>
        </div>

        <div class="text-center lg:text-left mb-10">
          <h2 class="text-3xl font-bold text-brand-text mb-3">Welcome Back</h2>
          <p class="text-brand-desc">Please sign in to access your dashboard.</p>
        </div>

        <form @submit.prevent="login" class="space-y-6">
          <div class="group">
            <label class="block text-xs font-bold text-brand-desc uppercase tracking-wider mb-2 group-focus-within:text-indigo-500 transition-colors">Username / Email</label>
            <div class="relative">
              <span class="absolute left-4 top-3.5 text-brand-desc/60 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              </span>
              <input type="text" v-model="username" required placeholder="admin" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-brand-input border-2 border-transparent focus:border-indigo-500 focus:bg-brand-card text-brand-text placeholder-brand-desc/40 outline-none transition-all duration-200 shadow-sm">
            </div>
          </div>

          <div class="group">
            <label class="block text-xs font-bold text-brand-desc uppercase tracking-wider mb-2 group-focus-within:text-indigo-500 transition-colors">Password</label>
            <div class="relative">
              <span class="absolute left-4 top-3.5 text-brand-desc/60 group-focus-within:text-indigo-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              </span>
              <input type="password" v-model="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-brand-input border-2 border-transparent focus:border-indigo-500 focus:bg-brand-card text-brand-text placeholder-brand-desc/40 outline-none transition-all duration-200 shadow-sm">
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" class="rounded border-brand-border text-indigo-600 focus:ring-indigo-500 bg-brand-input w-4 h-4 cursor-pointer">
              <span class="text-brand-desc hover:text-brand-text transition-colors">Remember me</span>
            </label>
            <a href="#" class="text-indigo-500 hover:text-indigo-400 font-semibold transition-colors">Forgot password?</a>
          </div>

          <div v-if="error" class="text-sm text-red-500 bg-red-500/10 border border-red-500/20 p-4 rounded-xl flex items-start gap-3">
             <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             <span>{{ error }}</span>
          </div>

          <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-sm tracking-wide transition-all duration-200 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transform">
            Sign In to Portal
          </button>
        </form>
        
        <p class="mt-10 text-center text-xs text-brand-desc/60">
          &copy; {{ new Date().getFullYear() }} CMS EDU. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      username: '',
      password: '',
      error: ''
    }
  },
  methods: {
    async login() {
      this.error = '';
      try {
        const response = await axios.post('/api/login', {
          username: this.username,
          password: this.password
        });
        localStorage.setItem('token', response.data.token);
        this.$router.push('/');
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed. Please check your credentials.';
      }
    }
  }
}
</script>
