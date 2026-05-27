<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-brand-text">Dashboard Overview</h2>
      <p class="text-sm text-brand-desc">Quick statistics and operational status of LMS Portal</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">Total Branches</span>
        <span class="text-3xl font-bold text-brand-text mt-2">12</span>
        <span class="text-xs text-green-500 mt-2 font-medium">↑ Active & Operational</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">Total Teachers</span>
        <span class="text-3xl font-bold text-brand-text mt-2">48</span>
        <span class="text-xs text-indigo-500 mt-2 font-medium">Licensed Instructors</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">Active Classes</span>
        <span class="text-3xl font-bold text-brand-text mt-2">156</span>
        <span class="text-xs text-green-500 mt-2 font-medium">U-Crea & i-Garten</span>
      </div>

      <div class="bg-brand-card/60 backdrop-blur border border-brand-border p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-brand-desc">Enrolled Students</span>
        <span class="text-3xl font-bold text-brand-text mt-2">1,240</span>
        <span class="text-xs text-indigo-500 mt-2 font-medium">Synchronized with CRM</span>
      </div>
    </div>

    <!-- Active Lms programs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-brand-card/40 border border-brand-border p-6 rounded-2xl">
        <h3 class="text-lg font-bold text-brand-text mb-4">U-Crea Program</h3>
        <p class="text-sm text-brand-desc mb-4">Visual math & creative thinking classes evaluating cognitive capabilities.</p>
        <router-link to="/classes" class="text-sm text-indigo-500 font-semibold hover:underline">Manage U-Crea Classes →</router-link>
      </div>

      <div class="bg-brand-card/40 border border-brand-border p-6 rounded-2xl">
        <h3 class="text-lg font-bold text-brand-text mb-4">i-Garten Program</h3>
        <p class="text-sm text-brand-desc mb-4">Kindergarten and early childhood development tracking weekly and final achievements.</p>
        <router-link to="/classes" class="text-sm text-indigo-500 font-semibold hover:underline">Manage i-Garten Classes →</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {}
</script>
