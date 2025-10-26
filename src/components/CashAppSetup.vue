<template>
  <div class="bg-white rounded-lg shadow p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-lg font-medium text-gray-900">Cash App Setup</h2>
        <p class="mt-1 text-sm text-gray-500">
          Link your Cash App account to receive allowance payments
        </p>
      </div>
      <div class="flex items-center">
        <!-- Status Badge -->
        <span 
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
          :class="{
            'bg-green-100 text-green-800': isLinked,
            'bg-gray-100 text-gray-800': !isLinked
          }"
        >
          {{ isLinked ? 'Connected' : 'Not Connected' }}
        </span>
      </div>
    </div>

    <!-- Not Connected State -->
    <div v-if="!isLinked" class="space-y-4">
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v5a1 1 0 102 0V7zm-1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-gray-800">Before you begin</h3>
            <div class="mt-2 text-sm text-gray-600">
              <ul class="list-disc pl-5 space-y-1">
                <li>You'll need a Cash App account</li>
                <li>Make sure you have the latest version of Cash App installed</li>
                <li>Your Cash App account must be verified</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Connect Button -->
      <button
        @click="startLinking"
        :disabled="loading.linking"
        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <template v-if="!loading.linking">
          <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none">
            <path d="M12 4L12 20M20 12L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Connect Cash App
        </template>
        <svg 
          v-else
          class="animate-spin h-5 w-5 text-white" 
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
      </button>

      <!-- Error Message -->
      <div 
        v-if="error.linking"
        class="rounded-md bg-red-50 p-4"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Connection Error
            </h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ error.linking }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Connected State -->
    <div v-else class="space-y-6">
      <!-- Account Info -->
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-400" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm-1.5-6.5l-3-3 1.5-1.5 1.5 1.5 4-4 1.5 1.5-5.5 5.5z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-gray-900">Account Connected</h3>
            <p class="mt-2 text-sm text-gray-500">
              Your Cash App account (${{ cashtag }}) is connected and ready to receive payments.
            </p>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div v-if="recentPayments.length > 0">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Activity</h4>
        <div class="space-y-2">
          <div 
            v-for="payment in recentPayments" 
            :key="payment.id"
            class="flex items-center justify-between py-2 text-sm"
          >
            <div class="flex items-center">
              <span 
                class="w-2 h-2 rounded-full mr-2"
                :class="{
                  'bg-green-400': payment.status === 'completed',
                  'bg-yellow-400': payment.status === 'pending',
                  'bg-red-400': payment.status === 'failed'
                }"
              />
              <span class="text-gray-600">
                {{ formatDate(payment.payment_date) }}
              </span>
            </div>
            <span 
              class="font-medium"
              :class="{
                'text-green-600': payment.status === 'completed',
                'text-yellow-600': payment.status === 'pending',
                'text-red-600': payment.status === 'failed'
              }"
            >
              ${{ payment.amount.toFixed(2) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Disconnect Button -->
      <div class="border-t border-gray-200 pt-4">
        <button
          @click="confirmUnlink"
          :disabled="loading.linking"
          class="text-sm text-red-600 hover:text-red-500 font-medium focus:outline-none"
        >
          Disconnect Account
        </button>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div 
      v-if="showConfirmation"
      class="fixed z-10 inset-0 overflow-y-auto"
      aria-labelledby="modal-title" 
      role="dialog" 
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div 
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="showConfirmation = false"
        />

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Disconnect Cash App Account
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Are you sure you want to disconnect your Cash App account? You will need to reconnect it to receive future allowance payments.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              @click="unlinkAccount"
            >
              Disconnect
            </button>
            <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
              @click="showConfirmation = false"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { format } from 'date-fns'

export default {
  name: 'CashAppSetup',

  setup() {
    const store = useStore()
    const showConfirmation = ref(false)

    // Computed properties from store
    const isLinked = computed(() => store.state.cashapp.isLinked)
    const cashtag = computed(() => store.state.cashapp.cashtag)
    const loading = computed(() => store.state.cashapp.loading)
    const error = computed(() => store.state.cashapp.error)
    const recentPayments = computed(() => {
      return store.getters['cashapp/recentPayments'](3)
    })

    // Methods
    const startLinking = () => {
      store.dispatch('cashapp/startLinking')
    }

    const confirmUnlink = () => {
      showConfirmation.value = true
    }

    const unlinkAccount = async () => {
      await store.dispatch('cashapp/unlinkAccount')
      showConfirmation.value = false
    }

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy')
    }

    // Initialize on mount
    onMounted(() => {
      store.dispatch('cashapp/initializeCashApp')
    })

    return {
      isLinked,
      cashtag,
      loading,
      error,
      recentPayments,
      showConfirmation,
      startLinking,
      confirmUnlink,
      unlinkAccount,
      formatDate
    }
  }
}
</script>