<template>
  <div class="space-y-8">
    <div>
      <h2 class="text-2xl font-bold text-white">Dashboard Overview</h2>
      <p class="text-sm text-gray-400">Quick statistics and operational status of LMS Portal</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-[#0d1527]/40 backdrop-blur border border-gray-800 p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-gray-400">Total Branches</span>
        <span class="text-3xl font-bold text-white mt-2">12</span>
        <span class="text-xs text-green-400 mt-2 font-medium">↑ Active & Operational</span>
      </div>

      <div class="bg-[#0d1527]/40 backdrop-blur border border-gray-800 p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-gray-400">Total Teachers</span>
        <span class="text-3xl font-bold text-white mt-2">48</span>
        <span class="text-xs text-indigo-400 mt-2 font-medium">Licensed Instructors</span>
      </div>

      <div class="bg-[#0d1527]/40 backdrop-blur border border-gray-800 p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-gray-400">Active Classes</span>
        <span class="text-3xl font-bold text-white mt-2">156</span>
        <span class="text-xs text-green-400 mt-2 font-medium">U-Crea & i-Garten</span>
      </div>

      <div class="bg-[#0d1527]/40 backdrop-blur border border-gray-800 p-6 rounded-2xl flex flex-col justify-between shadow-lg shadow-indigo-500/5">
        <span class="text-sm font-medium text-gray-400">Enrolled Students</span>
        <span class="text-3xl font-bold text-white mt-2">1,240</span>
        <span class="text-xs text-indigo-400 mt-2 font-medium">Synchronized with CRM</span>
      </div>
    </div>

    <!-- Active Lms programs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="bg-[#0d1527]/30 border border-gray-800 p-6 rounded-2xl">
        <h3 class="text-lg font-bold text-white mb-4">U-Crea Program</h3>
        <p class="text-sm text-gray-400 mb-4">Visual math & creative thinking classes evaluating cognitive capabilities.</p>
        <router-link to="/classes" class="text-sm text-indigo-400 font-semibold hover:underline">Manage U-Crea Classes →</router-link>
      </div>

      <div class="bg-[#0d1527]/30 border border-gray-800 p-6 rounded-2xl">
        <h3 class="text-lg font-bold text-white mb-4">i-Garten Program</h3>
        <p class="text-sm text-gray-400 mb-4">Kindergarten and early childhood development tracking weekly and final achievements.</p>
        <router-link to="/classes" class="text-sm text-indigo-400 font-semibold hover:underline">Manage i-Garten Classes →</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {}
</script>
