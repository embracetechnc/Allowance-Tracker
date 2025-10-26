<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Profile</h3>
          <p class="mt-1 text-sm text-gray-500">
            Update your profile information and photo.
          </p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-6 gap-6">
              <!-- Profile Photo -->
              <div class="col-span-6 sm:col-span-4">
                <div class="flex items-center">
                  <div class="relative">
                    <img
                      :src="photoPreview || user.profile_photo || '/default-avatar.png'"
                      class="h-24 w-24 rounded-full object-cover"
                      alt="Profile photo"
                    />
                    <button
                      type="button"
                      @click="$refs.photoInput.click()"
                      class="absolute bottom-0 right-0 bg-blue-600 rounded-full p-1 text-white hover:bg-blue-700"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>
                  <input
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handlePhotoChange"
                  />
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-700">Profile photo</div>
                    <button
                      type="button"
                      class="mt-1 text-sm text-blue-600 hover:text-blue-500"
                      @click="$refs.photoInput.click()"
                    >
                      Change
                    </button>
                  </div>
                </div>
              </div>

              <!-- First Name -->
              <div class="col-span-6 sm:col-span-4">
                <label for="first_name" class="block text-sm font-medium text-gray-700">
                  First name
                </label>
                <input
                  type="text"
                  v-model="formData.first_name"
                  name="first_name"
                  id="first_name"
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                />
              </div>

              <!-- Email (Read-only) -->
              <div class="col-span-6 sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-50 rounded-md shadow-sm text-gray-500 sm:text-sm">
                  {{ user.email }}
                </div>
              </div>

              <!-- Birthday (Read-only for children) -->
              <div v-if="isChild" class="col-span-6 sm:col-span-4">
                <label class="block text-sm font-medium text-gray-700">
                  Birthday
                </label>
                <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-50 rounded-md shadow-sm text-gray-500 sm:text-sm">
                  {{ formatDate(user.birthday) }}
                </div>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mt-4 text-sm text-red-600">
              {{ error }}
            </div>

            <!-- Success Message -->
            <div v-if="success" class="mt-4 text-sm text-green-600">
              {{ success }}
            </div>

            <div class="mt-6">
              <button
                type="submit"
                :disabled="loading"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :class="{ 'opacity-75 cursor-not-allowed': loading }"
              >
                {{ loading ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
import moment from 'moment'

export default {
  name: 'ProfileManagement',
  setup() {
    const store = useStore()
    const photoInput = ref(null)
    const photoPreview = ref(null)
    const loading = ref(false)
    const error = ref('')
    const success = ref('')

    const user = computed(() => store.getters['auth/user'])
    const isChild = computed(() => user.value?.role === 'child')

    const formData = reactive({
      first_name: user.value?.first_name || ''
    })

    const handlePhotoChange = (event) => {
      const file = event.target.files[0]
      if (!file) return

      // Preview photo
      const reader = new FileReader()
      reader.onload = (e) => {
        photoPreview.value = e.target.result
      }
      reader.readAsDataURL(file)

      // Upload photo
      uploadPhoto(file)
    }

    const uploadPhoto = async (file) => {
      try {
        loading.value = true
        error.value = ''

        const formData = new FormData()
        formData.append('photo', file)

        const response = await axios.post('/api/profile/upload-photo', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Update store with new photo URL
        store.commit('auth/UPDATE_USER', {
          ...user.value,
          profile_photo: response.data.photo_url
        })

        success.value = 'Photo updated successfully'
      } catch (err) {
        error.value = err.response?.data?.message || 'Error uploading photo'
      } finally {
        loading.value = false
      }
    }

    const handleSubmit = async () => {
      try {
        loading.value = true
        error.value = ''

        const response = await axios.post('/api/profile/update', formData)

        // Update store with new user data
        store.commit('auth/UPDATE_USER', response.data.user)

        success.value = 'Profile updated successfully'
      } catch (err) {
        error.value = err.response?.data?.message || 'Error updating profile'
      } finally {
        loading.value = false
      }
    }

    const formatDate = (date) => {
      return moment(date).format('MMMM D, YYYY')
    }

    return {
      user,
      isChild,
      formData,
      photoInput,
      photoPreview,
      loading,
      error,
      success,
      handlePhotoChange,
      handleSubmit,
      formatDate
    }
  }
}
</script>
