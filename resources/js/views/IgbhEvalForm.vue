<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <button @click="$router.push({ name: 'igbh-evaluations' })" class="text-brand-desc hover:text-brand-text transition">
            {{ $t('igbh.form.back_list') }}
          </button>
        </div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('igbh.form.title_diagnostic') }}</h2>
        <p class="text-sm text-brand-desc mt-1" v-if="general">
          {{ general.test_nm }} | {{ $t('igbh.form.student') }}: <strong class="text-indigo-400">{{ general.stu_nm }}</strong>
          <span v-if="general.stu_birth_dt"> | {{ $t('igbh.form.dob') }}: {{ general.stu_birth_dt }}</span>
          <span v-if="general.reg_name"> | {{ $t('igbh.form.teacher') }}: {{ general.reg_name }}</span>
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
      <p class="text-brand-desc text-sm">{{ $t('igbh.loading') }}</p>
    </div>

    <template v-else-if="general">
      <!-- Thông tin chung -->
      <div class="bg-brand-card/30 border border-brand-border rounded-xl p-5">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="space-y-1">
            <label class="text-xs text-brand-desc uppercase font-semibold">{{ $t('igbh.form.test_date') }}</label>
            <input
              type="date"
              v-model="form.eval_dt"
              class="w-full px-3 py-2 rounded-lg bg-brand-input border border-brand-border text-brand-text text-sm focus:outline-none focus:border-indigo-500 transition"
            />
          </div>
          <div class="space-y-1">
            <label class="text-xs text-brand-desc uppercase font-semibold">{{ $t('igbh.form.assigned_level') }}</label>
            <input
              type="text"
              v-model="form.assigned_level"
              placeholder="VD: L1, L2..."
              class="w-full px-3 py-2 rounded-lg bg-brand-input border border-brand-border text-brand-text text-sm focus:outline-none focus:border-indigo-500 transition"
            />
          </div>
        </div>
      </div>

      <!-- Phần 1: Đáp án kiến thức cơ bản (20 câu) -->
      <div class="bg-brand-card/30 border border-brand-border rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-brand-border bg-brand-header">
          <h3 class="text-base font-bold text-brand-text">📚 {{ $t('igbh.form.curriculum_answers') }}</h3>
          <p class="text-xs text-brand-desc mt-0.5">{{ $t('igbh.form.curriculum_desc') }}</p>
        </div>
        <div class="p-5">
          <!-- Row of 20 inputs -->
          <div class="overflow-x-auto">
            <table class="w-full border-collapse text-center text-sm">
              <thead>
                <tr class="bg-brand-header">
                  <th v-for="q in curriculum" :key="q.question_no"
                    class="px-2 py-2 text-xs font-bold text-brand-desc border border-brand-border w-12">
                    {{ q.question_no }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td v-for="(q, idx) in curriculum" :key="q.question_no"
                    :class="[
                      'border border-brand-border p-1',
                      q.is_correct === 'O' ? 'bg-emerald-500/10' : (q.is_correct === 'X' ? 'bg-red-500/10' : '')
                    ]"
                  >
                    <input
                      type="text"
                      v-model="q.assigned_score"
                      maxlength="1"
                      :id="'answer_' + q.question_no"
                      @input="onCurriculumInput(idx, $event)"
                      @keyup.enter="focusNext('answer', idx + 1, curriculum.length, 'score', 0)"
                      class="w-10 h-9 text-center rounded-lg border bg-brand-input text-brand-text text-sm font-bold focus:outline-none focus:border-indigo-500 transition"
                      :class="q.is_correct === 'O' ? 'border-emerald-400' : (q.is_correct === 'X' ? 'border-red-400' : 'border-brand-border')"
                    />
                  </td>
                </tr>
                <tr>
                  <td v-for="q in curriculum" :key="'r_' + q.question_no"
                    class="border border-brand-border px-1 py-1 text-center"
                  >
                    <span v-if="q.is_correct === 'O'" class="text-emerald-400 font-bold">O</span>
                    <span v-else-if="q.is_correct === 'X'" class="text-red-400 font-bold">X</span>
                    <span v-else class="text-brand-desc">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Score summary -->
          <div class="mt-3 flex items-center gap-4 text-sm">
            <div class="text-brand-desc">
              {{ $t('igbh.form.correct_count') }} <strong class="text-emerald-400">{{ correctCount }}</strong>/{{ curriculum.length }}
            </div>
            <div class="text-brand-desc">
              {{ $t('igbh.form.knowledge_score') }} <strong class="text-indigo-400">{{ subjectTotal }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Phần 2: Tư duy toán học (10 câu) -->
      <div class="bg-brand-card/30 border border-brand-border rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b border-brand-border bg-brand-header">
          <h3 class="text-base font-bold text-brand-text">🧮 {{ $t('igbh.form.thinking_skills') }}</h3>
          <p class="text-xs text-brand-desc mt-0.5">{{ $t('igbh.form.thinking_desc') }}</p>
        </div>
        <div class="p-5">
          <div class="overflow-x-auto">
            <table class="w-full border-collapse text-center text-sm">
              <thead>
                <tr class="bg-brand-header">
                  <th v-for="q in thinking" :key="'th_' + q.question_no"
                    class="px-2 py-2 text-xs font-bold text-brand-desc border border-brand-border">
                    {{ q.question_no }} (0~{{ q.max_score }})
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td v-for="(q, idx) in thinking" :key="'tv_' + q.question_no"
                    class="border border-brand-border p-1"
                  >
                    <input
                      type="number"
                      v-model.number="q.assigned_score"
                      :min="0"
                      :max="q.max_score"
                      :id="'score_' + q.question_no"
                      @input="onThinkingInput(idx, q)"
                      class="w-12 h-9 text-center rounded-lg border border-brand-border bg-brand-input text-brand-text text-sm font-bold focus:outline-none focus:border-indigo-500 transition"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Score summary -->
          <div class="mt-3 flex items-center gap-4 text-sm">
            <div class="text-brand-desc">
              {{ $t('igbh.form.thinking_total') }} <strong class="text-indigo-400">{{ thinkingTotal }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Total Summary -->
      <div class="bg-indigo-600/10 border border-indigo-500/30 rounded-xl p-5 flex items-center gap-6">
        <div class="text-center">
          <p class="text-xs text-indigo-300 uppercase font-semibold">{{ $t('igbh.form.knowledge_score') }}</p>
          <p class="text-3xl font-black text-emerald-400">{{ subjectTotal }}</p>
        </div>
        <div class="text-2xl text-brand-desc">+</div>
        <div class="text-center">
          <p class="text-xs text-indigo-300 uppercase font-semibold">{{ $t('igbh.form.thinking_total') }}</p>
          <p class="text-3xl font-black text-indigo-400">{{ thinkingTotal }}</p>
        </div>
        <div class="text-2xl text-brand-desc">=</div>
        <div class="text-center">
          <p class="text-xs text-indigo-300 uppercase font-semibold">{{ $t('igbh.form.total_score') }}</p>
          <p class="text-3xl font-black text-brand-text">{{ subjectTotal + thinkingTotal }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-4 border-t border-brand-border">
        <button
          @click="$router.push({ name: 'igbh-evaluations' })"
          class="px-5 py-2 rounded-xl border border-brand-border text-brand-desc hover:bg-brand-input transition text-sm font-semibold"
        >
          {{ $t('igbh.form.cancel') }}
        </button>
        <div class="flex gap-3">
          <button
            v-if="general.total_score > 0"
            @click="$router.push({ name: 'igbh-eval-result', params: { id: resultId } })"
            class="px-5 py-2 rounded-xl border border-emerald-500 text-emerald-400 hover:bg-emerald-500/10 transition text-sm font-semibold"
          >
            {{ $t('igbh.form.view_result') }}
          </button>
          <button
            @click="saveGrade"
            :disabled="saving"
            class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition text-sm shadow-lg shadow-indigo-600/30 disabled:opacity-50"
          >
            {{ saving ? $t('igbh.form.saving') : $t('igbh.form.save_btn') }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgbhEvalForm',
  data() {
    return {
      resultId: null,
      general: null,
      curriculum: [],
      thinking: [],
      form: {
        eval_dt: new Date().toISOString().substr(0, 10),
        assigned_level: ''
      },
      loading: false,
      saving: false
    };
  },
  computed: {
    correctCount() {
      return this.curriculum.filter(q => q.is_correct === 'O').length;
    },
    subjectTotal() {
      return this.curriculum.reduce((sum, q) => q.is_correct === 'O' ? sum + q.point : sum, 0);
    },
    thinkingTotal() {
      return this.thinking.reduce((sum, q) => sum + (parseInt(q.assigned_score) || 0), 0);
    }
  },
  created() {
    this.resultId = this.$route.params.id;
    this.fetchDetail();
  },
  methods: {
    async fetchDetail() {
      this.loading = true;
      try {
        const res = await axios.get(`/api/igbh/results/${this.resultId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (res.data.status === 'success') {
          const { general, details, test_questions } = res.data.data;
          this.general = general;
          this.form.eval_dt = general.eval_dt || new Date().toISOString().substr(0, 10);
          this.form.assigned_level = general.assigned_level || '';

          const currConfigs = test_questions ? test_questions.filter(q => q.question_type === 'curriculum') : [];
          const currLength = currConfigs.length > 0 ? currConfigs.length : 20;

          // Build curriculum array
          this.curriculum = Array.from({ length: currLength }, (_, i) => {
            const no = i + 1;
            const q = details.find(d => d.question_type === 'curriculum' && parseInt(d.question_no) === no);
            const conf = currConfigs.find(c => c.sort_no === no);
            
            return {
              question_no: no,
              seq_id: q?.seq_id || null,
              assigned_score: q?.assigned_score || '',
              unit: conf?.sector || q?.unit || null,
              correct_answer: conf?.answer || null,
              is_correct: q?.is_correct || null,
              point: conf?.standard_point || 2
            };
          });

          const thkConfigs = test_questions ? test_questions.filter(q => q.question_type === 'thinking') : [];
          const thkLength = thkConfigs.length > 0 ? thkConfigs.length : 10;

          // Build thinking array
          this.thinking = Array.from({ length: thkLength }, (_, i) => {
            const no = i + 1;
            const q = details.find(d => d.question_type === 'thinking' && parseInt(d.question_no) === no);
            const conf = thkConfigs.find(c => c.sort_no === no);
            
            return {
              question_no: no,
              seq_id: q?.seq_id || null,
              assigned_score: parseInt(q?.assigned_score) || 0,
              max_score: conf?.standard_point || q?.max_score || 5
            };
          });
        }
      } catch (e) {
        console.error('Error loading detail:', e);
      } finally {
        this.loading = false;
      }
    },
    onCurriculumInput(idx, event) {
      const val = event.target.value;
      const q = this.curriculum[idx];
      // Only allow 1-5
      if (val && (isNaN(val) || val < 1 || val > 5)) {
        this.curriculum[idx].assigned_score = '';
        return;
      }
      // Auto-advance to next
      if (val && val.length >= 1) {
        
        // Dynamically compute correct/wrong immediately on UI if we have config
        if (q.correct_answer) {
            this.curriculum[idx].is_correct = (val == q.correct_answer) ? 'O' : 'X';
        }

        const nextIdx = idx + 1;
        if (nextIdx < this.curriculum.length) {
          this.$nextTick(() => {
            document.getElementById('answer_' + (nextIdx + 1))?.select();
          });
        } else {
          // Move to thinking section
          this.$nextTick(() => {
            document.getElementById('score_1')?.select();
          });
        }
      }
    },
    onThinkingInput(idx, q) {
      const max = q.max_score;
      if (q.assigned_score > max) {
        alert(`Giá trị vượt quá phạm vi cho phép (0~${max})`);
        this.thinking[idx].assigned_score = 0;
        return;
      }
      if (q.assigned_score < 0) {
        this.thinking[idx].assigned_score = 0;
      }
    },
    focusNext(prefix, nextIdx, maxIdx, altPrefix, altIdx) {
      if (nextIdx < maxIdx) {
        document.getElementById(`${prefix}_${nextIdx + 1}`)?.select();
      } else {
        document.getElementById(`${altPrefix}_${altIdx + 1}`)?.select();
      }
    },
    async saveGrade() {
      this.saving = true;
      try {
        const payload = {
          eval_dt: this.form.eval_dt,
          assigned_level: this.form.assigned_level,
          curriculum: this.curriculum.map(q => ({
            question_no: q.question_no,
            seq_id: q.seq_id,
            assigned_score: q.assigned_score,
            unit: q.unit
          })),
          thinking: this.thinking.map(q => ({
            question_no: q.question_no,
            seq_id: q.seq_id,
            assigned_score: q.assigned_score,
            max_score: q.max_score
          }))
        };

        const res = await axios.post(`/api/igbh/results/${this.resultId}/grade`, payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        if (res.data.status === 'success') {
          await this.fetchDetail(); // Reload to show updated is_correct
          alert('Đã lưu điểm thành công!');
        } else {
          alert('Lỗi khi lưu điểm.');
        }
      } catch (e) {
        console.error('Save grade error:', e);
        alert('Lỗi khi lưu điểm.');
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
