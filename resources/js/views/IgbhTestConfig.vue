<template>
  <div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">{{ configType === 'summative' ? $t('igbh.config.title_summative') : $t('igbh.config.title_diagnostic') }}</h2>
        <p class="text-sm text-brand-desc" v-if="test">
          {{ $t('igbh.config.test') }}: <span class="font-semibold text-brand-text">{{ test.name }}</span> - {{ $t('igbh.config.level') }}: {{ test.level_cd }}
        </p>
      </div>
      <div class="flex gap-2">
        <router-link :to="{ name: 'tests' }" class="px-4 py-2 border border-brand-border hover:bg-brand-input text-brand-desc hover:text-brand-text rounded-lg text-sm font-semibold transition">
          {{ $t('igbh.config.back') }}
        </router-link>
        <button @click="saveConfig" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold transition shadow-lg shadow-indigo-500/30">
          {{ $t('igbh.config.save') }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Form for SUMMATIVE -->
    <div v-else-if="configType === 'summative'" class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-brand-border bg-brand-input/30">
        <h3 class="font-bold text-brand-text">{{ $t('igbh.config.achievement_12_weeks') }}</h3>
      </div>
      <div class="p-6">
        <table class="w-full text-left border-collapse border border-brand-border text-sm">
          <thead class="bg-brand-header text-brand-desc">
            <tr>
              <th class="border border-brand-border px-4 py-2 w-16 text-center">{{ $t('igbh.config.number') }}</th>
              <th class="border border-brand-border px-4 py-2">{{ $t('igbh.config.content_eval') }}</th>
              <th class="border border-brand-border px-4 py-2 w-32 text-center">{{ $t('igbh.config.standard_score') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(theme, index) in themes" :key="index">
              <td class="border border-brand-border px-4 py-2 text-center font-medium">{{ index + 1 }}</td>
              <td class="border border-brand-border px-4 py-2">
                <input type="text" v-model="theme.theme_desc" class="w-full px-3 py-1.5 rounded-lg border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition">
              </td>
              <td class="border border-brand-border px-4 py-2">
                <input type="number" v-model="theme.theme_point" class="w-full text-center px-3 py-1.5 rounded-lg border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition">
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-brand-header font-bold text-brand-text">
              <td colspan="2" class="border border-brand-border px-4 py-2 text-right">{{ $t('igbh.config.total') }}</td>
              <td class="border border-brand-border px-4 py-2 text-center">{{ summativeTotal }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Form for DIAGNOSTIC -->
    <div v-else-if="configType === 'diagnostic'" class="space-y-6">
      
      <!-- Info Header Table -->
      <div class="bg-white border border-brand-border rounded-xl shadow-sm overflow-hidden text-sm">
        <div class="px-4 py-3 border-b border-brand-border bg-brand-input/30">
          <h3 class="font-bold text-brand-text">{{ $t('igbh.config.input_info') }}</h3>
        </div>
        <table class="w-full text-left border-collapse">
          <tbody>
            <tr>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text w-40">{{ $t('igbh.config.test_name') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc">{{ test?.name }}</td>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text w-24">{{ $t('igbh.config.unit') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc">{{ test?.level_cd }}</td>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text w-48">{{ $t('igbh.config.start_end_date') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc"></td>
            </tr>
            <tr>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text">{{ $t('igbh.config.grade') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc">{{ test?.level_cd }}</td>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text">{{ $t('igbh.config.eval_year') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc"></td>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text">{{ $t('igbh.config.quarter') }}</th>
              <td class="border border-brand-border px-4 py-3 text-brand-desc"></td>
            </tr>
            <tr>
              <th class="border border-brand-border bg-brand-input/10 px-4 py-3 font-semibold text-brand-text">{{ $t('igbh.config.folder') }}</th>
              <td colspan="5" class="border border-brand-border px-4 py-3 text-brand-desc">
                {{ test?.pdf_url || test?.local_pdf_path }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Diagnostic Tabs -->
      <div class="flex border-b border-brand-border">
        <button 
          @click="activeTab = 'curriculum'" 
          :class="['px-6 py-3 text-sm font-semibold transition', activeTab === 'curriculum' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50' : 'text-brand-desc hover:text-brand-text']"
        >
          {{ $t('igbh.config.curriculum_issues') }}
        </button>
        <button 
          @click="activeTab = 'comments'" 
          :class="['px-6 py-3 text-sm font-semibold transition', activeTab === 'comments' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50' : 'text-brand-desc hover:text-brand-text']"
        >
          {{ $t('igbh.config.curriculum_comments') }}
        </button>
        <button 
          @click="activeTab = 'thinking'" 
          :class="['px-6 py-3 text-sm font-semibold transition', activeTab === 'thinking' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/50' : 'text-brand-desc hover:text-brand-text']"
        >
          {{ $t('igbh.config.thinking_issues') }}
        </button>
      </div>

      <!-- Tab 1: Curriculum -->
      <div v-if="activeTab === 'curriculum'" class="space-y-6">
        <!-- Sectors (Units) -->
        <div class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-brand-border bg-brand-input/30">
            <h3 class="font-bold text-brand-text">{{ $t('igbh.config.sectors') }}</h3>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            <div v-for="key in ['A','B','C','D','E']" :key="key" class="flex items-center gap-2">
              <span class="font-bold text-brand-desc">{{ key }}.</span>
              <input type="text" v-model="config[key]" class="w-full px-3 py-2 rounded-lg border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm">
            </div>
          </div>
        </div>

        <!-- Curriculum Questions -->
        <div class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-brand-border bg-brand-input/30">
            <h3 class="font-bold text-brand-text">{{ $t('igbh.config.question_config') }}</h3>
          </div>
          <div class="overflow-x-auto p-4">
            <table class="w-full text-center border-collapse text-sm whitespace-nowrap">
              <thead>
                <tr>
                  <th rowspan="2" class="border border-brand-border px-2 py-2 w-10 text-brand-desc">{{ $t('igbh.config.number') }}</th>
                  <th colspan="5" class="border border-brand-border px-2 py-1 text-brand-desc bg-brand-header">{{ $t('igbh.config.sectors') }}</th>
                  <th colspan="3" class="border border-brand-border px-2 py-1 text-brand-desc bg-brand-header">{{ $t('igbh.config.difficulty') }}</th>
                  <th colspan="2" class="border border-brand-border px-2 py-1 text-brand-desc bg-brand-header">Non-word and word problems</th>
                  <th rowspan="2" class="border border-brand-border px-2 py-2 w-20 text-brand-desc">{{ $t('igbh.config.correct_answer') }}</th>
                  <th rowspan="2" class="border border-brand-border px-2 py-2 w-20 text-brand-desc">{{ $t('igbh.config.standard_score') }}</th>
                </tr>
                <tr class="text-xs text-brand-desc bg-brand-input/50">
                  <th class="border border-brand-border px-2 py-1" title="A.">A</th>
                  <th class="border border-brand-border px-2 py-1" title="B.">B</th>
                  <th class="border border-brand-border px-2 py-1" title="C.">C</th>
                  <th class="border border-brand-border px-2 py-1" title="D.">D</th>
                  <th class="border border-brand-border px-2 py-1" title="E.">E</th>
                  <th class="border border-brand-border px-2 py-1">High</th>
                  <th class="border border-brand-border px-2 py-1">Medium</th>
                  <th class="border border-brand-border px-2 py-1">Low</th>
                  <th class="border border-brand-border px-2 py-1" :title="$t('igbh.config.non_word_problems')">{{ $t('igbh.config.non_word_problems') }}</th>
                  <th class="border border-brand-border px-2 py-1" :title="$t('igbh.config.word_problems')">{{ $t('igbh.config.word_problems') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(q, index) in curriculumQuestions" :key="index" class="hover:bg-brand-input/30 transition">
                  <td class="border border-brand-border px-2 py-1.5 font-medium">{{ index + 1 }}</td>
                  <!-- Sector -->
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_sec_'+index" value="A" v-model="q.sector"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_sec_'+index" value="B" v-model="q.sector"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_sec_'+index" value="C" v-model="q.sector"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_sec_'+index" value="D" v-model="q.sector"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_sec_'+index" value="E" v-model="q.sector"></td>
                  <!-- Difficulty -->
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_dif_'+index" value="DC001" v-model="q.difficulty"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_dif_'+index" value="DC002" v-model="q.difficulty"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_dif_'+index" value="DC003" v-model="q.difficulty"></td>
                  <!-- Type -->
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_typ_'+index" value="ST001" v-model="q.type_cd"></td>
                  <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'cur_typ_'+index" value="ST002" v-model="q.type_cd"></td>
                  
                  <td class="border border-brand-border px-2 py-1.5">
                    <input type="text" v-model="q.answer" class="w-full text-center px-1 py-1 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500">
                  </td>
                  <td class="border border-brand-border px-2 py-1.5">
                    <input type="number" v-model="q.standard_point" class="w-full text-center px-1 py-1 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500">
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-brand-header font-bold text-brand-text">
                  <td class="border border-brand-border px-2 py-2 text-center">{{ $t('igbh.config.total') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('sector', 'A') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('sector', 'B') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('sector', 'C') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('sector', 'D') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('sector', 'E') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('difficulty', 'DC001') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('difficulty', 'DC002') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('difficulty', 'DC003') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('type_cd', 'ST001') }}</td>
                  <td class="border border-brand-border px-2 py-2 text-center">{{ countCur('type_cd', 'ST002') }}</td>
                  <td class="border border-brand-border px-2 py-2"></td>
                  <td class="border border-brand-border px-2 py-2 text-center text-indigo-600">{{ diagnosticTotal }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab 2: Comments -->
      <div v-if="activeTab === 'comments'" class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border bg-brand-input/30 flex justify-between items-center">
          <h3 class="font-bold text-brand-text">{{ $t('igbh.config.total_comments') }}</h3>
          <button @click="addComment('total')" class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
            {{ $t('igbh.config.add_row') }}
          </button>
        </div>
        <div class="p-6">
          <table class="w-full text-left border-collapse border border-brand-border text-sm">
            <thead class="bg-brand-header text-brand-desc">
              <tr>
                <th class="border border-brand-border px-4 py-2 w-64 text-center">{{ $t('igbh.config.category') }}</th>
                <th class="border border-brand-border px-4 py-2 text-center">{{ $t('igbh.config.good_comment') }}</th>
                <th class="border border-brand-border px-4 py-2 text-center">{{ $t('igbh.config.weak_comment') }}</th>
                <th class="border border-brand-border px-4 py-2 w-16 text-center">{{ $t('igbh.config.delete') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(comment, index) in totalComments" :key="'total_'+index">
                <td class="border border-brand-border px-2 py-2">
                  <input type="text" v-model="comment.condition_value" class="w-full px-2 py-1.5 font-semibold rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm text-center">
                </td>
                <td class="border border-brand-border px-2 py-2">
                  <textarea v-model="comment.good_comment" rows="2" class="w-full px-3 py-1.5 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm leading-relaxed"></textarea>
                </td>
                <td class="border border-brand-border px-2 py-2">
                  <textarea v-model="comment.weak_comment" rows="2" class="w-full px-3 py-1.5 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm leading-relaxed"></textarea>
                </td>
                <td class="border border-brand-border px-2 py-2 text-center">
                  <button @click="removeComment(index, 'total')" class="text-rose-500 hover:text-rose-700 p-1">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </td>
              </tr>
              <tr v-if="totalComments.length === 0">
                <td colspan="4" class="text-center py-4 text-brand-desc">{{ $t('igbh.config.no_comment') }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-4 border-b border-t border-brand-border bg-brand-input/30 flex justify-between items-center">
          <div>
            <h3 class="font-bold text-brand-text">{{ $t('igbh.config.unit_comment') }}</h3>
            <p class="text-xs text-brand-desc">{{ $t('igbh.config.unit_comment_desc') }}</p>
          </div>
          <button @click="addComment('unit')" class="px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
            {{ $t('igbh.config.add_row') }}
          </button>
        </div>
        <div class="p-6">
          <table class="w-full text-left border-collapse border border-brand-border text-sm">
            <thead class="bg-brand-header text-brand-desc">
              <tr>
                <th class="border border-brand-border px-4 py-2 w-64 text-center">{{ $t('igbh.config.category') }}</th>
                <th class="border border-brand-border px-4 py-2 text-center">{{ $t('igbh.config.weak_comment') }}</th>
                <th class="border border-brand-border px-4 py-2 w-16 text-center">{{ $t('igbh.config.delete') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(comment, index) in unitComments" :key="'unit_'+index">
                <td class="border border-brand-border px-2 py-2">
                  <input type="text" v-model="comment.condition_value" placeholder="When A.Tính toán is missing" class="w-full px-2 py-1.5 font-semibold rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm text-center">
                </td>
                <td class="border border-brand-border px-2 py-2">
                  <textarea v-model="comment.weak_comment" rows="2" class="w-full px-3 py-1.5 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500 transition text-sm leading-relaxed"></textarea>
                </td>
                <td class="border border-brand-border px-2 py-2 text-center">
                  <button @click="removeComment(index, 'unit')" class="text-rose-500 hover:text-rose-700 p-1">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                  </button>
                </td>
              </tr>
              <tr v-if="unitComments.length === 0">
                <td colspan="3" class="text-center py-4 text-brand-desc">{{ $t('igbh.config.no_comment') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab 3: Thinking -->
      <div v-if="activeTab === 'thinking'" class="bg-brand-card border border-brand-border rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-border bg-brand-input/30">
          <h3 class="font-bold text-brand-text">{{ $t('igbh.config.thinking_config') }}</h3>
        </div>
        <div class="overflow-x-auto p-4">
          <table class="w-full text-center border-collapse text-sm whitespace-nowrap">
            <thead>
              <tr>
                <th rowspan="2" class="border border-brand-border px-2 py-2 w-10 text-brand-desc">{{ $t('igbh.config.number') }}</th>
                <th colspan="6" class="border border-brand-border px-2 py-1 text-brand-desc bg-brand-header">{{ $t('igbh.config.areas') }}</th>
                <th colspan="3" class="border border-brand-border px-2 py-1 text-brand-desc bg-brand-header">{{ $t('igbh.config.difficulty') }}</th>
                <th rowspan="2" class="border border-brand-border px-2 py-2 w-20 text-brand-desc">{{ $t('igbh.config.standard_score') }}</th>
              </tr>
              <tr class="text-xs text-brand-desc bg-brand-input/50">
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_a') }}</th>
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_b') }}</th>
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_c') }}</th>
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_d') }}</th>
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_e') }}</th>
                <th class="border border-brand-border px-2 py-1">{{ $t('igbh.config.area_f') }}</th>
                <th class="border border-brand-border px-2 py-1">High</th>
                <th class="border border-brand-border px-2 py-1">Medium</th>
                <th class="border border-brand-border px-2 py-1">Low</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(q, index) in thinkingQuestions" :key="index" class="hover:bg-brand-input/30 transition">
                <td class="border border-brand-border px-2 py-1.5 font-medium">{{ index + 1 }}</td>
                
                <!-- Lĩnh vực -->
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="A" v-model="q.areas"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="B" v-model="q.areas"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="C" v-model="q.areas"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="D" v-model="q.areas"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="E" v-model="q.areas"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="checkbox" value="F" v-model="q.areas"></td>

                <!-- Difficulty -->
                <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'thk_dif_'+index" value="DC003" v-model="q.difficulty"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'thk_dif_'+index" value="DC002" v-model="q.difficulty"></td>
                <td class="border border-brand-border px-2 py-1.5"><input type="radio" :name="'thk_dif_'+index" value="DC001" v-model="q.difficulty"></td>
                
                <td class="border border-brand-border px-2 py-1.5">
                  <input type="number" v-model="q.standard_point" class="w-full text-center px-1 py-1 rounded border border-brand-border bg-brand-input text-brand-text focus:outline-none focus:border-indigo-500">
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-brand-header font-bold text-brand-text">
                <td class="border border-brand-border px-2 py-2 text-center">{{ $t('igbh.config.total') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('A') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('B') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('C') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('D') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('E') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThkArea('F') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThk('difficulty', 'DC003') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThk('difficulty', 'DC002') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center">{{ countThk('difficulty', 'DC001') }}</td>
                <td class="border border-brand-border px-2 py-2 text-center text-indigo-600">{{ thinkingTotal }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'IgbhTestConfig',
  data() {
    return {
      loading: true,
      testId: this.$route.params.id,
      configType: '', // 'summative' or 'diagnostic'
      activeTab: 'curriculum',
      test: null,
      themes: [],
      config: { A: '', B: '', C: '', D: '', E: '' },
      curriculumQuestions: [],
      thinkingQuestions: [],
      totalComments: [],
      unitComments: []
    };
  },
  computed: {
    summativeTotal() {
      return this.themes.reduce((sum, theme) => sum + (parseFloat(theme.theme_point) || 0), 0);
    },
    diagnosticTotal() {
      return this.curriculumQuestions.reduce((sum, q) => sum + (parseFloat(q.standard_point) || 0), 0);
    },
    thinkingTotal() {
      return this.thinkingQuestions.reduce((sum, q) => sum + (parseFloat(q.standard_point) || 0), 0);
    }
  },
  async created() {
    await this.fetchConfig();
  },
  methods: {
    async fetchConfig() {
      this.loading = true;
      try {
        const res = await axios.get(`/api/igbh/tests/${this.testId}/config`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        
        this.configType = res.data.type;
        this.test = res.data.test;
        
        if (this.configType === 'summative') {
          // Fill missing themes up to 12
          let loadedThemes = res.data.themes || [];
          while (loadedThemes.length < 12) {
            loadedThemes.push({ theme_desc: '', theme_point: 0 });
          }
          this.themes = loadedThemes;
        } else {
          this.config = res.data.config || { A: '', B: '', C: '', D: '', E: '' };
          let loadedQs = res.data.questions || [];
          let loadedComments = res.data.comments || [];
          
          this.curriculumQuestions = loadedQs.filter(q => q.question_type === 'curriculum');
          this.thinkingQuestions = loadedQs.filter(q => q.question_type === 'thinking');
          
          this.totalComments = loadedComments.filter(c => c.comment_type === 'total' && !(c.condition_value || '').startsWith('When'));
          this.unitComments = loadedComments.filter(c => c.comment_type === 'unit' || (c.condition_value || '').startsWith('When'));
          
          // Ensure arrays
          this.curriculumQuestions.forEach(q => {
            if (!Array.isArray(q.areas)) q.areas = [];
          });
          this.thinkingQuestions.forEach(q => {
            if (!Array.isArray(q.areas)) q.areas = [];
          });
          
          while (this.curriculumQuestions.length < 20) {
            this.curriculumQuestions.push({ question_type: 'curriculum', sector: null, difficulty: null, type_cd: null, answer: '', standard_point: 2, areas: [] });
          }
          while (this.thinkingQuestions.length < 10) {
            this.thinkingQuestions.push({ question_type: 'thinking', sector: null, difficulty: null, type_cd: null, answer: '', standard_point: 5, areas: [] });
          }
        }
      } catch (e) {
        console.error(e);
        alert('Có lỗi xảy ra khi tải dữ liệu cấu hình!');
      } finally {
        this.loading = false;
      }
    },
    addComment(type = 'total') {
      if (type === 'total') {
        this.totalComments.push({ condition_value: '', good_comment: '', weak_comment: '', comment_type: 'total' });
      } else {
        this.unitComments.push({ condition_value: '', good_comment: '', weak_comment: '', comment_type: 'unit' });
      }
    },
    removeComment(index, type = 'total') {
      if (type === 'total') {
        this.totalComments.splice(index, 1);
      } else {
        this.unitComments.splice(index, 1);
      }
    },
    async saveConfig() {
      if (!confirm('Bạn có chắc chắn muốn lưu lại toàn bộ cấu hình này không?')) return;
      
      try {
        const payload = {
          type: this.configType,
          themes: this.themes,
          config: this.config,
          questions: [...this.curriculumQuestions, ...this.thinkingQuestions],
          comments: [...this.totalComments, ...this.unitComments]
        };
        
        await axios.put(`/api/igbh/tests/${this.testId}/config`, payload, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        
        alert('Đã lưu cấu hình bài kiểm tra thành công!');
      } catch (e) {
        console.error(e);
        alert('Có lỗi khi lưu cấu hình. Vui lòng thử lại.');
      }
    },
    countCur(field, value) {
      return this.curriculumQuestions.filter(q => q[field] === value).length || '';
    },
    countThk(field, value) {
      return this.thinkingQuestions.filter(q => q[field] === value).length || '';
    },
    countThkArea(area) {
      return this.thinkingQuestions.filter(q => q.areas && q.areas.includes(area)).length || '';
    }
  }
}
</script>
