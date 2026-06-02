<template>
  <div class="min-h-screen flex text-slate-800 font-sans selection:bg-blue-500/30 bg-white">
    <!-- Left: Branding & Graphic (hidden on small screens) -->
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-50 via-white to-sky-100 items-center justify-center relative overflow-hidden">
      <!-- LogicLab-esque abstract shapes -->
      <div class="absolute top-0 right-0 w-full h-full opacity-30 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMiIgZmlsbD0iIzNiODJmNiIvPjwvc3ZnPg==')]"></div>
      <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-400 blur-3xl opacity-20 mix-blend-multiply animate-pulse"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-teal-400 blur-3xl opacity-20 mix-blend-multiply animate-pulse" style="animation-delay: 2s;"></div>
      
      <!-- A large soft polygon or swoosh -->
      <svg class="absolute w-full h-full opacity-5 text-blue-600" viewBox="0 0 100 100" preserveAspectRatio="none">
         <path d="M0,100 C30,70 70,100 100,50 L100,0 L0,0 Z" fill="currentColor"></path>
      </svg>

      <div class="relative z-10 p-16 max-w-2xl text-slate-800">
        <div class="h-20 w-20 rounded-3xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-2xl mb-8 text-3xl shadow-blue-500/30">
          LMS
        </div>
        <h1 class="text-5xl font-black mb-6 leading-tight tracking-tight text-slate-900">
          Khơi nguồn <br>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-teal-500">Đam mê Toán học</span>
        </h1>
        <p class="text-slate-600 text-lg leading-relaxed mb-12 max-w-lg font-medium">
          Hệ thống quản lý trung tâm toàn diện. Tối ưu hoá quy trình giảng dạy, quản lý học sinh và chương trình học trong một nền tảng duy nhất.
        </p>
        
        <div class="grid grid-cols-2 gap-6 border-t border-slate-200 pt-8">
          <div>
            <div class="text-3xl font-black text-blue-600 mb-1">LogicLab</div>
            <div class="text-slate-500 text-sm font-medium">Phát triển tư duy toàn diện</div>
          </div>
          <div>
            <div class="text-3xl font-black text-blue-600 mb-1">STEM</div>
            <div class="text-slate-500 text-sm font-medium">Phương pháp giáo dục hiện đại</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white relative">
      <!-- Decorative circle -->
      <div class="absolute top-1/4 -right-20 w-72 h-72 rounded-full bg-blue-50 blur-3xl"></div>
      
      <div class="w-full max-w-md relative z-10">
        <!-- Logo for mobile only -->
        <div class="lg:hidden flex justify-center mb-8">
           <div class="h-16 w-16 rounded-3xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-xl text-2xl shadow-blue-500/30">
            LL
          </div>
        </div>

        <div class="text-center lg:text-left mb-10">
          <h2 class="text-3xl font-black text-slate-800 mb-3 tracking-tight">Đăng nhập</h2>
          <p class="text-slate-500 font-medium">Vui lòng đăng nhập để truy cập hệ thống.</p>
        </div>

        <form @submit.prevent="login" class="space-y-6">
          <div class="group">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 group-focus-within:text-blue-600 transition-colors">Tài khoản</label>
            <div class="relative">
              <span class="absolute left-4 top-3.5 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              </span>
              <input type="text" v-model="username" required placeholder="Nhập tài khoản..." class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border-2 border-transparent focus:border-blue-500 focus:bg-white text-slate-800 placeholder-slate-400 outline-none transition-all duration-200 shadow-sm font-medium">
            </div>
          </div>

          <div class="group">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 group-focus-within:text-blue-600 transition-colors">Mật khẩu</label>
            <div class="relative">
              <span class="absolute left-4 top-3.5 text-slate-400 group-focus-within:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              </span>
              <input type="password" v-model="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border-2 border-transparent focus:border-blue-500 focus:bg-white text-slate-800 placeholder-slate-400 outline-none transition-all duration-200 shadow-sm font-medium">
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 bg-white w-4 h-4 cursor-pointer">
              <span class="text-slate-600 hover:text-slate-900 transition-colors font-medium">Ghi nhớ đăng nhập</span>
            </label>
            <a href="#" class="text-blue-600 hover:text-blue-500 font-semibold transition-colors">Quên mật khẩu?</a>
          </div>

          <div v-if="error" class="text-sm text-red-600 bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3 font-medium">
             <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             <span>{{ error }}</span>
          </div>

          <button type="submit" class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm tracking-wide transition-all duration-200 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transform">
            Đăng nhập
          </button>
        </form>
        
        <p class="mt-10 text-center text-xs text-slate-400 font-medium">
          &copy; {{ new Date().getFullYear() }} LogicLab EDU. All rights reserved.
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
        if (response.data.user) {
          localStorage.setItem('user_role', response.data.user.role || 'admin');
          localStorage.setItem('user_name', response.data.user.name || 'Admin');
          localStorage.setItem('user_branch_id', response.data.user.branch_id || '');
          localStorage.setItem('user_teacher_id', response.data.user.teacher_id || '');
        }
        this.$router.push('/');
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed. Please check your credentials.';
      }
    }
  }
}
</script>
