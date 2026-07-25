<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('classes.title') }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('classes.desc') }}</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        {{ $t('classes.add_btn') }}
      </button>
    </div>

    <!-- Search / Filter -->
    <div class="bg-brand-card/40 border border-brand-border p-4 rounded-xl flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex items-center gap-4 flex-wrap w-full md:w-auto">
        <input type="text" v-model="search" @input="fetchClasses(1)" :placeholder="$t('classes.search')" class="px-4 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-600 focus:outline-none focus:border-indigo-500 transition duration-150 text-sm w-72">
        
        <select v-model="selectedTypeGroup" @change="fetchClasses(1)" class="px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
          <option value="">Tất cả loại lớp</option>
          <option value="ucrea">U-CREA</option>
          <option value="igaten">BRIGHT IG</option>
          <option value="black_hold">BLACK HOLD</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-brand-card/20 border border-brand-border rounded-xl">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
            <th class="px-6 py-4 w-16">{{ $t('common.stt') }}</th>
            <th class="px-6 py-4">{{ $t('classes.cols.class_name') }}</th>
            <th class="px-6 py-4">{{ $t('classes.cols.level') }}</th>
            <th class="px-6 py-4">{{ $t('classes.cols.type') }}</th>
            <th class="px-6 py-4">Giáo viên</th>
            <th class="px-6 py-4">{{ $t('common.branch') }}</th>
            <th class="px-6 py-4">{{ $t('common.status') }}</th>
            <th class="px-6 py-4 text-right">{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-border text-sm text-brand-text/90">
          <tr v-for="(cls, index) in classes" :key="cls.id" class="hover:bg-brand-card/40 transition duration-150">
            <td class="px-6 py-4 text-brand-desc">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
            <td class="px-6 py-4 font-medium text-brand-text">{{ cls.cls_name }}</td>
            <td class="px-6 py-4">{{ cls.level_name }}</td>
            <td class="px-6 py-4">
              <span :class="clsTypeColor(cls.cls_type)" class="text-xs px-2 py-1 rounded font-semibold">
                {{ clsTypeLabel(cls.cls_type) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm">{{ teacherName(cls.teacher_id_lms) }}</td>
            <td class="px-6 py-4">{{ cls.branch_id_lms }}</td>
            <td class="px-6 py-4">
              <span :class="['US001','1'].includes(String(cls.cls_status)) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" class="px-2.5 py-1 rounded-full text-xs font-medium uppercase">
                {{ ['US001','1'].includes(String(cls.cls_status)) ? $t('common.active') : $t('common.inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openModal(cls)" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">{{ $t('common.edit') }}</button>
              <button @click="deleteClass(cls.id)" class="text-sm text-red-400 hover:text-red-300 font-medium">{{ $t('common.delete') }}</button>
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
        <h3 class="text-lg font-bold text-brand-text">{{ editingId ? $t('classes.modal_edit') : $t('classes.modal_add') }}</h3>

        <form @submit.prevent="saveClass" class="space-y-4">
          <!-- 1. Branch -->
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.branch') }}</label>
            <select v-model="form.branch_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
              <option value="">{{ $t('system.select_branch') }}</option>
              <option v-for="b in branchOptions" :key="b.id" :value="b.id_lms">{{ b.name }} ({{ b.id_lms }})</option>
            </select>
          </div>

          <!-- 2. Class Name -->
          <div>
            <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('classes.cols.class_name') }}</label>
            <input type="text" v-model="form.cls_name" required placeholder="VD: KL.UC.MON1.18.L1" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text placeholder-gray-500 focus:outline-none focus:border-indigo-500 text-sm">
          </div>

          <!-- 3. Class Type & Level (Type BEFORE Level!) -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('classes.cols.type') }}</label>
              <select v-model="form.cls_type" @change="onClsTypeChange" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="CT001">CT001 (UCREA)</option>
                <option value="CT003">CT003 (BRIGHT IG)</option>
                <option value="CT004">CT004 (BLACK HOLE)</option>
                <option value="CT002">CT002 (Demo / Khác)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('classes.cols.level') }}</label>
              <select v-model="form.level_name" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="" disabled>-- Chọn level --</option>
                <option v-for="lvl in availableLevels" :key="lvl" :value="lvl">{{ lvl }}</option>
              </select>
            </div>
          </div>

          <!-- 4. Teacher & Status -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('classes.cols.teacher_id') }}</label>
              <select v-model="form.teacher_id_lms" required class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="">{{ $t('system.select_teacher') }}</option>
                <option v-for="t in teacherOptions" :key="t.id" :value="t.id_lms">{{ t.ins_name }} ({{ t.id_lms }})</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-brand-desc uppercase mb-2">{{ $t('common.status') }}</label>
              <select v-model="form.cls_status" class="w-full px-4 py-2.5 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 text-sm">
                <option value="1">{{ $t('common.active') }} (Hoạt động)</option>
                <option value="US001">{{ $t('common.active') }} (US001)</option>
                <option value="0">Ngừng hoạt động (0)</option>
                <option value="US002">Ngừng hoạt động (US002)</option>
              </select>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end gap-3 pt-4 border-t border-brand-border">
            <button type="button" @click="showModal = false" class="btn-secondary">{{ $t('common.cancel') }}</button>
            <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? 'Đang lưu...' : $t('common.save') }}</button>
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
      selectedTypeGroup: '',
      showModal: false,
      editingId: null,
      saving: false,
      form: {
        cls_name: '',
        class_seq: '',
        level_name: 'L1',
        cls_type: 'CT001',
        teacher_id_lms: '',
        branch_id_lms: '',
        cls_status: '1'
      },
      branchOptions: [],
      teacherOptions: [],
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
    this.fetchFormOptions();
  },
  computed: {
    filteredClasses() {
      const q = this.search.toLowerCase();
      return this.classes.filter(c => (c.cls_name || '').toLowerCase().includes(q) || (c.teacher_id_lms || '').toLowerCase().includes(q));
    },
    availableLevels() {
      if (this.form.cls_type === 'CT003') {
        // i-Garten (IG)
        return ['LW', 'LJ', 'LC', 'LQ', 'LT', 'LU'];
      }
      if (this.form.cls_type === 'CT004') {
        // Bright Heading (BH)
        return [
          'LB1', 'LB2', 'LB3', 'LB4',
          'LR1', 'LR2', 'LR3', 'LR4',
          'LG1', 'LG2',
          'LP1', 'LP2', 'LP3', 'LP4'
        ];
      }
      if (this.form.cls_type === 'CT001') {
        // U-Crea (UC)
        return ['L1', 'L2', 'L3', 'L4'];
      }
      // CT002 (Demo / Khác)
      return [
        'L1', 'L2', 'L3', 'L4',
        'LW', 'LJ', 'LC', 'LQ', 'LT', 'LU',
        'LB1', 'LB2', 'LB3', 'LB4',
        'LR1', 'LR2', 'LR3', 'LR4',
        'LG1', 'LG2',
        'LP1', 'LP2', 'LP3', 'LP4'
      ];
    }
  },
  methods: {
    async fetchClasses(page = 1) {
      try {
        const response = await axios.get('/api/classes', {
          params: {
            search: this.search,
            cls_type_group: this.selectedTypeGroup,
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
    async fetchFormOptions() {
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        const [brRes, teRes] = await Promise.all([
          axios.get('/api/options/branches', { headers }),
          axios.get('/api/options/teachers', { headers })
        ]);
        this.branchOptions = brRes.data.data || [];
        this.teacherOptions = teRes.data.data || [];
      } catch (e) { console.error(e); }
    },
    onPageChange(page) {
      this.fetchClasses(page);
    },
    onPerPageChange(perPage) {
      this.pagination.per_page = perPage;
      this.fetchClasses(1);
    },
    onClsTypeChange() {
      const levels = this.availableLevels;
      if (!levels.includes(this.form.level_name)) {
        this.form.level_name = levels[0] || '';
      }
    },
    openModal(cls = null) {
      if (cls) {
        this.editingId = cls.id;
        this.form = {
          cls_name: cls.cls_name || '',
          class_seq: cls.class_seq || '',
          level_name: cls.level_name || 'L1',
          cls_type: cls.cls_type || 'CT001',
          teacher_id_lms: cls.teacher_id_lms || '',
          branch_id_lms: cls.branch_id_lms || '',
          cls_status: cls.cls_status ? String(cls.cls_status) : '1'
        };
      } else {
        this.editingId = null;
        this.form = {
          cls_name: '',
          class_seq: '',
          level_name: 'L1',
          cls_type: 'CT001',
          teacher_id_lms: '',
          branch_id_lms: '',
          cls_status: '1'
        };
      }
      this.onClsTypeChange();
      this.showModal = true;
    },
    async saveClass() {
      this.saving = true;
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
        if (this.editingId) {
          await axios.put(`/api/classes/${this.editingId}`, this.form, { headers });
        } else {
          await axios.post('/api/classes', this.form, { headers });
        }
        this.showModal = false;
        this.fetchClasses(this.pagination.current_page);
      } catch (error) {
        console.error("Error saving class", error);
        alert("Lỗi khi lưu lớp học. Vui lòng thử lại.");
      } finally {
        this.saving = false;
      }
    },
    async deleteClass(id) {
      if (confirm('Bạn có chắc chắn muốn xóa lớp học này?')) {
        try {
          const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` };
          await axios.delete(`/api/classes/${id}`, { headers });
          this.fetchClasses(this.pagination.current_page);
        } catch (error) {
          console.error("Error deleting class", error);
          alert("Lỗi khi xóa lớp học.");
        }
      }
    },
    clsTypeLabel(type) {
      const map = { CT001: 'UC', CT003: 'IG', CT004: 'BH', CT002: 'Demo' };
      return map[type] || type || '—';
    },
    clsTypeColor(type) {
      const map = {
        CT001: 'bg-violet-500/15 text-violet-300',
        CT003: 'bg-emerald-500/15 text-emerald-300',
        CT004: 'bg-amber-500/15 text-amber-300',
        CT002: 'bg-slate-500/15 text-slate-400',
      };
      return map[type] || 'bg-blue-500/10 text-blue-400';
    },
    teacherName(idLms) {
      if (!idLms) return '—';
      const t = this.teacherOptions.find(x => x.id_lms === idLms);
      return t ? t.ins_name : idLms;
    }
  }
}
</script>
