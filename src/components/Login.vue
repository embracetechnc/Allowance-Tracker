<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Sign in to your account
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Please use your authorized email address to sign in.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="email"
                type="email"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @blur="checkEmailAllowed"
              />
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="text-red-600 text-sm text-center">
            {{ error }}
          </div>

          <!-- Email Not Allowed Alert -->
          <div
            v-if="!isEmailAllowed && email"
            class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative"
            role="alert"
          >
            <span class="block sm:inline">This email address is not authorized. Please use an authorized email address.</span>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="loading || !isEmailAllowed"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              :class="{ 'opacity-75 cursor-not-allowed': loading || !isEmailAllowed }"
            >
              {{ loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </div>
        </form>

        <!-- Register Link -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">
                Don't have an account?
              </span>
            </div>
          </div>

          <div class="mt-6 text-center">
            <router-link
              to="/register"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              Register now
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAllowedEmailsStore } from '@/stores/allowedEmails'
import { useRouter } from 'vue-router'

export default {
  name: 'Login',
  setup() {
    const authStore = useAuthStore()
    const allowedEmailsStore = useAllowedEmailsStore()
    const router = useRouter()

    const email = ref('')
    const error = ref('')
    const loading = ref(false)
    const isEmailAllowed = ref(false)

    const checkEmailAllowed = () => {
      if (email.value) {
        isEmailAllowed.value = allowedEmailsStore.isEmailAllowed(email.value)
      } else {
        isEmailAllowed.value = false
      }
    }

    const handleSubmit = async () => {
      try {
        loading.value = true
        error.value = ''

        if (!isEmailAllowed.value) {
          error.value = 'This email address is not authorized'
          return
        }

        await authStore.login({
          email: email.value
        })

        // Redirect based on role
        const user = authStore.user
        if (user.role === 'parent') {
          router.push('/parent-dashboard')
        } else {
          router.push('/dashboard')
        }
      } catch (err) {
        console.error('Login error:', err)
        error.value = err.response?.data?.message || 'Login failed. Please try again.'
      } finally {
        loading.value = false
      }
    }

    return {
      email,
      error,
      loading,
      isEmailAllowed,
      handleSubmit,
      checkEmailAllowed
    }
  }
}
</script>