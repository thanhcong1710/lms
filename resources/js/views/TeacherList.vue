<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Teachers</h2>
        <p class="text-sm text-brand-desc">Manage instructor accounts and center assignments</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-brand-text text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        + Add Teacher
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" placeholder="Search teachers..." class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4">Name</th>
            <th class="px-6 py-4">LMS ID</th>
            <th class="px-6 py-4">Branch LMS ID</th>
            <th class="px-6 py-4">Email</th>
            <th class="px-6 py-4">Head Teacher</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="teacher in filteredTeachers" :key="teacher.id" class="hover:bg-gray-800/20 transition duration-150">
            <td class="px-6 py-4 font-medium text-brand-text">{{ teacher.ins_name }}</td>
            <td class="px-6 py-4 font-mono text-indigo-400">{{ teacher.id_lms }}</td>
            <td class="px-6 py-4">{{ teacher.branch_id_lms || 'N/A' }}</td>
            <td class="px-6 py-4">{{ teacher.email }}</td>
            <td class="px-6 py-4">
              <span :class="teacher.head === 'Y' ? 'text-indigo-400 bg-indigo-500/10' : 'text-brand-desc bg-gray-500/10'" class="px-2 py-0.5 rounded text-xs font-semibold">
                {{ teacher.head === 'Y' ? 'YES' : 'NO' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span :class="teacher.status === 'US001' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ teacher.status === 'US001' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(teacher)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Edit</button>
              <button @click="deleteTeacher(teacher.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-brand-card border border-brand-border rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? 'Edit Teacher' : 'Add New Teacher' }}</h3>

        <form @submit.prevent="saveTeacher" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Teacher Name</label>
            <input type="text" v-model="form.ins_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">LMS ID</label>
              <input type="text" v-model="form.id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Branch LMS ID</label>
              <input type="text" v-model="form.branch_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Email</label>
              <input type="email" v-model="form.email" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Phone</label>
              <input type="text" v-model="form.phone" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Head Teacher</label>
              <select v-model="form.head" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="N">No</option>
                <option value="Y">Yes</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">Status</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="US001">Active</option>
                <option value="US002">Inactive</option>
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
export default {
  data() {
    return {
      teachers: [
        { id: 1, ins_name: 'Teacher Test 1', id_lms: 'giaovientest', branch_id_lms: 'HCM_PXL', email: 'teacher1@cmsedu.vn', phone: '0987654321', head: 'N', status: 'US001' }
      ],
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
      }
    }
  },
  computed: {
    filteredTeachers() {
      const q = this.search.toLowerCase();
      return this.teachers.filter(t => t.ins_name.toLowerCase().includes(q) || t.id_lms.toLowerCase().includes(q));
    }
  },
  methods: {
    openModal(teacher = null) {
      if (teacher) {
        this.editingId = teacher.id;
        this.form = { ...teacher };
      } else {
        this.editingId = null;
        this.form = { ins_name: '', id_lms: '', branch_id_lms: '', email: '', phone: '', head: 'N', status: 'US001' };
      }
      this.showModal = true;
    },
    saveTeacher() {
      if (this.editingId) {
        const idx = this.teachers.findIndex(t => t.id === this.editingId);
        this.teachers[idx] = { ...this.form, id: this.editingId };
      } else {
        this.teachers.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteTeacher(id) {
      if (confirm('Are you sure you want to delete this teacher?')) {
        this.teachers = this.teachers.filter(t => t.id !== id);
      }
    }
  }
}
</script>
