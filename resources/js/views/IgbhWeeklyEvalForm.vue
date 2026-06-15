<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Full page saving overlay -->
    <div v-if="saving" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex flex-col items-center justify-center backdrop-blur-sm transition-opacity">
      <div class="bg-white p-6 rounded-xl shadow-2xl flex flex-col items-center min-w-[200px]">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-indigo-600 mb-4"></div>
        <p class="text-gray-800 font-semibold">Đang lưu dữ liệu...</p>
        <p class="text-gray-500 text-sm mt-1">Vui lòng đợi trong giây lát</p>
      </div>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <router-link :to="{ name: 'igbh-weekly-evaluations' }" class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center gap-1 mb-2 transition">
          <span>&larr;</span> {{ $t('igbh.form.back_list') }}
        </router-link>
        <h2 class="text-2xl font-bold text-brand-text">Đánh giá hàng tuần IG.BH - {{ evaluation?.each_cd_nm }}</h2>
        <p class="text-sm text-brand-desc">{{ $t('igbh.cols.class') }}: <span class="font-bold text-indigo-400">{{ evaluation?.class_nm }}</span> | {{ $t('igbh.cols.test_name') }}: <span class="font-bold text-brand-text">{{ evaluation?.test_nm }}</span></p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <!-- Week Selector -->
        <select v-model="selectedWeek" @change="changeWeek" class="px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
          <option value="" disabled>Chọn tuần</option>
          <option v-for="w in weeks" :key="w.each_cd" :value="w.each_cd">{{ w.each_cd_nm }}</option>
        </select>
        <!-- Date Selector -->
        <input type="date" v-model="selectedDate" class="px-3 py-2 rounded-xl bg-brand-input border border-brand-border text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">

        <button 
          @click="saveGrades" 
          :disabled="saving"
          class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition text-sm flex items-center gap-2 shadow-lg shadow-indigo-600/30 disabled:opacity-50"
        >
          <span v-if="saving" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ saving ? $t('igbh.form.saving') : $t('igbh.form.save_btn') }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">{{ $t('igbh.loading') }}</p>
    </div>

    <div v-else class="bg-brand-card/20 border border-brand-border rounded-xl overflow-hidden">
      <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse table-auto text-sm">
          <thead>
            <tr class="bg-brand-header border-b border-brand-border text-xs font-semibold text-brand-desc">
              <th rowspan="2" class="px-2 py-2 bg-brand-header z-10 border-r border-brand-border align-middle text-center w-32">Học sinh Tên</th>
              <th rowspan="2" class="px-2 py-2 text-center border-r border-brand-border align-middle w-24">Sách bài tập<br>(1~20)</th>
              <th colspan="4" class="px-2 py-2 text-center border-r border-brand-border border-b">Thái độ học tập Đánh giá</th>
              <th colspan="4" class="px-2 py-2 text-center border-b border-brand-border">Đánh giá theo lĩnh vực</th>
            </tr>
            <tr class="bg-brand-header border-b border-brand-border text-xs font-semibold text-brand-desc">
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Khả năng lắng nghe<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Tham gia bài học<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Khả năng thể hiện<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Sự hợp tác<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Kỹ năng cơ bản<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Khả năng lãnh đạo<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center border-r border-brand-border leading-tight">Khả năng toán học<br>(1~5 Điểm)</th>
              <th class="px-1 py-2 text-center leading-tight">Tính sáng tạo<br>(1~5 Điểm)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-brand-border">
            <tr v-for="(std, index) in students" :key="std.stu_seq" class="hover:bg-brand-card/40 transition">
              <td class="px-2 py-3 font-semibold text-indigo-400 bg-brand-card z-10 border-r border-brand-border text-center">
                {{ std.stu_nm }}
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="0" max="20" v-model.number="std.workbook" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.attd_listen" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.attd_join" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.attd_express" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.attd_coop" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.detect_normal" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.detect_leadersh" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2 border-r border-brand-border">
                <input type="number" min="1" max="5" v-model.number="std.detect_math" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
              <td class="px-1 py-2">
                <input type="number" min="1" max="5" v-model.number="std.detect_creative" class="w-full px-1 py-1.5 text-center bg-white border border-gray-200 text-gray-800 focus:border-indigo-500 focus:outline-none transition">
              </td>
            </tr>
            <tr v-if="students.length === 0">
              <td colspan="10" class="px-6 py-12 text-center text-brand-desc">{{ $t('igbh.no_data') }}</td>
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
      weeks: [],
      selectedWeek: '',
      selectedDate: ''
    }
  },
  async created() {
    await this.fetchInitData();
    await this.fetchDetails();
  },
  methods: {
    async fetchInitData() {
      try {
        const response = await axios.get('/api/igbh/weekly/init-data', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data) {
          this.weeks = response.data.weeks;
        }
      } catch (error) {
        console.error("Error fetching init data", error);
      }
    },
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
          this.selectedWeek = this.evaluation.each_cd;
          this.selectedDate = this.evaluation.eval_ymd ? this.evaluation.eval_ymd.substring(0, 10) : new Date().toISOString().substring(0, 10);
        }
      } catch (error) {
        console.error("Error fetching weekly details", error);
        alert("Lỗi khi tải thông tin. Vui lòng quay lại.");
      } finally {
        this.loading = false;
      }
    },
    async changeWeek() {
      if (!this.evaluation) return;
      this.loading = true;
      try {
        // Find or create evaluation for the newly selected week
        const response = await axios.post('/api/igbh/weekly/results', {
          test_seq: this.evaluation.test_seq,
          class_seq: this.evaluation.class_seq,
          each_cd: this.selectedWeek,
          eval_ymd: this.selectedDate
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        if (response.data && response.data.id) {
          const newId = response.data.id;
          // Update URL silently or just route push
          if (newId != this.$route.params.id) {
            this.$router.replace({ name: 'igbh-weekly-eval-form', params: { id: newId } });
            // The route replace will NOT remount the component automatically if it's the same component. 
            // So we manually fetch new details after replacing route.
            setTimeout(() => {
              this.fetchDetails();
            }, 100);
          } else {
             this.fetchDetails();
          }
        }
      } catch (error) {
        console.error("Error changing week", error);
        alert("Lỗi khi chuyển tuần. Vui lòng thử lại.");
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
          students: this.students,
          eval_ymd: this.selectedDate
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
