<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <router-link :to="{ name: 'igbh-weekly-evaluations' }" class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center gap-1 mb-2 transition">
          <span>&larr;</span> Back to Weekly Evaluations
        </router-link>
        <h2 class="text-2xl font-bold text-brand-text">Nhập Điểm Tuần - {{ evaluation?.eachCdNm }}</h2>
        <p class="text-sm text-brand-desc">Lớp: <span class="font-bold text-indigo-400">{{ evaluation?.classNm }}</span> | Bài Test: <span class="font-bold text-brand-text">{{ evaluation?.test_nm }}</span></p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <button 
          @click="saveGrades" 
          :disabled="saving"
          class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition text-sm flex items-center gap-2 shadow-lg shadow-indigo-600/30 disabled:opacity-50"
        >
          <span v-if="saving" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ saving ? 'Đang lưu...' : 'Lưu Điểm' }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Loading students and grades...</p>
    </div>

    <div v-else class="bg-brand-card/20 border border-brand-border rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap min-w-max">
          <thead>
            <tr class="bg-brand-header border-b border-brand-border text-xs font-semibold text-brand-desc uppercase">
              <th class="px-4 py-3 sticky left-0 bg-brand-header z-10 w-48 border-r border-brand-border">Học sinh</th>
              <th class="px-4 py-3 text-center">Sách BT (1-20)</th>
              <th class="px-4 py-3 text-center" title="Khả năng lắng nghe">Lắng nghe (1-5)</th>
              <th class="px-4 py-3 text-center" title="Tham gia bài học">Tham gia (1-5)</th>
              <th class="px-4 py-3 text-center" title="Khả năng thể hiện">Thể hiện (1-5)</th>
              <th class="px-4 py-3 text-center" title="Sự hợp tác">Hợp tác (1-5)</th>
              <th class="px-4 py-3 text-center" title="Kỹ năng cơ bản">Kỹ năng (1-5)</th>
              <th class="px-4 py-3 text-center" title="Khả năng lãnh đạo">Lãnh đạo (1-5)</th>
              <th class="px-4 py-3 text-center" title="Khả năng toán học">Toán học (1-5)</th>
              <th class="px-4 py-3 text-center" title="Tính sáng tạo">Sáng tạo (1-5)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-brand-border text-sm">
            <tr v-for="(std, index) in students" :key="std.stu_seq" class="hover:bg-brand-card/40 transition">
              <td class="px-4 py-3 font-semibold text-indigo-400 sticky left-0 bg-brand-card z-10 border-r border-brand-border">
                {{ std.stu_nm }}
              </td>
              <td class="px-4 py-2">
                <input type="number" min="0" max="20" v-model.number="std.workbook" class="w-20 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.attd_listen" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.attd_join" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.attd_express" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.attd_coop" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.detect_normal" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.detect_leadersh" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.detect_math" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
              <td class="px-4 py-2">
                <input type="number" min="1" max="5" v-model.number="std.detect_creative" class="w-16 px-2 py-1.5 text-center rounded bg-brand-input border border-brand-border text-brand-text focus:border-indigo-500 focus:outline-none transition mx-auto block">
              </td>
            </tr>
            <tr v-if="students.length === 0">
              <td colspan="10" class="px-6 py-12 text-center text-brand-desc">Không có học sinh trong lớp này.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgbhWeeklyEvalForm',
  data() {
    return {
      evaluation: null,
      students: [],
      loading: true,
      saving: false,
    }
  },
  async created() {
    await this.fetchDetails();
  },
  methods: {
    async fetchDetails() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`/api/igbh/weekly/results/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (response.data) {
          this.evaluation = response.data.evaluation;
          this.students = response.data.students;
        }
      } catch (error) {
        console.error("Error fetching weekly details", error);
        alert("Lỗi khi tải thông tin. Vui lòng quay lại.");
      } finally {
        this.loading = false;
      }
    },
    async saveGrades() {
      this.saving = true;
      try {
        const id = this.$route.params.id;
        
        // Basic validation
        let isValid = true;
        for (let s of this.students) {
          if (s.workbook < 0 || s.workbook > 20) isValid = false;
          if (s.attd_listen < 1 || s.attd_listen > 5) isValid = false;
          if (s.attd_join < 1 || s.attd_join > 5) isValid = false;
          if (s.attd_express < 1 || s.attd_express > 5) isValid = false;
          if (s.attd_coop < 1 || s.attd_coop > 5) isValid = false;
          if (s.detect_normal < 1 || s.detect_normal > 5) isValid = false;
          if (s.detect_leadersh < 1 || s.detect_leadersh > 5) isValid = false;
          if (s.detect_math < 1 || s.detect_math > 5) isValid = false;
          if (s.detect_creative < 1 || s.detect_creative > 5) isValid = false;
        }

        if (!isValid) {
          alert("Vui lòng nhập điểm hợp lệ. Sách BT (0-20), Các tiêu chí khác (1-5).");
          this.saving = false;
          return;
        }

        const response = await axios.post(`/api/igbh/weekly/results/${id}/grade`, {
          students: this.students
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (response.status === 200) {
          alert("Lưu điểm thành công!");
          this.$router.push({ name: 'igbh-weekly-evaluations' });
        }
      } catch (error) {
        console.error("Error saving grades", error);
        alert("Có lỗi xảy ra khi lưu điểm!");
      } finally {
        this.saving = false;
      }
    }
  }
}
</script>

<style scoped>
/* To keep sticky columns visible over rows */
th.sticky {
  box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
}
td.sticky {
  box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
}
/* Hide number arrows for clean UI */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
