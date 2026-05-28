<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">UCREA Evaluation Result</h2>
        <p class="text-sm text-brand-desc">Detailed scores and report for student</p>
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
      <p class="text-sm text-brand-desc">Loading detailed result...</p>
    </div>

    <div v-else-if="result" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- General Info -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm">
          <h3 class="text-lg font-bold text-brand-text mb-4 border-b border-brand-border pb-2">Student Info</h3>
          <div class="space-y-3">
            <div>
              <p class="text-xs text-brand-desc uppercase">Student Name</p>
              <p class="font-semibold text-brand-text">{{ result.general.stu_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Test Name</p>
              <p class="font-medium text-brand-text">{{ result.general.test_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Level</p>
              <p class="text-brand-text">{{ result.general.level_cd_nm }} ({{ result.general.level_cd }})</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Teacher</p>
              <p class="text-brand-text">{{ result.general.memb_nm }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Evaluated At</p>
              <p class="text-brand-text">{{ result.general.eval_dt || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-brand-desc uppercase">Status</p>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold mt-1">
                {{ result.general.result_cd_nm }}
              </span>
            </div>
          </div>
        </div>

        <!-- Radar Stats -->
        <div class="bg-brand-card border border-brand-border rounded-xl p-6 shadow-sm">
           <h3 class="text-lg font-bold text-brand-text mb-4 border-b border-brand-border pb-2">Skill Overview</h3>
           <div class="space-y-2">
             <div v-for="skill in parsedSkillsGrade" :key="skill.skill" class="flex items-center justify-between">
               <span class="text-sm text-brand-desc">{{ skill.skill }}</span>
               <span :class="['px-2 py-0.5 rounded text-xs font-bold', getGradeColor(skill.grade)]">
                 {{ skill.grade }}
               </span>
             </div>
           </div>
        </div>
      </div>

      <!-- Detailed Rubrics -->
      <div class="lg:col-span-2">
        <div class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
          <div class="p-6 border-b border-brand-border bg-brand-header">
            <h3 class="text-lg font-bold text-brand-text">Detailed Rubric Scores</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-brand-border bg-brand-input text-xs font-semibold text-brand-desc uppercase">
                  <th class="px-4 py-3">No.</th>
                  <th class="px-4 py-3">Category</th>
                  <th class="px-4 py-3">Sub Category</th>
                  <th class="px-4 py-3">Rubric</th>
                  <th class="px-4 py-3 text-center">Score</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-brand-border text-sm text-brand-text">
                <tr v-for="rubric in result.rubrics" :key="rubric.id" class="hover:bg-brand-card/40 transition">
                  <td class="px-4 py-3 text-brand-desc">{{ rubric.question_no }}</td>
                  <td class="px-4 py-3">{{ rubric.main_category }}</td>
                  <td class="px-4 py-3">{{ rubric.sub_category }}</td>
                  <td class="px-4 py-3 font-medium">{{ rubric.rubric_name }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold text-xs">
                      {{ rubric.assigned_score }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UcreaEvalResult',
  data() {
    return {
      result: null,
      loading: true
    }
  },
  computed: {
    parsedSkillsGrade() {
      if (!this.result || !this.result.general.skills_grade) return [];
      try {
        return JSON.parse(this.result.general.skills_grade);
      } catch(e) {
        return [];
      }
    }
  },
  created() {
    this.fetchResult();
  },
  methods: {
    async fetchResult() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`/api/ucrea/results/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        if (response.data.status === 'success') {
          this.result = response.data.data;
        }
      } catch (error) {
        console.error("Error fetching UCREA result", error);
      } finally {
        this.loading = false;
      }
    },
    getGradeColor(grade) {
      const colors = {
        'S': 'bg-fuchsia-100 text-fuchsia-700',
        'A': 'bg-emerald-100 text-emerald-700',
        'B': 'bg-indigo-100 text-indigo-700',
        'C': 'bg-amber-100 text-amber-700',
        'L': 'bg-rose-100 text-rose-700',
      };
      return colors[grade] || 'bg-gray-100 text-gray-700';
    }
  }
}
</script>
