<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Test Management (Quản lý bài kiểm tra)</h2>
        <p class="text-sm text-brand-desc">View, search, and preview academic tests and placement sheets</p>
      </div>

      <!-- Tabs -->
      <div class="flex bg-brand-card border border-brand-border p-1 rounded-xl">
        <button 
          @click="changeTab('UCREA')" 
          :class="[
            activeTab === 'UCREA' ? 'bg-indigo-600 text-white' : 'text-brand-desc hover:text-brand-text',
            'px-4 py-2 rounded-lg text-sm font-semibold transition'
          ]"
        >
          U-Crea Tests
        </button>
        <button 
          @click="changeTab('IG.BH')" 
          :class="[
            activeTab === 'IG.BH' ? 'bg-indigo-600 text-white' : 'text-brand-desc hover:text-brand-text',
            'px-4 py-2 rounded-lg text-sm font-semibold transition'
          ]"
        >
          IG.BH Tests
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="relative w-full md:w-80">
        <input 
          type="text" 
          v-model="search" 
          @input="fetchTests"
          placeholder="Search by test name, level, code..." 
          class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-brand-desc/60 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
        <span class="absolute right-3 top-3 text-brand-desc/60">🔍</span>
      </div>
      
      <div class="text-xs text-brand-desc font-medium">
        Showing {{ tests.length }} records
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Loading tests...</p>
    </div>

    <!-- Tests Table -->
    <div v-else class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">STT</th>
            <th class="px-6 py-4">Test Name</th>
            <th class="px-6 py-4">Grade / Level</th>
            <th class="px-6 py-4 text-center">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(test, index) in tests" :key="test.id" class="hover:bg-brand-card/40 transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-semibold text-brand-text">{{ test.name }}</td>
            <td class="px-6 py-4">{{ test.level_cd || 'N/A' }}</td>
            <td class="px-6 py-4 text-center">
              <span v-if="test.status == 1" class="inline-flex items-center justify-center p-1.5 rounded-full bg-emerald-100 text-emerald-600" title="Active">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
              </span>
              <span v-else class="inline-flex items-center justify-center p-1.5 rounded-full bg-rose-100 text-rose-600" title="Inactive">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-3">
              <a 
                v-if="test.local_pdf_path" 
                :href="`/${test.local_pdf_path}`" 
                target="_blank"
                title="Preview File"
                class="inline-flex items-center justify-center p-2 rounded-lg bg-indigo-600/10 text-indigo-500 hover:bg-indigo-600 hover:text-white transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </a>
              <a 
                v-if="test.local_pdf_path"
                :href="`/${test.local_pdf_path}`" 
                download
                title="Download File"
                class="inline-flex items-center justify-center p-2 rounded-lg border border-brand-border text-brand-desc hover:text-brand-text hover:bg-brand-input transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
              </a>
              <a 
                v-else
                :href="test.pdf_url" 
                target="_blank" 
                class="px-3.5 py-1.5 rounded-lg border border-brand-border text-indigo-500 hover:underline transition font-medium text-xs inline-block"
              >
                LMS Link
              </a>
            </td>
          </tr>
          <tr v-if="tests.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-brand-desc">No test sheets found matching criteria.</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <Pagination 
      v-if="pagination.total > 0"
      :pagination="pagination"
      @page-change="onPageChange"
      @per-page-change="onPerPageChange"
    />

    <!-- PDF/Excel View Modal -->
    <div v-if="showModal && activeTest" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-brand-card border border-brand-border rounded-2xl w-full max-w-4xl p-6 shadow-2xl space-y-4 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-brand-border pb-4">
          <div>
            <h3 class="text-lg font-bold text-brand-text">{{ activeTest.name }}</h3>
            <p class="text-xs text-brand-desc">Grade: {{ activeTest.level_cd }} (Code: {{ activeTest.test_cd }})</p>
          </div>
          <button @click="closePdf" class="p-1.5 rounded-lg bg-brand-input hover:bg-brand-border transition text-brand-desc hover:text-brand-text">
            ✕ Close
          </button>
        </div>

        <!-- Render PDF in iframe -->
        <div class="flex-1 min-h-[50vh] flex items-center justify-center bg-brand-input rounded-xl overflow-hidden relative">
          <iframe 
            v-if="isPdf(activeTest.local_pdf_path)"
            :src="activeTest.local_pdf_path" 
            class="w-full h-full border-0"
          ></iframe>
          <div v-else class="text-center p-8 space-y-4">
            <div class="text-5xl">📊</div>
            <p class="text-brand-text font-semibold">Spreadsheet File Preview</p>
            <p class="text-sm text-brand-desc">This test contains tabular spreadsheet data (.xlsx) and cannot be directly viewed in the browser.</p>
            <a 
              :href="activeTest.local_pdf_path" 
              download
              class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition inline-block"
            >
              Download Excel Spreadsheet
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      tests: [],
      loading: false,
      activeTab: 'UCREA',
      search: '',
      showModal: false,
      activeTest: null,
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
    this.fetchTests();
  },
  methods: {
    async fetchTests(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get('/api/tests', {
          params: {
            type: this.activeTab,
            search: this.search,
            page: page,
            per_page: this.pagination.per_page
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data.status === 'success') {
          this.tests = response.data.data;
          this.pagination = response.data.pagination;
        }
      } catch (error) {
        console.error("Error fetching tests", error);
      } finally {
        this.loading = false;
      }
    },
    onPageChange(page) {
      this.fetchTests(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchTests(1);
    },
    changeTab(tab) {
      this.activeTab = tab;
      this.fetchTests(1);
    },
    getFilename(path) {
      if (!path) return '';
      return path.substring(path.lastIndexOf('/') + 1);
    },
    isPdf(path) {
      if (!path) return false;
      return path.toLowerCase().endsWith('.pdf');
    },
    openPdf(test) {
      this.activeTest = test;
      this.showModal = true;
    },
    closePdf() {
      this.showModal = false;
      this.activeTest = null;
    }
  }
}
</script>
