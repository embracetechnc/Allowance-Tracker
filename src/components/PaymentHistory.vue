<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">Payment History</h3>
          <p class="mt-1 text-sm text-gray-500">Track your allowance payments and status</p>
        </div>
        <!-- Total Amount -->
        <div class="text-right">
          <p class="text-sm text-gray-500">Total Received</p>
          <p class="text-2xl font-semibold text-indigo-600">${{ totalReceived.toFixed(2) }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 sm:px-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
        <!-- Status Filter -->
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-500">Status:</span>
          <div class="relative">
            <select
              v-model="statusFilter"
              class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
              <option value="all">All</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
        </div>

        <!-- Date Range Filter -->
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-500">Period:</span>
          <div class="relative">
            <select
              v-model="dateRange"
              class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
              <option value="all">All Time</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment List -->
    <div class="overflow-hidden">
      <!-- Loading State -->
      <div 
        v-if="loading.history"
        class="flex items-center justify-center py-12"
      >
        <svg 
          class="animate-spin h-8 w-8 text-indigo-600" 
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
          />
          <path 
            class="opacity-75" 
            fill="currentColor" 
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
      </div>

      <!-- Error State -->
      <div 
        v-else-if="error.history"
        class="text-center py-12"
      >
        <svg 
          class="mx-auto h-12 w-12 text-red-400" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-red-800">Error Loading Payments</h3>
        <p class="mt-1 text-sm text-red-600">{{ error.history }}</p>
        <div class="mt-6">
          <button
            type="button"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            @click="retryLoading"
          >
            Try again
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div 
        v-else-if="filteredPayments.length === 0"
        class="text-center py-12"
      >
        <svg 
          class="mx-auto h-12 w-12 text-gray-400" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No Payments Found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ noPaymentsMessage }}
        </p>
      </div>

      <!-- Payment Table -->
      <div v-else class="min-w-full">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Transaction ID
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="payment in filteredPayments" 
              :key="payment.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(payment.payment_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${{ payment.amount.toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span 
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': payment.status === 'completed',
                    'bg-yellow-100 text-yellow-800': payment.status === 'pending',
                    'bg-red-100 text-red-800': payment.status === 'failed'
                  }"
                >
                  {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <template v-if="payment.transaction_id">
                  {{ payment.transaction_id }}
                </template>
                <template v-else-if="payment.error_message">
                  <span class="text-red-600">{{ payment.error_message }}</span>
                </template>
                <template v-else>
                  -
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div 
      v-if="filteredPayments.length > 0"
      class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
    >
      <div class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            :disabled="currentPage === 1"
            @click="currentPage--"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            :disabled="currentPage >= totalPages"
            @click="currentPage++"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ paginationStart }}</span>
              to
              <span class="font-medium">{{ paginationEnd }}</span>
              of
              <span class="font-medium">{{ totalPayments }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                :disabled="currentPage === 1"
                @click="currentPage = 1"
                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">First</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <button
                v-for="page in displayedPages"
                :key="page"
                @click="currentPage = page"
                :class="[
                  page === currentPage
                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                ]"
              >
                {{ page }}
              </button>
              <button
                :disabled="currentPage >= totalPages"
                @click="currentPage = totalPages"
                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">Last</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 15.707a1 1 0 001.414 0L10 11.414l4.293 4.293a1 1 0 001.414-1.414l-5-5a1 1 0 00-1.414 0l-5 5a1 1 0 000 1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { format, subDays, startOfWeek, startOfMonth, startOfYear } from 'date-fns'

export default {
  name: 'PaymentHistory',

  setup() {
    const store = useStore()
    const statusFilter = ref('all')
    const dateRange = ref('all')
    const currentPage = ref(1)
    const itemsPerPage = 10

    // Computed properties from store
    const loading = computed(() => store.state.cashapp.loading)
    const error = computed(() => store.state.cashapp.error)
    const paymentHistory = computed(() => store.state.cashapp.paymentHistory)

    // Filter payments by status and date
    const filteredPayments = computed(() => {
      let filtered = [...paymentHistory.value]

      // Apply status filter
      if (statusFilter.value !== 'all') {
        filtered = filtered.filter(p => p.status === statusFilter.value)
      }

      // Apply date filter
      const now = new Date()
      switch (dateRange.value) {
        case 'week':
          const weekStart = startOfWeek(now)
          filtered = filtered.filter(p => new Date(p.payment_date) >= weekStart)
          break
        case 'month':
          const monthStart = startOfMonth(now)
          filtered = filtered.filter(p => new Date(p.payment_date) >= monthStart)
          break
        case 'year':
          const yearStart = startOfYear(now)
          filtered = filtered.filter(p => new Date(p.payment_date) >= yearStart)
          break
      }

      return filtered
    })

    // Pagination
    const totalPayments = computed(() => filteredPayments.value.length)
    const totalPages = computed(() => Math.ceil(totalPayments.value / itemsPerPage))
    const paginatedPayments = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      return filteredPayments.value.slice(start, start + itemsPerPage)
    })

    const paginationStart = computed(() => {
      return ((currentPage.value - 1) * itemsPerPage) + 1
    })

    const paginationEnd = computed(() => {
      return Math.min(currentPage.value * itemsPerPage, totalPayments.value)
    })

    const displayedPages = computed(() => {
      const pages = []
      const maxPages = 5
      let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2))
      let end = Math.min(totalPages.value, start + maxPages - 1)

      if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1)
      }

      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      return pages
    })

    // Calculate total received amount
    const totalReceived = computed(() => {
      return paymentHistory.value
        .filter(p => p.status === 'completed')
        .reduce((sum, p) => sum + p.amount, 0)
    })

    // Empty state message
    const noPaymentsMessage = computed(() => {
      if (statusFilter.value !== 'all' || dateRange.value !== 'all') {
        return 'No payments match your filters'
      }
      return 'No payments have been processed yet'
    })

    // Methods
    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a')
    }

    const retryLoading = () => {
      store.dispatch('cashapp/fetchPaymentHistory')
    }

    // Reset pagination when filters change
    watch([statusFilter, dateRange], () => {
      currentPage.value = 1
    })

    // Load payment history on mount
    onMounted(() => {
      store.dispatch('cashapp/fetchPaymentHistory')
    })

    return {
      statusFilter,
      dateRange,
      currentPage,
      loading,
      error,
      filteredPayments,
      totalPayments,
      totalPages,
      paginationStart,
      paginationEnd,
      displayedPages,
      totalReceived,
      noPaymentsMessage,
      formatDate,
      retryLoading
    }
  }
}
</script>
