<template>
  <div class="h-full flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-brand-text">{{ $t('sidebar.summative_class_title') }}</h1>
        <p class="text-brand-desc mt-1">{{ $t('sidebar.summative_class_desc') }}</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative">
          <input type="text" v-model="search" @keyup.enter="loadResults"
            :placeholder="$t('igbh.search_student')"
            class="pl-10 pr-4 py-2.5 bg-brand-input border border-brand-border/50 rounded-xl text-brand-text placeholder-brand-desc/60 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all w-64">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-brand-desc/60"></i>
        </div>

        <button @click="showAddModal = true"
          class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-all shadow-lg shadow-indigo-500/20 font-medium">
          <i class="fas fa-plus"></i>
          <span>{{ $t('igbh.add_eval') }}</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-brand-surface border border-brand-border/50 rounded-2xl overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-brand-input/50 text-brand-desc border-b border-brand-border/50">
              <th class="py-4 px-5 font-medium w-16 text-center">STT</th>
              <th class="py-4 px-5 font-medium">{{ $t('igbh.test_name') }}</th>
              <th class="py-4 px-5 font-medium">{{ $t('igbh.level') }}</th>
              <th class="py-4 px-5 font-medium">{{ $t('igbh.class') }}</th>
              <th class="py-4 px-5 font-medium">{{ $t('igbh.teacher') }}</th>
              <th class="py-4 px-5 font-medium">Trạng thái</th>
              <th class="py-4 px-5 font-medium">{{ $t('igbh.eval_date') }}</th>
              <th class="py-4 px-5 font-medium text-center w-24">{{ $t('igbh.actions') }}</th>
            </tr>
          </thead>
          <tbody class="text-brand-text divide-y divide-brand-border/30">
            <tr v-for="(item, index) in results" :key="item.id" class="hover:bg-brand-input/30 transition-colors">
              <td class="py-3 px-5 text-center text-brand-desc">{{ (currentPage - 1) * perPage + index + 1 }}</td>
              <td class="py-3 px-5">
                <span class="font-medium">{{ item.test_nm }}</span>
              </td>
              <td class="py-3 px-5">
                <span class="px-2.5 py-1 bg-brand-input rounded-lg text-sm text-brand-desc font-medium">
                  {{ item.level_cd }}
                </span>
              </td>
              <td class="py-3 px-5 font-medium text-indigo-400">{{ item.class_nm }}</td>
              <td class="py-3 px-5 text-brand-desc">{{ item.teacher_nm }}</td>
              <td class="py-3 px-5">
                <span :class="item.status === 'completed' ? 'text-emerald-400 bg-emerald-400/10' : 'text-amber-400 bg-amber-400/10'" class="px-2.5 py-1 rounded-lg text-xs font-medium">
                  {{ item.status === 'completed' ? 'Đã hoàn thành' : 'Đang nhập' }}
                </span>
              </td>
              <td class="py-3 px-5 text-brand-desc">{{ formatDate(item.eval_ymd) }}</td>
              <td class="py-3 px-5">
                <div class="flex items-center justify-center gap-2">
                  <router-link :to="{ name: 'igbh-class-summative-eval-form', params: { id: item.id } }"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-brand-desc hover:bg-brand-input hover:text-indigo-400 transition-colors"
                    title="Nhập điểm">
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button @click="deleteResult(item.id)"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-brand-desc hover:bg-red-500/10 hover:text-red-400 transition-colors"
                    title="Xóa">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="results.length === 0">
              <td colspan="8" class="py-8 text-center text-brand-desc">
                Không tìm thấy dữ liệu đánh giá cuối kỳ theo lớp nào
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="border-t border-brand-border/50 p-4 flex items-center justify-between bg-brand-surface">
        <div class="text-sm text-brand-desc">
          Hiển thị {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, totalItems) }} / {{ totalItems }}
        </div>
        <div class="flex items-center gap-2">
          <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
            class="p-2 rounded-lg text-brand-desc hover:bg-brand-input hover:text-brand-text disabled:opacity-50 disabled:hover:bg-transparent transition-colors">
            <i class="fas fa-chevron-left"></i>
          </button>
          
          <div class="flex items-center gap-1">
            <button v-for="p in paginationPages" :key="p"
              @click="typeof p === 'number' ? changePage(p) : null"
              :class="[
                'w-8 h-8 flex items-center justify-center rounded-lg text-sm transition-all',
                p === currentPage 
                  ? 'bg-indigo-600 text-white font-medium shadow-md shadow-indigo-500/20' 
                  : typeof p === 'number' 
                    ? 'text-brand-desc hover:bg-brand-input hover:text-brand-text' 
                    : 'text-brand-desc/50 cursor-default'
              ]">
              {{ p }}
            </button>
          </div>

          <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
            class="p-2 rounded-lg text-brand-desc hover:bg-brand-input hover:text-brand-text disabled:opacity-50 disabled:hover:bg-transparent transition-colors">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-brand-surface border border-brand-border/50 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in-up">
        <div class="flex items-center justify-between p-5 border-b border-brand-border/50">
          <h3 class="text-lg font-bold text-brand-text">Tạo mới Đánh giá cuối kỳ lớp</h3>
          <button @click="showAddModal = false" class="text-brand-desc hover:text-brand-text transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="p-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-brand-desc mb-1.5">{{ $t('igbh.test_name') }} <span class="text-red-400">*</span></label>
            <div class="relative">
              <select v-model="addForm.test_seq" 
                class="w-full bg-brand-input border border-brand-border/50 rounded-xl px-4 py-2.5 text-brand-text focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 appearance-none">
                <option value="">-- Chọn bài kiểm tra --</option>
                <option v-for="t in tests" :key="t.test_seq" :value="t.test_seq">{{ t.test_nm }} ({{ t.level_cd }})</option>
              </select>
              <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-brand-desc pointer-events-none text-xs"></i>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-brand-desc mb-1.5">{{ $t('igbh.class') }} <span class="text-red-400">*</span></label>
            <div class="relative">
              <select v-model="addForm.class_seq" 
                class="w-full bg-brand-input border border-brand-border/50 rounded-xl px-4 py-2.5 text-brand-text focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 appearance-none">
                <option value="">-- Chọn lớp --</option>
                <option v-for="c in classes" :key="c.class_seq" :value="c.class_seq">{{ c.class_nm }}</option>
              </select>
              <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-brand-desc pointer-events-none text-xs"></i>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-brand-desc mb-1.5">Ngày đánh giá <span class="text-red-400">*</span></label>
            <input type="date" v-model="addForm.eval_ymd" 
              class="w-full bg-brand-input border border-brand-border/50 rounded-xl px-4 py-2.5 text-brand-text focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50">
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 p-5 border-t border-brand-border/50 bg-brand-input/30">
          <button @click="showAddModal = false" 
            class="px-4 py-2 text-brand-desc hover:text-brand-text font-medium transition-colors">
            {{ $t('common.cancel') }}
          </button>
          <button @click="submitAdd" :disabled="isSubmitting"
            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-medium shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            <span>{{ $t('common.save') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';

export default {
  data() {
    return {
      results: [],
      search: '',
      currentPage: 1,
      perPage: 15,
      totalItems: 0,
      totalPages: 0,
      
      showAddModal: false,
      isSubmitting: false,
      tests: [],
      classes: [],
      
      addForm: {
        test_seq: '',
        class_seq: '',
        eval_ymd: moment().format('YYYY-MM-DD')
      }
    };
  },
  computed: {
    paginationPages() {
      const current = this.currentPage;
      const total = this.totalPages;
      if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
      
      if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
      if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
      
      return [1, '...', current - 1, current, current + 1, '...', total];
    }
  },
  mounted() {
    this.loadResults();
    this.loadInitData();
  },
  methods: {
    formatDate(date) {
      if (!date) return '';
      return moment(date).format('DD/MM/YYYY');
    },
    async loadResults() {
      try {
        const response = await axios.get('/api/igbh/summative-class/results', {
          params: {
            page: this.currentPage,
            per_page: this.perPage,
            search: this.search
          }
        });
        this.results = response.data.data;
        this.totalItems = response.data.total;
        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error(error);
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.loadResults();
      }
    },
    async loadInitData() {
      try {
        const response = await axios.get('/api/igbh/summative-class/init-data');
        this.tests = response.data.tests;
        this.classes = response.data.classes;
      } catch (error) {
        console.error(error);
      }
    },
    async submitAdd() {
      if (!this.addForm.test_seq || !this.addForm.class_seq || !this.addForm.eval_ymd) {
        alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
        return;
      }
      
      this.isSubmitting = true;
      try {
        const response = await axios.post('/api/igbh/summative-class/create', this.addForm);
        this.showAddModal = false;
        
        alert('Đã tạo lớp đánh giá thành công');
        
        // redirect to grade page
        this.$router.push({ name: 'igbh-class-summative-eval-form', params: { id: response.data.id } });
      } catch (error) {
        alert(error.response?.data?.message || 'Có lỗi xảy ra');
      } finally {
        this.isSubmitting = false;
      }
    },
    async deleteResult(id) {
      if (confirm('Bạn có chắc chắn muốn xóa đánh giá này? Toàn bộ điểm của lớp sẽ bị xóa!')) {
        try {
          await axios.delete(`/api/igbh/summative-class/${id}`);
          alert('Đã xóa thành công');
          this.loadResults();
        } catch (error) {
          alert('Không thể xóa. Vui lòng thử lại.');
        }
      }
    }
  }
};
</script>
