<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\LmsClass;

class ContractController extends Controller
{
        public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = Contract::with(['student', 'lmsClass.teacher', 'branch']);

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user && method_exists($user, 'scopeContracts')) {
            $user->scopeContracts($query);
        }

        // Search Keyword
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('crm_id', 'LIKE', "%{$search}%")
                       ->orWhere('id_lms', 'LIKE', "%{$search}%");
                });
            });
        }

        // Branch filter
        if ($request->has('branch_id')) {
            $branch_ids = $request->query('branch_id');
            if (is_array($branch_ids) && count($branch_ids) > 0) {
                $query->whereIn('branch_id', $branch_ids);
            } elseif (!is_array($branch_ids) && $branch_ids) {
                $query->where('branch_id', $branch_ids);
            }
        }

        // Class filter
        if ($class_id = $request->query('class_id')) {
            $query->where('class_id', $class_id);
        }

        // Status filter
        $status = $request->query('status', 1);
        if ($status !== 'all') {
            if ($status == 1) {
                $query->where('status', '!=', 'SS004'); // Đăng ký
            } elseif ($status == 0) {
                $query->where('status', 'SS004'); // Hủy đăng ký
            }
        }

        $contracts = $query->orderBy('id', 'desc')->paginate($limit);

        // Map joined names for frontend
        $data = $contracts->toArray();
        $data['data'] = collect($data['data'])->map(function ($c) {
            $c['student_name'] = $c['student']['name'] ?? '';
            $c['student_crm_id'] = $c['student']['crm_id'] ?? '';
            $c['student_lms_id'] = $c['student']['id_lms'] ?? '';
            $gender = $c['student']['gender'] ?? '';
            $c['student_gender'] = ($gender == 'M') ? 'Nam' : (($gender == 'F') ? 'Nữ' : 'Khác');
            
            $c['class_name'] = $c['lms_class']['cls_name'] ?? '';
            $c['level_name'] = $c['lms_class']['level_name'] ?? '';
            $c['teacher_name'] = $c['lms_class']['teacher']['ins_name'] ?? '';
            
            $c['branch_name'] = $c['branch']['name'] ?? '';
            
            $status_label = ($c['status'] == 'SS004') ? 'Hủy đăng ký' : 'Đã đăng ký';
            $c['status_label'] = $status_label;
            
            $days = 0;
            if (!empty($c['enrolment_start_date']) && !empty($c['enrolment_last_date'])) {
                $days = (strtotime($c['enrolment_last_date']) - strtotime($c['enrolment_start_date'])) / (60 * 60 * 24);
                $days = round($days) . 'd';
            }
            $c['time_range'] = (!empty($c['enrolment_start_date']) && !empty($c['enrolment_last_date'])) 
                ? $c['enrolment_start_date'] . '~' . $c['enrolment_last_date'] . ' (' . $days . ')' 
                : '';

            unset($c['student'], $c['lms_class'], $c['branch']);
            return $c;
        })->toArray();

        return response()->json($data);
    }

    public function export(Request $request)
    {
        $query = Contract::with(['student', 'lmsClass.teacher', 'branch']);

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user && method_exists($user, 'scopeContracts')) {
            $user->scopeContracts($query);
        }

        // Search Keyword
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('crm_id', 'LIKE', "%{$search}%")
                       ->orWhere('id_lms', 'LIKE', "%{$search}%");
                });
            });
        }

        // Branch filter
        if ($request->has('branch_id')) {
            $branch_ids = $request->query('branch_id');
            if (is_array($branch_ids) && count($branch_ids) > 0) {
                $query->whereIn('branch_id', $branch_ids);
            } elseif (!is_array($branch_ids) && $branch_ids) {
                $query->where('branch_id', $branch_ids);
            }
        }

        // Class filter
        if ($class_id = $request->query('class_id')) {
            $query->where('class_id', $class_id);
        }

        // Status filter
        $status = $request->query('status', 1);
        if ($status !== 'all') {
            if ($status == 1) {
                $query->where('status', '!=', 'SS004');
            } elseif ($status == 0) {
                $query->where('status', 'SS004');
            }
        }

        $list = $query->orderBy('id', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Define Headers
        $headers = [
            'STT', 'Tên trung tâm', 'Tên lớp', 'Giáo viên', 'LEVEL', 'Mã LMS', 'Mã học sinh', 'Học sinh Tên', 'Giới tính', 'Thời gian đăng ký', 'Trạng thái', 'Ngày đăng ký'
        ];
        
        $sheet->setCellValue('A1', 'DANH SÁCH HỢP ĐỒNG TUYỂN SINH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'L') as $idx => $col) {
            $sheet->setCellValue($col . '3', $headers[$idx]);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
        }

        $widths = [8, 25, 20, 20, 15, 15, 15, 25, 10, 35, 15, 20];
        foreach (range('A', 'L') as $idx => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$idx]);
        }

        $borderOnly = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]]];
        $centerAlign = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

        $row = 4;
        foreach ($list as $i => $item) {
            $student_name = $item->student->name ?? '';
            $student_crm_id = $item->student->crm_id ?? '';
            $student_lms_id = $item->student->id_lms ?? '';
            $gender = $item->student->gender ?? '';
            $gender_str = ($gender == 'M') ? 'Nam' : (($gender == 'F') ? 'Nữ' : 'Khác');
            
            $class_name = $item->lmsClass->cls_name ?? '';
            $level_name = $item->lmsClass->level_name ?? '';
            $teacher_name = $item->lmsClass->teacher->ins_name ?? '';
            $branch_name = $item->branch->name ?? '';
            
            $status_label = ($item->status == 'SS004') ? 'Hủy đăng ký' : 'Đã đăng ký';
            
            $days = 0;
            if (!empty($item->enrolment_start_date) && !empty($item->enrolment_last_date)) {
                $days = (strtotime($item->enrolment_last_date) - strtotime($item->enrolment_start_date)) / (60 * 60 * 24);
                $days = round($days) . 'd';
            }
            $time_range = (!empty($item->enrolment_start_date) && !empty($item->enrolment_last_date)) 
                ? $item->enrolment_start_date . '~' . $item->enrolment_last_date . ' (' . $days . ')' 
                : '';

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $branch_name);
            $sheet->setCellValue('C' . $row, $class_name);
            $sheet->setCellValue('D' . $row, $teacher_name);
            $sheet->setCellValue('E' . $row, $level_name);
            $sheet->setCellValueExplicit('F' . $row, $student_lms_id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $student_crm_id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $row, $student_name);
            $sheet->setCellValue('I' . $row, $gender_str);
            $sheet->setCellValue('J' . $row, $time_range);
            $sheet->setCellValue('K' . $row, $status_label);
            $sheet->setCellValue('L' . $row, substr($item->created_at, 0, 10));

            $sheet->getStyle("A$row:L$row")->applyFromArray($borderOnly);
            $sheet->getStyle("A$row")->applyFromArray($centerAlign);
            $sheet->getStyle("I$row")->applyFromArray($centerAlign);
            $sheet->getStyle("K$row")->applyFromArray($centerAlign);
            
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        ob_start();
        $writer->save("php://output");
        $content = ob_get_contents();
        ob_end_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Danh_sach_hop_dong.xlsx"',
        ]);
    }

    public function store(Request $request)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $contract = Contract::create($request->all());
        return response()->json($contract, 201);
    }

    public function show($id)
    {
        return response()->json(Contract::with(['student', 'lmsClass', 'branch'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'student_id' => 'sometimes|required|exists:students,id',
            'class_id' => 'sometimes|required|exists:classes,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        $contract = Contract::findOrFail($id);
        
        // Filter out null values which might come from empty strings
        $data = array_filter($request->all(), function($value) {
            return $value !== null && $value !== '';
        });
        
        $contract->update($data);
        return response()->json($contract);
    }

    public function destroy(Request $request, $id)
    {
        $user = AuthController::resolveUser($request);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        Contract::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
