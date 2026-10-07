<template>
  <div class="h-full flex flex-col" v-if="!loadingInit">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <router-link :to="{ name: 'igbh-class-summative-evaluations' }" class="flex items-center gap-2 text-brand-desc hover:text-indigo-400 transition font-medium bg-brand-input px-3 py-1.5 rounded-lg">
          <i class="fas fa-arrow-left"></i>
          Quay lại
        </router-link>
        <h2 class="text-xl font-bold text-brand-text">Nhập điểm đánh giá cuối kỳ lớp: <span class="text-indigo-400">{{ sessionInfo.class_nm }}</span></h2>
        <span :class="sessionInfo.status === 'completed' ? 'text-emerald-400 bg-emerald-400/10' : 'text-amber-400 bg-amber-400/10'" class="px-2.5 py-1 rounded-lg text-xs font-medium ml-2">
          {{ sessionInfo.status === 'completed' ? 'Đã hoàn thành' : 'Đang nhập' }}
        </span>
      </div>
      
      <div class="flex items-center gap-3">
        <button v-if="sessionInfo.status === 'draft'" @click="updateStatus('completed')" 
          class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-medium shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
          <i class="fas fa-check-circle"></i>
          <span>Chốt sổ lớp (Hoàn thành)</span>
        </button>
        <button v-else @click="updateStatus('draft')" 
          class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
          <i class="fas fa-undo"></i>
          <span>Mở lại chấm điểm</span>
        </button>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="flex flex-col md:flex-row gap-4 flex-1 overflow-hidden">
      <!-- Left Sidebar: Student List -->
      <div class="w-full md:w-64 flex flex-col bg-brand-surface border border-brand-border/50 rounded-2xl overflow-hidden shrink-0">
        <div class="p-3 bg-brand-input/30 border-b border-brand-border/50">
          <h3 class="font-bold text-brand-text">Danh sách học sinh</h3>
          <p class="text-xs text-brand-desc mt-1">{{ students.length }} học sinh</p>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
          <button v-for="stu in students" :key="stu.id" 
            @click="selectStudent(stu)"
            :class="['w-full text-left px-3 py-2.5 rounded-xl transition-all flex items-center justify-between', 
              selectedStudent && selectedStudent.id === stu.id 
                ? 'bg-indigo-600/10 text-indigo-400 font-medium' 
                : 'text-brand-desc hover:bg-brand-input hover:text-brand-text'
            ]">
            <span class="truncate">{{ stu.stu_nm }}</span>
            <i class="fas fa-chevron-right text-[10px] opacity-50" v-if="selectedStudent && selectedStudent.id === stu.id"></i>
          </button>
        </div>
      </div>

      <!-- Right Content: Form -->
      <div class="flex-1 bg-brand-surface border border-brand-border/50 rounded-2xl overflow-y-auto relative">
        <div v-if="!selectedStudent" class="absolute inset-0 flex flex-col items-center justify-center text-brand-desc/60">
          <i class="fas fa-user-graduate text-5xl mb-3"></i>
          <p>Chọn một học sinh từ danh sách để bắt đầu nhập điểm</p>
        </div>

        <div v-else class="p-4 md:p-6 w-full max-w-full overflow-x-auto">
          <!-- Form Component embedded -->
          <div v-if="loadingForm" class="flex justify-center py-10">
            <i class="fas fa-spinner fa-spin text-indigo-500 text-3xl"></i>
          </div>
          
          <div v-else-if="formData" class="bg-white text-black p-4 md:p-6 font-sans shadow-xl border border-gray-200 rounded-xl min-w-[800px]">
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
                        <input type="number" v-model="formData.subjective_data[n-1].concept" @input="limitInput(formData.subjective_data[n-1], 'concept', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2">
                        <input type="number" v-model="formData.subjective_data[n-1].strategy" @input="limitInput(formData.subjective_data[n-1], 'strategy', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2">
                        <input type="number" v-model="formData.subjective_data[n-1].calculation" @input="limitInput(formData.subjective_data[n-1], 'calculation', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                      </td>
                      <td class="border border-gray-300 p-2">
                        <input type="number" v-model="formData.subjective_data[n-1].expression" @input="limitInput(formData.subjective_data[n-1], 'expression', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
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
                      <input type="number" v-model="formData.subjective_data[4].concept" @input="limitInput(formData.subjective_data[4], 'concept', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2">
                      <input type="number" v-model="formData.subjective_data[4].strategy" @input="limitInput(formData.subjective_data[4], 'strategy', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2">
                      <input type="number" v-model="formData.subjective_data[4].calculation" @input="limitInput(formData.subjective_data[4], 'calculation', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <td class="border border-gray-300 p-2">
                      <input type="number" v-model="formData.subjective_data[4].expression" @input="limitInput(formData.subjective_data[4], 'expression', 1, 6)" min="1" max="6" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
                    </td>
                    <!-- Inputs 6-17 -->
                    <template v-for="(wd, index) in formData.weekly_data" :key="'inw_'+index">
                      <td class="border border-gray-300 p-2">
                        <input type="number" v-model="wd.workbook" @input="limitInput(wd, 'workbook', 1, wd.max_score)" :min="1" :max="wd.max_score" class="w-[50px] text-center border border-gray-300 rounded-sm p-1.5 focus:border-indigo-500 focus:outline-none transition-colors">
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
              <button @click="saveForm" :disabled="saving" class="px-8 py-2 bg-[#1ba494] hover:bg-[#158779] text-white font-medium rounded shadow-sm transition disabled:opacity-50">
                <i class="fas fa-spinner fa-spin mr-2" v-if="saving"></i>
                Lưu điểm học sinh này
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="h-full flex items-center justify-center">
    <i class="fas fa-spinner fa-spin text-indigo-500 text-4xl"></i>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

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
      } catch (error) {
        console.error(error);
        Swal.fire('Lỗi', 'Không thể tải dữ liệu lớp', 'error');
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
        Swal.fire('Lỗi', 'Không thể tải dữ liệu học sinh.', 'error');
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
        
        Swal.fire({
          icon: 'success',
          title: 'Lưu thành công!',
          timer: 1000,
          showConfirmButton: false
        });
        
        if (currentIndex !== -1 && currentIndex < this.students.length - 1) {
          // Go to next student
          setTimeout(() => {
            this.selectStudent(this.students[currentIndex + 1]);
          }, 1000);
        }
        
      } catch (error) {
        console.error("Error saving form", error);
        Swal.fire('Lỗi', 'Có lỗi xảy ra khi lưu.', 'error');
      } finally {
        this.saving = false;
      }
    },
    async updateStatus(newStatus) {
      const id = this.$route.params.id;
      
      const confirmText = newStatus === 'completed' 
        ? 'Khi hoàn thành, điểm của tất cả học sinh trong lớp sẽ được hiển thị ở màn Kết quả. Bạn chắc chắn chứ?'
        : 'Mở lại trạng thái sẽ cho phép bạn tiếp tục chỉnh sửa. Bạn chắc chắn chứ?';
        
      const result = await Swal.fire({
        title: 'Cập nhật trạng thái?',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
      });

      if (result.isConfirmed) {
        try {
          await axios.post(`/api/igbh/summative-class/update-status/${id}`, { status: newStatus });
          this.sessionInfo.status = newStatus;
          Swal.fire({
            icon: 'success',
            title: 'Thành công',
            timer: 1500,
            showConfirmButton: false
          });
        } catch (error) {
          Swal.fire('Lỗi', 'Không thể cập nhật trạng thái', 'error');
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
