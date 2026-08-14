<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-brand-text">{{ $t('dashboard.title') }}</h2>
      <p class="text-sm text-brand-desc">{{ $t('dashboard.desc') }}</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">{{ $t('dashboard.total_branches') }}</span>
        <span class="text-3xl font-bold text-brand-text mt-2">{{ stats.total_branches.toLocaleString() }}</span>
        <span class="text-xs text-green-500 mt-2 font-medium">{{ $t('dashboard.active_operational') }}</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">{{ $t('dashboard.total_teachers') }}</span>
        <span class="text-3xl font-bold text-brand-text mt-2">{{ stats.total_teachers.toLocaleString() }}</span>
        <span class="text-xs text-indigo-500 mt-2 font-medium">{{ $t('dashboard.licensed_instructors') }}</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">{{ $t('dashboard.active_classes') }}</span>
        <span class="text-3xl font-bold text-brand-text mt-2">{{ stats.active_classes.toLocaleString() }}</span>
        <span class="text-xs text-green-500 mt-2 font-medium">{{ $t('dashboard.ucrea_igbh') }}</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">{{ $t('dashboard.enrolled_students') }}</span>
        <span class="text-3xl font-bold text-brand-text mt-2">{{ stats.enrolled_students.toLocaleString() }}</span>
        <span class="text-xs text-indigo-500 mt-2 font-medium">{{ $t('dashboard.synced_crm') }}</span>
      </div>
    </div>

    <!-- Active Lms programs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="bg-brand-card/40 border border-brand-border p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-brand-text">Chương trình U-Crea</h3>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
            {{ stats.uc_classes }} Lớp
          </span>
        </div>
        <p class="text-sm text-brand-desc mb-4">Lớp học toán tư duy trực quan và tư duy sáng tạo đánh giá năng lực nhận thức.</p>
        <router-link to="/classes?cls_type_group=ucrea" class="text-sm text-indigo-500 font-semibold hover:underline">Quản lý Lớp U-Crea &rarr;</router-link>
      </div>

      <div class="bg-brand-card/40 border border-brand-border p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-brand-text">Chương trình i-Garten</h3>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            {{ stats.ig_classes }} Lớp
          </span>
        </div>
        <p class="text-sm text-brand-desc mb-4">Lớp học phát triển mầm non, đánh giá năng lực tư duy.</p>
        <router-link to="/classes?cls_type_group=igaten" class="text-sm text-indigo-500 font-semibold hover:underline">Quản lý Lớp i-Garten &rarr;</router-link>
      </div>

      <div class="bg-brand-card/40 border border-brand-border p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-brand-text">Chương trình Black Hole</h3>
          <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">
            {{ stats.bh_classes }} Lớp
          </span>
        </div>
        <p class="text-sm text-brand-desc mb-4">Chương trình tư duy nâng cao dành cho học sinh lứa tuổi tiểu học.</p>
        <router-link to="/classes?cls_type_group=black_hold" class="text-sm text-indigo-500 font-semibold hover:underline">Quản lý Lớp Black Hole &rarr;</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      stats: {
        total_branches: 0,
        total_teachers: 0,
        active_classes: 0,
        enrolled_students: 0,
        uc_classes: 0,
        ig_classes: 0,
        bh_classes: 0,
      }
    }
  },
  created() {
    this.fetchStats();
  },
  methods: {
    async fetchStats() {
      try {
        const response = await axios.get('/api/dashboard/stats', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (response.data) {
          this.stats = response.data;
        }
      } catch (error) {
        console.error("Error fetching dashboard stats", error);
      }
    }
  }
}
</script>
