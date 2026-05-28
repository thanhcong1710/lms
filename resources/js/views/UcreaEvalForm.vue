<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Đánh Giá Năng Lực UCREA</h2>
        <p class="text-sm text-brand-desc">Nhập điểm đánh giá chi tiết cho học sinh theo từng tiêu chí</p>
      </div>
      <div>
        <router-link :to="{ name: 'ucrea-evaluations' }" class="px-4 py-2 rounded-lg border border-brand-border text-brand-desc hover:text-brand-text hover:bg-brand-input transition text-sm font-semibold">
          Quay lại danh sách
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Đang tải dữ liệu đánh giá...</p>
    </div>

    <div v-else-if="result" class="space-y-6">
      
      <!-- General Info -->
      <div class="bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm flex flex-wrap gap-6 items-center justify-between">
        <div class="flex gap-6 flex-wrap">
            <div>
              <p class="text-xs text-brand-desc uppercase">Học sinh</p>
              <p class="font-semibold text-brand-text text-base mt-0.5 text-indigo-400">{{ result.stu_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Bài đánh giá</p>
              <p class="font-medium text-brand-text mt-0.5">{{ result.test_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Cấp độ</p>
              <p class="text-brand-text mt-0.5">{{ result.level_cd_nm || result.level_cd }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Lớp</p>
              <p class="text-brand-text mt-0.5">{{ result.class_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Giáo viên đánh giá</p>
              <p class="text-brand-text mt-0.5">{{ result.memb_nm }}</p>
            </div>
        </div>
        
        <div>
           <button @click="submitGrades" :disabled="submitting" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition disabled:opacity-50">
             {{ submitting ? 'Đang lưu...' : 'Lưu Kết Quả' }}
           </button>
        </div>
      </div>

      <!-- Evaluation Form -->
      <div class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-brand-border bg-brand-header">
          <h3 class="text-lg font-bold text-brand-text">Bảng Nhập Điểm</h3>
          <p class="text-xs text-brand-desc mt-1">Chọn điểm số thực tế của học sinh đạt được cho mỗi tiêu chí dưới đây.</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
              <tr class="border-b border-brand-border bg-brand-header text-xs font-semibold text-brand-desc uppercase">
                <th class="px-4 py-4 w-20 text-center">Vấn đề số</th>
                <th class="px-4 py-4 w-44">Lĩnh vực kiểm tra</th>
                <th class="px-4 py-4 w-44">Lĩnh vực chi tiết</th>
                <th class="px-4 py-4 w-24 text-center">Thời gian</th>
                <th class="px-4 py-4 text-center" colspan="5">Điểm thực tế</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm text-brand-text">
              <tr v-for="(item, index) in rubrics" :key="index" class="hover:bg-brand-card/40 transition">
                <td class="px-4 py-4 text-center text-brand-desc font-bold">{{ index + 1 }}</td>
                <td class="px-4 py-4 font-semibold text-brand-text">{{ item.main }}</td>
                <td class="px-4 py-4">
                   <p class="font-medium text-brand-text">{{ item.sub }}</p>
                   <p class="text-xs text-brand-desc mt-0.5 font-bold">{{ item.name }}</p>
                </td>
                <td class="px-4 py-4 text-center text-brand-desc">
                   {{ item.key.startsWith('sk3') ? '120' : (item.key === 'sk13' ? '0' : '60') }} giây
                </td>
                
                <!-- 5 columns for choices matching actual options -->
                <td v-for="(opt, optIdx) in item.options" :key="optIdx" class="px-2 py-4 text-center w-28 border-l border-brand-border/40">
                  <label class="cursor-pointer flex flex-col items-center justify-center gap-1.5 group select-none">
                    <input type="radio" :name="'rubric_'+index" :value="opt" v-model="item.score" class="sr-only peer">
                    <!-- Radio dot custom styling -->
                    <div class="w-5 h-5 rounded-full border-2 border-brand-border peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition flex items-center justify-center">
                       <div class="w-1.5 h-1.5 rounded-full bg-white scale-0 peer-checked:scale-100 transition duration-150"></div>
                    </div>
                    <!-- Actual score text -->
                    <span class="text-xs font-semibold text-brand-desc group-hover:text-brand-text transition mt-0.5 peer-checked:text-indigo-400">
                      {{ opt }}
                    </span>
                  </label>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UcreaEvalForm',
  data() {
    return {
      result: null,
      loading: true,
      submitting: false,
      rubrics: [
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy cơ bản', name: 'Chú ý', skillName: 'Năng lực chú ý', key: 'sk11', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy cơ bản', name: 'Quan sát', skillName: 'Năng lực quan sát', key: 'sk12', options: ['9-10', '7-8', '5-6', '3-4', '0-2'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy cơ bản', name: 'Ghi nhớ', skillName: 'Năng lực ghi nhớ', key: 'sk13', options: ['5-6', '4', '2-3', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy logic', name: 'Hiểu', skillName: 'Hiểu biết', key: 'sk21', options: ['6', '4-5', '2-3', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy logic', name: 'Áp dụng', skillName: 'Ứng dụng', key: 'sk22', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy logic', name: 'Phân tích', skillName: 'Phân tích', key: 'sk23', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy logic', name: 'Tổng hợp', skillName: 'Tổng hợp', key: 'sk24', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Kiến thức tích lũy', sub: 'Kỹ năng tư duy toán học', name: 'Con số và hoạt động', skillName: 'Số và tính toán', key: 'kn11', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Kiến thức tích lũy', sub: 'Kỹ năng tư duy toán học', name: 'Hình học', skillName: 'Hình học không gian', key: 'kn12', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Kiến thức tích lũy', sub: 'Kỹ năng tư duy toán học', name: 'Đo lường', skillName: 'Đo lường', key: 'kn13', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Kiến thức tích lũy', sub: 'Kỹ năng tư duy toán học', name: 'Kiểu Mẫu', skillName: 'Kiểu mẫu', key: 'kn14', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Kiến thức tích lũy', sub: 'Kỹ năng tư duy toán học', name: 'Dữ liệu', skillName: 'Dữ liệu', key: 'kn15', options: ['order O, 3', 'order O, 2', 'order O, 0-1', 'order X, 2-3', 'order X, 0-1'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy sáng tạo', name: 'Lưu loát', skillName: 'Sự trôi chảy', key: 'sk31', options: ['6', '4-5', '2-3', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy sáng tạo', name: 'Tính linh hoạt', skillName: 'Tính linh hoạt', key: 'sk32', options: ['8', '6-7', '3-5', '1-2', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy sáng tạo', name: 'Độc đáo', skillName: 'Tính độc đáo', key: 'sk33', options: ['4', '3', '2', '1', '0'], score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Kỹ năng tư duy sáng tạo', name: 'Xây dựng', skillName: 'Tính chính xác', key: 'sk34', options: ['4', '3', '2', '1', '0'], score: null }
      ]
    }
  },
  created() {
    this.fetchInfo();
  },
  methods: {
    async fetchInfo() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`/api/ucrea/results/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (response.data.status === 'success') {
          this.result = response.data.data.general;
          const savedRubrics = response.data.data.rubrics || [];
          if (savedRubrics.length > 0) {
            this.rubrics.forEach(rub => {
              const found = savedRubrics.find(sr => sr.rubric_name === rub.name);
              if (found) {
                rub.score = found.assigned_score;
              }
            });
          }
        }
      } catch (error) {
        console.error("Error fetching UCREA test info", error);
      } finally {
        this.loading = false;
      }
    },
    async submitGrades() {
      const unanswered = this.rubrics.filter(r => !r.score);
      if (unanswered.length > 0) {
        alert("Vui lòng hoàn thành đánh giá cho tất cả các tiêu chí!");
        return;
      }

      this.submitting = true;
      try {
        const id = this.$route.params.id;
        const payload = {
            rubrics: this.rubrics
        };
        const response = await axios.post(`/api/ucrea/results/${id}/grade`, payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        
        if (response.data.status === 'success') {
          alert('Đã cập nhật điểm đánh giá thành công!');
          this.$router.push({ name: 'ucrea-evaluations' });
        }
      } catch (error) {
        alert("Có lỗi xảy ra khi lưu điểm. Vui lòng thử lại.");
        console.error(error);
      } finally {
        this.submitting = false;
      }
    }
  }
}
</script>
