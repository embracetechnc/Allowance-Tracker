<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Please use your authorized email address to register.
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="name" class="sr-only">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Full Name"
            />
          </div>
          <div>
            <label for="email-address" class="sr-only">Email address</label>
            <input
              id="email-address"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Email address"
              @blur="checkEmailAllowed"
            />
          </div>
          <div>
            <label for="role" class="sr-only">Role</label>
            <select
              id="role"
              v-model="form.role"
              name="role"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
            >
              <option value="">Select Role</option>
              <option value="parent">Parent</option>
              <option value="child">Child</option>
            </select>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !isEmailAllowed"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :class="{ 'opacity-50 cursor-not-allowed': !isEmailAllowed }"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg
                v-if="!loading"
                class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd"
                />
              </svg>
              <svg
                v-else
                class="animate-spin h-5 w-5 text-indigo-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
            </span>
            {{ loading ? 'Registering...' : 'Register' }}
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div class="text-sm">
            <router-link
              to="/login"
              class="font-medium text-indigo-600 hover:text-indigo-500"
            >
              Already have an account? Sign in
            </router-link>
          </div>
        </div>
      </form>

      <!-- Error Alert -->
      <div
        v-if="error"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
        role="alert"
      >
        <span class="block sm:inline">{{ error }}</span>
      </div>

      <!-- Email Not Allowed Alert -->
      <div
        v-if="!isEmailAllowed && form.email"
        class="mt-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative"
        role="alert"
      >
        <span class="block sm:inline">This email address is not authorized to register. Please use an authorized email address.</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useAllowedEmailsStore } from '@/stores/allowedEmails';

export default {
  name: 'RegisterForm',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const allowedEmailsStore = useAllowedEmailsStore();
    const loading = ref(false);
    const error = ref('');
    const isEmailAllowed = ref(false);

    const form = reactive({
      name: '',
      email: '',
      role: '',
    });

    const checkEmailAllowed = () => {
      if (form.email) {
        isEmailAllowed.value = allowedEmailsStore.isEmailAllowed(form.email);
      } else {
        isEmailAllowed.value = false;
      }
    };

    const validateForm = () => {
      if (!form.name.trim()) {
        error.value = 'Name is required';
        return false;
      }
      if (!form.email.trim()) {
        error.value = 'Email is required';
        return false;
      }
      if (!isEmailAllowed.value) {
        error.value = 'This email address is not authorized to register';
        return false;
      }
      if (!form.role) {
        error.value = 'Please select a role';
        return false;
      }
      return true;
    };

    const handleRegister = async () => {
      error.value = '';
      if (!validateForm()) return;

      loading.value = true;
      try {
        await authStore.register({
          name: form.name,
          email: form.email,
          role: form.role,
        });
        router.push('/dashboard');
      } catch (err) {
        error.value = err.response?.data?.message || 'Registration failed. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      isEmailAllowed,
      handleRegister,
      checkEmailAllowed,
    };
  },
};
</script>