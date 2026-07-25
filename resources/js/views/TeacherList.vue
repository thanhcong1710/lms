<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('teachers.title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('teachers.desc') }}</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        {{ $t('teachers.add_btn') }}
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchTeachers(1)" :placeholder="$t('teachers.search')" class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('teachers.form.name') }}</th>
            <th class="px-6 py-4">{{ $t('common.lms_id') }}</th>
            <th class="px-6 py-4">{{ $t('teachers.cols.branch_lms_id') }}</th>
            <th class="px-6 py-4">{{ $t('common.email') }}</th>
            <th class="px-6 py-4">{{ $t('teachers.cols.head_teacher') }}</th>
            <th class="px-6 py-4">{{ $t('common.status') }}</th>
            <th class="px-6 py-4 text-right">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(teacher, index) in teachers" :key="teacher.id" class="hover:bg-brand-card/40 transition duration-150">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ teacher.ins_name }}</td>
            <td class="px-6 py-4 font-mono text-indigo-400">{{ teacher.id_lms }}</td>
            <td class="px-6 py-4">{{ teacher.branch_id_lms || 'N/A' }}</td>
            <td class="px-6 py-4">{{ teacher.email }}</td>
            <td class="px-6 py-4">
              <span :class="isHeadTeacher(teacher.head) ? 'text-indigo-400 bg-indigo-500/10' : 'text-brand-desc bg-gray-500/10'" class="px-2 py-0.5 rounded text-xs font-semibold">
                {{ isHeadTeacher(teacher.head) ? $t('teachers.form.yes') : $t('teachers.form.no') }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="isTeacherActive(teacher.status) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ isTeacherActive(teacher.status) ? $t('common.active') : $t('common.inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(teacher)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">{{ $t('common.edit') }}</button>
              <button @click="deleteTeacher(teacher.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">{{ $t('common.delete') }}</button>
            </td>
          </tr>
          <tr v-if="teachers.length === 0">
            <td colspan="9" class="px-6 py-8 text-center text-brand-desc">{{ $t('teachers.no_data') }}</td>
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
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? $t('teachers.modal_edit') : $t('teachers.modal_add') }}</h3>

        <form @submit.prevent="saveTeacher" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('teachers.cols.branch_lms_id') }}</label>
            <select v-model="form.branch_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
              <option value="">{{ $t('system.select_branch') }}</option>
              <option v-for="b in branchOptions" :key="b.id" :value="b.id_lms">{{ b.name }} ({{ b.id_lms }})</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('teachers.form.name') }}</label>
            <input type="text" v-model="form.ins_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.lms_id') }}</label>
            <input type="text" v-model="form.id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.email') }}</label>
              <input type="email" v-model="form.email" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.phone') }}</label>
              <input type="text" v-model="form.phone" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('teachers.cols.head_teacher') }}</label>
              <select v-model="form.head" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="N">{{ $t('teachers.form.no') }}</option>
                <option value="Y">{{ $t('teachers.form.yes') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.status') }}</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="US001">{{ $t('common.active') }}</option>
                <option value="US002">{{ $t('common.inactive') }}</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-brand-border">
            <button type="button" @click="showModal = false" class="btn-secondary">{{ $t('common.cancel') }}</button>
            <button type="submit" class="btn-primary">{{ $t('common.save') }}</button>
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
      teachers: [],
      search: '',
      showModal: false,
      editingId: null,
      form: {
        ins_name: '',
        id_lms: '',
        branch_id_lms: '',
        email: '',
        phone: '',
        head: 'N',
        status: 'US001'
      },
      branchOptions: [],
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
    this.fetchTeachers(1);
    this.fetchBranchOptions();
  },
  computed: {
    filteredTeachers() {
      const q = this.search.toLowerCase();
      return this.teachers.filter(t => t.ins_name.toLowerCase().includes(q) || (t.id_lms && t.id_lms.toLowerCase().includes(q)));
    }
  },
  methods: {
    isHeadTeacher(head) {
      return head === 'Y' || head == 1 || head === '1' || head === true;
    },
    isTeacherActive(status) {
      return status == 1 || status === '1' || status === 'US001' || status === 'active';
    },
    async fetchTeachers(page = 1) {
      try {
        const response = await axios.get('/api/teachers', {
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
          this.teachers = response.data.data;
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
        console.error("Error fetching teachers", error);
      }
    },
    async fetchBranchOptions() {
      try {
        const res = await axios.get('/api/options/branches', { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } });
        this.branchOptions = res.data.data || [];
      } catch (e) { console.error(e); }
    },
    onPageChange(page) {
      this.fetchTeachers(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchTeachers(1);
    },
    openModal(teacher = null) {
      if (teacher) {
        this.editingId = teacher.id;
        this.form = {
          ...teacher,
          head: this.isHeadTeacher(teacher.head) ? 'Y' : 'N',
          status: this.isTeacherActive(teacher.status) ? 'US001' : 'US002'
        };
      } else {
        this.editingId = null;
        this.form = { ins_name: '', id_lms: '', branch_id_lms: '', email: '', phone: '', head: 'N', status: 'US001' };
      }
      this.showModal = true;
    },
    async saveTeacher() {
      try {
        const token = localStorage.getItem('token');
        const headers = token ? { Authorization: `Bearer ${token}` } : {};
        if (this.editingId) {
          await axios.put(`/api/teachers/${this.editingId}`, this.form, { headers });
        } else {
          await axios.post('/api/teachers', this.form, { headers });
        }
        this.showModal = false;
        await this.fetchTeachers(this.pagination.current_page);
      } catch (error) {
        console.error("Error saving teacher", error);
      }
    },
    async deleteTeacher(id) {
      if (confirm('Bạn có chắc chắn muốn xóa giáo viên này?')) {
        try {
          const token = localStorage.getItem('token');
          const headers = token ? { Authorization: `Bearer ${token}` } : {};
          await axios.delete(`/api/teachers/${id}`, { headers });
          await this.fetchTeachers(this.pagination.current_page);
        } catch (error) {
          console.error("Error deleting teacher", error);
        }
      }
    }
  }
}
</script>
