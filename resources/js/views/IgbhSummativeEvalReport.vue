<template>
  <div class="space-y-6 max-w-5xl mx-auto">
    <!-- Action Bar (Not Printed) -->
    <div class="flex items-center justify-between no-print">
      <router-link :to="{ name: 'igbh-summative-evaluations' }" class="flex items-center gap-2 text-brand-desc hover:text-indigo-400 transition font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        {{ $t('igbh.form.back_list') }}
      </router-link>
      <button @click="printReport" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium shadow-lg shadow-indigo-600/30 flex items-center gap-2 text-sm transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        {{ $t('igbh.form.print_report') }}
      </button>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Printable Report Container -->
    <div v-else-if="reportData" class="bg-white text-black p-4 md:p-8 font-sans shadow-xl border border-gray-200" id="printable-report">
      
      <!-- Top Header Area -->
      <div class="bg-[#fdb913] text-white p-4 relative mb-6 rounded-t-sm">
        <div class="flex justify-between items-center mb-2">
          <div class="flex items-center gap-4">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-md">CMS<span class="text-xl font-normal align-top">EDU</span></h1>
            <h2 class="text-2xl font-bold uppercase drop-shadow-md">ĐÁNH GIÁ CUỐI KỲ</h2>
          </div>
          <div class="text-sm font-medium border-l-2 border-white/50 pl-3">
            | Bài kiểm tra đầu vào |
          </div>
        </div>
        
        <div class="bg-white text-black rounded-full py-1.5 px-6 flex flex-wrap gap-4 md:gap-8 justify-center text-sm shadow-md mt-4 font-medium max-w-3xl mx-auto border-2 border-[#fdb913]">
          <div><span class="text-blue-600">{{ $t('igbh.cols.class') }} :</span> {{ reportData.student_info.class_nm }}</div>
          <div><span class="text-blue-600">{{ $t('igbh.form.dob') }} :</span> 2018-02-19</div>
          <div><span class="text-blue-600">{{ $t('igbh.form.student') }} :</span> {{ reportData.student_info.stu_nm }}</div>
        </div>
      </div>

      <!-- Center Class Name -->
      <div class="text-center font-bold text-gray-700 mb-6 text-sm">
        {{ reportData.student_info.class_nm }}
      </div>

      <!-- Section: Nhận xét sách bài tập -->
      <div class="mb-8">
        <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
          <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
            Nhận xét sách bài tập
          </div>
        </div>
        
        <table class="w-full text-center border-collapse border border-gray-400 text-sm mt-4">
          <thead class="bg-[#f0f0f0]">
            <tr>
              <th class="border border-gray-400 py-2 font-semibold">Tuần</th>
              <th v-for="i in 12" :key="i" class="border border-gray-400 py-2 font-medium w-8">{{ i }}</th>
              <th class="border border-gray-400 py-2 font-semibold">Tổng</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold text-gray-800">Sách bài tập</td>
              <td v-for="w in reportData.report_data" :key="'wb'+w.week" class="border border-gray-400 py-2">
                {{ w.score }}
              </td>
              <td class="border-2 border-red-600 py-2 font-bold text-red-600 bg-red-50 text-base">
                {{ reportData.summary.workbook_score }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Section: Nhận xét thái độ & Quan sát lớp học -->
      <div class="flex flex-col md:flex-row gap-6 mb-8">
        <!-- Left: Thái độ -->
        <div class="flex-1">
          <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
            <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
              Nhận xét thái độ học tập
            </div>
          </div>
          <table class="w-full text-center border-collapse border border-gray-400 text-xs mt-4 h-[200px]">
            <thead class="bg-[#f0f0f0]">
              <tr>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Tuần</th>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Lắng nghe</th>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Tham gia bài học</th>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Sự thể hiện</th>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Tinh thần đội nhóm</th>
                <th class="border border-gray-400 py-2 px-1 font-semibold">Trung bình</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold">1 ~ 4</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
              </tr>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold">5 ~ 8</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
              </tr>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold">9 ~ 12</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
                <td class="border border-gray-400 py-2">-</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold">Điểm quy đổi</td>
                <td class="border border-gray-400 py-2">/</td>
                <td class="border border-gray-400 py-2">/</td>
                <td class="border border-gray-400 py-2">/</td>
                <td class="border border-gray-400 py-2">/</td>
                <td class="border-2 border-red-600 bg-white text-red-600 font-bold text-sm">
                  {{ ( (reportData.summary.avg_attitude.listen + reportData.summary.avg_attitude.join + reportData.summary.avg_attitude.express + reportData.summary.avg_attitude.coop)/4 * 2 ).toFixed(1) }}
                </td>
              </tr>
            </tbody>
          </table>
          
          <div class="border border-gray-300 rounded-xl p-4 mt-4 bg-gray-50/50 shadow-inner relative">
            <h4 class="font-bold text-[#d2691e] text-lg leading-tight mb-2" style="-webkit-text-stroke: 0.5px white; text-shadow: 1px 1px 0 #fff;">
              Nhận xét thái độ học tập và khả<br>năng quan sát
            </h4>
            <p class="text-xs text-gray-700 leading-relaxed">
              Giáo viên sẽ đánh giá dựa trên thái độ học tập của học sinh trên lớp trong suốt quá trình theo học tại trung tâm.
            </p>
          </div>
        </div>

        <!-- Right: Quan sát -->
        <div class="flex-1">
          <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
            <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
              Quan sát lớp học
            </div>
          </div>
          <table class="w-full text-center border-collapse border border-gray-400 text-xs mt-4 h-[200px]">
            <thead class="bg-[#f0f0f0]">
              <tr>
                <th colspan="2" class="border border-gray-400 py-2 px-2 font-semibold">Hạng mục quan sát</th>
                <th class="border border-gray-400 py-2 px-2 font-semibold w-16">Điểm</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold px-2 w-24">Kỹ năng cơ bản</td>
                <td class="border border-gray-400 py-2 px-3 text-left text-gray-700 leading-tight">
                  Học sinh thể hiện khả năng sử dụng từ vựng phong phú hơn so với các học sinh trung bình cùng độ tuổi.
                </td>
                <td class="border border-gray-400 py-2 font-semibold">{{ reportData.summary.avg_detection.normal }}</td>
              </tr>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold px-2">Khả năng lãnh đạo</td>
                <td class="border border-gray-400 py-2 px-3 text-left text-gray-700 leading-tight">
                  Học sinh trình bày suy nghĩ của mình một cách rõ ràng và có thể giao tiếp tốt với những người khác.
                </td>
                <td class="border border-gray-400 py-2 font-semibold">{{ reportData.summary.avg_detection.leadersh }}</td>
              </tr>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold px-2">Khả năng toán học</td>
                <td class="border border-gray-400 py-2 px-3 text-left text-gray-700 leading-tight">
                  Học sinh thể hiện sự hứng thú với toán và có tính kiên trì khi giải quyết các vấn đề khác nhau.
                </td>
                <td class="border border-gray-400 py-2 font-semibold">{{ reportData.summary.avg_detection.math }}</td>
              </tr>
              <tr>
                <td class="border border-gray-400 py-2 bg-[#fbe5a2] font-semibold px-2">Tính sáng tạo</td>
                <td class="border border-gray-400 py-2 px-3 text-left text-gray-700 leading-tight">
                  Học sinh thể hiện trí tưởng tượng rất tốt trong quá trình học tập.
                </td>
                <td class="border border-gray-400 py-2 font-semibold">{{ reportData.summary.avg_detection.creative }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td colspan="2" class="border border-gray-400 py-2 font-semibold text-right pr-4">Điểm quy đổi</td>
                <td class="border-2 border-red-600 bg-white text-red-600 font-bold text-sm">
                  {{ ( (reportData.summary.avg_detection.normal + reportData.summary.avg_detection.leadersh + reportData.summary.avg_detection.math + reportData.summary.avg_detection.creative)/4 * 2 ).toFixed(1) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Section: Đánh giá theo bài học -->
      <div class="mb-8">
        <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
          <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
            Đánh giá theo bài học
          </div>
        </div>
        
        <table class="w-full text-center border-collapse border border-gray-400 text-sm mt-4">
          <thead class="bg-[#f0f0f0]">
            <tr>
              <th class="border border-gray-400 py-2 font-semibold w-12">Số</th>
              <th class="border border-gray-400 py-2 font-semibold">Nội dung đánh giá</th>
              <th class="border border-gray-400 py-2 font-semibold w-24">Điểm chuẩn</th>
              <th class="border border-gray-400 py-2 font-semibold w-24">Điểm thực tế</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(week, index) in reportData.report_data" :key="'desc'+index">
              <td class="border border-gray-400 py-2 text-gray-600">{{ index + 1 }}</td>
              <td class="border border-gray-400 py-2 px-4 text-left">{{ week.theme_desc }}</td>
              <td class="border border-gray-400 py-2">{{ week.max_score }}</td>
              <td class="border border-gray-400 py-2 font-medium">{{ week.score }}</td>
            </tr>
            <tr class="bg-[#f0f0f0]">
              <td colspan="2" class="border border-gray-400 py-2 font-bold text-right pr-4">Tổng</td>
              <td class="border border-gray-400 py-2 font-bold">{{ reportData.summary.max_workbook }}</td>
              <td class="border-2 border-red-600 bg-white text-red-600 font-bold text-base">{{ reportData.summary.workbook_score }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Section: Điểm tổng -->
      <div class="mb-4">
        <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
          <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
            Điểm tổng
          </div>
        </div>
        
        <table class="w-full text-center border-collapse border border-gray-400 text-sm mt-4">
          <thead class="bg-[#f0f0f0]">
            <tr>
              <th class="border border-gray-400 py-2 font-semibold">Mục</th>
              <th class="border border-gray-400 py-2 font-semibold">Sách bài tập</th>
              <th class="border border-gray-400 py-2 font-semibold">Thái độ học tập</th>
              <th class="border border-gray-400 py-2 font-semibold">Quan sát lớp học</th>
              <th class="border border-gray-400 py-2 font-semibold">Tổng</th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-[#fbe5a2]">
              <td class="border border-gray-400 py-2 font-semibold text-gray-800">Điểm chuẩn</td>
              <td class="border border-gray-400 py-2">20</td>
              <td class="border border-gray-400 py-2">10</td>
              <td class="border border-gray-400 py-2">10</td>
              <td class="border border-gray-400 py-2 font-bold bg-white text-red-600 border-2 border-red-600">40</td>
            </tr>
            <tr class="bg-[#fbe5a2]">
              <td class="border border-gray-400 py-2 font-semibold text-gray-800">Điểm thực tế</td>
              <td class="border border-gray-400 py-2 bg-white">{{ reportData.summary.workbook_score }}</td>
              <td class="border border-gray-400 py-2 bg-white">{{ ( (reportData.summary.avg_attitude.listen + reportData.summary.avg_attitude.join + reportData.summary.avg_attitude.express + reportData.summary.avg_attitude.coop)/4 * 2 ).toFixed(1) }}</td>
              <td class="border border-gray-400 py-2 bg-white">{{ ( (reportData.summary.avg_detection.normal + reportData.summary.avg_detection.leadersh + reportData.summary.avg_detection.math + reportData.summary.avg_detection.creative)/4 * 2 ).toFixed(1) }}</td>
              <td class="border-2 border-red-600 bg-white font-bold text-red-600 text-lg">
                {{ (reportData.summary.workbook_score + ((reportData.summary.avg_attitude.listen + reportData.summary.avg_attitude.join + reportData.summary.avg_attitude.express + reportData.summary.avg_attitude.coop)/4 * 2) + ((reportData.summary.avg_detection.normal + reportData.summary.avg_detection.leadersh + reportData.summary.avg_detection.math + reportData.summary.avg_detection.creative)/4 * 2)).toFixed(1) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="text-right text-xs text-gray-500 mt-8 mb-4 border-t border-gray-200 pt-4">
        Copyright © CMS Edu Co., Ltd. All rights reserved.<br>
        <span class="inline-block mt-2 font-medium">Page 1</span>
      </div>
      
      <!-- PAGE BREAK -->
      <div class="page-break-before mt-8 pt-8">
        
        <!-- Page 2 Header -->
        <div class="bg-[#fdb913] text-white p-4 relative mb-6 rounded-t-sm">
          <div class="flex justify-between items-center mb-2">
            <div class="flex items-center gap-4">
              <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-md">CMS<span class="text-xl font-normal align-top">EDU</span></h1>
              <h2 class="text-2xl font-bold uppercase drop-shadow-md">Đánh giá nội dung câu hỏi tự luận</h2>
            </div>
            <div class="text-sm font-medium border-l-2 border-white/50 pl-3">
              | Bài kiểm tra đầu vào |
            </div>
          </div>
          
          <div class="bg-white text-black rounded-full py-1.5 px-6 flex flex-wrap gap-4 md:gap-8 justify-center text-sm shadow-md mt-4 font-medium max-w-3xl mx-auto border-2 border-[#fdb913]">
            <div><span class="text-blue-600">{{ $t('igbh.cols.class') }} :</span> {{ reportData.student_info.class_nm }}</div>
            <div><span class="text-blue-600">{{ $t('igbh.form.dob') }} :</span> 2018-02-19</div>
            <div><span class="text-blue-600">{{ $t('igbh.form.student') }} :</span> {{ reportData.student_info.stu_nm }}</div>
          </div>
        </div>

        <div class="text-center font-bold text-gray-700 mb-6 text-sm">
          {{ reportData.student_info.class_nm }}
        </div>

        <!-- Section: Kết quả đánh giá theo câu hỏi riêng biệt -->
        <div class="mb-8">
          <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
            <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
              Kết quả đánh giá theo câu hỏi riêng biệt
            </div>
          </div>
          
          <table class="w-full text-center border-collapse border border-gray-400 text-sm mt-4">
            <thead class="bg-[#f0f0f0]">
              <tr>
                <th class="border border-gray-400 py-2 font-semibold">NO</th>
                <th v-for="sub in displaySubjectiveData" :key="'no'+sub.sort_no" class="border border-gray-400 py-2 font-semibold w-16">{{ sub.sort_no }}</th>
                <th class="border border-gray-400 py-2 font-semibold w-24">Tổng</th>
              </tr>
            </thead>
            <tbody>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Điểm chuẩn</td>
                <td v-for="sub in displaySubjectiveData" :key="'max'+sub.sort_no" class="border border-gray-400 py-2">{{ sub.max_score }}</td>
                <td class="border border-gray-400 py-2 font-bold">{{ reportData.summary?.subjective_total?.max_score || '' }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Điểm thực tế</td>
                <td v-for="sub in displaySubjectiveData" :key="'score'+sub.sort_no" class="border border-gray-400 py-2 bg-white">{{ formatNumber(sub.score) }}</td>
                <td class="border-2 border-red-600 py-2 font-bold text-red-600 bg-white text-base">{{ formatNumber(reportData.summary?.subjective_total?.score) }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Khái niệm/hiểu</td>
                <td v-for="sub in displaySubjectiveData" :key="'c'+sub.sort_no" class="border border-gray-400 py-2 bg-white">{{ sub.concept }}</td>
                <td class="border border-gray-400 py-2 bg-white">{{ formatNumber(reportData.summary?.subjective_total?.concept) }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Chiến lược/suy luận</td>
                <td v-for="sub in displaySubjectiveData" :key="'s'+sub.sort_no" class="border border-gray-400 py-2 bg-white">{{ sub.strategy }}</td>
                <td class="border border-gray-400 py-2 bg-white">{{ formatNumber(reportData.summary?.subjective_total?.strategy) }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Tính toán/thực hành</td>
                <td v-for="sub in displaySubjectiveData" :key="'ca'+sub.sort_no" class="border border-gray-400 py-2 bg-white">{{ sub.calculation }}</td>
                <td class="border border-gray-400 py-2 bg-white">{{ formatNumber(reportData.summary?.subjective_total?.calculation) }}</td>
              </tr>
              <tr class="bg-[#fbe5a2]">
                <td class="border border-gray-400 py-2 font-semibold text-gray-800 px-2 text-left">Diễn đạt/biểu hiện</td>
                <td v-for="sub in displaySubjectiveData" :key="'e'+sub.sort_no" class="border border-gray-400 py-2 bg-white">{{ sub.expression }}</td>
                <td class="border border-gray-400 py-2 bg-white">{{ formatNumber(reportData.summary?.subjective_total?.expression) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Section: Thành phần đánh giá -->
        <div class="border border-gray-300 rounded-xl p-4 mt-4 mb-8 bg-gray-50/50 shadow-inner relative">
          <h4 class="font-bold text-[#d2691e] text-lg leading-tight mb-2" style="-webkit-text-stroke: 0.5px white; text-shadow: 1px 1px 0 #fff;">
            Thành phần đánh giá
          </h4>
          <ul class="text-sm text-gray-700 leading-relaxed space-y-1">
            <li><span class="text-[#d2691e]">✔</span> <span class="text-red-500 font-bold">Khái niệm/hiểu</span> | Học sinh có hiểu vấn đề và yêu cầu của đề không?</li>
            <li><span class="text-[#d2691e]">✔</span> <span class="text-red-500 font-bold">Chiến lược/suy luận</span> | Học sinh có sử dụng chiến lược thích hợp để giải quyết vấn đề không?</li>
            <li><span class="text-[#d2691e]">✔</span> <span class="text-red-500 font-bold">Tính toán/thực hành</span> | Học sinh có tuân theo một quy trình giải quyết vấn đề đầy đủ và chính xác không?</li>
            <li><span class="text-[#d2691e]">✔</span> <span class="text-red-500 font-bold">Diễn đạt/biểu hiện</span> | Có thể dễ dàng hiểu được ý của học sinh thông qua cách diễn đạt trong bài không?</li>
          </ul>
        </div>

        <!-- Section: Phân tích BTM/LTM -->
        <div class="mb-8">
          <div class="bg-[#fdb913] rounded-sm p-1.5 mb-2 relative flex items-center">
            <div class="bg-white border-2 border-red-600 rounded-full px-4 py-1 text-red-600 font-bold text-sm inline-block shadow-sm z-10 absolute -left-2 top-1/2 -translate-y-1/2">
              Phân tích BTM/LTM
            </div>
          </div>
          
          <div class="flex flex-col md:flex-row gap-6 mt-4">
            <!-- BTM Table -->
            <div class="flex-1">
              <table class="w-full text-center border-collapse border border-gray-400 text-sm">
                <thead class="bg-[#f0f0f0]">
                  <tr>
                    <th class="border border-gray-400 py-2 font-semibold px-2 w-24">Phân tích BTM</th>
                    <th class="border border-gray-400 py-2 font-semibold">{{ displayAnalysis.btm.q1_label }}</th>
                    <th class="border border-gray-400 py-2 font-semibold">{{ displayAnalysis.btm.q2_label }}</th>
                    <th class="border border-gray-400 py-2 font-semibold w-20">Bình quân</th>
                    <th class="border border-gray-400 py-2 font-semibold w-16">Ý kiến</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="bg-[#fbe5a2]" v-for="(label, key) in {'concept':'Khái niệm/hiểu', 'strategy':'Chiến lược/suy luận', 'calculation':'Tính toán/thực hành', 'expression':'Diễn đạt/biểu hiện'}" :key="key">
                    <td class="border border-gray-400 py-2 font-semibold px-1">{{ label }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm[key][0] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm[key][1] }}</td>
                    <td :class="isMinAverage(displayAnalysis.btm[key][2], displayAnalysis.btm) ? 'border-2 border-red-600 py-2 font-bold text-red-600 bg-white' : 'border border-gray-400 py-2 bg-white'">{{ displayAnalysis.btm[key][2] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm[key][3] }}</td>
                  </tr>
                  <tr class="bg-[#fbe5a2]">
                    <td class="border border-gray-400 py-2 font-semibold px-1">Trung bình</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm.average[0] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm.average[1] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.btm.average[2] }}</td>
                    <td class="border border-gray-400 py-2 bg-white"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- LTM Table -->
            <div class="flex-1">
              <table class="w-full text-center border-collapse border border-gray-400 text-sm">
                <thead class="bg-[#f0f0f0]">
                  <tr>
                    <th class="border border-gray-400 py-2 font-semibold px-2 w-24">Phân tích LTM</th>
                    <th class="border border-gray-400 py-2 font-semibold">{{ displayAnalysis.ltm.q1_label }}</th>
                    <th class="border border-gray-400 py-2 font-semibold">{{ displayAnalysis.ltm.q2_label }}</th>
                    <th class="border border-gray-400 py-2 font-semibold w-20">Bình quân</th>
                    <th class="border border-gray-400 py-2 font-semibold w-16">Ý kiến</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="bg-[#fbe5a2]" v-for="(label, key) in {'concept':'Khái niệm/hiểu', 'strategy':'Chiến lược/suy luận', 'calculation':'Tính toán/thực hành', 'expression':'Diễn đạt/biểu hiện'}" :key="'ltm'+key">
                    <td class="border border-gray-400 py-2 font-semibold px-1">{{ label }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm[key][0] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm[key][1] }}</td>
                    <td :class="isMinAverage(displayAnalysis.ltm[key][2], displayAnalysis.ltm) ? 'border-2 border-red-600 py-2 font-bold text-red-600 bg-white' : 'border border-gray-400 py-2 bg-white'">{{ displayAnalysis.ltm[key][2] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm[key][3] }}</td>
                  </tr>
                  <tr class="bg-[#fbe5a2]">
                    <td class="border border-gray-400 py-2 font-semibold px-1">Trung bình</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm.average[0] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm.average[1] }}</td>
                    <td class="border border-gray-400 py-2 bg-white">{{ displayAnalysis.ltm.average[2] }}</td>
                    <td class="border border-gray-400 py-2 bg-white"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="border border-gray-300 rounded-xl p-4 mt-4 bg-gray-50/50 shadow-inner mb-6">
          <strong class="font-bold text-[#d2691e] text-lg mb-2 block" style="-webkit-text-stroke: 0.5px white; text-shadow: 1px 1px 0 #fff;"><span class="text-yellow-600">Phân tích BTM/LTM</span> là gì?</strong>
          <p class="text-sm text-gray-700 leading-relaxed mb-4">
            Nó là một dạng phân tích sử dụng điểm trung bình từ phân bố xác suất để tìm ra các khía cạnh yếu nhất trong quá trình giải quyết vấn đề. Điểm số của mỗi vấn đề được liệt kê từ cao nhất đến thấp nhất và chỉ định điểm số ở giữa là trung bình. Sau đó, 2 điểm cao hơn điểm trung bình (BTM) và 2 điểm thấp hơn điểm trung bình (LTM) được chọn cho mỗi khu vực và sau đó tìm điểm thấp nhất. Cuối cùng, so sánh phân tích yếu tố đánh giá từ điểm thấp nhất.
          </p>
          <ul class="text-sm text-gray-700 list-disc list-inside">
            <li>Nhìn chung, trong trường hợp kết quả là LTM, học sinh có thể đã bị hết thời gian hoặc bỏ cuộc vì độ khó của vấn đề.</li>
          </ul>
        </div>

        <div class="border border-gray-300 rounded-xl bg-gray-50/50 p-4 shadow-inner">
          <strong class="font-bold text-gray-800 text-lg mb-2 block">Thang điểm đánh giá phần mô tả</strong>
          <table class="w-full text-center border-collapse border border-gray-400 text-sm mt-2">
            <thead class="bg-[#f0f0f0]">
              <tr>
                <th class="border border-gray-400 py-2 font-semibold">Cấp độ</th>
                <th class="border border-gray-400 py-2 font-semibold">Thang điểm</th>
                <th class="border border-gray-400 py-2 font-semibold">Đánh giá</th>
                <th class="border border-gray-400 py-2 font-semibold">Ghi chú</th>
              </tr>
            </thead>
            <tbody>
              <tr class="bg-[#fcd3aa]">
                <td class="border border-gray-400 py-3 font-semibold">A</td>
                <td class="border border-gray-400 py-3">24~30</td>
                <td class="border border-gray-400 py-3">Xuất sắc</td>
                <td class="border border-gray-400 py-3 text-left px-4">
                  Năng lực giải quyết vấn đề rất tốt.<br>
                  Nên dành thời gian để học sinh thử thách với những bài toán nâng cao.
                </td>
              </tr>
              <tr class="bg-[#fcd3aa]">
                <td class="border border-gray-400 py-3 font-semibold">B</td>
                <td class="border border-gray-400 py-3">14~23</td>
                <td class="border border-gray-400 py-3">Khá</td>
                <td class="border border-gray-400 py-3 text-left px-4">
                  Năng lực giải quyết vấn đề còn nhiều thiếu sót.<br>
                  Tuy nhiên, cần tập trung phát triển hơn nữa.
                </td>
              </tr>
              <tr class="bg-[#fcd3aa]">
                <td class="border border-gray-400 py-3 font-semibold">C</td>
                <td class="border border-gray-400 py-3">13</td>
                <td class="border border-gray-400 py-3">Trung bình</td>
                <td class="border border-gray-400 py-3 text-left px-4">
                  Năng lực giải quyết vấn đề kém.<br>
                  Cần luyện tập và hình thành thói quen giải các bài tập một cách cẩn thận.
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
  name: 'IgbhSummativeEvalReport',
  data() {
    return {
      loading: true,
      reportData: null
    };
  },
  computed: {
    displaySubjectiveData() {
      if (this.reportData && this.reportData.subjective_data && this.reportData.subjective_data.length > 0) {
        return this.reportData.subjective_data;
      }
      return [
        { sort_no: 1, max_score: '', score: '', concept: '', strategy: '', calculation: '', expression: '' },
        { sort_no: 2, max_score: '', score: '', concept: '', strategy: '', calculation: '', expression: '' },
        { sort_no: 3, max_score: '', score: '', concept: '', strategy: '', calculation: '', expression: '' },
        { sort_no: 4, max_score: '', score: '', concept: '', strategy: '', calculation: '', expression: '' },
        { sort_no: 5, max_score: '', score: '', concept: '', strategy: '', calculation: '', expression: '' }
      ];
    },
    displayAnalysis() {
      const emptyMatrix = {
        q1_label: 'Số 1', q2_label: 'Số 2',
        concept: ['', '', '', ''],
        strategy: ['', '', '', ''],
        calculation: ['', '', '', ''],
        expression: ['', '', '', ''],
        average: ['', '', '']
      };
      
      let data = this.reportData && this.reportData.subjective_analysis ? this.reportData.subjective_analysis : null;
      
      // Provide a safe fallback for all nested keys
      return {
        btm: Object.assign({}, emptyMatrix, data && data.btm ? data.btm : {}),
        ltm: Object.assign({}, emptyMatrix, data && data.ltm ? data.ltm : {})
      };
    }
  },
  async created() {
    await this.fetchReport();
  },
  methods: {
    async fetchReport() {
      const id = this.$route.params.id;
      this.loading = true;
      try {
        const response = await axios.get(`/api/igbh/summative/results/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.reportData = response.data;
      } catch (error) {
        console.error("Error fetching report", error);
        alert("Không thể tải dữ liệu báo cáo.");
      } finally {
        this.loading = false;
      }
    },
    printReport() {
      window.print();
    },
    formatNumber(val) {
      return (val !== null && val !== undefined && val !== '') ? Number(val).toFixed(1) : '';
    },
    isMinAverage(val, analysisObj) {
      if (!val || val === '') return false;
      const minAvg = this.getMinAverage(analysisObj);
      return minAvg !== null && parseFloat(val) === minAvg;
    },
    getMinAverage(analysisObj) {
      if (!analysisObj || !analysisObj.concept) return null;
      const averages = [
        parseFloat(analysisObj.concept[2]),
        parseFloat(analysisObj.strategy[2]),
        parseFloat(analysisObj.calculation[2]),
        parseFloat(analysisObj.expression[2])
      ].filter(n => !isNaN(n));
      return averages.length ? Math.min(...averages) : null;
    }
  }
}
</script>

<style scoped>
@media print {
  body {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .no-print {
    display: none !important;
  }
  #printable-report {
    width: 100%;
    margin: 0;
    padding: 0;
    box-shadow: none;
    border: none;
  }
  .page-break-before {
    page-break-before: always;
  }
}
</style>
