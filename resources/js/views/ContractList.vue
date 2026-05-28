<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Contracts & Enrollments</h2>
        <p class="text-sm text-brand-desc">Manage student contracts, dates and status codes</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        + Add Contract
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchContracts(1)" placeholder="Search contracts..." class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">STT</th>
            <th class="px-6 py-4">Student</th>
            <th class="px-6 py-4">Class</th>
            <th class="px-6 py-4">Branch</th>
            <th class="px-6 py-4">Start Date</th>
            <th class="px-6 py-4">End Date</th>
            <th class="px-6 py-4">Valid CD</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
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
              <button @click="openModal(contract)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Edit</button>
              <button @click="deleteContract(contract.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">Delete</button>
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
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? 'Edit Contract' : 'Add New Contract' }}</h3>

        <form @submit.prevent="saveContract" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Student Name</label>
              <input type="text" v-model="form.student_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Class Name</label>
              <input type="text" v-model="form.class_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Branch Name</label>
            <input type="text" v-model="form.branch_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Start Date</label>
              <input type="date" v-model="form.enrolment_start_date" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">End Date</label>
              <input type="date" v-model="form.enrolment_last_date" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Valid CD</label>
              <select v-model="form.valid_cd" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="VC005">VC005 (Regular)</option>
                <option value="VC001">VC001 (Trial)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Status</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="SS001">Enrolled (SS001)</option>
                <option value="SS002">Pending (SS002)</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Remark</label>
            <textarea v-model="form.remark" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-brand-border">
            <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-brand-border text-brand-text/90 hover:bg-gray-800 text-sm transition">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition shadow-lg shadow-indigo-600/20">Save</button>
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
      form: {
        student_name: '',
        class_name: '',
        branch_name: '',
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
  },
  computed: {
    filteredContracts() {
      const q = this.search.toLowerCase();
      return this.contracts.filter(c => c.student_name.toLowerCase().includes(q) || c.class_name.toLowerCase().includes(q));
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
    onPageChange(page) {
      this.fetchContracts(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchContracts(1);
    },
    openModal(contract = null) {
      if (contract) {
        this.editingId = contract.id;
        this.form = { ...contract };
      } else {
        this.editingId = null;
        this.form = { student_name: '', class_name: '', branch_name: '', enrolment_start_date: '', enrolment_last_date: '', valid_cd: 'VC005', status: 'SS001', remark: '' };
      }
      this.showModal = true;
    },
    saveContract() {
      if (this.editingId) {
        const idx = this.contracts.findIndex(c => c.id === this.editingId);
        this.contracts[idx] = { ...this.form, id: this.editingId };
      } else {
        this.contracts.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteContract(id) {
      if (confirm('Are you sure you want to delete this contract?')) {
        this.contracts = this.contracts.filter(c => c.id !== id);
      }
    }
  }
}
</script>
