<template>
  <div class="min-h-screen bg-brand-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-brand-card/50 backdrop-blur-lg border border-brand-border p-8 rounded-2xl shadow-xl shadow-indigo-500/5">
      <div class="text-center mb-8">
        <div class="h-12 w-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-brand-text shadow-lg mx-auto mb-4 text-xl">
          LMS
        </div>
        <h2 class="text-2xl font-bold bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">CMS EDU LMS Portal</h2>
        <p class="text-sm text-brand-desc mt-2">Sign in to manage centers, teachers, classes & students</p>
      </div>

      <form @submit.prevent="login" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-brand-desc uppercase tracking-wider mb-2">Username / Email</label>
          <input type="text" v-model="username" required placeholder="admin" class="w-full px-4 py-3 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm">
        </div>

        <div>
          <label class="block text-xs font-semibold text-brand-desc uppercase tracking-wider mb-2">Password</label>
          <input type="password" v-model="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm">
        </div>

        <div v-if="error" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 p-3 rounded-xl">
          {{ error }}
        </div>

        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text font-semibold transition duration-150 text-sm shadow-lg shadow-indigo-600/20">
          Sign In
        </button>
      </form>
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
