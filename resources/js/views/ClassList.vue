<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Classes</h2>
        <p class="text-sm text-brand-desc">Manage U-Crea and i-Garten class structures</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        + Add Class
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" @input="fetchClasses(1)" placeholder="Search classes..." class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">STT</th>
            <th class="px-6 py-4">Class Name</th>
            <th class="px-6 py-4">LMS Sequence</th>
            <th class="px-6 py-4">Level</th>
            <th class="px-6 py-4">Type</th>
            <th class="px-6 py-4">Teacher ID</th>
            <th class="px-6 py-4">Branch</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(cls, index) in classes" :key="cls.id" class="hover:bg-gray-800/20 transition duration-150">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ cls.cls_name }}</td>
            <td class="px-6 py-4 font-mono text-indigo-400">{{ cls.class_seq || 'N/A' }}</td>
            <td class="px-6 py-4">{{ cls.level_name }}</td>
            <td class="px-6 py-4">
              <span class="text-xs px-2 py-0.5 rounded font-semibold bg-blue-500/10 text-blue-400">
                {{ cls.cls_type }}
              </span>
            </td>
            <td class="px-6 py-4 font-mono text-sm">{{ cls.teacher_id_lms }}</td>
            <td class="px-6 py-4">{{ cls.branch_id_lms }}</td>
            <td class="px-6 py-4">
              <span :class="cls.cls_status === 'US001' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ cls.cls_status === 'US001' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(cls)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Edit</button>
              <button @click="deleteClass(cls.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">Delete</button>
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
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? 'Edit Class' : 'Add New Class' }}</h3>

        <form @submit.prevent="saveClass" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Class Name</label>
            <input type="text" v-model="form.cls_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">LMS Sequence</label>
              <input type="number" v-model="form.class_seq" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Level Name</label>
              <input type="text" v-model="form.level_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Type</label>
              <select v-model="form.cls_type" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="CT001">CT001 (U-Crea)</option>
                <option value="CT002">CT002 (i-Garten)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Teacher ID</label>
              <input type="text" v-model="form.teacher_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Branch ID</label>
              <input type="text" v-model="form.branch_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Status</label>
            <select v-model="form.cls_status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
              <option value="US001">Active</option>
              <option value="US002">Inactive</option>
            </select>
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
      classes: [],
      search: '',
      showModal: false,
      editingId: null,
      form: {
        cls_name: '',
        class_seq: '',
        level_name: '',
        cls_type: 'CT001',
        teacher_id_lms: '',
        branch_id_lms: '',
        cls_status: 'US001'
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
    this.fetchClasses(1);
  },
  computed: {
    filteredClasses() {
      const q = this.search.toLowerCase();
      return this.classes.filter(c => c.cls_name.toLowerCase().includes(q) || c.teacher_id_lms.toLowerCase().includes(q));
    }
  },
  methods: {
    async fetchClasses(page = 1) {
      try {
        const response = await axios.get('/api/classes', {
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
          this.classes = response.data.data;
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
        console.error("Error fetching classes", error);
      }
    },
    onPageChange(page) {
      this.fetchClasses(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchClasses(1);
    },
    openModal(cls = null) {
      if (cls) {
        this.editingId = cls.id;
        this.form = { ...cls };
      } else {
        this.editingId = null;
        this.form = { cls_name: '', class_seq: '', level_name: '', cls_type: 'CT001', teacher_id_lms: '', branch_id_lms: '', cls_status: 'US001' };
      }
      this.showModal = true;
    },
    saveClass() {
      if (this.editingId) {
        const idx = this.classes.findIndex(c => c.id === this.editingId);
        this.classes[idx] = { ...this.form, id: this.editingId };
      } else {
        this.classes.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteClass(id) {
      if (confirm('Are you sure you want to delete this class?')) {
        this.classes = this.classes.filter(c => c.id !== id);
      }
    }
  }
}
</script>
