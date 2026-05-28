import { createI18n } from 'vue-i18n';

const messages = {
  en: {
    sidebar: {
      dashboard: 'Dashboard',
      branches: 'Branches',
      teachers: 'Teachers',
      classes: 'Classes',
      students: 'Students',
      contracts: 'Contracts',
      tests: 'Tests'
    },
    header: {
      welcome: 'Welcome to CMS EDU LMS Management Portal',
      light_mode: 'Light Mode',
      dark_mode: 'Dark Mode',
      system_live: 'System Live',
      logout: 'Logout'
    },
    common: {
      search: 'Search...',
      actions: 'Actions',
      edit: 'Edit',
      delete: 'Delete',
      cancel: 'Cancel',
      save: 'Save',
      add_new: 'Add New',
      status: 'Status',
      active: 'Active',
      inactive: 'Inactive',
      stt: 'STT',
      name: 'Name',
      lms_id: 'LMS ID',
      email: 'Email',
      phone: 'Phone',
      branch: 'Branch',
      remark: 'Remark'
    },
    branches: {
      title: 'Branches (Centers)',
      desc: 'View and manage LMS branch settings',
      add_btn: '+ Add Branch',
      search: 'Search branches by name, LMS ID...',
      no_data: 'No branches found.',
      modal_add: 'Add New Branch',
      modal_edit: 'Edit Branch',
      form: {
        name: 'Branch Name',
        hotline: 'Hotline'
      }
    },
    teachers: {
      title: 'Teachers',
      desc: 'Manage instructor accounts and center assignments',
      add_btn: '+ Add Teacher',
      search: 'Search teachers...',
      no_data: 'No teachers found.',
      modal_add: 'Add New Teacher',
      modal_edit: 'Edit Teacher',
      cols: {
        branch_lms_id: 'Branch LMS ID',
        head_teacher: 'Head Teacher'
      },
      form: {
        name: 'Teacher Name',
        yes: 'Yes',
        no: 'No'
      }
    },
    classes: {
      title: 'Classes',
      desc: 'Manage U-Crea and i-Garten class structures',
      add_btn: '+ Add Class',
      search: 'Search classes...',
      no_data: 'No classes found.',
      modal_add: 'Add New Class',
      modal_edit: 'Edit Class',
      cols: {
        class_name: 'Class Name',
        lms_seq: 'LMS Sequence',
        level: 'Level',
        type: 'Type',
        teacher_id: 'Teacher ID'
      }
    },
    students: {
      title: 'Students',
      desc: 'View and edit LMS student sync records',
      add_btn: '+ Add Student',
      search: 'Search students...',
      no_data: 'No students found.',
      modal_add: 'Add New Student',
      modal_edit: 'Edit Student',
      cols: {
        student_name: 'Student Name',
        accounting_id: 'Accounting ID',
        dob: 'Date of Birth',
        gender: 'Gender'
      },
      form: {
        male: 'Male',
        female: 'Female'
      }
    },
    contracts: {
      title: 'Contracts & Enrollments',
      desc: 'Manage student contracts, dates and status codes',
      add_btn: '+ Add Contract',
      search: 'Search contracts...',
      no_data: 'No contracts found.',
      modal_add: 'Add New Contract',
      modal_edit: 'Edit Contract',
      cols: {
        student: 'Student',
        class: 'Class',
        start_date: 'Start Date',
        end_date: 'End Date',
        valid_cd: 'Valid CD'
      },
      form: {
        enrolled: 'Enrolled (SS001)',
        pending: 'Pending (SS002)',
        trial: 'Trial (VC001)',
        regular: 'Regular (VC005)'
      }
    },
    tests: {
      title: 'Test Management',
      desc: 'View, search, and preview academic tests and placement sheets',
      search: 'Search by test name, level, code...',
      no_data: 'No test sheets found matching criteria.',
      cols: {
        test_name: 'Test Name',
        level: 'Grade / Level'
      }
    }
  },
  vi: {
    sidebar: {
      dashboard: 'Bảng điều khiển',
      branches: 'Cơ sở',
      teachers: 'Giáo viên',
      classes: 'Lớp học',
      students: 'Học viên',
      contracts: 'Hợp đồng',
      tests: 'Bài kiểm tra'
    },
    header: {
      welcome: 'Chào mừng đến với Cổng Quản Lý LMS CMS EDU',
      light_mode: 'Giao diện sáng',
      dark_mode: 'Giao diện tối',
      system_live: 'Hệ thống online',
      logout: 'Đăng xuất'
    },
    common: {
      search: 'Tìm kiếm...',
      actions: 'Thao tác',
      edit: 'Sửa',
      delete: 'Xóa',
      cancel: 'Hủy',
      save: 'Lưu',
      add_new: 'Thêm mới',
      status: 'Trạng thái',
      active: 'Hoạt động',
      inactive: 'Ngưng hoạt động',
      stt: 'STT',
      name: 'Tên',
      lms_id: 'Mã LMS',
      email: 'Email',
      phone: 'Điện thoại',
      branch: 'Cơ sở',
      remark: 'Ghi chú'
    },
    branches: {
      title: 'Cơ sở (Trung tâm)',
      desc: 'Xem và quản lý cấu hình các cơ sở LMS',
      add_btn: '+ Thêm Cơ sở',
      search: 'Tìm kiếm cơ sở theo tên, mã LMS...',
      no_data: 'Không tìm thấy cơ sở nào.',
      modal_add: 'Thêm Cơ sở mới',
      modal_edit: 'Sửa Cơ sở',
      form: {
        name: 'Tên Cơ sở',
        hotline: 'Hotline'
      }
    },
    teachers: {
      title: 'Giáo viên',
      desc: 'Quản lý tài khoản giáo viên và phân bổ cơ sở',
      add_btn: '+ Thêm Giáo viên',
      search: 'Tìm kiếm giáo viên...',
      no_data: 'Không tìm thấy giáo viên nào.',
      modal_add: 'Thêm Giáo viên mới',
      modal_edit: 'Sửa Giáo viên',
      cols: {
        branch_lms_id: 'Mã LMS Cơ sở',
        head_teacher: 'Giáo viên trưởng'
      },
      form: {
        name: 'Tên Giáo viên',
        yes: 'Có',
        no: 'Không'
      }
    },
    classes: {
      title: 'Lớp học',
      desc: 'Quản lý cấu trúc lớp học U-Crea và i-Garten',
      add_btn: '+ Thêm Lớp học',
      search: 'Tìm kiếm lớp học...',
      no_data: 'Không tìm thấy lớp học nào.',
      modal_add: 'Thêm Lớp học mới',
      modal_edit: 'Sửa Lớp học',
      cols: {
        class_name: 'Tên lớp',
        lms_seq: 'Sequence LMS',
        level: 'Cấp độ (Level)',
        type: 'Loại',
        teacher_id: 'Mã Giáo viên'
      }
    },
    students: {
      title: 'Học viên',
      desc: 'Xem và chỉnh sửa dữ liệu đồng bộ học viên LMS',
      add_btn: '+ Thêm Học viên',
      search: 'Tìm kiếm học viên...',
      no_data: 'Không tìm thấy học viên nào.',
      modal_add: 'Thêm Học viên mới',
      modal_edit: 'Sửa Học viên',
      cols: {
        student_name: 'Tên Học viên',
        accounting_id: 'Mã Kế toán',
        dob: 'Ngày sinh',
        gender: 'Giới tính'
      },
      form: {
        male: 'Nam',
        female: 'Nữ'
      }
    },
    contracts: {
      title: 'Hợp đồng & Tuyển sinh',
      desc: 'Quản lý hợp đồng học viên, ngày tháng và mã trạng thái',
      add_btn: '+ Thêm Hợp đồng',
      search: 'Tìm kiếm hợp đồng...',
      no_data: 'Không tìm thấy hợp đồng nào.',
      modal_add: 'Thêm Hợp đồng mới',
      modal_edit: 'Sửa Hợp đồng',
      cols: {
        student: 'Học viên',
        class: 'Lớp học',
        start_date: 'Ngày bắt đầu',
        end_date: 'Ngày kết thúc',
        valid_cd: 'Mã hiệu lực (Valid CD)'
      },
      form: {
        enrolled: 'Đã nhập học (SS001)',
        pending: 'Chờ xử lý (SS002)',
        trial: 'Học thử (VC001)',
        regular: 'Chính thức (VC005)'
      }
    },
    tests: {
      title: 'Quản lý Bài kiểm tra',
      desc: 'Xem, tìm kiếm và xem trước bài kiểm tra',
      search: 'Tìm kiếm theo tên, cấp độ, mã bài...',
      no_data: 'Không tìm thấy bài kiểm tra nào khớp với tiêu chí.',
      cols: {
        test_name: 'Tên Bài kiểm tra',
        level: 'Lớp / Cấp độ'
      }
    }
  }
};

const i18n = createI18n({
  legacy: true,
  locale: localStorage.getItem('locale') || 'vi',
  fallbackLocale: 'en',
  messages,
});

export default i18n;
