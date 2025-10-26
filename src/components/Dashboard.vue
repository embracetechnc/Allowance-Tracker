<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Bible Verse Banner -->
    <BibleVerseBanner />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Welcome, {{ user?.first_name }}!</h1>
            <p class="mt-1 text-sm text-gray-600">
              Track your tasks and earnings
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Current Balance -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
              <p class="text-sm text-gray-600">Current Balance</p>
              <p class="text-2xl font-bold text-indigo-600">${{ currentBalance.toFixed(2) }}</p>
            </div>
            <!-- Next Payout -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
              <p class="text-sm text-gray-600">Next Payout</p>
              <p class="text-lg font-semibold text-gray-900">{{ nextPayoutDate }}</p>
            </div>
          </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <!-- Tasks Section -->
          <div class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Today's Tasks</h2>
              <!-- Task List will go here -->
              <div class="space-y-4">
                <p class="text-sm text-gray-500">Loading tasks...</p>
              </div>
            </div>
          </div>

          <!-- Progress Chart -->
          <div class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Weekly Progress</h2>
              <!-- Chart will go here -->
              <div class="h-64 flex items-center justify-center">
                <p class="text-sm text-gray-500">Loading chart...</p>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white rounded-lg shadow lg:col-span-2">
            <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h2>
              <!-- Activity List will go here -->
              <div class="space-y-4">
                <p class="text-sm text-gray-500">Loading activity...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div 
      v-if="loading"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-xl">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading your dashboard...</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { format, addDays } from 'date-fns'
import BibleVerseBanner from './BibleVerseBanner.vue'

export default {
  name: 'Dashboard',
  
  components: {
    BibleVerseBanner
  },

  setup() {
    const store = useStore()
    const loading = ref(true)
    const currentBalance = ref(0)

    // Computed properties
    const user = computed(() => store.state.auth.user)
    const nextPayoutDate = computed(() => {
      // Calculate next Saturday
      const today = new Date()
      const daysUntilSaturday = 6 - today.getDay() // 6 is Saturday
      const nextSaturday = addDays(today, daysUntilSaturday)
      return format(nextSaturday, 'MMMM d, yyyy')
    })

    // Methods
    const loadDashboardData = async () => {
      try {
        loading.value = true
        // TODO: Load tasks, progress, and activity data
        // For now, just simulate loading time
        await new Promise(resolve => setTimeout(resolve, 1000))
        currentBalance.value = 45.00 // This will be loaded from the API
      } catch (error) {
        console.error('Error loading dashboard:', error)
      } finally {
        loading.value = false
      }
    }

    // Lifecycle hooks
    onMounted(() => {
      loadDashboardData()
    })

    return {
      user,
      loading,
      currentBalance,
      nextPayoutDate
    }
  }
}
</script>

<style scoped>
.dashboard-enter-active,
.dashboard-leave-active {
  transition: opacity 0.5s ease;
}

.dashboard-enter-from,
.dashboard-leave-to {
  opacity: 0;
}
</style>