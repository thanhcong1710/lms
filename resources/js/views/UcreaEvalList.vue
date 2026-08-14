<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('ucrea.title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('ucrea.desc') }}</p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4 flex-wrap md:flex-nowrap">
        <button 
          @click="openCreateModal" 
          class="btn-primary"
        >
          <span class="text-base font-bold">+</span> {{ $t('ucrea.add_eval') }}
        </button>

        <!-- Tabs -->
        <div class="flex bg-brand-card border border-brand-border p-1 rounded-xl">
          <button 
            @click="changeTab('pending')" 
            :class="[
              activeTab === 'pending' ? 'bg-indigo-600 text-white' : 'text-brand-desc hover:text-brand-text',
              'px-4 py-2 rounded-lg text-sm font-semibold transition'
            ]"
          >
            {{ $t('ucrea.tab_pending') }}
          </button>
          <button 
            @click="changeTab('completed')" 
            :class="[
              activeTab === 'completed' ? 'bg-emerald-500 text-white' : 'text-brand-desc hover:text-brand-text',
              'px-4 py-2 rounded-lg text-sm font-semibold transition'
            ]"
          >
            {{ $t('ucrea.tab_completed') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="relative w-full md:w-80">
        <input 
          type="text" 
          v-model="search" 
          @input="fetchData"
          :placeholder="$t('ucrea.search_placeholder')" 
          class="w-full pl-4 pr-10 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-brand-desc/60 focus:outline-none focus:border-indigo-500 transition text-sm"
        >
        <span class="absolute right-3 top-3 text-brand-desc/60">🔍</span>
      </div>
      
      <div class="text-xs text-brand-desc font-medium">
        {{ $t('pagination.showing_count', { count: results.length }) }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">{{ $t('ucrea.loading') }}</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('tests.cols.test_name') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.level') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.teacher') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.class') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.student_name') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.test_date') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.created_at') }}</th>
            <th class="px-6 py-4">{{ $t('igbh.cols.updated_at') }}</th>
            <th class="px-6 py-4 text-right sticky right-0 bg-brand-header z-10 border-l border-brand-border shadow-[-4px_0_10px_rgba(0,0,0,0.1)]">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(item, index) in results" :key="item.id" class="group hover:bg-brand-card transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-semibold text-brand-text">{{ item.test_nm || item.test?.test_nm }}</td>
            <td class="px-6 py-4">{{ item.level_cd }}</td>
            <td class="px-6 py-4">{{ item.memb_nm }}</td>
            <td class="px-6 py-4">{{ item.class_nm || '-' }}</td>
            <td class="px-6 py-4 font-medium text-indigo-400">{{ item.stu_nm }}</td>
            <td class="px-6 py-4">{{ item.eval_dt ? item.eval_dt.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-xs text-brand-desc">{{ item.created_at ? item.created_at.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-xs text-brand-desc">{{ item.updated_at ? item.updated_at.substring(0,10) : '-' }}</td>
            <td class="px-6 py-4 text-right sticky right-0 bg-brand-bg z-10 border-l border-brand-border shadow-[-4px_0_10px_rgba(0,0,0,0.1)] group-hover:bg-brand-card transition-colors">
              <div class="flex justify-end items-center gap-2">
                <router-link 
                  v-if="activeTab === 'pending'"
                  :to="{ name: 'ucrea-eval-form', params: { id: item.id } }"
                  :title="$t('igbh.actions.enter_score')"
                  class="inline-flex items-center justify-center p-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition shadow-lg shadow-indigo-600/30"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </router-link>
                <div v-else class="flex justify-end items-center gap-2">
                  <router-link 
                    :to="{ name: 'ucrea-eval-form', params: { id: item.id } }"
                    :title="$t('igbh.actions.edit_score')"
                    class="inline-flex items-center justify-center p-2 rounded-lg border border-indigo-600 text-indigo-500 hover:bg-indigo-600 hover:text-white transition"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </router-link>
                  <router-link 
                    :to="{ name: 'ucrea-eval-result', params: { id: item.id } }"
                    :title="$t('igbh.actions.view_result')"
                    class="inline-flex items-center justify-center p-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white transition shadow-lg shadow-emerald-600/30"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </router-link>
                </div>
              </div>
            </td>
          </tr>
          <tr v-if="results.length === 0">
            <td colspan="10" class="px-6 py-12 text-center text-brand-desc">{{ $t('ucrea.no_data') }}</td>
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

    <!-- Create Evaluation Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-brand-card border border-brand-border w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-brand-border bg-brand-header flex justify-between items-center">
          <h3 class="text-lg font-bold text-brand-text">Thêm Mới Nhập Điểm UCREA</h3>
          <button @click="closeCreateModal" class="text-brand-desc hover:text-brand-text text-xl font-bold">&times;</button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="submitCreate" class="p-6 space-y-4">
          <!-- Step 1: Branch Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">1. Trung tâm (Cơ sở)</label>
            <select v-model="form.branch_id" @change="onBranchChange" required class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
              <option :value="null" disabled>-- Chọn trung tâm --</option>
              <option v-for="b in initData.branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>

          <!-- Step 2: Class Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">2. Lớp học (Theo phân quyền)</label>
            <select v-model="form.selected_class_id" @change="onClassChange" required :disabled="!form.branch_id && initData.branches.length > 1" class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm disabled:opacity-50">
              <option value="" disabled>-- Chọn lớp học --</option>
              <option v-for="c in filteredClasses" :key="c.id" :value="c.id">{{ c.cls_name }} (Level: {{ c.level_name || 'N/A' }})</option>
            </select>
          </div>

          <!-- Step 3: Test Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">3. Bài Kiểm Tra UCREA (Cùng Level {{ selectedClassObj ? selectedClassObj.level_name : '' }})</label>
            <select v-model="form.test_id" @change="onTestChange" required :disabled="!form.selected_class_id" class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm disabled:opacity-50">
              <option value="" disabled>{{ !form.selected_class_id ? '-- Vui lòng chọn lớp học trước --' : '-- Chọn bài kiểm tra --' }}</option>
              <option v-for="t in filteredTests" :key="t.id" :value="t.id">{{ t.test_nm }} (Level: {{ t.level_cd_nm || t.level_cd }})</option>
            </select>
          </div>

          <!-- Step 4: Student Selector -->
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-brand-desc uppercase">4. Học sinh (Trong lớp & chưa chấm đề này)</label>
            <select v-model="form.stu_nm" required :disabled="!form.test_id || filteredStudents.length === 0" class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm disabled:opacity-50">
              <option value="" disabled>{{ !form.test_id ? '-- Vui lòng chọn bài kiểm tra trước --' : (filteredStudents.length === 0 ? '-- Đã chấm hết học sinh trong lớp --' : '-- Chọn học sinh --') }}</option>
              <option v-for="std in filteredStudents" :key="std.student_id" :value="std.student_name">{{ std.student_name }} ({{ std.student_lms_id }})</option>
            </select>
            <p v-if="form.test_id && filteredStudents.length === 0" class="text-xs text-amber-500 mt-1 font-medium">⚠️ Tất cả học sinh trong lớp này đã được tạo bài chấm cho đề thi này.</p>
          </div>

          <!-- Step 5: Evaluating Teacher & Date -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-semibold text-brand-desc uppercase">Giáo viên đánh giá</label>
              <select v-model="form.memb_nm" required class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
                <option value="" disabled>-- Chọn giáo viên --</option>
                <option v-for="t in initData.teachers" :key="t.id" :value="t.name">{{ t.name }}</option>
              </select>
            </div>
            <div class="space-y-1.5">
              <label class="block text-xs font-semibold text-brand-desc uppercase">Ngày đánh giá</label>
              <input type="date" v-model="form.eval_dt" required class="w-full px-3 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="pt-4 border-t border-brand-border flex justify-end gap-3">
            <button type="button" @click="closeCreateModal" class="btn-secondary">
              Hủy
            </button>
            <button type="submit" :disabled="creating || !form.stu_nm" class="btn-primary disabled:opacity-50">
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
import Pagination from '../components/Pagination.vue';

export default {
  name: 'UcreaEvalList',
  components: {
    Pagination
  },
  data() {
    return {
      results: [],
      loading: false,
      activeTab: 'completed',
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
        branches: [],
        classes: [],
        teachers: [],
        tests: [],
        existing_results: [],
        contracts: []
      },
      form: {
        branch_id: null,
        selected_class_id: '',
        class_nm: '',
        test_id: '',
        stu_nm: '',
        memb_nm: '',
        eval_dt: new Date().toISOString().substr(0, 10)
      }
    }
  },
  computed: {
    filteredClasses() {
      let list = this.initData.classes || [];
      if (this.form.branch_id) {
        list = list.filter(c => c.branch_id == this.form.branch_id);
      }
      // ONLY include UC (UCREA) classes: exclude BH and IG
      return list.filter(c => {
        return c.product_id === 1;
      });
    },
    selectedClassObj() {
      if (!this.form.selected_class_id) return null;
      return (this.initData.classes || []).find(c => c.id == this.form.selected_class_id);
    },
    filteredTests() {
      if (!this.selectedClassObj || !this.selectedClassObj.level_name) {
        return [];
      }
      const classLevel = this.selectedClassObj.level_name.trim().toUpperCase();
      return (this.initData.tests || []).filter(t => {
        const testLevel = (t.level_cd || '').trim().toUpperCase();
        return testLevel === classLevel || (t.level_cd_nm && t.level_cd_nm.toUpperCase().includes(classLevel));
      });
    },
    selectedTestObj() {
      if (!this.form.test_id) return null;
      return (this.initData.tests || []).find(t => t.id == this.form.test_id);
    },
    filteredStudents() {
      if (!this.selectedClassObj || !this.selectedTestObj) {
        return [];
      }
      const classId = this.selectedClassObj.id;
      const className = this.selectedClassObj.cls_name;
      const testCd = this.selectedTestObj.test_cd;

      // 1. Get all students enrolled in this class
      const classContracts = (this.initData.contracts || []).filter(cnt => cnt.class_id == classId);
      
      // 2. Filter out students who already have a result entry for this class & test
      // Note: match ONLY by test_cd (not test_seq) because all tests share test_seq=1
      return classContracts.filter(cnt => {
        const hasResult = (this.initData.existing_results || []).some(res => {
          const matchClass = res.class_nm === className;
          const matchTest = res.test_cd === testCd;
          const matchStudent = res.stu_nm === cnt.student_name;
          return matchClass && matchTest && matchStudent;
        });
        return !hasResult;
      });
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
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchData(1);
    },
    changeTab(tab) {
      this.activeTab = tab;
      this.fetchData(1);
    },
    onBranchChange() {
      this.form.selected_class_id = '';
      this.form.class_nm = '';
      this.form.test_id = '';
      this.form.stu_nm = '';
      this.form.memb_nm = '';
    },
    onClassChange() {
      const cls = this.selectedClassObj;
      if (cls) {
        this.form.class_nm = cls.cls_name;
        if (cls.teacher_name) {
          this.form.memb_nm = cls.teacher_name;
        }
      } else {
        this.form.class_nm = '';
      }
      this.form.test_id = '';
      this.form.stu_nm = '';
    },
    onTestChange() {
      this.form.stu_nm = '';
    },
    async openCreateModal() {
      this.showModal = true;
      try {
        const response = await axios.get('/api/ucrea/init-data', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data.status === 'success') {
          this.initData = response.data.data;
          if (this.initData.branches && this.initData.branches.length === 1) {
            this.form.branch_id = this.initData.branches[0].id;
          }
        }
      } catch (error) {
        console.error("Error fetching initialization data", error);
        alert("Không thể tải dữ liệu khởi tạo. Vui lòng thử lại.");
      }
    },
    closeCreateModal() {
      this.showModal = false;
      this.form = {
        branch_id: null,
        selected_class_id: '',
        class_nm: '',
        test_id: '',
        stu_nm: '',
        memb_nm: '',
        eval_dt: new Date().toISOString().substr(0, 10)
      };
    },
    async submitCreate() {
      this.creating = true;
      try {
        const response = await axios.post('/api/ucrea/results', this.form, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data.status === 'success') {
          const newId = response.data.data.id;
          this.closeCreateModal();
          this.$router.push({ name: 'ucrea-eval-form', params: { id: newId } });
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
