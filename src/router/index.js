import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../components/Dashboard.vue';
import Login from '../components/Login.vue';
import SchoolPoints from '../views/SchoolPoints.vue';
import AdminDashboard from '../views/AdminDashboard.vue';
import { useAllowedEmailsStore } from '../stores/allowedEmails';
import RegisterForm from '../components/auth/RegisterForm.vue';
import ForgotPassword from '../components/auth/ForgotPassword.vue';
import ResetPassword from '../components/auth/ResetPassword.vue';

const routes = [
  {
    path: '/',
    redirect: '/parent-dashboard'
  },
  {
    path: '/parent-dashboard',
    name: 'ParentDashboard',
    component: () => import('@/views/ParentDashboard.vue')
  },
  {
    path: '/child-dashboard',
    name: 'ChildDashboard',
    component: () => import('@/views/ChildDashboard.vue')
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterForm
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword
  },
  {
    path: '/points',
    name: 'SchoolPoints',
    component: SchoolPoints,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Temporarily bypass authentication
router.beforeEach((to, from, next) => {
  // Set default user data for testing
  localStorage.setItem('token', 'bypass-token');
  localStorage.setItem('userEmail', 'william_stokes@hotmail.com');
  next();
});

export default router;
