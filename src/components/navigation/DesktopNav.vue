<template>
  <nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Navigation Links -->
        <div class="flex">
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center">
            <img class="h-8 w-auto" src="@/assets/logo.svg" alt="Logo" />
            <span class="ml-2 text-lg font-semibold text-gray-900">Family Allowance</span>
          </div>

          <!-- Navigation Links -->
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <router-link
              :to="{ name: 'parent-dashboard' }"
              :class="[
                isActive('parent-dashboard')
                  ? 'border-indigo-500 text-gray-900'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
              ]"
            >
              Dashboard
            </router-link>

            <router-link
              :to="{ name: 'tasks' }"
              :class="[
                isActive('tasks')
                  ? 'border-indigo-500 text-gray-900'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
              ]"
            >
              Tasks
            </router-link>

            <router-link
              :to="{ name: 'children' }"
              :class="[
                isActive('children')
                  ? 'border-indigo-500 text-gray-900'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
              ]"
            >
              Children
            </router-link>

            <router-link
              :to="{ name: 'reports' }"
              :class="[
                isActive('reports')
                  ? 'border-indigo-500 text-gray-900'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium'
              ]"
            >
              Reports
            </router-link>
          </div>
        </div>

        <!-- User Menu -->
        <div class="hidden sm:ml-6 sm:flex sm:items-center">
          <!-- Settings Link -->
          <router-link
            :to="{ name: 'settings' }"
            :class="[
              isActive('settings')
                ? 'text-gray-700'
                : 'text-gray-400 hover:text-gray-500',
              'p-1 rounded-full'
            ]"
          >
            <span class="sr-only">Settings</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
          </router-link>

          <!-- Profile Dropdown -->
          <div class="ml-3 relative">
            <div>
              <button
                @click="isProfileOpen = !isProfileOpen"
                class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <span class="sr-only">Open user menu</span>
                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                  <span class="text-sm font-medium text-indigo-600">{{ userInitials }}</span>
                </div>
              </button>
            </div>

            <!-- Profile Dropdown Menu -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isProfileOpen"
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5"
              >
                <div class="px-4 py-2">
                  <div class="text-sm font-medium text-gray-900">{{ userName }}</div>
                  <div class="text-sm text-gray-500">{{ userEmail }}</div>
                </div>
                <div class="border-t border-gray-100"></div>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Sign out
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'DesktopNav',

  setup() {
    const isProfileOpen = ref(false);
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
      isProfileOpen,
      userName,
      userEmail,
      userInitials,
      isActive,
      handleLogout
    };
  }
};
</script>

