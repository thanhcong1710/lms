<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('contracts.title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('contracts.desc') }}</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        {{ $t('contracts.add_btn') }}
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchContracts(1)" :placeholder="$t('contracts.search')" class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('contracts.cols.student') }}</th>
            <th class="px-6 py-4">{{ $t('contracts.cols.class') }}</th>
            <th class="px-6 py-4">{{ $t('common.branch') }}</th>
            <th class="px-6 py-4">{{ $t('contracts.cols.start_date') }}</th>
            <th class="px-6 py-4">{{ $t('contracts.cols.end_date') }}</th>
            <th class="px-6 py-4">{{ $t('contracts.cols.valid_cd') }}</th>
            <th class="px-6 py-4">{{ $t('common.status') }}</th>
            <th class="px-6 py-4 text-right">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(contract, index) in contracts" :key="contract.id" class="hover:bg-gray-800/20 transition duration-150">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ contract.student_name }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ contract.class_name }}</td>
            <td class="px-6 py-4 text-sm">{{ contract.branch_name }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ contract.enrolment_start_date }}</td>
            <td class="px-6 py-4 font-mono text-xs">{{ contract.enrolment_last_date }}</td>
            <td class="px-6 py-4 font-mono text-xs text-indigo-400">{{ contract.valid_cd }}</td>
            <td class="px-6 py-4">
              <span :class="contract.status === 'SS001' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ contract.status === 'SS001' ? 'Enrolled' : 'Pending' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(contract)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">{{ $t('common.edit') }}</button>
              <button @click="deleteContract(contract.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">{{ $t('common.delete') }}</button>
            </td>
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

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-brand-card border border-brand-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? $t('contracts.modal_edit') : $t('contracts.modal_add') }}</h3>

        <form @submit.prevent="saveContract" class="space-y-4">
          <!-- Move Branch to Top -->
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.branch') }}</label>
            <select v-model="form.branch_id" @change="onBranchChange" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
              <option value="">{{ $t('system.select_branch') }}</option>
              <option v-for="b in branchOptions" :key="b.id" :value="b.id">{{ b.name }} ({{ b.id_lms }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('contracts.cols.class') }}</label>
              <select v-model="form.class_id" @change="onClassChange" :disabled="!form.branch_id" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="">{{ $t('system.select_class') }}</option>
                <option v-for="c in filteredClassOptions" :key="c.id" :value="c.id">{{ c.cls_name }} ({{ c.level_name }})</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('contracts.cols.student') }}</label>
              <div class="relative">
                <input type="text" v-model="studentSearch" @input="searchStudents" :disabled="!form.class_id" :placeholder="$t('system.select_student')" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
                <div v-if="studentResults.length > 0" class="absolute z-10 w-full mt-1 bg-brand-card border border-brand-border rounded-xl shadow-xl max-h-48 overflow-y-auto">
                  <div v-for="s in studentResults" :key="s.id" @click="selectStudent(s)" class="px-4 py-2.5 hover:bg-brand-input cursor-pointer text-sm text-brand-text border-b border-brand-border/50 last:border-0">
                    {{ s.name }} <span class="text-brand-desc">({{ s.id_lms }})</span>
                  </div>
                </div>
              </div>
              <p v-if="form.student_id" class="text-xs text-indigo-400 mt-1">ID: {{ form.student_id }} - {{ form.student_name }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('contracts.cols.start_date') }}</label>
              <input type="date" v-model="form.enrolment_start_date" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('contracts.cols.end_date') }}</label>
              <input type="date" v-model="form.enrolment_last_date" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('contracts.cols.valid_cd') }}</label>
              <select v-model="form.valid_cd" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="VC005">{{ $t('contracts.form.regular') }}</option>
                <option value="VC001">{{ $t('contracts.form.trial') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.status') }}</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="SS001">{{ $t('contracts.form.enrolled') }}</option>
                <option value="SS002">{{ $t('contracts.form.pending') }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.remark') }}</label>
            <textarea v-model="form.remark" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-brand-border">
            <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-brand-border text-brand-text/90 hover:bg-gray-800 text-sm transition">{{ $t('common.cancel') }}</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition shadow-lg shadow-indigo-600/20">{{ $t('common.save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      contracts: [],
      search: '',
      showModal: false,
      editingId: null,
      studentSearch: '',
      studentResults: [],
      branchOptions: [],
      classOptions: [],
      form: {
        student_id: '',
        student_name: '',
        class_id: '',
        branch_id: '',
        enrolment_start_date: '',
        enrolment_last_date: '',
        valid_cd: 'VC005',
        status: 'SS001',
        remark: ''
      },
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
    this.fetchContracts(1);
    this.fetchFormOptions();
  },
  computed: {
    filteredContracts() {
      return this.contracts;
    },
    filteredClassOptions() {
      if (!this.form.branch_id) return [];
      const selectedBranch = this.branchOptions.find(b => b.id === this.form.branch_id);
      if (!selectedBranch) return [];
      return this.classOptions.filter(c => c.branch_id_lms === selectedBranch.id_lms);
    }
  },
  methods: {
    async fetchContracts(page = 1) {
      try {
        const response = await axios.get('/api/contracts', {
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
          this.contracts = response.data.data;
          this.pagination = {
            total: response.data.total,
            per_page: response.data.per_page,
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            from: response.data.from,
            to: response.data.to
          };
        }
      } catch (error) {
        console.error("Error fetching contracts", error);
      }
    },
    onBranchChange() {
      this.form.class_id = '';
      this.form.student_id = '';
      this.form.student_name = '';
      this.studentSearch = '';
    },
    onClassChange() {
      this.form.student_id = '';
      this.form.student_name = '';
      this.studentSearch = '';
    },
    async fetchFormOptions() {
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        const [brRes, clRes] = await Promise.all([
          axios.get('/api/options/branches', { headers }),
          axios.get('/api/options/classes', { headers })
        ]);
        this.branchOptions = brRes.data.data || [];
        this.classOptions = clRes.data.data || [];
      } catch (e) { console.error(e); }
    },
    async searchStudents() {
      if (this.studentSearch.length < 2 || !this.form.class_id) {
        this.studentResults = [];
        return;
      }
      try {
        const res = await axios.get('/api/options/students', {
          params: { search: this.studentSearch, class_id: this.form.class_id },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        this.studentResults = res.data.data || [];
      } catch (e) { console.error(e); }
    },
    selectStudent(student) {
      this.form.student_id = student.id;
      this.form.student_name = student.name;
      this.studentSearch = student.name;
      this.studentResults = [];
    },
    onPageChange(page) {
      this.fetchContracts(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchContracts(1);
    },
    openModal(contract = null) {
      this.studentSearch = '';
      this.studentResults = [];
      if (contract) {
        this.editingId = contract.id;
        this.form = {
          student_id: contract.student_id || '',
          student_name: contract.student_name || '',
          class_id: contract.class_id || '',
          branch_id: contract.branch_id || '',
          enrolment_start_date: contract.enrolment_start_date || '',
          enrolment_last_date: contract.enrolment_last_date || '',
          valid_cd: contract.valid_cd || 'VC005',
          status: contract.status || 'SS001',
          remark: contract.remark || ''
        };
        this.studentSearch = contract.student_name || '';
      } else {
        this.editingId = null;
        this.form = { student_id: '', student_name: '', class_id: '', branch_id: '', enrolment_start_date: '', enrolment_last_date: '', valid_cd: 'VC005', status: 'SS001', remark: '' };
      }
      this.showModal = true;
    },
    async saveContract() {
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        const payload = { ...this.form };
        if (this.editingId) {
          await axios.put(`/api/contracts/${this.editingId}`, payload, { headers });
        } else {
          await axios.post('/api/contracts', payload, { headers });
        }
        this.showModal = false;
        this.fetchContracts(this.pagination.current_page);
      } catch (error) {
        const msg = error.response?.data?.message || 'Save failed';
        alert(msg);
      }
    },
    deleteContract(id) {
      if (confirm('Are you sure you want to delete this contract?')) {
        this.contracts = this.contracts.filter(c => c.id !== id);
      }
    }
  }
}
</script>
