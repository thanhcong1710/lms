<template>
  <div class="space-y-6 mx-auto" style="max-width: 100%; padding: 0 10px;">
    <!-- Full page saving overlay -->
    <div v-if="saving" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex flex-col items-center justify-center backdrop-blur-sm transition-opacity">
      <div class="bg-white p-6 rounded-xl shadow-2xl flex flex-col items-center min-w-[200px]">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-indigo-600 mb-4"></div>
        <p class="text-gray-800 font-semibold">Đang lưu dữ liệu...</p>
        <p class="text-gray-500 text-sm mt-1">Vui lòng đợi trong giây lát</p>
      </div>
    </div>

    <div class="flex items-center justify-between">
      <router-link :to="{ name: 'igbh-summative-evaluations' }" class="flex items-center gap-2 text-brand-desc hover:text-indigo-400 transition font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ $t('igbh.form.back_list') }}
      </router-link>
      <h2 class="text-2xl font-bold text-brand-text">Nhập điểm đánh giá cuối kỳ</h2>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="formData" class="bg-white text-black p-4 md:p-6 font-sans shadow-xl border border-gray-200 rounded-xl">
      <!-- Top Header Area -->
      <div class="flex flex-wrap border border-gray-300 rounded-sm mb-6 bg-gray-50 text-sm">
        <div class="flex-1 flex border-r border-gray-300 min-w-[200px]">
          <div class="w-24 bg-gray-100 p-2 font-semibold text-gray-700 border-r border-gray-300 flex items-center justify-center text-xs uppercase">LEVEL</div>
          <div class="p-2 font-medium text-gray-800 flex-1 flex items-center">{{ formData.student_info.test_nm }}</div>
        </div>
        <div class="flex-1 flex border-r border-gray-300 min-w-[200px]">
          <div class="w-24 bg-gray-100 p-2 font-semibold text-gray-700 border-r border-gray-300 flex items-center justify-center text-xs">Tên lớp</div>
          <div class="p-2 font-medium text-gray-800 flex-1 flex items-center">{{ formData.student_info.class_nm }}</div>
        </div>
        <div class="flex-1 flex min-w-[200px]">
          <div class="w-32 bg-gray-100 p-2 font-semibold text-gray-700 border-r border-gray-300 flex items-center justify-center text-xs">Ngày kiểm tra</div>
          <div class="p-2 font-medium text-gray-800 flex-1 flex items-center">
            <input type="date" v-model="formData.student_info.eval_dt" class="w-full border border-gray-300 rounded-sm px-2 py-1 bg-white focus:border-indigo-500 focus:outline-none">
          </div>
        </div>
      </div>

      <!-- Main Input Table -->
      <div class="border border-gray-300 rounded-sm w-full overflow-hidden">
        <table class="w-full text-center border-collapse text-[11px] sm:text-xs md:text-sm table-fixed">
          <tbody>
            <!-- Row 1 -->
            <tr class="bg-gray-50">
              <th class="border border-gray-300 p-3 font-semibold text-gray-600 w-28 sm:w-36">Học sinh Tên</th>
              <th class="border border-gray-300 p-3 font-semibold text-gray-600" colspan="16">Đánh giá Kiểu</th>
            </tr>
            <!-- Row 2 -->
            <tr class="bg-gray-50">
              <td class="border border-gray-300 p-3 font-bold text-gray-700 align-middle bg-white" rowspan="8">
                {{ formData.student_info.stu_nm }}
              </td>
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="16">Đánh giá nội dung câu hỏi tự luận</th>
            </tr>
            <!-- Row 3 -->
            <tr class="bg-white">
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">No. 1</th>
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">No. 2</th>
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">No. 3</th>
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">No. 4</th>
            </tr>
            <!-- Row 4 -->
            <tr class="bg-white">
              <template v-for="n in 4" :key="'h2_'+n">
                <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Khái niệm<br>hiểu</th>
                <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Chiến<br>lược<br>suy luận</th>
                <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Tính toán<br>thực hành</th>
                <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Diễn đạt<br>biểu hiện</th>
              </template>
            </tr>
            <!-- Row 5 (Inputs 1-4) -->
            <tr class="bg-white">
              <template v-for="n in 4" :key="'in_'+n">
                <td class="border border-gray-300 p-2">
                  <input type="number" v-model="formData.subjective_data[n-1].concept" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                </td>
                <td class="border border-gray-300 p-2">
                  <input type="number" v-model="formData.subjective_data[n-1].strategy" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                </td>
                <td class="border border-gray-300 p-2">
                  <input type="number" v-model="formData.subjective_data[n-1].calculation" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                </td>
                <td class="border border-gray-300 p-2">
                  <input type="number" v-model="formData.subjective_data[n-1].expression" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                </td>
              </template>
            </tr>
            <!-- Row 6 -->
            <tr class="bg-gray-50">
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">Đánh giá nội dung câu hỏi tự luận</th>
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="12">Thành tích theo từng bài học</th>
            </tr>
            <!-- Row 7 -->
            <tr class="bg-white">
              <th class="border border-gray-300 p-2 font-semibold text-gray-600" colspan="4">No. 5</th>
              <template v-for="n in 12" :key="'hw_'+n">
                <th class="border border-gray-300 p-1.5 font-semibold text-gray-600 leading-tight" rowspan="2">
                  No. {{ n + 5 }}<br>
                  <span class="text-gray-500 font-normal" v-if="formData.weekly_data && formData.weekly_data[n-1]">({{ formData.weekly_data[n-1].max_score }} Điểm)</span>
                </th>
              </template>
            </tr>
            <!-- Row 8 -->
            <tr class="bg-white">
              <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Khái niệm<br>hiểu</th>
              <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Chiến<br>lược<br>suy luận</th>
              <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Tính toán<br>thực hành</th>
              <th class="border border-gray-300 p-1.5 font-medium text-gray-500 leading-tight">Diễn đạt<br>biểu hiện</th>
            </tr>
            <!-- Row 9 (Inputs 5, and 6-17) -->
            <tr class="bg-white">
              <!-- Input 5 -->
              <td class="border border-gray-300 p-2">
                <input type="number" v-model="formData.subjective_data[4].concept" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
              </td>
              <td class="border border-gray-300 p-2">
                <input type="number" v-model="formData.subjective_data[4].strategy" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
              </td>
              <td class="border border-gray-300 p-2">
                <input type="number" v-model="formData.subjective_data[4].calculation" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
              </td>
              <td class="border border-gray-300 p-2">
                <input type="number" v-model="formData.subjective_data[4].expression" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
              </td>
              <!-- Inputs 6-17 -->
              <template v-for="(wd, index) in formData.weekly_data" :key="'inw_'+index">
                <td class="border border-gray-300 p-2">
                  <input type="number" v-model="wd.workbook" class="w-full min-w-[30px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Teacher Comment Section -->
      <div class="mt-8 mb-4 border border-gray-300 rounded-sm overflow-hidden">
        <div class="bg-gray-100 p-2 border-b border-gray-300 flex items-center justify-center font-semibold text-gray-700 text-sm">
          Nhận xét của giáo viên
        </div>
        <div class="p-3 bg-white">
          <textarea 
            v-model="formData.teacher_comment" 
            rows="4" 
            placeholder="최대 480자 (Nhập tối đa 480 ký tự...)"
            class="w-full border border-gray-300 rounded-sm p-3 text-sm focus:border-indigo-500 focus:outline-none resize-none"
            maxlength="480"
          ></textarea>
        </div>
      </div>

      <div class="flex justify-center gap-3 mt-8">
        <button @click="save" :disabled="saving" class="px-8 py-2 bg-[#1ba494] hover:bg-[#158779] text-white font-medium rounded shadow-sm transition disabled:opacity-50">
          Chỉnh sửa
        </button>
        <button @click="cancel" class="px-8 py-2 bg-[#5c7080] hover:bg-[#4a5a68] text-white font-medium rounded shadow-sm transition">
          Bỏ qua
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgbhSummativeEvalForm',
  data() {
    return {
      loading: true,
      saving: false,
      formData: null
    };
  },
  async created() {
    await this.fetchData();
  },
  methods: {
    async fetchData() {
      const id = this.$route.params.id;
      this.loading = true;
      try {
        const response = await axios.get(`/api/igbh/summative/form-data/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        let data = response.data;
        // Ensure 5 questions exist
        let subjective_data = [];
        for (let i = 1; i <= 5; i++) {
          let found = data.subjective_data.find(s => s.sort_no === i);
          if (found) {
            subjective_data.push(found);
          } else {
            subjective_data.push({
              sort_no: i,
              concept: null,
              strategy: null,
              calculation: null,
              expression: null
            });
          }
        }
        data.subjective_data = subjective_data;
        
        this.formData = data;
      } catch (error) {
        console.error("Error fetching form data", error);
        alert("Không thể tải dữ liệu.");
      } finally {
        this.loading = false;
      }
    },
    async save() {
      const id = this.$route.params.id;
      this.saving = true;
      try {
        await axios.post(`/api/igbh/summative/save/${id}`, {
          subjective_data: this.formData.subjective_data,
          weekly_data: this.formData.weekly_data,
          teacher_comment: this.formData.teacher_comment,
          eval_dt: this.formData.student_info.eval_dt
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        alert("Lưu thành công!");
        this.$router.push({ name: 'igbh-summative-evaluations' });
      } catch (error) {
        console.error("Error saving form", error);
        alert("Có lỗi xảy ra khi lưu.");
      } finally {
        this.saving = false;
      }
    },
    cancel() {
      this.$router.push({ name: 'igbh-summative-evaluations' });
    }
  }
}
</script>
