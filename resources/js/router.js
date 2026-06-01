import { createRouter, createWebHistory } from 'vue-router';
import Login from './views/Login.vue';
import Dashboard from './views/Dashboard.vue';
import BranchList from './views/BranchList.vue';
import TeacherList from './views/TeacherList.vue';
import ClassList from './views/ClassList.vue';
import StudentList from './views/StudentList.vue';
import ContractList from './views/ContractList.vue';
import TestList from './views/TestList.vue';
import UcreaEvalList from './views/UcreaEvalList.vue';
import UcreaEvalResult from './views/UcreaEvalResult.vue';
import UcreaEvalForm from './views/UcreaEvalForm.vue';
import IgbhEvalList from './views/IgbhEvalList.vue';
import IgbhEvalResult from './views/IgbhEvalResult.vue';
import IgbhEvalForm from './views/IgbhEvalForm.vue';
import IgbhWeeklyEvalList from './views/IgbhWeeklyEvalList.vue';
import IgbhWeeklyEvalForm from './views/IgbhWeeklyEvalForm.vue';
import IgbhSummativeEvalList from './views/IgbhSummativeEvalList.vue';
import IgbhSummativeEvalReport from './views/IgbhSummativeEvalReport.vue';
import IgbhTestConfig from './views/IgbhTestConfig.vue';
import UserList from './views/UserList.vue';

const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/branches', name: 'branches', component: BranchList, meta: { requiresAuth: true } },
    { path: '/teachers', name: 'teachers', component: TeacherList, meta: { requiresAuth: true } },
    { path: '/classes', name: 'classes', component: ClassList, meta: { requiresAuth: true } },
    { path: '/students', name: 'students', component: StudentList, meta: { requiresAuth: true } },
    { path: '/contracts', name: 'contracts', component: ContractList, meta: { requiresAuth: true } },
    { path: '/ucrea/tests', name: 'ucrea-tests', component: TestList, meta: { requiresAuth: true } },
    { path: '/igbh/tests', name: 'igbh-tests', component: TestList, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations', name: 'ucrea-evaluations', component: UcreaEvalList, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations/:id', name: 'ucrea-eval-result', component: UcreaEvalResult, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations/grade/:id', name: 'ucrea-eval-form', component: UcreaEvalForm, meta: { requiresAuth: true } },
    { path: '/igbh/evaluations', name: 'igbh-evaluations', component: IgbhEvalList, meta: { requiresAuth: true } },
    { path: '/igbh/evaluations/:id', name: 'igbh-eval-result', component: IgbhEvalResult, meta: { requiresAuth: true } },
    { path: '/igbh/evaluations/grade/:id', name: 'igbh-eval-form', component: IgbhEvalForm, meta: { requiresAuth: true } },
    { path: '/igbh/weekly', name: 'igbh-weekly-evaluations', component: IgbhWeeklyEvalList, meta: { requiresAuth: true } },
    { path: '/igbh/weekly/grade/:id?', name: 'igbh-weekly-eval-form', component: IgbhWeeklyEvalForm, meta: { requiresAuth: true } },
    { path: '/igbh/summative/evaluations', name: 'igbh-summative-evaluations', component: IgbhSummativeEvalList, meta: { requiresAuth: true } },
    { path: '/igbh/summative/report/:id', name: 'igbh-summative-eval-report', component: IgbhSummativeEvalReport, meta: { requiresAuth: true } },
    { path: '/igbh/test-config/:id', name: 'igbh-test-config', component: IgbhTestConfig, meta: { requiresAuth: true, role: 'admin' } },
    { path: '/system/users', name: 'users', component: UserList, meta: { requiresAuth: true, role: 'admin' } },
];


const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    if (to.meta.requiresAuth && !token) {
        next({ name: 'login' });
    } else if (to.name === 'login' && token) {
        next({ name: 'dashboard' });
    } else if (to.meta.role) {
        const userRole = localStorage.getItem('user_role');
        if (to.meta.role === 'admin' && userRole !== 'admin') {
            next({ name: 'dashboard' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;
