<template>
  <div class="space-y-6">
    <!-- Action buttons (Hidden on print) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
      <div>
        <h2 class="text-2xl font-bold text-brand-text">UCREA Assessment Report</h2>
        <p class="text-sm text-brand-desc">Legacy report view format</p>
      </div>
      <div class="flex space-x-3">
        <button @click="exportPdf" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition shadow-lg shadow-indigo-600/30 text-sm">
          In Báo Cáo
        </button>
        <router-link :to="{ name: 'ucrea-evaluations' }" class="px-4 py-2 rounded-lg border border-brand-border text-brand-desc hover:text-brand-text hover:bg-brand-input transition text-sm font-semibold">
          Quay lại
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 space-y-4 print:hidden">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
      <p class="text-sm text-brand-desc">Loading report...</p>
    </div>

    <!-- MAIN REPORT (Same for both Screen and Print) -->
    <div v-else-if="result" id="pdf-content" class="bg-white text-black p-6 mx-auto w-full max-w-[900px] shadow-md print:shadow-none print:p-0 print:m-0 text-[12px] leading-relaxed font-sans border border-gray-200 print:border-none">
      
      <!-- PAGE 1 -->
      <div class="page-container bg-white pb-8">
        
        <!-- Header (Dark blue background with white pill student info) -->
        <div class="bg-[#1b3664] text-white px-6 py-4 flex flex-col md:flex-row md:items-center justify-between rounded-t-lg print:rounded-none">
          <div class="flex items-center gap-6">
            <h1 class="text-2xl font-black tracking-tight text-white">CMS<span class="text-xs font-normal">EDU</span></h1>
            <div class="text-left border-l border-white/20 pl-4">
               <p class="text-[9px] uppercase text-white/70 font-semibold mb-0 leading-none">Ucrea Assessment</p>
               <h2 class="text-[16px] font-bold text-[#f5c518] uppercase tracking-wide leading-none mt-1">{{ reportData.name || 'ĐÁNH GIÁ ĐẦU KỲ' }}</h2>
            </div>
          </div>
          <!-- White pill student info -->
          <div class="bg-white text-[#1b3664] font-bold text-[11px] px-4 py-1.5 rounded-full mt-2 md:mt-0 shadow-sm">
             <span>{{ result.general.stu_nm }}</span>
             <span class="mx-2">|</span>
             <span>{{ result.general.gender_nm || 'Nam' }}</span>
             <span class="mx-2">|</span>
             <span>{{ result.general.age_nm || '3 Tuổi' }}</span>
             <span class="mx-2">|</span>
             <span>{{ result.general.class_nm || 'Timecity' }}</span>
          </div>
        </div>

        <div class="mt-6 px-2">
          <!-- 1. Lĩnh vực đánh giá -->
          <h2 class="text-base font-extrabold text-[#1b3664] mb-3 border-b-2 border-[#1b3664] pb-1">Lĩnh vực đánh giá</h2>
          <div class="grid grid-cols-12 gap-4 mb-8">
            <div class="col-span-7">
              <table class="w-full border-collapse border border-gray-200">
                <tbody>
                  <tr class="border-b border-gray-200">
                    <td class="p-2.5 text-center font-bold text-gray-700 w-[25%] bg-gray-50">Tư duy cơ bản</td>
                    <td class="p-2.5 text-gray-600 text-[11px]">Đánh giá sự chú ý và tập trung, quan sát và ghi nhớ, đó là những nhân tố cơ bản cần thiết cho sự phát triển tư duy.</td>
                  </tr>
                  <tr class="border-b border-gray-200">
                    <td class="p-2.5 text-center font-bold text-gray-700 bg-gray-50">Tư duy toán học</td>
                    <td class="p-2.5 text-gray-600 text-[11px]">Đánh giá năng lực về số và phép toán, không gian, đo lường, kiểu mẫu, thống kê; đó là những nhân tố cơ bản cần thiết cho sự phát triển tư duy.</td>
                  </tr>
                  <tr class="border-b border-gray-200">
                    <td class="p-2.5 text-center font-bold text-gray-700 bg-gray-50">Tư duy logic</td>
                    <td class="p-2.5 text-gray-600 text-[11px]">Đánh giá sự hiểu biết, áp dụng, phân tích và tổng hợp; đó là các thành phần của kỹ năng tư duy logic, kỹ năng cốt lõi để phát triển tư duy.</td>
                  </tr>
                  <tr>
                    <td class="p-2.5 text-center font-bold text-gray-700 bg-gray-50">Tư duy sáng tạo</td>
                    <td class="p-2.5 text-gray-600 text-[11px]">Đánh giá sự lưu loát, linh hoạt, độc đáo và tinh tế, đó là những kỹ năng cần thiết cho tư duy đa dạng và sâu sắc.</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div class="col-span-5">
              <table class="w-full border-collapse border border-gray-200 text-center text-[11px]">
                <thead>
                  <tr class="bg-[#e86036] text-white">
                    <th colspan="3" class="p-2 font-bold text-xs uppercase">Tiêu chuẩn và cấp độ</th>
                  </tr>
                  <tr class="bg-gray-100 text-gray-700 border-b border-gray-200">
                    <th class="p-1.5 font-semibold w-1/3 border-r border-gray-200">Tiêu chuẩn</th>
                    <th class="p-1.5 font-semibold w-1/3 border-r border-gray-200">Cấp độ</th>
                    <th class="p-1.5 font-semibold w-1/3">Điểm</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200"><td class="p-1.5 border-r border-gray-200 font-medium text-gray-700">Vượt trội</td><td class="p-1.5 border-r border-gray-200 font-bold text-gray-800">S</td><td class="p-1.5 text-gray-500">91~100</td></tr>
                  <tr class="border-b border-gray-200"><td class="p-1.5 border-r border-gray-200 font-medium text-gray-700">Rất tốt</td><td class="p-1.5 border-r border-gray-200 font-bold text-gray-800">A</td><td class="p-1.5 text-gray-500">71~90</td></tr>
                  <tr class="border-b border-gray-200"><td class="p-1.5 border-r border-gray-200 font-medium text-gray-700">Trung bình</td><td class="p-1.5 border-r border-gray-200 font-bold text-gray-800">B</td><td class="p-1.5 text-gray-500">41~70</td></tr>
                  <tr><td class="p-1.5 border-r border-gray-200 font-medium text-gray-700">Yếu</td><td class="p-1.5 border-r border-gray-200 font-bold text-gray-800">C</td><td class="p-1.5 text-gray-500">0~40</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 2. Kết quả đánh giá -->
          <h2 class="text-base font-extrabold text-[#1b3664] mb-4 border-b-2 border-[#1b3664] pb-1">Kết quả đánh giá</h2>
          
          <!-- Tư duy cơ bản -->
          <div class="grid-section grid grid-cols-12 gap-4 mb-8 items-center">
            <!-- Chart Left -->
            <div class="chart-container col-span-5 relative h-[250px] w-full border border-gray-100 rounded-lg p-1">
               <div ref="chart1" class="w-full h-full"></div>
            </div>
            <!-- Table Right -->
            <div class="col-span-7">
              <table class="w-full border-collapse border border-[#0050a0] text-center text-[11px] shadow-sm">
                <thead>
                  <tr class="bg-[#0050a0] text-white">
                    <th class="font-bold p-2 border-r border-white/20 w-[25%] text-left pl-3">Lĩnh vực chi tiết</th>
                    <th class="font-bold p-2 border-r border-white/20 text-left pl-3">Nội dung chi tiết</th>
                    <th class="font-bold p-2 w-[15%]">Cấp độ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Năng lực chú ý</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng tập trung quan sát để giải quyết vấn đề.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Năng lực chú ý') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Năng lực quan sát</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng quan sát tỉ mỉ một sự vật nào đó.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Năng lực quan sát') }}</td>
                  </tr>
                  <tr class="bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Năng lực ghi nhớ</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng ghi nhớ một thông tin mới về sự vật, sự việc.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Năng lực ghi nhớ') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tư duy toán học -->
          <div class="grid-section grid grid-cols-12 gap-4 mb-6 items-center">
            <!-- Chart Left -->
            <div class="chart-container col-span-5 relative h-[250px] w-full border border-gray-100 rounded-lg p-1">
               <div ref="chart2" class="w-full h-full"></div>
            </div>
            <!-- Table Right -->
            <div class="col-span-7">
              <table class="w-full border-collapse border border-[#0050a0] text-center text-[11px] shadow-sm">
                <thead>
                  <tr class="bg-[#0050a0] text-white">
                    <th class="font-bold p-2 border-r border-white/20 w-[25%] text-left pl-3">Lĩnh vực chi tiết</th>
                    <th class="font-bold p-2 border-r border-white/20 text-left pl-3">Nội dung chi tiết</th>
                    <th class="font-bold p-2 w-[15%]">Cấp độ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Số và tính toán</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng hiểu những khái niệm về tính toán cơ bản, hiểu được ý nghĩa của toán học trong đời sống sinh hoạt hàng ngày.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Số và tính toán') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Hình học không gian</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng nhận biết vị trí và hướng trong không gian và hiểu biết về hình dạng.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Hình học không gian') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Đo lường</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng thể hiện số lượng bằng cách sử dụng số và so sánh các đồ vật với nhau.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Đo lường') }}</td>
                  </tr>
                  <tr class="bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Kiểu mẫu</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng nhận diện và dự đoán mối quan hệ mang tính quy luật của đồ vật.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Kiểu mẫu') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
        </div>
      </div>

      <!-- PAGE BREAK -->
      <div class="page-break-element"></div>

      <!-- PAGE 2 (NO HEADER - AS REQUESTED) -->
      <div class="page-container bg-white pb-8 pt-6">
        <div class="mt-4 px-2">
          <!-- Tư duy logic -->
          <div class="grid-section grid grid-cols-12 gap-4 mb-8 items-center">
            <!-- Chart Left -->
            <div class="chart-container col-span-5 relative h-[250px] w-full border border-gray-100 rounded-lg p-1">
               <div ref="chart3" class="w-full h-full"></div>
            </div>
            <!-- Table Right -->
            <div class="col-span-7">
              <table class="w-full border-collapse border border-[#0050a0] text-center text-[11px] shadow-sm">
                <thead>
                  <tr class="bg-[#0050a0] text-white">
                    <th class="font-bold p-2 border-r border-white/20 w-[25%] text-left pl-3">Lĩnh vực chi tiết</th>
                    <th class="font-bold p-2 border-r border-white/20 text-left pl-3">Nội dung chi tiết</th>
                    <th class="font-bold p-2 w-[15%]">Cấp độ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Hiểu biết</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng phân tích những dự kiện liên quan và hiểu về vấn đề cần giải quyết.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Hiểu biết') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Ứng dụng</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng vận dụng linh hoạt những vấn đề đã học vào thực hành thực tế.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Ứng dụng') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Phân tích</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Năng lực so sánh các thuộc tính và khái quát hóa vấn đề.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Phân tích') }}</td>
                  </tr>
                  <tr class="bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tổng hợp</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng phân tích tình huống và giải quyết toàn diện vấn đề.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Tổng hợp') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tư duy sáng tạo -->
          <div class="grid-section grid grid-cols-12 gap-4 mb-10 items-center">
            <!-- Chart Left -->
            <div class="chart-container col-span-5 relative h-[250px] w-full border border-gray-100 rounded-lg p-1">
               <div ref="chart4" class="w-full h-full"></div>
            </div>
            <!-- Table Right -->
            <div class="col-span-7">
              <table class="w-full border-collapse border border-[#0050a0] text-center text-[11px] shadow-sm">
                <thead>
                  <tr class="bg-[#0050a0] text-white">
                    <th class="font-bold p-2 border-r border-white/20 w-[25%] text-left pl-3">Lĩnh vực chi tiết</th>
                    <th class="font-bold p-2 border-r border-white/20 text-left pl-3">Nội dung chi tiết</th>
                    <th class="font-bold p-2 w-[15%]">Cấp độ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Sự trôi chảy</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng thể hiện nhiều ý tưởng mới lạ.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Sự trôi chảy') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tính linh hoạt</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng tìm và giải quyết vấn đề và những ý tưởng đa dạng bằng cách vận dụng một đối tượng thống nhất.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Tính linh hoạt') }}</td>
                  </tr>
                  <tr class="border-b border-gray-200 bg-white">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tính độc đáo</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng đưa ra một ý tưởng độc đáo và mới lạ hoặc hoàn toàn khác biệt.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Tính độc đáo') }}</td>
                  </tr>
                  <tr class="bg-[#f4f7fa]">
                    <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tính chính xác</td>
                    <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">Khả năng bổ sung thêm những điểm chi tiết của ý tưởng có tính phức tạp cao.</td>
                    <td class="p-2.5 font-bold text-gray-800">{{ getGrade('Tính chính xác') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 3. Đánh giá tổng quát -->
          <div class="overall-section">
            <h2 class="text-base font-extrabold text-[#1b3664] mb-4 border-b-2 border-[#1b3664] pb-1">Đánh giá tổng quát</h2>
            <div class="grid-section grid grid-cols-12 gap-4 items-center">
              <!-- Chart Left -->
              <div class="overall-chart-container col-span-5 relative h-[270px] w-full border border-gray-100 rounded-lg p-1">
                 <div ref="chartOverall" class="w-full h-full"></div>
              </div>
              <!-- Table Right -->
              <div class="col-span-7">
                <table class="w-full border-collapse border border-[#e86036] text-center text-[11px] shadow-sm">
                  <thead>
                    <tr class="bg-[#e86036] text-white">
                      <th class="font-bold p-2 border-r border-white/20 w-[20%] text-left pl-3">Lĩnh vực</th>
                      <th class="font-bold p-2 border-r border-white/20 text-left pl-3">Nội dung</th>
                      <th class="font-bold p-2 w-[15%]">Trung bình</th>
                      <th class="font-bold p-2 w-[20%]">Bình quân của tất cả HS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-b border-gray-200 bg-[#fef6ef]">
                      <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tư duy cơ bản</td>
                      <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">
                        Tư duy cơ bản của học sinh {{ result.general.stu_nm }} ở mức độ <span class="text-[#dc3545] font-bold">{{ getGradeLabel(getGrade('Tư duy cơ bản')) }}</span>.
                      </td>
                      <td class="border-r border-gray-200 p-2.5 font-bold text-gray-800">{{ getGrade('Tư duy cơ bản') }}</td>
                      <td class="p-2.5 text-gray-500">B</td>
                    </tr>
                    <tr class="border-b border-gray-200 bg-white">
                      <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tư duy toán học</td>
                      <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">
                        Tư duy toán học của học sinh {{ result.general.stu_nm }} ở mức độ <span class="text-[#dc3545] font-bold">{{ getGradeLabel(getGrade('Tư duy toán học')) }}</span>.
                      </td>
                      <td class="border-r border-gray-200 p-2.5 font-bold text-gray-800">{{ getGrade('Tư duy toán học') }}</td>
                      <td class="p-2.5 text-gray-500">B</td>
                    </tr>
                    <tr class="border-b border-gray-200 bg-[#fef6ef]">
                      <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tư duy logic</td>
                      <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">
                        Tư duy logic của học sinh {{ result.general.stu_nm }} ở mức độ <span class="text-[#dc3545] font-bold">{{ getGradeLabel(getGrade('Tư duy logic')) }}</span>.
                      </td>
                      <td class="border-r border-gray-200 p-2.5 font-bold text-gray-800">{{ getGrade('Tư duy logic') }}</td>
                      <td class="p-2.5 text-gray-500">B</td>
                    </tr>
                    <tr class="bg-white">
                      <td class="border-r border-gray-200 p-2.5 text-left font-bold text-gray-700 pl-3">Tư duy sáng tạo</td>
                      <td class="border-r border-gray-200 p-2.5 text-left text-gray-600">
                        Tư duy sáng tạo của học sinh {{ result.general.stu_nm }} ở mức độ <span class="text-[#dc3545] font-bold">{{ getGradeLabel(getGrade('Tư duy sáng tạo')) }}</span>.
                      </td>
                      <td class="border-r border-gray-200 p-2.5 font-bold text-gray-800">{{ getGrade('Tư duy sáng tạo') }}</td>
                      <td class="p-2.5 text-gray-500">B</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import * as echarts from 'echarts';

export default {
  name: 'UcreaEvalResult',
  data() {
    return {
      result: null,
      loading: true,
      charts: []
    }
  },
  computed: {
    reportData() {
      if (!this.result || !this.result.general.report_data) return {};
      try {
        return JSON.parse(this.result.general.report_data);
      } catch(e) {
        return {};
      }
    },
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
  unmounted() {
    this.charts.forEach(c => {
      if (c && !c.isDisposed()) c.dispose();
    });
  },
  methods: {
    async fetchResult() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const response = await axios.get(`/api/ucrea/results/${id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        if (response.data.status === 'success') {
          this.result = response.data.data;
          this.loading = false;
          this.$nextTick(() => {
            this.initCharts();
          });
        }
      } catch (error) {
        console.error("Error fetching UCREA result", error);
        this.loading = false;
      }
    },
    getGrade(skillName) {
       const found = this.parsedSkillsGrade.find(s => s.skill === skillName);
       return found ? found.grade : 'B';
    },
    getGradeLabel(grade) {
      const mapping = {
        'S': 'Vượt trội',
        'A': 'Rất tốt',
        'B': 'Tốt',
        'C': 'Trung bình',
        'L': 'Yếu'
      };
      return mapping[grade] || grade || 'Tốt';
    },
    exportPdf() {
      // Add print class to body to force 750px layout
      document.body.classList.add('is-printing');
      
      const restoreLayout = () => {
        document.body.classList.remove('is-printing');
        this.charts.forEach(c => { if(c) c.resize(); });
        window.removeEventListener('afterprint', restoreLayout);
      };
      window.addEventListener('afterprint', restoreLayout);
      
      this.$nextTick(() => {
        // Force all charts to resize to the 750px width
        this.charts.forEach(c => { if(c) c.resize(); });
        
        // Allow DOM to update and then trigger native print
        setTimeout(() => {
          window.print();
          // Fallback if afterprint doesn't fire
          setTimeout(restoreLayout, 2000);
        }, 300);
      });
    },
    initCharts() {
      const rd = this.reportData;
      if(!rd.name) return;

      const commonOpts = {
        grid: { top: 30, bottom: 25, left: 25, right: 5 },
        tooltip: { trigger: 'axis' },
        yAxis: { type: 'value', max: 100, axisLabel: { fontSize: 10 }, splitLine: { lineStyle: { color: '#eee' } } },
        xAxis: { type: 'category', axisLabel: { interval: 0, fontSize: 9, width: 50, overflow: 'break' }, axisTick: { show: false } },
        animation: false
      };

      const legendTopRight = { top: 0, right: 0, data: [rd.name, 'Giữa kỳ', 'Cuối kỳ'], itemGap: 5, itemWidth: 15, itemHeight: 10, textStyle: { fontSize: 10, color: '#666' } };

      // 1. Tư duy cơ bản
      const c1 = echarts.init(this.$refs.chart1);
      c1.setOption({
        ...commonOpts,
        title: { text: 'Tư duy cơ bản', left: 'center', textStyle: { fontSize: 13, color: '#333', fontWeight: 'bold' } },
        legend: legendTopRight,
        xAxis: { ...commonOpts.xAxis, data: ['Năng lực\nchú ý', 'Năng lực\nquan sát', 'Năng lực\nghi nhớ'] },
        series: [{ name: rd.name, type: 'bar', data: [rd.sk11, rd.sk12, rd.sk13], barMaxWidth: 20, itemStyle: { color: '#dc3545' } }]
      });

      // 2. Tư duy toán học
      const c2 = echarts.init(this.$refs.chart2);
      c2.setOption({
        ...commonOpts,
        title: { text: 'Tư duy toán học', left: 'center', textStyle: { fontSize: 13, color: '#333', fontWeight: 'bold' } },
        legend: legendTopRight,
        xAxis: { ...commonOpts.xAxis, data: ['Số và\ntính toán', 'Hình học\nkhông gian', 'Đo lường', 'Kiểu mẫu'] },
        series: [{ name: rd.name, type: 'bar', data: [rd.kn11, rd.kn12, rd.kn13, rd.kn14], barMaxWidth: 20, itemStyle: { color: '#dc3545' } }]
      });

      // 3. Tư duy logic
      const c3 = echarts.init(this.$refs.chart3);
      c3.setOption({
        ...commonOpts,
        title: { text: 'Tư duy logic', left: 'center', textStyle: { fontSize: 13, color: '#333', fontWeight: 'bold' } },
        legend: legendTopRight,
        xAxis: { ...commonOpts.xAxis, data: ['Hiểu biết', 'Ứng dụng', 'Phân tích', 'Tổng hợp'] },
        series: [{ name: rd.name, type: 'bar', data: [rd.sk21, rd.sk22, rd.sk23, rd.sk24], barMaxWidth: 20, itemStyle: { color: '#dc3545' } }]
      });

      // 4. Tư duy sáng tạo
      const c4 = echarts.init(this.$refs.chart4);
      c4.setOption({
        ...commonOpts,
        title: { text: 'Tư duy sáng tạo', left: 'center', textStyle: { fontSize: 13, color: '#333', fontWeight: 'bold' } },
        legend: legendTopRight,
        xAxis: { ...commonOpts.xAxis, data: ['Sự trôi\nchảy', 'Tính linh\nhoạt', 'Tính độc\nđáo', 'Tính chính\nxác'] },
        series: [{ name: rd.name, type: 'bar', data: [rd.sk31, rd.sk32, rd.sk33, rd.sk34], barMaxWidth: 20, itemStyle: { color: '#dc3545' } }]
      });

      // 5. Đánh giá tổng quát
      const cOverall = echarts.init(this.$refs.chartOverall);
      const avg1 = Math.round((Number(rd.sk11 || 0) + Number(rd.sk12 || 0) + Number(rd.sk13 || 0)) / 3);
      const avg2 = Math.round((Number(rd.kn11 || 0) + Number(rd.kn12 || 0) + Number(rd.kn13 || 0) + Number(rd.kn14 || 0)) / 4);
      const avg3 = Math.round((Number(rd.sk21 || 0) + Number(rd.sk22 || 0) + Number(rd.sk23 || 0) + Number(rd.sk24 || 0)) / 4);
      const avg4 = Math.round((Number(rd.sk31 || 0) + Number(rd.sk32 || 0) + Number(rd.sk33 || 0) + Number(rd.sk34 || 0)) / 4);
      
      const stuName = this.result.general.stu_nm || 'Học sinh';

      cOverall.setOption({
        ...commonOpts,
        title: { text: 'Bình quân tổng quát', left: 'center', textStyle: { fontSize: 13, color: '#333', fontWeight: 'bold' } },
        legend: { top: 0, right: 0, data: [stuName, 'Bình quân tất cả HS'], itemGap: 5, itemWidth: 15, itemHeight: 10, textStyle: { fontSize: 10, color: '#666' } },
        xAxis: { ...commonOpts.xAxis, data: ['Tư duy\ncơ bản', 'Tư duy\ntoán học', 'Tư duy\nlogic', 'Tư duy\nsáng tạo'] },
        series: [
          { name: stuName, type: 'bar', data: [avg1, avg2, avg3, avg4], barMaxWidth: 20, itemStyle: { color: '#dc3545' } },
          { name: 'Bình quân tất cả HS', type: 'bar', data: [66, 71, 69, 61], barMaxWidth: 20, itemStyle: { color: '#cccccc' } }
        ]
      });

      this.charts = [c1, c2, c3, c4, cOverall];
      
      // Auto resize
      window.addEventListener('resize', () => {
        this.charts.forEach(c => { if(c) c.resize() });
      });
    }
  }
}
</script>

<style>
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
}

body.is-printing, body.is-printing html, body.is-printing #app, body.is-printing div[class*="min-h-screen"], body.is-printing .layout-wrapper, body.is-printing main {
  height: auto !important;
  min-height: 0 !important;
  overflow: visible !important;
  position: static !important;
}

body.is-printing :has(#pdf-content) {
  height: auto !important;
  min-height: 0 !important;
  max-height: none !important;
  overflow: visible !important;
  position: static !important;
}

body.is-printing #pdf-content {
  width: 900px !important;
  max-width: 900px !important;
  zoom: 0.82 !important;
  padding: 24px !important;
  margin: 0 auto !important;
  border: none !important;
  box-shadow: none !important;
}

body.is-printing .grid-section, body.is-printing .overall-section {
  break-inside: avoid !important;
  page-break-inside: avoid !important;
}

@media print {
  @page {
    margin: 0.5cm;
    size: A4 portrait;
  }
  :has(#pdf-content) {
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    position: static !important;
  }
  .page-break-element {
    page-break-before: always;
    height: 0;
  }
  #pdf-content {
    width: 900px !important;
    max-width: 900px !important;
    zoom: 0.82 !important;
    position: static !important;
    opacity: 1 !important;
    display: block !important;
    border: none !important;
    box-shadow: none !important;
    padding: 24px !important;
    margin: 0 auto !important;
  }
  .grid-section, .overall-section {
    break-inside: avoid !important;
    page-break-inside: avoid !important;
  }
}
</style>
<style scoped>
.page-break-element {
  height: 20px;
  background-color: transparent;
}
</style>
