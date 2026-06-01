<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('system.title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('system.desc') }}</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition shadow-lg shadow-indigo-600/20">
        {{ $t('system.add_btn') }}
      </button>
    </div>

    <!-- Search -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchUsers(1)" :placeholder="$t('system.search')" class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('system.cols.username') }}</th>
            <th class="px-6 py-4">{{ $t('common.email') }}</th>
            <th class="px-6 py-4">{{ $t('system.cols.role') }}</th>
            <th class="px-6 py-4">{{ $t('system.cols.branch') }}</th>
            <th class="px-6 py-4">{{ $t('common.status') }}</th>
            <th class="px-6 py-4 text-right">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(user, index) in users" :key="user.id" class="hover:bg-gray-800/20 transition">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ user.name }}</td>
            <td class="px-6 py-4">{{ user.email }}</td>
            <td class="px-6 py-4">
              <span :class="roleClass(user.role)" class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase">
                {{ $t('system.roles.' + user.role) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">{{ user.branch_name || '-' }}</td>
            <td class="px-6 py-4">
              <span :class="user.status === 1 ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ user.status === 1 ? $t('common.active') : $t('common.inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(user)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">{{ $t('common.edit') }}</button>
              <button @click="deleteUser(user.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">{{ $t('common.delete') }}</button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-brand-desc">{{ $t('system.no_data') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-brand-card border border-brand-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? $t('system.modal_edit') : $t('system.modal_add') }}</h3>

        <form @submit.prevent="saveUser" class="space-y-4">
          <!-- Move Branch to Top -->
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('system.cols.branch') }}</label>
            <select v-model="form.branch_id" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
              <option :value="null">{{ $t('system.select_branch') }}</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} ({{ b.id_lms }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('system.cols.username') }}</label>
              <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.email') }}</label>
              <input type="email" v-model="form.email" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('system.cols.password') }}</label>
              <input type="password" v-model="form.password" :placeholder="editingId ? $t('system.pwd_placeholder') : ''" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
              <p v-if="!editingId" class="text-xs text-brand-desc mt-1">{{ $t('system.default_pwd') }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('system.cols.role') }}</label>
              <select v-model="form.role" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="admin">{{ $t('system.roles.admin') }}</option>
                <option value="team_leader">{{ $t('system.roles.team_leader') }}</option>
                <option value="teacher">{{ $t('system.roles.teacher') }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('system.cols.teacher') }}</label>
              <select v-model="form.teacher_id" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option :value="null">{{ $t('system.select_teacher') }}</option>
                <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.ins_name }} ({{ t.id_lms }})</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.status') }}</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option :value="1">{{ $t('common.active') }}</option>
                <option :value="0">{{ $t('common.inactive') }}</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-brand-border">
            <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-brand-border text-brand-text/90 hover:bg-gray-800 text-sm transition">{{ $t('common.cancel') }}</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition shadow-lg shadow-indigo-600/20">{{ $t('common.save') }}</button>
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
      users: [],
      branches: [],
      teachers: [],
      search: '',
      showModal: false,
      editingId: null,
      form: {
        name: '',
        email: '',
        password: '',
        role: 'teacher',
        branch_id: null,
        teacher_id: null,
        status: 1
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
    this.fetchUsers(1);
    this.fetchOptions();
  },
  methods: {
    async fetchUsers(page = 1) {
      try {
        const response = await axios.get('/api/users', {
          params: { search: this.search, page, per_page: this.pagination.per_page },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (response.data.data) {
          this.users = response.data.data;
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
        console.error("Error fetching users", error);
      }
    },
    async fetchOptions() {
      try {
        const [brRes, teRes] = await Promise.all([
          axios.get('/api/options/branches', { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }),
          axios.get('/api/options/teachers', { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } })
        ]);
        this.branches = brRes.data.data || [];
        this.teachers = teRes.data.data || [];
      } catch (error) {
        console.error("Error fetching options", error);
      }
    },
    roleClass(role) {
      if (role === 'admin') return 'bg-purple-500/10 text-purple-400 border border-purple-500/20';
      if (role === 'team_leader') return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
    },
    openModal(user = null) {
      if (user) {
        this.editingId = user.id;
        this.form = {
          name: user.name,
          email: user.email,
          password: '',
          role: user.role,
          branch_id: user.branch_id,
          teacher_id: user.teacher_id,
          status: user.status
        };
      } else {
        this.editingId = null;
        this.form = { name: '', email: '', password: '', role: 'teacher', branch_id: null, teacher_id: null, status: 1 };
      }
      this.showModal = true;
    },
    async saveUser() {
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        const payload = { ...this.form };
        if (!payload.password) delete payload.password;

        if (this.editingId) {
          await axios.put(`/api/users/${this.editingId}`, payload, { headers });
        } else {
          await axios.post('/api/users', payload, { headers });
        }
        this.showModal = false;
        this.fetchUsers(this.pagination.current_page);
      } catch (error) {
        const msg = error.response?.data?.message || error.response?.data?.errors || 'Save failed';
        alert(typeof msg === 'object' ? JSON.stringify(msg) : msg);
      }
    },
    async deleteUser(id) {
      if (!confirm('Are you sure?')) return;
      try {
        await axios.delete(`/api/users/${id}`, { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } });
        this.fetchUsers(this.pagination.current_page);
      } catch (error) {
        alert(error.response?.data?.message || 'Delete failed');
      }
    }
  }
}
</script>
