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

const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/branches', name: 'branches', component: BranchList, meta: { requiresAuth: true } },
    { path: '/teachers', name: 'teachers', component: TeacherList, meta: { requiresAuth: true } },
    { path: '/classes', name: 'classes', component: ClassList, meta: { requiresAuth: true } },
    { path: '/students', name: 'students', component: StudentList, meta: { requiresAuth: true } },
    { path: '/contracts', name: 'contracts', component: ContractList, meta: { requiresAuth: true } },
    { path: '/tests', name: 'tests', component: TestList, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations', name: 'ucrea-evaluations', component: UcreaEvalList, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations/:id', name: 'ucrea-eval-result', component: UcreaEvalResult, meta: { requiresAuth: true } },
    { path: '/ucrea/evaluations/grade/:id', name: 'ucrea-eval-form', component: UcreaEvalForm, meta: { requiresAuth: true } },
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
    } else {
        next();
    }
});

export default router;
