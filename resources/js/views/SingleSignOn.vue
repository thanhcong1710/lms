<template>
  <div class="min-h-screen flex items-center justify-center bg-brand-bg text-brand-text">
    <div class="text-center p-8 rounded-2xl bg-brand-card/50 border border-brand-border shadow-xl">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500 mb-4"></div>
      <h3 class="text-xl font-bold text-slate-800 mb-2">Hệ thống LMS</h3>
      <p class="text-slate-500 font-medium">Đang xử lý đăng nhập hệ thống...</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SingleSignOn',
  async mounted() {
    const hrm_id = this.$route.params.hrm_id;
    const token = this.$route.params.token;

    try {
      const response = await axios.post('/api/single-sign-on', {
        hrm_id: hrm_id,
        token: token
      });

      if (response.data.token) {
        localStorage.setItem('token', response.data.token);
        if (response.data.user) {
          localStorage.setItem('user_role', response.data.user.role || 'admin');
          localStorage.setItem('user_name', response.data.user.name || 'Admin');
          localStorage.setItem('user_branch_id', response.data.user.branch_id || '');
          localStorage.setItem('user_teacher_id', response.data.user.teacher_id || '');
        }
        this.$router.push('/');
      } else {
        this.$router.push('/login');
      }
    } catch (error) {
      console.error('SSO Login Error:', error);
      this.$router.push('/login');
    }
  }
}
</script>
