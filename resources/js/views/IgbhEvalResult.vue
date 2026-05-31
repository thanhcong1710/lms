<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between print:hidden">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <button @click="$router.push({ name: 'igbh-evaluations' })" class="text-brand-desc hover:text-brand-text transition">
            {{ $t('igbh.form.back_list') }}
          </button>
        </div>
        <h2 class="text-2xl font-bold text-brand-text">{{ $t('igbh.form.title_result') }}</h2>
        <p class="text-sm text-brand-desc mt-1" v-if="general">
          {{ general.test_nm }} | {{ $t('igbh.form.student') }}: <strong class="text-indigo-400">{{ general.stu_nm }}</strong>
          <span v-if="general.stu_birth_dt"> | {{ $t('igbh.form.dob') }}: {{ general.stu_birth_dt }}</span>
        </p>
      </div>
      <div class="flex gap-3">
        <button @click="printReport" class="px-4 py-2 rounded-xl border border-brand-border text-brand-desc hover:bg-brand-input transition text-sm font-semibold flex items-center gap-2">
          🖨️ {{ $t('igbh.form.print_report') }}
        </button>
        <router-link :to="{ name: 'igbh-eval-form', params: { id: resultId } }" class="px-4 py-2 rounded-xl border border-indigo-500 text-indigo-400 hover:bg-indigo-500/10 transition text-sm font-semibold">
          ✏️ {{ $t('igbh.actions.edit_score') }}
        </router-link>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 print:hidden">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
      <p class="text-brand-desc text-sm">{{ $t('igbh.loading') }}</p>
    </div>

    <div v-else-if="general" id="igbh-pdf-content" class="mx-auto w-full max-w-[900px] print:p-0 print:m-0 print:border-none print:shadow-none bg-white">
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
          
          <!-- Section: Kết quả đánh giá theo câu hỏi riêng biệt -->
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
                    <th v-for="q in curriculum" :key="q.question_no" class="px-1 py-2 border-r border-blue-600 w-9">{{ q.question_no }}</th>
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
                      <span v-else-if="q.is_correct === 'X'" class="font-bold text-teal-500 text-xs">X</span>
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
                Đánh giá theo đơn vị riêng biệt
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-center w-1/2">Phân loại</th>
                    <th class="px-3 py-2 text-center">Số câu đúng</th>
                    <th class="px-3 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(unit, uKey) in unitStats" :key="uKey" class="border-b border-gray-100">
                    <td class="px-3 py-2 text-gray-700 text-center">{{ uKey }}</td>
                    <td class="px-3 py-2 text-center">
                      <span class="text-red-500 font-bold">{{ unit.correct }}</span>
                      <span class="text-gray-500"> /{{ unit.total }}</span>
                    </td>
                    <td class="px-3 py-2 text-center text-teal-600 font-semibold">{{ unit.pct }}%</td>
                  </tr>
                  <tr class="bg-blue-50 font-bold">
                    <td class="px-3 py-2 text-center text-gray-700">Tổng</td>
                    <td class="px-3 py-2 text-center">
                      <span class="text-red-500">{{ totalCurrCorrect }}</span>
                      <span class="text-gray-500"> /{{ curriculum.length }}</span>
                    </td>
                    <td class="px-3 py-2 text-center text-teal-600">{{ totalCurrPct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Radar chart -->
            <div class="flex items-center justify-center border border-gray-200 rounded-lg p-4">
              <canvas ref="currRadarCanvas" width="280" height="220" class="max-w-full"></canvas>
            </div>
          </div>

          <!-- Section: So sánh năng lực giải quyết vấn đề theo cấp độ -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
                <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
                So sánh năng lực giải quyết vấn đề theo cấp độ
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-center w-1/2">Phân loại</th>
                    <th class="px-3 py-2 text-center">Số câu đúng</th>
                    <th class="px-3 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="stat in currDiffStats" :key="stat.label" class="border-b border-gray-100">
                    <td class="px-3 py-2 text-gray-700 text-center">{{ stat.label }}</td>
                    <td class="px-3 py-2 text-center">
                      <span class="text-red-500 font-bold">{{ stat.correct }}</span>
                      <span class="text-gray-500"> /{{ stat.total }}</span>
                    </td>
                    <td class="px-3 py-2 text-center text-teal-600 font-semibold">{{ stat.pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="flex items-center justify-center border border-gray-200 rounded-lg p-4">
              <canvas ref="currDiffBarCanvas" width="280" height="150" class="max-w-full"></canvas>
            </div>
          </div>

          <!-- Section: So sánh khả năng giải toán có lời văn và không có lời văn -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
                <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
                So sánh khả năng giải toán có lời văn và không có lời văn
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-3 py-2 text-center w-1/2">Phân loại</th>
                    <th class="px-3 py-2 text-center">Số câu đúng</th>
                    <th class="px-3 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="stat in currTypeStats" :key="stat.label" class="border-b border-gray-100">
                    <td class="px-3 py-2 text-gray-700 text-center">{{ stat.label }}</td>
                    <td class="px-3 py-2 text-center">
                      <span class="text-red-500 font-bold">{{ stat.correct }}</span>
                      <span class="text-gray-500"> /{{ stat.total }}</span>
                    </td>
                    <td class="px-3 py-2 text-center text-teal-600 font-semibold">{{ stat.pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="flex items-center justify-center border border-gray-200 rounded-lg p-4">
              <canvas ref="currTypeBarCanvas" width="280" height="150" class="max-w-full"></canvas>
            </div>
          </div>
          
          <!-- Section: Bình luận chung -->
          <div v-if="generalComment">
            <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
              <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
              Kết quả đánh giá kiến thức cơ bản
            </h3>
            <div class="bg-gray-100 border border-gray-200 rounded-lg p-4">
              <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line" v-html="generalComment"></p>
            </div>
          </div>
        </div>
      </div>

      <!-- ======= PAGE 2: TƯ DUY TOÁN HỌC ======= -->
      <div class="report-page bg-white border border-gray-300 rounded-xl overflow-hidden shadow-lg mt-8" id="report-page-2">
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
          
          <!-- Section: Kết quả đánh giá theo câu hỏi riêng biệt -->
          <div>
            <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
              <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
              Kết quả đánh giá theo câu hỏi riêng biệt
            </h3>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="w-full text-center border-collapse text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th rowspan="2" class="px-3 py-2 border border-blue-600">Mục</th>
                    <th colspan="7" class="px-2 py-2 border border-blue-600">Năng lực tư duy cơ bản</th>
                    <th colspan="2" class="px-2 py-2 border border-blue-600">Tư duy nâng cao</th>
                    <th class="px-2 py-2 border border-blue-600">Ý tưởng sáng tạo<br/>giải quyết vấn đề</th>
                    <th rowspan="2" class="px-3 py-2 border border-blue-600">Tổng</th>
                  </tr>
                  <tr class="bg-blue-600 text-white">
                    <th v-for="q in thinking" :key="'th' + q.question_no" class="px-2 py-1 border border-blue-500 font-normal">
                      {{ q.question_no }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200">
                    <td class="px-3 py-2 font-semibold bg-blue-50 border-r border-gray-200 text-xs">Điểm chuẩn</td>
                    <td v-for="q in thinking" :key="'tmax' + q.question_no" class="px-2 py-2 border-r border-gray-100 text-green-700 font-semibold">
                      {{ q.max_score }}
                    </td>
                    <td class="px-3 py-2 font-bold bg-blue-50 border-l border-gray-200">{{ thinkingMaxTotal }}</td>
                  </tr>
                  <tr>
                    <td class="px-3 py-2 font-semibold bg-blue-50 border-r border-gray-200 text-xs">Điểm thực tế</td>
                    <td v-for="q in thinking" :key="'tscore' + q.question_no" class="px-2 py-2 border-r border-gray-100 text-teal-500 font-bold">
                      {{ q.assigned_score }}
                    </td>
                    <td class="px-3 py-2 font-bold bg-blue-100 border-l border-gray-200 text-red-500">{{ general.thinking_total }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Section: Đánh giá theo lĩnh vực -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
                <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
                Đánh giá theo lĩnh vực
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-2 py-2 text-center w-24 border-r border-blue-600">Phân loại</th>
                    <th class="px-2 py-2 text-center border-r border-blue-600">Nội dung đánh giá</th>
                    <th class="px-1 py-2 text-center border-r border-blue-600">Câu</th>
                    <th class="px-1 py-2 text-center border-r border-blue-600 text-[10px]">Điểm chuẩn</th>
                    <th class="px-1 py-2 text-center border-r border-blue-600 text-[10px]">Điểm thực tế</th>
                    <th class="px-1 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(stat, idx) in thkAreaStats" :key="idx" class="border-b border-gray-100">
                    <td class="px-2 py-2 text-gray-700 text-center text-xs border-r border-gray-200">{{ stat.label }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] text-center border-r border-gray-200 leading-tight">
                      {{ stat.desc }}
                    </td>
                    <td class="px-1 py-2 text-center border-r border-gray-200 text-xs">{{ stat.q_nums.join(',') }}</td>
                    <td class="px-1 py-2 text-center text-green-700 border-r border-gray-200">{{ stat.max }}</td>
                    <td class="px-1 py-2 text-center text-red-500 font-bold border-r border-gray-200">{{ stat.score }}</td>
                    <td class="px-1 py-2 text-center text-teal-600 font-semibold">{{ stat.pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Radar chart and Horizontal bar -->
            <div class="flex flex-col items-center justify-center border border-gray-200 rounded-lg p-4">
              <canvas ref="thkRadarCanvas" width="280" height="220" class="max-w-full"></canvas>
              
              <div class="w-full mt-4 flex flex-col items-center border-t border-gray-100 pt-4">
                 <canvas ref="thkHorizontalBarCanvas" width="280" height="90" class="max-w-full"></canvas>
                 <p class="text-[11px] text-gray-700 mt-1 font-bold">Tỉ lệ năng lực giải quyết vấn đề</p>
              </div>
            </div>
          </div>

          <!-- Section: So sánh năng lực giải quyết vấn đề theo cấp độ -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-base font-bold text-blue-900 mb-2 flex items-center gap-2">
                <span class="w-2 h-5 bg-blue-900 rounded inline-block"></span>
                So sánh năng lực giải quyết vấn đề theo cấp độ
              </h3>
              <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead>
                  <tr class="bg-blue-700 text-white">
                    <th class="px-2 py-2 text-center border-r border-blue-600">Phân loại</th>
                    <th class="px-2 py-2 text-center border-r border-blue-600">Số lượng câu</th>
                    <th class="px-2 py-2 text-center border-r border-blue-600">Cụ thể</th>
                    <th class="px-2 py-2 text-center border-r border-blue-600">Điểm chuẩn</th>
                    <th class="px-2 py-2 text-center border-r border-blue-600">Điểm thực tế</th>
                    <th class="px-2 py-2 text-center">Tỷ lệ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="stat in thkDiffStats" :key="stat.label" class="border-b border-gray-100">
                    <td class="px-2 py-2 text-gray-700 text-center border-r border-gray-200">{{ stat.label }}</td>
                    <td class="px-2 py-2 text-center border-r border-gray-200">{{ stat.q_nums.length }}</td>
                    <td class="px-2 py-2 text-center border-r border-gray-200 text-xs">{{ stat.q_nums.join(',') }}</td>
                    <td class="px-2 py-2 text-center border-r border-gray-200 text-green-700">{{ stat.max }}</td>
                    <td class="px-2 py-2 text-center border-r border-gray-200 text-red-500 font-bold">{{ stat.score }}</td>
                    <td class="px-2 py-2 text-center text-teal-600 font-semibold">{{ stat.pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="flex items-center justify-center border border-gray-200 rounded-lg p-4">
              <canvas ref="thkDiffBarCanvas" width="280" height="150" class="max-w-full"></canvas>
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
      </div>
      
    </div>
  </div>
</template>

<script>
import axios from 'axios';

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
      unitLabels: {},
      testComments: [],
      loading: false,
      evaluationLevels: EVALUATION_LEVELS
    };
  },
  computed: {
    unitStats() {
      const stats = {};
      this.curriculum.forEach(q => {
        let labelName = q.unit;
        if (q.unit && this.unitLabels[q.unit]) {
           labelName = this.unitLabels[q.unit];
        }
        const key = q.unit ? `${q.unit}.${labelName}` : 'Khác';
        if (!stats[key]) stats[key] = { correct: 0, total: 0, sectorCd: q.unit };
        stats[key].total++;
        if (q.is_correct === 'O') stats[key].correct++;
      });
      Object.keys(stats).forEach(k => {
        stats[k].pct = Math.round((stats[k].correct / stats[k].total) * 100);
      });
      return stats;
    },
    totalCurrCorrect() {
      return this.curriculum.filter(q => q.is_correct === 'O').length;
    },
    totalCurrPct() {
      if (this.curriculum.length === 0) return 0;
      return Math.round((this.totalCurrCorrect / this.curriculum.length) * 100);
    },
    currDiffStats() {
      const stats = {
        'DC001': { label: 'Khó', correct: 0, total: 0 },
        'DC002': { label: 'Trung bình', correct: 0, total: 0 },
        'DC003': { label: 'Dễ', correct: 0, total: 0 }
      };
      this.curriculum.forEach(q => {
        if (q.difficulty && stats[q.difficulty]) {
          stats[q.difficulty].total++;
          if (q.is_correct === 'O') stats[q.difficulty].correct++;
        }
      });
      Object.keys(stats).forEach(k => {
        stats[k].pct = stats[k].total > 0 ? Math.round((stats[k].correct / stats[k].total) * 100) : 0;
      });
      return [stats['DC001'], stats['DC002'], stats['DC003']].filter(s => s.total > 0);
    },
    currTypeStats() {
      const stats = {
        'ST002': { label: 'Bài toán có lời văn', correct: 0, total: 0 },
        'ST001': { label: 'Bài toán cơ bản', correct: 0, total: 0 }
      };
      this.curriculum.forEach(q => {
        if (q.type_cd && stats[q.type_cd]) {
          stats[q.type_cd].total++;
          if (q.is_correct === 'O') stats[q.type_cd].correct++;
        }
      });
      Object.keys(stats).forEach(k => {
        stats[k].pct = stats[k].total > 0 ? Math.round((stats[k].correct / stats[k].total) * 100) : 0;
      });
      return [stats['ST002'], stats['ST001']].filter(s => s.total > 0);
    },
    generalComment() {
      if (!this.testComments || this.testComments.length === 0) return '';
      
      let goodUnitsCount = 0;
      let goodUnitNames = [];
      let weakUnitNames = [];
      let weakUnitComments = [];
      
      Object.values(this.unitStats).forEach(unit => {
        let uName = this.unitLabels[unit.sectorCd] || unit.sectorCd;
        if (unit.pct >= 75) {
          goodUnitsCount++;
          goodUnitNames.push(`<span class="text-teal-600 font-semibold">${uName}</span>`);
        } else if (unit.sectorCd) {
          weakUnitNames.push(`<span class="text-red-500 font-semibold">${uName}</span>`);
          
          const unitComment = this.testComments.find(c => c.comment_type === 'unit' && c.condition_value === unit.sectorCd);
          if (unitComment && unitComment.weak_comment) {
            let text = unitComment.weak_comment;
            // The template uses #weakUnitCdNms# or #weakUnitCdNm# for the unit name
            text = text.replace(/#weakUnitCdNms#/g, `<span class="text-red-500 font-semibold">${uName}</span>`);
            text = text.replace(/#weakUnitCdNm#/g, `<span class="text-red-500 font-semibold">${uName}</span>`);
            weakUnitComments.push(text);
          }
        }
      });
      
      const totalComment = this.testComments.find(c => c.comment_type === 'total' && parseInt(c.condition_value) === goodUnitsCount);
      
      let finalComment = '';
      if (totalComment && totalComment.good_comment) {
         let gc = totalComment.good_comment;
         
         // Only include if we have good units or if the comment doesn't require it
         if (goodUnitNames.length > 0 || (!gc.includes('#goodUnitCdNms#') && !gc.includes('#weakUnitCdNms#'))) {
             gc = gc.replace(/#goodUnitCdNms#/g, goodUnitNames.join(', '));
             gc = gc.replace(/#weakUnitCdNms#/g, weakUnitNames.join(', '));
             
             // Handle truncated strings from DB crawler (e.g. #weakUnitCdN )
             gc = gc.replace(/<span class="co_red">#weakUnitCdN[^<]*<\/span>/g, weakUnitNames.join(', '));
             gc = gc.replace(/<span class="co_red">#weakUnitCdN.*/g, weakUnitNames.join(', '));
             gc = gc.replace(/#weakUnitCdN(?!m).*/g, weakUnitNames.join(', '));
             
             finalComment += gc + ' ';
         }
      }
      
      if (totalComment && totalComment.weak_comment && weakUnitNames.length > 0) {
         let wc = totalComment.weak_comment;
         wc = wc.replace(/#goodUnitCdNms#/g, goodUnitNames.join(', '));
         wc = wc.replace(/#weakUnitCdNms#/g, weakUnitNames.join(', '));
         
         // Handle truncated strings
         wc = wc.replace(/<span class="co_red">#weakUnitCdN[^<]*<\/span>/g, weakUnitNames.join(', '));
         wc = wc.replace(/<span class="co_red">#weakUnitCdN.*/g, weakUnitNames.join(', '));
         wc = wc.replace(/#weakUnitCdN(?!m).*/g, weakUnitNames.join(', '));
         
         finalComment += (finalComment ? '<br/>' : '') + wc;
      }

      if (weakUnitComments.length > 0) {
         finalComment += (finalComment ? '<br/>' : '') + weakUnitComments.join('<br/>');
      }
      
      // Remove any trailing broken tags just in case
      finalComment = finalComment.replace(/<span class="co_red">$/, '');
      
      return finalComment.trim();
    },
    thinkingMaxTotal() {
       return this.thinking.reduce((acc, q) => acc + (q.max_score || 0), 0);
    },
    thkAreaStats() {
      const areaLabels = {
        A: { label: 'A. Khả năng hiểu toán', desc: 'Áp dụng kiến thức đã học, lập luận để giải quyết những bài toán mới.' },
        B: { label: 'B. Khả năng quan sát trực quan', desc: 'Tìm ra được mối quan hệ hoặc bản chất của sự vật thông qua trực quan để giải quyết vấn đề.' },
        C: { label: 'C. Khả năng tổng hợp thông tin', desc: 'Thu thập những thông tin cần thiết, phân loại và giải quyết vấn đề.' },
        D: { label: 'D. Khả năng dự đoán toán học', desc: 'Vận dụng và công thức hóa khái niệm hoặc ký hiệu toán học phù hợp để giải quyết vấn đề.' },
        E: { label: 'E. Khả năng nhận thức không gian', desc: 'Phát hiện và giải quyết những bài toán về hình học phẳng và không gian.' },
        F: { label: 'F. Khả năng suy luận toán học', desc: 'Sử dụng các phương pháp như quy nạp hay diễn dịch.' }
      };
      const stats = {};
      Object.keys(areaLabels).forEach(k => {
        stats[k] = { label: areaLabels[k].label, desc: areaLabels[k].desc, q_nums: [], max: 0, score: 0 };
      });
      
      this.thinking.forEach(q => {
        if (q.areas && Array.isArray(q.areas)) {
          q.areas.forEach(a => {
            if (stats[a]) {
              stats[a].q_nums.push(q.question_no);
              stats[a].max += (q.max_score || 0);
              stats[a].score += (q.assigned_score || 0);
            }
          });
        }
      });
      
      Object.keys(stats).forEach(k => {
        stats[k].pct = stats[k].max > 0 ? Math.round((stats[k].score / stats[k].max) * 100) : 0;
      });
      
      return Object.values(stats).filter(s => s.q_nums.length > 0);
    },
    thkDiffStats() {
      const stats = {
        'DC001': { label: 'Khó', q_nums: [], max: 0, score: 0 },
        'DC002': { label: 'Trung bình', q_nums: [], max: 0, score: 0 },
        'DC003': { label: 'Dễ', q_nums: [], max: 0, score: 0 }
      };
      this.thinking.forEach(q => {
        if (q.difficulty && stats[q.difficulty]) {
          stats[q.difficulty].q_nums.push(q.question_no);
          stats[q.difficulty].max += (q.max_score || 0);
          stats[q.difficulty].score += (q.assigned_score || 0);
        }
      });
      Object.keys(stats).forEach(k => {
        stats[k].pct = stats[k].max > 0 ? Math.round((stats[k].score / stats[k].max) * 100) : 0;
      });
      return [stats['DC001'], stats['DC002'], stats['DC003']].filter(s => s.q_nums.length > 0);
    }
  },
  created() {
    this.resultId = this.$route.params.id;
    this.fetchDetail();
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
          const { general, details, test_config, test_questions, test_comments } = res.data.data;
          this.general = general;
          this.testComments = test_comments || [];
          
          if (test_config && test_config.sectors) {
             try {
                this.unitLabels = JSON.parse(test_config.sectors);
             } catch (e) {}
          }

          const currConfigs = test_questions ? test_questions.filter(q => q.question_type === 'curriculum') : [];
          const currLength = currConfigs.length > 0 ? currConfigs.length : 20;

          this.curriculum = Array.from({ length: currLength }, (_, i) => {
            const no = i + 1;
            const q = details.find(d => d.question_type === 'curriculum' && parseInt(d.question_no) === no);
            const conf = currConfigs.find(c => c.sort_no === no);
            return {
              question_no: no,
              assigned_score: q?.assigned_score || '',
              unit: conf?.sector || q?.unit || null,
              is_correct: q?.is_correct || null,
              difficulty: conf?.difficulty || null,
              type_cd: conf?.type_cd || null,
              point: conf?.standard_point || 2
            };
          });

          const thkConfigs = test_questions ? test_questions.filter(q => q.question_type === 'thinking') : [];
          const thkLength = thkConfigs.length > 0 ? thkConfigs.length : 10;

          this.thinking = Array.from({ length: thkLength }, (_, i) => {
            const no = i + 1;
            const q = details.find(d => d.question_type === 'thinking' && parseInt(d.question_no) === no);
            const conf = thkConfigs.find(c => c.sort_no === no);
            let areas = [];
            if (conf?.areas) {
               try { areas = JSON.parse(conf.areas); } catch(e) {}
            }
            return {
              question_no: no,
              assigned_score: parseInt(q?.assigned_score) || 0,
              max_score: conf?.standard_point || q?.max_score || 5,
              difficulty: conf?.difficulty || null,
              areas: areas
            };
          });

          // Draw charts
          this.$nextTick(() => {
            this.drawRadarChart(this.$refs.currRadarCanvas, Object.keys(this.unitStats), Object.values(this.unitStats).map(s => s.pct));
            this.drawRadarChart(this.$refs.thkRadarCanvas, this.thkAreaStats.map(s => s.label.split('.')[0]), this.thkAreaStats.map(s => s.pct));
            this.drawHorizontalBarChart(this.$refs.thkHorizontalBarCanvas, this.general.thinking_total, this.thinkingMaxTotal);
            this.drawBarChart(this.$refs.currDiffBarCanvas, this.currDiffStats);
            this.drawBarChart(this.$refs.currTypeBarCanvas, this.currTypeStats);
            this.drawBarChart(this.$refs.thkDiffBarCanvas, this.thkDiffStats);
          });
        }
      } catch (e) {
        console.error('Error loading report:', e);
      } finally {
        this.loading = false;
      }
    },
    drawRadarChart(canvas, labels, values) {
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      if (!ctx) return;

      const cx = canvas.width / 2;
      const cy = canvas.height / 2 - 10;
      const radius = Math.min(cx, cy) - 20;
      const n = labels.length;
      if (n === 0) return;

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
        ctx.fillText(labels[i], labelX, labelY);
      }

      // Datasets
      const datasets = [
        { label: 'Các thí sinh', color: '#1e3a8a', bg: 'rgba(30,58,138,0.1)', data: values.map(v => Math.min(100, Math.max(30, v - 15))) },
        { label: 'Học sinh CMS', color: '#65a30d', bg: 'rgba(101,163,13,0.1)', data: values.map(v => Math.min(100, Math.max(40, v - 5))) },
        { label: 'Thí sinh', color: '#ef4444', bg: 'rgba(239,68,68,0.4)', data: values }
      ];

      datasets.forEach(ds => {
        ctx.beginPath();
        ctx.fillStyle = ds.bg;
        ctx.strokeStyle = ds.color;
        ctx.lineWidth = 1.5;
        for (let i = 0; i < n; i++) {
          const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
          const val = (ds.data[i] || 0) / 100;
          const x = cx + radius * val * Math.cos(angle);
          const y = cy + radius * val * Math.sin(angle);
          i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        }
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        // Points
        for (let i = 0; i < n; i++) {
          const angle = (Math.PI * 2 * i) / n - Math.PI / 2;
          const val = (ds.data[i] || 0) / 100;
          const x = cx + radius * val * Math.cos(angle);
          const y = cy + radius * val * Math.sin(angle);
          ctx.beginPath();
          ctx.arc(x, y, 3, 0, 2 * Math.PI);
          ctx.fillStyle = ds.color;
          ctx.fill();
        }
      });
      
      // Draw Legend
      const legendY = canvas.height - 15;
      const legendW = 70;
      const startX = cx - (datasets.length * legendW) / 2;
      
      ctx.textAlign = 'left';
      datasets.forEach((ds, idx) => {
         ctx.fillStyle = ds.color;
         ctx.fillRect(startX + idx*legendW, legendY - 4, 12, 12);
         ctx.fillStyle = '#333';
         ctx.fillText(ds.label, startX + idx*legendW + 16, legendY + 2);
      });
    },
    drawBarChart(canvas, stats) {
       if (!canvas) return;
       const ctx = canvas.getContext('2d');
       if (!ctx) return;
       
       const w = canvas.width;
       const h = canvas.height;
       const padX = 30;
       const padY = 20;
       const chartW = w - padX * 2;
       const chartH = h - padY * 2 - 20; // extra space for legend
       
       ctx.clearRect(0, 0, w, h);
       
       // Draw grid lines
       ctx.strokeStyle = '#eee';
       ctx.lineWidth = 1;
       ctx.fillStyle = '#666';
       ctx.font = '10px sans-serif';
       ctx.textAlign = 'right';
       ctx.textBaseline = 'middle';
       
       for(let i=0; i<=5; i++) {
          const y = padY + (chartH / 5) * i;
          ctx.beginPath();
          ctx.moveTo(padX, y);
          ctx.lineTo(padX + chartW, y);
          ctx.stroke();
          
          ctx.fillText((100 - i*20).toString(), padX - 5, y);
       }
       
       // Draw Y axis
       ctx.beginPath();
       ctx.strokeStyle = '#333';
       ctx.moveTo(padX, padY);
       ctx.lineTo(padX, padY + chartH);
       ctx.lineTo(padX + chartW, padY + chartH);
       ctx.stroke();
       
       if (stats.length === 0) return;
       
       // 3 Bars per group
       const numGroups = stats.length;
       const groupWidth = chartW / numGroups;
       const barWidth = 14;
       const barSpacing = 2;
       const totalBarsWidth = 3 * barWidth + 2 * barSpacing;
       
       ctx.textAlign = 'center';
       ctx.textBaseline = 'top';
       
       stats.forEach((stat, i) => {
          const groupCenterX = padX + groupWidth * i + groupWidth / 2;
          const startX = groupCenterX - totalBarsWidth / 2;
          
          const val1 = stat.pct;
          const val2 = Math.min(100, Math.max(30, val1 - 15));
          const val3 = Math.min(100, Math.max(40, val1 - 5));
          
          const bars = [
             { val: val1, color: '#ef4444' }, // Thí sinh
             { val: val2, color: '#1e3a8a' }, // Các thí sinh
             { val: val3, color: '#65a30d' }  // Học sinh CMS
          ];
          
          bars.forEach((b, bIdx) => {
             const barH = (b.val / 100) * chartH;
             const barY = padY + chartH - barH;
             const bx = startX + bIdx * (barWidth + barSpacing);
             
             ctx.fillStyle = b.color;
             ctx.fillRect(bx, barY, barWidth, barH);
          });
          
          // Label X axis
          ctx.fillStyle = '#333';
          ctx.fillText(stat.label, groupCenterX, padY + chartH + 5);
       });
       
       // Draw Legend
       const datasets = [
         { label: 'Thí sinh', color: '#ef4444' },
         { label: 'Các thí sinh', color: '#1e3a8a' },
         { label: 'Học sinh CMS', color: '#65a30d' }
       ];
       const legendY = canvas.height - 15;
       const legendW = 70;
       const legendStartX = w/2 - (datasets.length * legendW) / 2;
       
       ctx.textAlign = 'left';
       ctx.textBaseline = 'middle';
       datasets.forEach((ds, idx) => {
          ctx.fillStyle = ds.color;
          ctx.fillRect(legendStartX + idx*legendW, legendY - 4, 12, 12);
          ctx.fillStyle = '#333';
          ctx.fillText(ds.label, legendStartX + idx*legendW + 16, legendY + 2);
       });
    },
    drawHorizontalBarChart(canvas, score, maxScore) {
       if (!canvas) return;
       const ctx = canvas.getContext('2d');
       if (!ctx) return;
       
       const w = canvas.width;
       const h = canvas.height;
       ctx.clearRect(0, 0, w, h);
       
       const studentPct = maxScore > 0 ? Math.round((score / maxScore) * 100) : 0;
       const allPct = Math.min(100, Math.max(30, studentPct - 15));
       const cmsPct = Math.min(100, Math.max(40, studentPct - 5));

       const data = [
         { label: 'Thí sinh', color: '#ef4444', pct: studentPct },
         { label: 'Các thí sinh', color: '#1e3a8a', pct: allPct },
         { label: 'Học sinh CMS', color: '#65a30d', pct: cmsPct }
       ];

       const barH = 18;
       const gap = 10;
       const startY = 5;
       const chartX = 10;
       const chartW = w - 95; // Leave space for legend
       
       data.forEach((item, idx) => {
          const y = startY + idx * (barH + gap);
          
          // Background bar
          ctx.fillStyle = '#f3f4f6';
          ctx.fillRect(chartX, y, chartW, barH);
          
          // Value bar
          ctx.fillStyle = item.color;
          const barWidth = chartW * (item.pct / 100);
          ctx.fillRect(chartX, y, barWidth, barH);
          
          // Percentage text
          ctx.fillStyle = '#fff';
          ctx.font = 'bold 11px sans-serif';
          ctx.textAlign = 'right';
          ctx.textBaseline = 'middle';
          if (barWidth > 30) {
            ctx.fillText(item.pct + '%', chartX + barWidth - 5, y + barH/2);
          } else {
            ctx.fillStyle = '#666';
            ctx.textAlign = 'left';
            ctx.fillText(item.pct + '%', chartX + barWidth + 5, y + barH/2);
          }
          
          // Legend
          const legendX = chartX + chartW + 15;
          ctx.fillStyle = item.color;
          ctx.fillRect(legendX, y + 3, 10, 10);
          ctx.fillStyle = '#333';
          ctx.font = '10px sans-serif';
          ctx.textAlign = 'left';
          ctx.fillText(item.label, legendX + 14, y + 8);
       });
    },
    printReport() {
      // Add print class to body to force layout
      document.body.classList.add('is-printing');
      
      const restoreLayout = () => {
        document.body.classList.remove('is-printing');
        window.removeEventListener('afterprint', restoreLayout);
      };
      
      window.addEventListener('afterprint', restoreLayout);
      
      this.$nextTick(() => {
        // Allow DOM to update and then trigger native print
        setTimeout(() => {
          window.print();
          // Fallback if afterprint doesn't fire
          setTimeout(restoreLayout, 2000);
        }, 300);
      });
    }
  }
};
</script>

<style scoped>
/* Global print overrides */
@media print {
  html, body, #app, div[class*="min-h-screen"], .layout-wrapper, main {
    background-color: #ffffff !important;
    background: #ffffff !important;
    color: #000000 !important;
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    position: static !important;
  }
  header, aside, nav, .print\:hidden {
    display: none !important;
  }
  main {
    padding: 0 !important;
    margin: 0 !important;
    padding-top: 0 !important;
  }
  div[class*="pt-16"] {
    padding-top: 0 !important;
  }
  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Make sure background colors render properly */
  .bg-blue-900 { background-color: #1e3a8a !important; color: white !important; }
  .bg-blue-700 { background-color: #1d4ed8 !important; color: white !important; }
  .bg-blue-600 { background-color: #2563eb !important; color: white !important; }
  .bg-blue-50 { background-color: #eff6ff !important; }
  .bg-blue-100 { background-color: #dbeafe !important; }
  .bg-gray-100 { background-color: #f3f4f6 !important; }
  .text-red-500 { color: #ef4444 !important; }
  .text-teal-600 { color: #0d9488 !important; }
  .text-green-700 { color: #15803d !important; }
  
  .report-page { 
    box-shadow: none !important; 
    border: none !important; 
    margin-top: 0 !important;
  }
  #report-page-2 { 
    margin-top: 2rem !important; 
  }
}

:global(body.is-printing), :global(body.is-printing html), :global(body.is-printing #app), :global(body.is-printing div[class*="min-h-screen"]), :global(body.is-printing .layout-wrapper), :global(body.is-printing main) {
  height: auto !important;
  min-height: 0 !important;
  overflow: visible !important;
  position: static !important;
}

:global(body.is-printing :has(#igbh-pdf-content)) {
  height: auto !important;
  min-height: 0 !important;
  max-height: none !important;
  overflow: visible !important;
  position: static !important;
}

:global(body.is-printing #igbh-pdf-content) {
  width: 900px !important;
  max-width: 900px !important;
  zoom: 0.82 !important;
  padding: 0 !important;
  margin: 0 auto !important;
  border: none !important;
  box-shadow: none !important;
}
</style>
