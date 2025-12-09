import StudentForm from '@/components/students/StudentForm.vue';
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/students',
        name: 'Students',
        component: StudentForm,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
