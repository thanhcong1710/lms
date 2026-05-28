<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">Grade UCREA Assessment</h2>
        <p class="text-sm text-brand-desc">Evaluate the student based on standard rubrics</p>
      </div>
      <div>
        <router-link :to="{ name: 'ucrea-evaluations' }" class="px-4 py-2 rounded-lg border border-brand-border text-brand-desc hover:text-brand-text hover:bg-brand-input transition text-sm font-semibold">
          Back to List
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Loading test data...</p>
    </div>

    <div v-else-if="result" class="space-y-6">
      
      <!-- General Info -->
      <div class="bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm flex flex-wrap gap-6 items-center justify-between">
        <div class="flex gap-6 flex-wrap">
            <div>
              <p class="text-xs text-brand-desc uppercase">Student Name</p>
              <p class="font-semibold text-brand-text">{{ result.stu_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Test Name</p>
              <p class="font-medium text-brand-text">{{ result.test_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Level</p>
              <p class="text-brand-text">{{ result.level_cd_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Teacher</p>
              <p class="text-brand-text">{{ result.memb_nm }}</p>
            </div>
        </div>
        
        <div>
           <button @click="submitGrades" :disabled="submitting" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition disabled:opacity-50">
             {{ submitting ? 'Saving...' : 'Submit Evaluation' }}
           </button>
        </div>
      </div>

      <!-- Evaluation Form -->
      <div class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-brand-border bg-brand-header">
          <h3 class="text-lg font-bold text-brand-text">Rubric Scoring</h3>
          <p class="text-xs text-brand-desc mt-1">Select the appropriate level (S, A, B, C) for each criteria.</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[600px]">
            <thead>
              <tr class="border-b border-brand-border bg-brand-input text-xs font-semibold text-brand-desc uppercase">
                <th class="px-6 py-4">No.</th>
                <th class="px-6 py-4">Category</th>
                <th class="px-6 py-4">Rubric / Skill</th>
                <th class="px-6 py-4 text-center">
                  <span class="text-fuchsia-600">S</span> <br/><span class="text-[10px] font-normal text-brand-desc">(Vượt trội)</span>
                </th>
                <th class="px-6 py-4 text-center">
                  <span class="text-emerald-600">A</span> <br/><span class="text-[10px] font-normal text-brand-desc">(Rất tốt)</span>
                </th>
                <th class="px-6 py-4 text-center">
                  <span class="text-indigo-600">B</span> <br/><span class="text-[10px] font-normal text-brand-desc">(Tốt)</span>
                </th>
                <th class="px-6 py-4 text-center">
                  <span class="text-amber-600">C</span> <br/><span class="text-[10px] font-normal text-brand-desc">(Trung bình)</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-brand-border text-sm text-brand-text">
              <tr v-for="(item, index) in rubrics" :key="index" class="hover:bg-brand-card/40 transition">
                <td class="px-6 py-4 text-brand-desc">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                   <p class="font-medium">{{ item.main }}</p>
                   <p class="text-xs text-brand-desc">{{ item.sub }}</p>
                </td>
                <td class="px-6 py-4 font-semibold">{{ item.name }}</td>
                
                <td class="px-6 py-4 text-center">
                  <label class="cursor-pointer inline-flex items-center justify-center relative">
                    <input type="radio" :name="'rubric_'+index" value="S" v-model="item.score" class="sr-only peer">
                    <div class="w-6 h-6 rounded-full border-2 border-brand-border peer-checked:border-fuchsia-500 peer-checked:bg-fuchsia-500 transition flex items-center justify-center">
                       <svg v-if="item.score === 'S'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                  </label>
                </td>
                
                <td class="px-6 py-4 text-center">
                  <label class="cursor-pointer inline-flex items-center justify-center relative">
                    <input type="radio" :name="'rubric_'+index" value="A" v-model="item.score" class="sr-only peer">
                    <div class="w-6 h-6 rounded-full border-2 border-brand-border peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition flex items-center justify-center">
                       <svg v-if="item.score === 'A'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                  </label>
                </td>
                
                <td class="px-6 py-4 text-center">
                  <label class="cursor-pointer inline-flex items-center justify-center relative">
                    <input type="radio" :name="'rubric_'+index" value="B" v-model="item.score" class="sr-only peer">
                    <div class="w-6 h-6 rounded-full border-2 border-brand-border peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition flex items-center justify-center">
                       <svg v-if="item.score === 'B'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                  </label>
                </td>
                
                <td class="px-6 py-4 text-center">
                  <label class="cursor-pointer inline-flex items-center justify-center relative">
                    <input type="radio" :name="'rubric_'+index" value="C" v-model="item.score" class="sr-only peer">
                    <div class="w-6 h-6 rounded-full border-2 border-brand-border peer-checked:border-amber-500 peer-checked:bg-amber-500 transition flex items-center justify-center">
                       <svg v-if="item.score === 'C'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
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
      // Standard template based on extracted LMS data
      rubrics: [
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy cơ bản', name: 'Năng lực chú ý', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy cơ bản', name: 'Năng lực quan sát', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy cơ bản', name: 'Năng lực ghi nhớ', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy logic', name: 'Hiểu biết', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy logic', name: 'Ứng dụng', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy logic', name: 'Phân tích', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy logic', name: 'Tổng hợp', score: null },
        { main: 'Kiến thức', sub: 'Tư duy toán học', name: 'Số và tính toán', score: null },
        { main: 'Kiến thức', sub: 'Tư duy toán học', name: 'Hình học không gian', score: null },
        { main: 'Kiến thức', sub: 'Tư duy toán học', name: 'Đo lường', score: null },
        { main: 'Kiến thức', sub: 'Tư duy toán học', name: 'Kiểu mẫu', score: null },
        { main: 'Kiến thức', sub: 'Tư duy toán học', name: 'Dữ liệu', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy sáng tạo', name: 'Sự trôi chảy', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy sáng tạo', name: 'Tính linh hoạt', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy sáng tạo', name: 'Tính độc đáo', score: null },
        { main: 'Khả năng suy nghĩ', sub: 'Tư duy sáng tạo', name: 'Tính chính xác', score: null }
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
        // Reusing the detail endpoint to get general info
        const response = await axios.get(`/api/ucrea/results/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (response.data.status === 'success') {
          this.result = response.data.data.general;
        }
      } catch (error) {
        console.error("Error fetching UCREA test info", error);
      } finally {
        this.loading = false;
      }
    },
    async submitGrades() {
      // Validate all answered
      const unanswered = this.rubrics.filter(r => !r.score);
      if (unanswered.length > 0) {
        alert("Please complete all rubric scores before submitting!");
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
          alert('Evaluation submitted successfully!');
          this.$router.push({ name: 'ucrea-evaluations' });
        }
      } catch (error) {
        alert("Failed to submit grades. Please try again.");
        console.error(error);
      } finally {
        this.submitting = false;
      }
    }
  }
}
</script>
