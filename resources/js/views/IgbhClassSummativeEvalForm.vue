<template>
  <div class="space-y-6 mx-auto" style="max-width: 100%; padding: 0 10px;" v-if="!loadingInit">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <router-link :to="{ name: 'igbh-class-summative-evaluations' }" class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center gap-1 mb-2 transition">
          <span>&larr;</span> Danh sách
        </router-link>
        <h2 class="text-2xl font-bold text-brand-text">Nhập điểm đánh giá cuối kỳ lớp: <span class="text-indigo-400">{{ sessionInfo.class_nm }}</span></h2>
      </div>
      
      <!-- Actions -->
      <div class="flex items-center gap-4 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide text-xs">Trạng thái lớp:</span>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" :checked="sessionInfo.status === 'completed'" @change="updateStatus($event.target.checked ? 'completed' : 'draft')" class="sr-only peer">
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
          <span class="ml-3 text-sm font-semibold" :class="sessionInfo.status === 'completed' ? 'text-emerald-500' : 'text-amber-500'">
            {{ sessionInfo.status === 'completed' ? 'Hoàn thành' : 'Đang nhập' }}
          </span>
        </label>
      </div>
    </div>

    <div v-if="loadingForm" class="flex justify-center py-10">
      <i class="fas fa-spinner fa-spin text-indigo-500 text-3xl"></i>
    </div>
    
    <div v-else-if="formData" class="bg-white text-black p-4 md:p-6 font-sans shadow-xl border border-gray-200 rounded-xl w-full">
      <!-- Student Selector Dropdown -->
      <div class="mb-6 flex items-center bg-gray-50 border border-gray-300 p-3 rounded-sm shadow-sm gap-4">
        <label class="font-bold text-gray-700 whitespace-nowrap">Chọn học sinh:</label>
        <select class="w-full max-w-md border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:border-indigo-500"
          :value="selectedStudent?.id"
          @change="e => selectStudent(students.find(s => s.id == e.target.value))">
          <option v-for="(stu, index) in students" :key="stu.id" :value="stu.id">
            {{ index + 1 }}. {{ stu.stu_nm }}
          </option>
        </select>
        <div class="text-sm text-gray-500 font-medium">
          (Học sinh {{ students.findIndex(s => s.id === selectedStudent?.id) + 1 }} / {{ students.length }})
        </div>
      </div>
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
            <div class="border border-gray-300 rounded-sm w-full overflow-x-auto">
              <table class="w-full text-center border-collapse text-[11px] sm:text-xs md:text-sm min-w-[1400px]">
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
                      <td class="border border-gray-300 p-2 text-center align-middle min-w-[80px]">
                        <input type="number" v-model="formData.subjective_data[n-1].concept" @input="limitInput(formData.subjective_data[n-1], 'concept', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2 text-center align-middle min-w-[80px]">
                        <input type="number" v-model="formData.subjective_data[n-1].strategy" @input="limitInput(formData.subjective_data[n-1], 'strategy', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2 text-center align-middle min-w-[80px]">
                        <input type="number" v-model="formData.subjective_data[n-1].calculation" @input="limitInput(formData.subjective_data[n-1], 'calculation', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2 text-center align-middle min-w-[80px]">
                        <input type="number" v-model="formData.subjective_data[n-1].expression" @input="limitInput(formData.subjective_data[n-1], 'expression', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
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
                    <td class="border border-gray-300 p-2 text-center align-middle">
                      <input type="number" v-model="formData.subjective_data[4].concept" @input="limitInput(formData.subjective_data[4], 'concept', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2 text-center align-middle">
                      <input type="number" v-model="formData.subjective_data[4].strategy" @input="limitInput(formData.subjective_data[4], 'strategy', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2 text-center align-middle">
                      <input type="number" v-model="formData.subjective_data[4].calculation" @input="limitInput(formData.subjective_data[4], 'calculation', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2 text-center align-middle">
                      <input type="number" v-model="formData.subjective_data[4].expression" @input="limitInput(formData.subjective_data[4], 'expression', 1, 6)" min="1" max="6" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <!-- Inputs 6-17 -->
                    <template v-for="(wd, index) in formData.weekly_data" :key="'inw_'+index">
                      <td class="border border-gray-300 p-2 text-center align-middle min-w-[80px]">
                        <input type="number" v-model="wd.workbook" @input="limitInput(wd, 'workbook', 1, wd.max_score)" :min="1" :max="wd.max_score" class="w-full min-w-[50px] max-w-[70px] mx-auto text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
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

            <div class="flex justify-center gap-4 mt-8">
              <button @click="saveForm" :disabled="saving" class="px-8 py-2 bg-[#1ba494] hover:bg-[#158779] text-white font-medium rounded shadow-sm transition disabled:opacity-50">
                <i class="fas fa-spinner fa-spin mr-2" v-if="saving"></i>
                Lưu điểm & Chuyển HS tiếp theo
              </button>
            </div>
          </div>
  </div>
  <div v-else class="h-full flex items-center justify-center">
    <i class="fas fa-spinner fa-spin text-indigo-500 text-4xl"></i>
  </div>
</template>

<style scoped>
th, td {
  min-width: 80px;
}
</style>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      loadingInit: true,
      sessionInfo: null,
      testInfo: null,
      students: [],
      
      selectedStudent: null,
      loadingForm: false,
      saving: false,
      formData: null
    };
  },
  async mounted() {
    await this.loadClassData();
  },
  methods: {
    async loadClassData() {
      const id = this.$route.params.id;
      this.loadingInit = true;
      try {
        const response = await axios.get(`/api/igbh/summative-class/class-data/${id}`);
        this.sessionInfo = response.data.session_info;
        this.testInfo = response.data.test_info;
        this.students = response.data.students;
        
        // Auto select first student if available
        if (this.students.length > 0) {
          this.selectStudent(this.students[0]);
        }
      } catch (error) {
        console.error(error);
        alert('Không thể tải dữ liệu lớp');
      } finally {
        this.loadingInit = false;
      }
    },
    async selectStudent(stu) {
      this.selectedStudent = stu;
      await this.fetchFormData(stu.id);
    },
    async fetchFormData(studentResultId) {
      this.loadingForm = true;
      this.formData = null;
      try {
        const response = await axios.get(`/api/igbh/summative/form-data/${studentResultId}`, {
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
        alert('Không thể tải dữ liệu học sinh.');
      } finally {
        this.loadingForm = false;
      }
    },
    async saveForm() {
      if (!this.selectedStudent || !this.formData) return;
      
      this.saving = true;
      try {
        await axios.post(`/api/igbh/summative/save/${this.selectedStudent.id}`, {
          subjective_data: this.formData.subjective_data,
          weekly_data: this.formData.weekly_data,
          teacher_comment: this.formData.teacher_comment,
          eval_dt: this.formData.student_info.eval_dt
        }, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        // Find next student to auto-select
        const currentIndex = this.students.findIndex(s => s.id === this.selectedStudent.id);
        
        alert('Lưu thành công!');
        
        if (currentIndex !== -1 && currentIndex < this.students.length - 1) {
          // Go to next student
          setTimeout(() => {
            this.selectStudent(this.students[currentIndex + 1]);
          }, 1000);
        }
        
      } catch (error) {
        console.error("Error saving form", error);
        alert('Có lỗi xảy ra khi lưu.');
      } finally {
        this.saving = false;
      }
    },
    async updateStatus(newStatus) {
      const id = this.$route.params.id;
      
      const confirmText = newStatus === 'completed' 
        ? 'Khi hoàn thành, điểm của tất cả học sinh trong lớp sẽ được hiển thị ở màn Kết quả. Bạn chắc chắn chứ?'
        : 'Mở lại trạng thái sẽ cho phép bạn tiếp tục chỉnh sửa. Bạn chắc chắn chứ?';
        
      if (confirm(confirmText)) {
        try {
          await axios.post(`/api/igbh/summative-class/update-status/${id}`, { status: newStatus });
          this.sessionInfo.status = newStatus;
          alert('Cập nhật trạng thái thành công');
        } catch (error) {
          alert('Không thể cập nhật trạng thái');
        }
      }
    },
    limitInput(obj, key, min, max) {
      if (obj[key] === '' || obj[key] === null) return;
      let val = parseInt(obj[key]);
      if (isNaN(val)) {
        obj[key] = null;
        return;
      }
      if (val < min) obj[key] = min;
      if (val > max) obj[key] = max;
    }
  }
};
</script>
