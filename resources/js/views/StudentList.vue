<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Students</h2>
        <p class="text-sm text-brand-desc">View and edit LMS student sync records</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        + Add Student
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchStudents(1)" placeholder="Search students..." class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">STT</th>
            <th class="px-6 py-4">Student Name</th>
            <th class="px-6 py-4">LMS ID</th>
            <th class="px-6 py-4">Accounting ID</th>
            <th class="px-6 py-4">Date of Birth</th>
            <th class="px-6 py-4">Gender</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(student, index) in students" :key="student.id" class="hover:bg-gray-800/20 transition duration-150">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ student.name }}</td>
            <td class="px-6 py-4 font-mono text-indigo-400">{{ student.id_lms }}</td>
            <td class="px-6 py-4 font-mono text-xs text-brand-desc">{{ student.accounting_id }}</td>
            <td class="px-6 py-4">{{ student.date_of_birth }}</td>
            <td class="px-6 py-4">
              <span class="text-xs px-2 py-0.5 rounded font-semibold bg-gray-800 text-brand-text/90">
                {{ student.gender === 'M' ? 'MALE' : 'FEMALE' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(student)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Edit</button>
              <button @click="deleteStudent(student.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">Delete</button>
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
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? 'Edit Student' : 'Add New Student' }}</h3>

        <form @submit.prevent="saveStudent" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Student Name</label>
            <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">LMS ID</label>
              <input type="text" v-model="form.id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Accounting ID</label>
              <input type="text" v-model="form.accounting_id" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Date of Birth</label>
              <input type="date" v-model="form.date_of_birth" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Gender</label>
              <select v-model="form.gender" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="M">Male</option>
                <option value="F">Female</option>
              </select>
            </div>
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
      students: [],
      search: '',
      showModal: false,
      editingId: null,
      form: {
        name: '',
        id_lms: '',
        accounting_id: '',
        date_of_birth: '',
        gender: 'M'
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
    this.fetchStudents(1);
  },
  computed: {
    filteredStudents() {
      const q = this.search.toLowerCase();
      return this.students.filter(s => s.name.toLowerCase().includes(q) || s.id_lms.toLowerCase().includes(q));
    }
  },
  methods: {
    async fetchStudents(page = 1) {
      try {
        const response = await axios.get('/api/students', {
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
          this.students = response.data.data;
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
        console.error("Error fetching students", error);
      }
    },
    onPageChange(page) {
      this.fetchStudents(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchStudents(1);
    },
    openModal(student = null) {
      if (student) {
        this.editingId = student.id;
        this.form = { ...student };
      } else {
        this.editingId = null;
        this.form = { name: '', id_lms: '', accounting_id: '', date_of_birth: '', gender: 'M' };
      }
      this.showModal = true;
    },
    saveStudent() {
      if (this.editingId) {
        const idx = this.students.findIndex(s => s.id === this.editingId);
        this.students[idx] = { ...this.form, id: this.editingId };
      } else {
        this.students.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteStudent(id) {
      if (confirm('Are you sure you want to delete this student?')) {
        this.students = this.students.filter(s => s.id !== id);
      }
    }
  }
}
</script>
