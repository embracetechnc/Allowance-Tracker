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
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
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

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const userEmail = localStorage.getItem('userEmail');
  const allowedEmailsStore = useAllowedEmailsStore();

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      next({
        path: '/login',
        query: { redirect: to.fullPath }
      });
    } else if (to.matched.some(record => record.meta.requiresAdmin)) {
      // Check if user is admin
      if (allowedEmailsStore.isAdmin(userEmail)) {
        next();
      } else {
        next('/'); // Redirect non-admin users to home
      }
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
