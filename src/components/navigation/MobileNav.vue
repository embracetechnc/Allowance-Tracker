<template>
  <nav class="bg-white shadow-sm">
    <!-- Top Bar -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Title -->
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <img class="h-8 w-auto" src="@/assets/logo.svg" alt="Logo" />
            <span class="ml-2 text-lg font-semibold text-gray-900">Family Allowance</span>
          </div>
        </div>

        <!-- Menu Button -->
        <div class="flex items-center">
          <button
            @click="isOpen = !isOpen"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          >
            <span class="sr-only">Open main menu</span>
            <!-- Menu Icon -->
            <svg
              v-if="!isOpen"
              class="block h-6 w-6"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <!-- Close Icon -->
            <svg
              v-else
              class="block h-6 w-6"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      enter-active-class="transition ease-out duration-100 transform"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75 transform"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="isOpen" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <!-- Dashboard Link -->
          <router-link
            :to="{ name: 'parent-dashboard' }"
            :class="[
              isActive('parent-dashboard')
                ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium'
            ]"
            @click="isOpen = false"
          >
            Dashboard
          </router-link>

          <!-- Tasks Link -->
          <router-link
            :to="{ name: 'tasks' }"
            :class="[
              isActive('tasks')
                ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium'
            ]"
            @click="isOpen = false"
          >
            Tasks
          </router-link>

          <!-- Children Link -->
          <router-link
            :to="{ name: 'children' }"
            :class="[
              isActive('children')
                ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium'
            ]"
            @click="isOpen = false"
          >
            Children
          </router-link>

          <!-- Reports Link -->
          <router-link
            :to="{ name: 'reports' }"
            :class="[
              isActive('reports')
                ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium'
            ]"
            @click="isOpen = false"
          >
            Reports
          </router-link>

          <!-- Settings Link -->
          <router-link
            :to="{ name: 'settings' }"
            :class="[
              isActive('settings')
                ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
              'block pl-3 pr-4 py-2 border-l-4 text-base font-medium'
            ]"
            @click="isOpen = false"
          >
            Settings
          </router-link>
        </div>

        <!-- User Menu -->
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="flex items-center px-4">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                <span class="text-lg font-medium text-indigo-600">{{ userInitials }}</span>
              </div>
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">{{ userName }}</div>
              <div class="text-sm font-medium text-gray-500">{{ userEmail }}</div>
            </div>
          </div>
          <div class="mt-3 space-y-1">
            <button
              @click="handleLogout"
              class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
            >
              Sign out
            </button>
          </div>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'MobileNav',

  setup() {
    const isOpen = ref(false);
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();

    const userName = computed(() => authStore.user?.name || 'User');
    const userEmail = computed(() => authStore.user?.email || '');
    const userInitials = computed(() => {
      const name = userName.value;
      return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
    });

    const isActive = (routeName) => {
      return route.name === routeName;
    };

    const handleLogout = async () => {
      try {
        await authStore.logout();
        router.push({ name: 'login' });
      } catch (error) {
        console.error('Failed to logout:', error);
      }
    };

    return {
      isOpen,
      userName,
      userEmail,
      userInitials,
      isActive,
      handleLogout
    };
  }
};
</script>

