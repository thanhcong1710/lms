<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">IG.BH Weekly Evaluation Management</h2>
        <p class="text-sm text-brand-desc">Manage class weekly evaluations and student grades</p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4 flex-wrap md:flex-nowrap">
        <button 
          @click="openCreateModal" 
          class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition text-sm flex items-center gap-1.5 shadow-lg shadow-indigo-600/30"
        >
          <span class="text-base font-bold">+</span> Thêm Mới Nhập Điểm Tuần
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
          placeholder="Search by class name or teacher..." 
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
      <p class="text-sm text-brand-desc">Loading IG.BH weekly data...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">No.</th>
            <th class="px-6 py-4">Test Name</th>
            <th class="px-6 py-4">Class</th>
            <th class="px-6 py-4">Week</th>
            <th class="px-6 py-4">Date</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(item, index) in results" :key="item.id" class="hover:bg-brand-card/40 transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-semibold text-brand-text">{{ item.evalNm }}</td>
            <td class="px-6 py-4 font-medium text-indigo-400">
              {{ item.classNm }}
            </td>
            <td class="px-6 py-4">{{ item.eachCdNm }}</td>
            <td class="px-6 py-4">
              {{ item.evalYmd }}
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end items-center gap-2">
                <router-link 
                  :to="{ name: 'igbh-weekly-eval-form', params: { id: item.id } }"
                  class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition shadow-lg shadow-indigo-600/30"
                >
                  Nhập điểm
                </router-link>
              </div>
            </td>
          </tr>
          <tr v-if="results.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-brand-desc">No tests found.</td>
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

    <!-- Create Evaluation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-brand-card border border-brand-border w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-brand-border bg-brand-header flex justify-between items-center">
          <h3 class="text-lg font-bold text-brand-text">Thêm Mới Nhập Điểm Tuần IG.BH</h3>
          <button @click="closeCreateModal" class="text-brand-desc hover:text-brand-text text-xl font-bold">&times;</button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
          <!-- Class Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">Lớp</label>
            <select v-model="form.class_seq" required class="w-full px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
              <option value="" disabled>-- Chọn lớp --</option>
              <option v-for="cls in initData.classes" :key="cls.class_seq" :value="cls.class_seq">{{ cls.class_nm }}</option>
            </select>
          </div>

          <!-- Test Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">Bài Kiểm Tra (Level)</label>
            <select v-model="form.test_seq" required class="w-full px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
              <option value="" disabled>-- Chọn bài kiểm tra --</option>
              <option v-for="t in initData.tests" :key="t.test_seq" :value="t.test_seq">{{ t.test_nm }}</option>
            </select>
          </div>

          <!-- Week Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">Tuần Đánh Giá</label>
            <select v-model="form.each_cd" required class="w-full px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
              <option value="" disabled>-- Chọn tuần --</option>
              <option v-for="w in initData.weeks" :key="w.each_cd" :value="w.each_cd">{{ w.each_cd_nm }}</option>
            </select>
          </div>

          <!-- Date Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">Ngày đánh giá</label>
            <input type="date" v-model="form.eval_ymd" required class="w-full px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
          </div>

          <!-- Modal Footer -->
          <div class="pt-4 border-t border-brand-border flex justify-end gap-3">
            <button type="button" @click="closeCreateModal" class="px-4 py-2 rounded-xl border border-brand-border text-brand-desc hover:bg-brand-input hover:text-brand-text transition text-sm font-semibold">
              Hủy
            </button>
            <button type="submit" :disabled="creating" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition text-sm shadow-lg shadow-indigo-600/30 disabled:opacity-50">
              {{ creating ? 'Đang tạo...' : 'Tạo Đánh Giá' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgbhWeeklyEvalList',
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
      },
      showModal: false,
      creating: false,
      initData: {
        classes: [],
        tests: [],
        weeks: []
      },
      form: {
        class_seq: '',
        test_seq: '',
        each_cd: '',
        eval_ymd: new Date().toISOString().substr(0, 10)
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
        const response = await axios.get('/api/igbh/weekly/results', {
          params: {
            search: this.search,
            page: page,
            per_page: this.pagination.per_page
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        // Laravel paginate response
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
        console.error("Error fetching IG.BH weekly results", error);
      } finally {
        this.loading = false;
      }
    },
    onPageChange(page) {
      if(page > 0 && page <= this.pagination.last_page) {
        this.fetchData(page);
      }
    },
    async openCreateModal() {
      this.showModal = true;
      try {
        const response = await axios.get('/api/igbh/weekly/init-data', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data) {
          this.initData = response.data;
        }
      } catch (error) {
        console.error("Error fetching initialization data", error);
        alert("Không thể tải dữ liệu khởi tạo. Vui lòng thử lại.");
      }
    },
    closeCreateModal() {
      this.showModal = false;
      this.form = {
        class_seq: '',
        test_seq: '',
        each_cd: '',
        eval_ymd: new Date().toISOString().substr(0, 10)
      };
    },
    async submitCreate() {
      this.creating = true;
      try {
        const response = await axios.post('/api/igbh/weekly/results', this.form, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data && response.data.id) {
          const newId = response.data.id;
          this.closeCreateModal();
          this.$router.push({ name: 'igbh-weekly-eval-form', params: { id: newId } });
        }
      } catch (error) {
        console.error("Error creating assessment", error);
        alert("Lỗi khi thêm mới đánh giá. Vui lòng thử lại.");
      } finally {
        this.creating = false;
      }
    }
  }
}
</script>
