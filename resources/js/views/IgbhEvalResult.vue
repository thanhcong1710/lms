<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <button @click="$router.push({ name: 'igbh-evaluations' })" class="text-brand-desc hover:text-brand-text transition">
            ← Danh sách
          </button>
        </div>
        <h2 class="text-2xl font-bold text-brand-text">Báo cáo Kết Quả IG.BH</h2>
        <p class="text-sm text-brand-desc mt-1" v-if="general">
          {{ general.test_nm }} | Học sinh: <strong class="text-indigo-400">{{ general.stu_nm }}</strong>
          <span v-if="general.stu_birth_dt"> | Sinh: {{ general.stu_birth_dt }}</span>
        </p>
      </div>
      <div class="flex gap-3">
        <button @click="printReport" class="px-4 py-2 rounded-xl border border-brand-border text-brand-desc hover:bg-brand-input transition text-sm font-semibold flex items-center gap-2">
          🖨️ In báo cáo
        </button>
        <router-link :to="{ name: 'igbh-eval-form', params: { id: resultId } }" class="px-4 py-2 rounded-xl border border-indigo-500 text-indigo-400 hover:bg-indigo-500/10 transition text-sm font-semibold">
          ✏️ Sửa điểm
        </router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
      <p class="text-brand-desc text-sm">Loading report...</p>
    </div>

    <template v-else-if="general">
      <!-- ======= PAGE 1: KIẾN THỨC CƠ BẢN ======= -->
      <div class="report-page bg-white border border-gray-300 rounded-xl overflow-hidden shadow-lg" id="report-page-1">
        <!-- Page Header -->
        <div class="bg-blue-900 text-white px-8 py-5 flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold tracking-wide">KIẾN THỨC CƠ BẢN</h1>
            <p class="text-blue-200 text-xs font-medium mt-0.5">ĐÁNH GIÁ ĐẦU KỲ</p>
          </div>
          <div class="bg-white rounded-full px-5 py-2 flex items-center gap-6 text-sm">
            <span class="text-blue-900 font-semibold">
              Trình độ: <span class="text-indigo-600">{{ general.assigned_level || '—' }}</span>
            </span>
            <span class="text-blue-900 font-semibold">
              Sinh: <span>{{ general.stu_birth_dt || '—' }}</span>
            </span>
            <span class="text-blue-900 font-bold">
              {{ general.stu_nm }}
            </span>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <!-- Section: Kết quả theo từng câu -->
          <div>
            <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
              <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
              Kết quả đánh giá theo câu hỏi riêng biệt
            </h3>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-center border-collapse text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-left border-r border-blue-600">Mục</th>
                    <th v-for="i in 20" :key="i" class="px-1 py-2 border-r border-blue-600 w-9">{{ i }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200">
                    <td class="px-3 py-2 text-left font-semibold bg-blue-50 border-r border-gray-200 text-xs">Đơn vị</td>
                    <td v-for="q in curriculum" :key="'u' + q.question_no" class="px-1 py-2 border-r border-gray-100 text-xs font-semibold text-gray-700">
                      {{ q.unit || '—' }}
                    </td>
                  </tr>
                  <tr class="border-b border-gray-200">
                    <td class="px-3 py-2 text-left font-semibold bg-blue-50 border-r border-gray-200 text-xs">Đáp án</td>
                    <td v-for="q in curriculum" :key="'a' + q.question_no" class="px-1 py-2 border-r border-gray-100 text-xs">
                      {{ q.assigned_score || '—' }}
                    </td>
                  </tr>
                  <tr>
                    <td class="px-3 py-2 text-left font-semibold bg-blue-50 border-r border-gray-200 text-xs">Kết quả</td>
                    <td v-for="q in curriculum" :key="'r' + q.question_no" class="px-1 py-2 border-r border-gray-100">
                      <span v-if="q.is_correct === 'O'" class="font-bold text-red-500 text-xs">O</span>
                      <span v-else-if="q.is_correct === 'X'" class="font-bold text-sky-500 text-xs">X</span>
                      <span v-else class="text-gray-400 text-xs">—</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Section: Đánh giá theo đơn vị -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
                <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
                Đánh giá theo đơn vị
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-left">Phân loại</th>
                    <th class="px-3 py-2 text-center">Số câu đúng</th>
                    <th class="px-3 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(unit, uKey) in unitStats" :key="uKey" class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-3 py-2 text-blue-50 font-semibold text-xs" style="background:#dee5f5; color:#333;">{{ uKey }}</td>
                    <td class="px-3 py-2 text-center">
                      <span class="text-red-500 font-bold">{{ unit.correct }}</span>
                      <span class="text-gray-500"> / {{ unit.total }}</span>
                    </td>
                    <td class="px-3 py-2 text-center text-sky-500 font-semibold">{{ unit.pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Radar chart placeholder -->
            <div class="flex items-center justify-center bg-gray-50 border border-gray-200 rounded-lg p-4">
              <canvas ref="radarCanvas" width="260" height="200" class="max-w-full"></canvas>
            </div>
          </div>
        </div>

        <!-- Page Footer -->
        <div class="border-t border-gray-200 px-6 py-2 text-right">
          <p class="text-xs text-gray-400">Copyright © CMS Edu Co., Ltd. All rights reserved</p>
        </div>
      </div>

      <!-- ======= PAGE 2: TƯ DUY TOÁN HỌC ======= -->
      <div class="report-page bg-white border border-gray-300 rounded-xl overflow-hidden shadow-lg" id="report-page-2">
        <!-- Page Header -->
        <div class="bg-blue-900 text-white px-8 py-5 flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold tracking-wide">ĐÁNH GIÁ NĂNG LỰC TƯ DUY TOÁN HỌC</h1>
            <p class="text-blue-200 text-xs font-medium mt-0.5">ĐÁNH GIÁ ĐẦU KỲ</p>
          </div>
          <div class="bg-white rounded-full px-5 py-2 text-sm text-blue-900 font-bold">
            {{ general.stu_nm }}
          </div>
        </div>

        <div class="p-6 space-y-6">
          <!-- Score summary -->
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
              <p class="text-xs text-blue-600 font-semibold uppercase">Điểm kiến thức</p>
              <p class="text-3xl font-black text-blue-800">{{ general.subject_total }}</p>
            </div>
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-center">
              <p class="text-xs text-indigo-600 font-semibold uppercase">Điểm tư duy</p>
              <p class="text-3xl font-black text-indigo-800">{{ general.thinking_total }}</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
              <p class="text-xs text-gray-600 font-semibold uppercase">Tổng điểm</p>
              <p class="text-3xl font-black text-gray-800">{{ general.total_score }}</p>
            </div>
          </div>

          <!-- Thinking scores table -->
          <div>
            <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
              <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
              Kết quả từng câu tư duy
            </h3>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-center border-collapse text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-left border-r border-blue-600">Mục</th>
                    <th v-for="q in thinking" :key="'tth' + q.question_no" class="px-2 py-2 border-r border-blue-600">
                      Câu {{ q.question_no }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200">
                    <td class="px-3 py-2 text-left font-semibold bg-blue-50 border-r border-gray-200 text-xs">Điểm chuẩn</td>
                    <td v-for="q in thinking" :key="'tmax' + q.question_no" class="px-2 py-2 border-r border-gray-100 text-green-600 font-semibold">
                      {{ q.max_score }}
                    </td>
                  </tr>
                  <tr>
                    <td class="px-3 py-2 text-left font-semibold bg-blue-50 border-r border-gray-200 text-xs">Điểm thực tế</td>
                    <td v-for="q in thinking" :key="'tscore' + q.question_no" class="px-2 py-2 border-r border-gray-100 text-red-500 font-bold">
                      {{ q.assigned_score }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Evaluation criteria table -->
          <div>
            <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
              <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
              Kết quả đánh giá năng lực tư duy toán học
            </h3>
            <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
              <thead>
                <tr class="bg-blue-700 text-white">
                  <th class="px-3 py-2 text-center w-32">Điểm thực tế</th>
                  <th class="px-3 py-2 text-center w-24">Đánh giá</th>
                  <th class="px-3 py-2 text-left">Ghi chú</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(level, idx) in evaluationLevels" :key="idx"
                  :class="[
                    'border-b border-gray-100',
                    isInRange(general.thinking_total, level.min, level.max) ? 'outline outline-2 outline-red-400 bg-red-50' : 'hover:bg-gray-50'
                  ]">
                  <td class="px-3 py-3 text-center font-bold text-blue-900" style="background:#dee5f5;">
                    {{ level.range }}
                  </td>
                  <td class="px-3 py-3 text-center font-semibold">{{ level.label }}</td>
                  <td class="px-3 py-3 text-left text-xs text-gray-700 leading-relaxed">{{ level.note }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Page Footer -->
        <div class="border-t border-gray-200 px-6 py-2 text-right">
          <p class="text-xs text-gray-400">Copyright © CMS Edu Co., Ltd. All rights reserved</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

const THINKING_MAX = [5, 5, 5, 5, 5, 5, 5, 7, 7, 11];

const UNIT_LABELS = {
  A: 'A.Phép cộng và phép trừ',
  B: 'B.Các hình dạng khác nhau',
  C: 'C.Phân loại',
  D: 'D.Kiểu mẫu',
  E: 'E.Khả năng suy luận toán học',
  F: 'F.Khác'
};

const EVALUATION_LEVELS = [
  { min: 48, max: 60, range: '48~60', label: 'Ưu tú', note: 'Khả năng tư duy và giải quyết vấn đề trong phạm vi đa dạng rất ưu tú và vượt trội so với các bạn cùng lứa tuổi. Nên thử thách nhiều hơn với những bài toán khó cũng như nội dung mới trước chương trình học trên trường.' },
  { min: 32, max: 47, range: '32~47', label: 'Xuất sắc', note: 'Là học sinh có khả năng giải quyết vấn đề xuất sắc. Nên thử thách những bài toán có độ khó, hơn là việc chỉ tập trung giải những đề thi thông thường.' },
  { min: 18, max: 31, range: '18~31', label: 'Khá', note: 'Năng lực giải quyết vấn đề ở mức độ khá. Tuy nhiên, cần giải các bài toán một cách cẩn thận hơn. Cần bổ sung kiến thức ở những nội dung còn thiếu sót.' },
  { min: 9, max: 17, range: '9~17', label: 'Trung bình', note: 'Là học sinh có khả năng giải quyết vấn đề và khả năng ứng dụng chưa được tốt và chưa linh hoạt. Cần hình thành thói quen giải toán một cách tỉ mỉ dù là một bài toán đơn giản, đồng thời đa dạng hóa các dạng bài.' },
  { min: 0, max: 8, range: '0~8', label: 'Yếu', note: 'Cần đầu tư nhiều hơn nữa vào việc củng cố những kiến thức cơ bản trong toán học, bắt đầu từ việc hình thành những thói quen giải toán bằng việc hiểu được phương pháp của từng dạng đề xuất hiện trong sách giáo khoa.' }
];

export default {
  name: 'IgbhEvalResult',
  data() {
    return {
      resultId: null,
      general: null,
      curriculum: [],
      thinking: [],
      loading: false,
      evaluationLevels: EVALUATION_LEVELS
    };
  },
  computed: {
    unitStats() {
      const stats = {};
      this.curriculum.forEach(q => {
        const key = q.unit ? `${q.unit}.${UNIT_LABELS[q.unit] ? UNIT_LABELS[q.unit].split('.')[1] : q.unit}` : 'Khác';
        if (!stats[key]) stats[key] = { correct: 0, total: 0 };
        stats[key].total++;
        if (q.is_correct === 'O') stats[key].correct++;
      });
      Object.keys(stats).forEach(k => {
        stats[k].pct = Math.round((stats[k].correct / stats[k].total) * 100);
      });
      return stats;
    }
  },
  created() {
    this.resultId = this.$route.params.id;
    this.fetchDetail();
  },
  mounted() {
    // Canvas radar chart will be drawn after data loads
  },
  methods: {
    isInRange(score, min, max) {
      return score >= min && score <= max;
    },
    async fetchDetail() {
      this.loading = true;
      try {
        const res = await axios.get(`/api/igbh/results/${this.resultId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (res.data.status === 'success') {
          const { general, details } = res.data.data;
          this.general = general;

          this.curriculum = Array.from({ length: 20 }, (_, i) => {
            const q = details.find(d => d.question_type === 'curriculum' && parseInt(d.question_no) === (i + 1));
            return {
              question_no: i + 1,
              assigned_score: q?.assigned_score || '',
              unit: q?.unit || null,
              is_correct: q?.is_correct || null
            };
          });

          this.thinking = Array.from({ length: 10 }, (_, i) => {
            const q = details.find(d => d.question_type === 'thinking' && parseInt(d.question_no) === (i + 1));
            return {
              question_no: i + 1,
              assigned_score: parseInt(q?.assigned_score) || 0,
              max_score: q?.max_score || THINKING_MAX[i]
            };
          });

          // Draw radar after next tick
          this.$nextTick(() => {
            this.drawRadarChart();
          });
        }
      } catch (e) {
        console.error('Error loading report:', e);
      } finally {
        this.loading = false;
      }
    },
    drawRadarChart() {
      const canvas = this.$refs.radarCanvas;
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      const units = Object.keys(this.unitStats);
      const values = units.map(k => this.unitStats[k].pct);

      const cx = canvas.width / 2;
      const cy = canvas.height / 2;
      const radius = Math.min(cx, cy) - 30;
      const n = units.length;

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Draw grid
      for (let r = 1; r <= 5; r++) {
        ctx.beginPath();
        ctx.strokeStyle = r === 5 ? '#aab' : '#dde';
        ctx.lineWidth = 0.5;
        for (let i = 0; i <= n; i++) {
          const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
          const x = cx + (radius * r / 5) * Math.cos(angle);
          const y = cy + (radius * r / 5) * Math.sin(angle);
          i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        }
        ctx.closePath();
        ctx.stroke();
      }

      // Draw axes
      for (let i = 0; i < n; i++) {
        const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
        ctx.beginPath();
        ctx.strokeStyle = '#aab';
        ctx.lineWidth = 0.5;
        ctx.moveTo(cx, cy);
        ctx.lineTo(cx + radius * Math.cos(angle), cy + radius * Math.sin(angle));
        ctx.stroke();

        // Label
        const labelX = cx + (radius + 16) * Math.cos(angle);
        const labelY = cy + (radius + 16) * Math.sin(angle);
        ctx.fillStyle = '#334';
        ctx.font = 'bold 10px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(units[i].split('.')[0] || units[i], labelX, labelY);
      }

      // Draw data polygon
      ctx.beginPath();
      ctx.fillStyle = 'rgba(79, 103, 172, 0.3)';
      ctx.strokeStyle = '#4f67ac';
      ctx.lineWidth = 1.5;
      for (let i = 0; i < n; i++) {
        const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
        const val = (values[i] || 0) / 100;
        const x = cx + radius * val * Math.cos(angle);
        const y = cy + radius * val * Math.sin(angle);
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
      }
      ctx.closePath();
      ctx.fill();
      ctx.stroke();
    },
    printReport() {
      const content = document.getElementById('report-page-1').outerHTML +
        '<div style="page-break-before:always"></div>' +
        document.getElementById('report-page-2').outerHTML;

      const win = window.open('', '_blank');
      win.document.write(`
        <html><head>
          <title>IG.BH Report</title>
          <style>
            body { font-family: 'Palatino Linotype', serif; font-size: 11pt; color: #333; margin: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #aeb1b8; padding: 4px 6px; text-align: center; }
            .bg-blue-900 { background-color: #1e3a8a !important; color: white !important; }
            @page { size: A4; margin: 1.5cm; }
            @media print { .report-page { page-break-after: always; } }
          </style>
        </head><body>${content}</body></html>
      `);
      win.document.close();
      win.focus();
      setTimeout(() => win.print(), 500);
    }
  }
};
</script>

<style scoped>
.report-page {
  print-color-adjust: exact;
  -webkit-print-color-adjust: exact;
}
</style>
