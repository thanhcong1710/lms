<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">UCREA Assessment Management</h2>
        <p class="text-sm text-brand-desc">Manage student evaluations and test results</p>
      </div>

      <!-- Tabs -->
      <div class="flex bg-brand-card border border-brand-border p-1 rounded-xl">
        <button 
          @click="changeTab('pending')" 
          :class="[
            activeTab === 'pending' ? 'bg-indigo-600 text-white' : 'text-brand-desc hover:text-brand-text',
            'px-4 py-2 rounded-lg text-sm font-semibold transition'
          ]"
        >
          Pending Evaluation
        </button>
        <button 
          @click="changeTab('completed')" 
          :class="[
            activeTab === 'completed' ? 'bg-emerald-500 text-white' : 'text-brand-desc hover:text-brand-text',
            'px-4 py-2 rounded-lg text-sm font-semibold transition'
          ]"
        >
          Completed Results
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="relative w-full md:w-80">
        <input 
          type="text" 
          v-model="search" 
          @input="fetchData"
          placeholder="Search by student name or teacher..." 
          class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-brand-desc/60 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
        <span class="absolute right-3 top-3 text-brand-desc/60">🔍</span>
      </div>
      
      <div class="text-xs text-brand-desc font-medium">
        Showing {{ results.length }} records
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Loading UCREA data...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">No.</th>
            <th class="px-6 py-4">Test Name</th>
            <th class="px-6 py-4">Level</th>
            <th class="px-6 py-4">Student</th>
            <th class="px-6 py-4">Teacher</th>
            <th class="px-6 py-4 text-center">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(item, index) in results" :key="item.id" class="hover:bg-brand-card/40 transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-semibold text-brand-text">{{ item.test_nm || item.test?.test_nm }}</td>
            <td class="px-6 py-4">{{ item.level_cd }}</td>
            <td class="px-6 py-4 font-medium text-indigo-400">{{ item.stu_nm }}</td>
            <td class="px-6 py-4">{{ item.memb_nm }}</td>
            <td class="px-6 py-4 text-center">
              <span v-if="activeTab === 'pending'" class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                Needs Grading
              </span>
              <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                Graded
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-3">
              <!-- Action Button based on status -->
              <router-link 
                v-if="activeTab === 'pending'"
                :to="{ name: 'ucrea-eval-form', params: { id: item.id } }"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition shadow-lg shadow-indigo-600/30"
              >
                Grade Test
              </router-link>
              <router-link 
                v-else
                :to="{ name: 'ucrea-eval-result', params: { id: item.id } }"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs transition shadow-lg shadow-emerald-600/30"
              >
                View Result
              </router-link>
            </td>
          </tr>
          <tr v-if="results.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-brand-desc">No tests found in this category.</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="flex items-center justify-between mt-4">
       <!-- Simplified pagination for now, usually would use <Pagination /> component -->
       <div class="text-sm text-brand-desc">
         Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
       </div>
       <div class="flex space-x-2">
         <button @click="onPageChange(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 rounded-md bg-brand-input border border-brand-border disabled:opacity-50 text-sm">Prev</button>
         <button @click="onPageChange(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 rounded-md bg-brand-input border border-brand-border disabled:opacity-50 text-sm">Next</button>
       </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UcreaEvalList',
  data() {
    return {
      results: [],
      loading: false,
      activeTab: 'completed', // Can default to pending or completed
      search: '',
      pagination: {
        current_page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0
      }
    }
  },
  created() {
    this.fetchData();
  },
  methods: {
    async fetchData(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get('/api/ucrea/results', {
          params: {
            status: this.activeTab,
            search: this.search,
            page: page,
            per_page: this.pagination.per_page
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data.status === 'success') {
          this.results = response.data.data;
          this.pagination = response.data.pagination;
        }
      } catch (error) {
        console.error("Error fetching UCREA results", error);
      } finally {
        this.loading = false;
      }
    },
    onPageChange(page) {
      if(page > 0 && page <= this.pagination.last_page) {
        this.fetchData(page);
      }
    },
    changeTab(tab) {
      this.activeTab = tab;
      this.fetchData(1);
    }
  }
}
</script>
