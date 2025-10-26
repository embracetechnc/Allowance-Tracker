<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
      <h3 class="text-lg leading-6 font-medium text-gray-900">School Points</h3>
      <p class="mt-1 text-sm text-gray-500">Add or remove points for behavior and school performance</p>
    </div>

    <!-- Form -->
    <div class="px-4 py-5 sm:p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Child Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Child
          </label>
          <select
            v-model="selectedChild"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            required
          >
            <option value="">Select a child</option>
            <option
              v-for="child in children"
              :key="child.id"
              :value="child.id"
            >
              {{ child.first_name }}
            </option>
          </select>
        </div>

        <!-- Action Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Action
          </label>
          <div class="mt-1 space-x-4">
            <label class="inline-flex items-center">
              <input
                type="radio"
                v-model="action"
                value="add"
                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
              >
              <span class="ml-2 text-sm text-gray-700">Add Points</span>
            </label>
            <label class="inline-flex items-center">
              <input
                type="radio"
                v-model="action"
                value="remove"
                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
              >
              <span class="ml-2 text-sm text-gray-700">Remove Points</span>
            </label>
          </div>
        </div>

        <!-- Points Input -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Number of Points
          </label>
          <div class="mt-1">
            <input
              type="number"
              v-model.number="points"
              min="1"
              max="5"
              required
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
          </div>
          <p class="mt-1 text-xs text-gray-500">
            Enter a number between 1 and 5
          </p>
        </div>

        <!-- Category Selection (for adding points) -->
        <div v-if="action === 'add'">
          <label class="block text-sm font-medium text-gray-700">
            Category
          </label>
          <select
            v-model="selectedCategory"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            required
          >
            <option value="">Select a category</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Reason Input -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Reason
          </label>
          <div class="mt-1">
            <textarea
              v-model="reason"
              rows="3"
              required
              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
              :placeholder="reasonPlaceholder"
            />
          </div>
          <p class="mt-1 text-xs text-gray-500">
            Briefly describe why points are being {{ action === 'add' ? 'added' : 'removed' }}
          </p>
        </div>

        <!-- Weekly Summary -->
        <div v-if="selectedChild && weeklySummary" class="rounded-md bg-gray-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg 
                class="h-5 w-5 text-gray-400" 
                viewBox="0 0 20 20" 
                fill="currentColor"
              >
                <path 
                  fill-rule="evenodd" 
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" 
                  clip-rule="evenodd" 
                />
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <h3 class="text-sm font-medium text-gray-800">
                Weekly Summary
              </h3>
              <div class="mt-2 text-sm text-gray-600">
                <p>Current Points: {{ weeklySummary.total_points }}</p>
                <p v-if="weeklySummary.allowance_deduction > 0" class="text-red-600">
                  Allowance Deduction: -${{ weeklySummary.allowance_deduction.toFixed(2) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div 
          v-if="error"
          class="rounded-md bg-red-50 p-4"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg 
                class="h-5 w-5 text-red-400" 
                viewBox="0 0 20 20" 
                fill="currentColor"
              >
                <path 
                  fill-rule="evenodd" 
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" 
                  clip-rule="evenodd" 
                />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Error
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ error }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="loading || !isValid"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <template v-if="!loading">
              {{ action === 'add' ? 'Add Points' : 'Remove Points' }}
            </template>
            <svg 
              v-else
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
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
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'ParentPointInput',

  props: {
    children: {
      type: Array,
      required: true,
      validator: children => {
        return children.every(child => 
          typeof child.id === 'number' && 
          typeof child.first_name === 'string'
        );
      }
    }
  },

  emits: ['points-updated'],

  setup(props, { emit }) {
    const store = useStore()
    
    // Form state
    const selectedChild = ref('')
    const action = ref('add')
    const points = ref(1)
    const selectedCategory = ref('')
    const reason = ref('')

    // Computed properties
    const categories = computed(() => store.state.points.categories)
    const loading = computed(() => 
      store.state.points.loading.adding || 
      store.state.points.loading.removing
    )
    const error = computed(() => 
      store.state.points.error.adding || 
      store.state.points.error.removing
    )
    const weeklySummary = computed(() => store.state.points.weeklySummary)

    const isValid = computed(() => {
      return (
        selectedChild.value &&
        points.value >= 1 &&
        points.value <= 5 &&
        reason.value.trim() &&
        (action.value !== 'add' || selectedCategory.value)
      );
    })

    const reasonPlaceholder = computed(() => {
      if (action.value === 'add') {
        return 'Describe the behavior or achievement that earned these points';
      }
      return 'Explain why points are being removed';
    })

    // Methods
    const handleSubmit = async () => {
      try {
        if (action.value === 'add') {
          await store.dispatch('points/addPoints', {
            userId: selectedChild.value,
            points: points.value,
            reason: reason.value,
            categoryId: selectedCategory.value
          });
        } else {
          await store.dispatch('points/removePoints', {
            userId: selectedChild.value,
            points: points.value,
            reason: reason.value
          });
        }

        // Reset form
        points.value = 1;
        selectedCategory.value = '';
        reason.value = '';

        // Emit event for parent component
        emit('points-updated');
      } catch (error) {
        console.error('Failed to update points:', error);
      }
    }

    // Watch for child selection to load their weekly summary
    watch(selectedChild, async (newValue) => {
      if (newValue) {
        await store.dispatch('points/fetchWeeklyPoints', { userId: newValue });
      }
    })

    // Load categories on mount
    onMounted(async () => {
      await store.dispatch('points/fetchCategories');
    })

    return {
      selectedChild,
      action,
      points,
      selectedCategory,
      reason,
      categories,
      loading,
      error,
      weeklySummary,
      isValid,
      reasonPlaceholder,
      handleSubmit
    }
  }
}
</script>
