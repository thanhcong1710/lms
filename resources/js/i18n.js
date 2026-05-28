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
      stt: 'STT'
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
      active: 'Đang hoạt động',
      inactive: 'Ngưng hoạt động',
      stt: 'STT'
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
