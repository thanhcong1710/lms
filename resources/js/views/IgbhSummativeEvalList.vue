<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('igbh.summative_title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('igbh.summative_desc') }}</p>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="relative w-full md:w-80">
        <input 
          type="text" 
          v-model="search" 
          @input="fetchData"
          :placeholder="$t('igbh.search_student')" 
          class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-brand-desc/60 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
        <span class="absolute right-3 top-3 text-brand-desc/60">🔍</span>
      </div>
      
      <div class="text-xs text-brand-desc font-medium">
        Đang hiển thị {{ results.length }} bản ghi
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">{{ $t('igbh.loading') }}</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.test_name') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.level') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.class') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.teacher') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.modal.student') }}</th>
            <th class="px-6 py-4 text-center">{{ $t('igbh.cols.total_score') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.test_date') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.created_at') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.updated_at') }}</th>
            <th class="px-6 py-4 text-right sticky right-0 bg-brand-header z-10 border-l border-brand-border shadow-[-4px_0_10px_rgba(0,0,0,0.1)]">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(item, index) in results" :key="item.id" class="group hover:bg-brand-card transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-semibold text-brand-text">{{ item.test_nm }}</td>
            <td class="px-6 py-4">{{ item.level_cd || '-' }}</td>
            <td class="px-6 py-4 font-medium text-indigo-400">{{ item.class_nm }}</td>
            <td class="px-6 py-4">{{ item.teacher_nm || '-' }}</td>
            <td class="px-6 py-4 font-bold text-emerald-400">{{ item.stu_nm }}</td>
            <td class="px-6 py-4 text-center font-bold text-indigo-400">{{ item.total_score || 0 }}</td>
            <td class="px-6 py-4 text-brand-desc">{{ item.eval_dt ? item.eval_dt.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-xs text-brand-desc">{{ item.created_at ? item.created_at.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-xs text-brand-desc">{{ item.updated_at ? item.updated_at.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-right sticky right-0 bg-brand-bg z-10 border-l border-brand-border shadow-[-4px_0_10px_rgba(0,0,0,0.1)] group-hover:bg-brand-card transition-colors">
              <div class="flex justify-end items-center gap-2">
                <router-link 
                  :to="{ name: 'igbh-summative-eval-report', params: { id: item.id } }"
                  :title="$t('igbh.actions.view_report')"
                  class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-lg shadow-emerald-600/30"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </router-link>
              </div>
            </td>
          </tr>
          <tr v-if="results.length === 0">
            <td colspan="11" class="px-6 py-12 text-center text-brand-desc">{{ $t('igbh.no_data') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="flex items-center justify-between mt-4">
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
  name: 'IgbhSummativeEvalList',
  data() {
    return {
      results: [],
      loading: false,
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
        const response = await axios.get('/api/igbh/summative/results', {
          params: {
            search: this.search,
            page: page,
            per_page: this.pagination.per_page
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (response.data.data) {
            this.results = response.data.data;
            this.pagination = {
                current_page: response.data.current_page,
                per_page: response.data.per_page,
                total: response.data.total,
                last_page: response.data.last_page,
                from: response.data.from,
                to: response.data.to
            };
        } else {
            this.results = response.data;
        }
      } catch (error) {
        console.error("Error fetching IG.BH summative results", error);
      } finally {
        this.loading = false;
      }
    },
    onPageChange(page) {
      if(page > 0 && page <= this.pagination.last_page) {
        this.fetchData(page);
      }
    }
  }
}
</script>
