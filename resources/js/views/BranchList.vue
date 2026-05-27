<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-white">Branches (Centers)</h2>
        <p class="text-sm text-gray-400">View and manage LMS branch settings</p>
      </div>
      <button @click="openModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition duration-150 shadow-lg shadow-indigo-600/20">
        + Add Branch
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-[#0d1527]/30 border border-gray-800 p-4 rounded-xl flex items-center justify-between">
      <input type="text" v-model="search" placeholder="Search branches by name, LMS ID..." class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
    </div>

    <!-- Branches Table -->
    <div class="overflow-x-auto bg-[#0d1527]/20 border border-gray-800 rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-gray-800 bg-gray-900/40 text-xs font-semibold text-gray-400 uppercase">
            <th class="px-6 py-4">Name</th>
            <th class="px-6 py-4">LMS ID</th>
            <th class="px-6 py-4">Email</th>
            <th class="px-6 py-4">Hotline</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800 text-sm text-gray-300">
          <tr v-for="branch in filteredBranches" :key="branch.id" class="hover:bg-gray-800/20 transition duration-150">
            <td class="px-6 py-4 font-medium text-white">{{ branch.name }}</td>
            <td class="px-6 py-4 font-mono text-indigo-400">{{ branch.id_lms || 'N/A' }}</td>
            <td class="px-6 py-4">{{ branch.email || 'N/A' }}</td>
            <td class="px-6 py-4">{{ branch.hotline || 'N/A' }}</td>
            <td class="px-6 py-4">
              <span :class="branch.status === 'US001' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ branch.status === 'US001' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(branch)" class="text-sm text-indigo-400 hover:text-indigo-300 transition font-medium">Edit</button>
              <button @click="deleteBranch(branch.id)" class="text-sm text-red-400 hover:text-red-300 transition font-medium">Delete</button>
            </td>
          </tr>
          <tr v-if="filteredBranches.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No branches found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-[#0d1527] border border-gray-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-white">{{ editingId ? 'Edit Branch' : 'Add New Branch' }}</h3>

        <form @submit.prevent="saveBranch" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Branch Name</label>
            <input type="text" v-model="form.name" required class="w-full px-4 py-2.5 rounded-xl bg-gray-900 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">LMS ID</label>
            <input type="text" v-model="form.id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-gray-900 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Email</label>
              <input type="email" v-model="form.email" class="w-full px-4 py-2.5 rounded-xl bg-gray-900 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Hotline</label>
              <input type="text" v-model="form.hotline" class="w-full px-4 py-2.5 rounded-xl bg-gray-900 border border-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-indigo-500 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase mb-2">Status</label>
            <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl bg-gray-900 border border-gray-800 text-white focus:outline-none focus:border-indigo-500 text-sm">
              <option value="US001">Active</option>
              <option value="US002">Inactive</option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
            <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800 text-sm transition">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition shadow-lg shadow-indigo-600/20">Save</button>
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
      branches: [
        { id: 1, name: 'CMS Phan Xích Long', id_lms: 'HCM_PXL', email: 'pxl@cmsedu.vn', hotline: '1900 633 979', status: 'US001' },
        { id: 2, name: 'CMS Nguyễn Chí Thanh', id_lms: 'HN_NCT', email: 'nct@cmsedu.vn', hotline: '1900 633 979', status: 'US001' }
      ],
      search: '',
      showModal: false,
      editingId: null,
      form: {
        name: '',
        id_lms: '',
        email: '',
        hotline: '',
        status: 'US001'
      }
    }
  },
  computed: {
    filteredBranches() {
      const q = this.search.toLowerCase();
      return this.branches.filter(b => b.name.toLowerCase().includes(q) || b.id_lms.toLowerCase().includes(q));
    }
  },
  methods: {
    openModal(branch = null) {
      if (branch) {
        this.editingId = branch.id;
        this.form = { ...branch };
      } else {
        this.editingId = null;
        this.form = { name: '', id_lms: '', email: '', hotline: '', status: 'US001' };
      }
      this.showModal = true;
    },
    saveBranch() {
      if (this.editingId) {
        const idx = this.branches.findIndex(b => b.id === this.editingId);
        this.branches[idx] = { ...this.form, id: this.editingId };
      } else {
        this.branches.push({ ...this.form, id: Date.now() });
      }
      this.showModal = false;
    },
    deleteBranch(id) {
      if (confirm('Are you sure you want to delete this branch?')) {
        this.branches = this.branches.filter(b => b.id !== id);
      }
    }
  }
}
</script>
