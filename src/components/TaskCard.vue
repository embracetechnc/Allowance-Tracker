<template>
  <div 
    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-md"
    :class="{
      'border-green-200 bg-green-50': isCompleted,
      'border-yellow-200 bg-yellow-50': isPending,
      'border-red-200 bg-red-50': isRejected
    }"
  >
    <div class="p-4">
      <!-- Task Header -->
      <div class="flex items-start justify-between">
        <div class="flex items-center space-x-3">
          <!-- Task Icon -->
          <div 
            class="w-10 h-10 rounded-full flex items-center justify-center"
            :class="{
              'bg-green-100 text-green-600': isCompleted,
              'bg-yellow-100 text-yellow-600': isPending,
              'bg-red-100 text-red-600': isRejected,
              'bg-gray-100 text-gray-600': !status
            }"
          >
            <component 
              :is="taskIcon" 
              class="w-6 h-6"
            />
          </div>

          <!-- Task Title -->
          <div>
            <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
            <p class="text-sm text-gray-500">{{ description }}</p>
          </div>
        </div>

        <!-- Task Value -->
        <div class="text-right">
          <p class="text-lg font-semibold text-indigo-600">${{ value.toFixed(2) }}</p>
          <p class="text-sm text-gray-500">{{ dueText }}</p>
        </div>
      </div>

      <!-- Task Requirements -->
      <div class="mt-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Requirements:</h4>
        <ul class="space-y-2">
          <li 
            v-for="(requirement, index) in requirements" 
            :key="index"
            class="flex items-center text-sm"
          >
            <svg 
              class="w-4 h-4 mr-2"
              :class="requirement.completed ? 'text-green-500' : 'text-gray-400'"
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M5 13l4 4L19 7"
              />
            </svg>
            <span 
              :class="requirement.completed ? 'text-gray-700' : 'text-gray-500'"
            >
              {{ requirement.text }}
            </span>
          </li>
        </ul>
      </div>

      <!-- Action Buttons -->
      <div class="mt-4 flex justify-end space-x-2">
        <button
          v-if="canComplete"
          @click="$emit('complete')"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          :disabled="loading"
        >
          <span v-if="!loading">Mark Complete</span>
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

        <button
          v-if="canVerify"
          @click="$emit('verify', true)"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          :disabled="loading"
        >
          Verify
        </button>

        <button
          v-if="canVerify"
          @click="$emit('verify', false)"
          class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          :disabled="loading"
        >
          Reject
        </button>
      </div>
    </div>

    <!-- Status Bar -->
    <div 
      class="h-1"
      :class="{
        'bg-green-500': isCompleted,
        'bg-yellow-500': isPending,
        'bg-red-500': isRejected,
        'bg-gray-200': !status
      }"
    />
  </div>
</template>

<script>
import { computed } from 'vue'
import {
  ClipboardCheckIcon,
  HomeIcon,
  SparklesIcon,
  RefreshIcon
} from '@heroicons/vue/outline'

export default {
  name: 'TaskCard',

  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      required: true,
      validator: value => [
        'room_cleaning',
        'bathroom_cleaning',
        'kitchen_cleaning',
        'laundry'
      ].includes(value)
    },
    value: {
      type: Number,
      required: true
    },
    status: {
      type: String,
      default: null,
      validator: value => [
        null,
        'completed',
        'pending',
        'verified',
        'rejected'
      ].includes(value)
    },
    requirements: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    dueDate: {
      type: Date,
      default: null
    }
  },

  setup(props) {
    // Computed properties for status
    const isCompleted = computed(() => props.status === 'verified')
    const isPending = computed(() => props.status === 'completed' || props.status === 'pending')
    const isRejected = computed(() => props.status === 'rejected')

    // Computed property for action buttons visibility
    const canComplete = computed(() => !props.status || props.status === 'rejected')
    const canVerify = computed(() => props.status === 'completed')

    // Computed property for due date text
    const dueText = computed(() => {
      if (!props.dueDate) return ''
      const now = new Date()
      const due = new Date(props.dueDate)
      const diffDays = Math.ceil((due - now) / (1000 * 60 * 60 * 24))
      
      if (diffDays < 0) return 'Overdue'
      if (diffDays === 0) return 'Due today'
      if (diffDays === 1) return 'Due tomorrow'
      return `Due in ${diffDays} days`
    })

    // Computed property for task icon
    const taskIcon = computed(() => {
      switch (props.type) {
        case 'room_cleaning':
          return HomeIcon
        case 'bathroom_cleaning':
          return SparklesIcon
        case 'kitchen_cleaning':
          return ClipboardCheckIcon
        case 'laundry':
          return RefreshIcon
        default:
          return ClipboardCheckIcon
      }
    })

    return {
      isCompleted,
      isPending,
      isRejected,
      canComplete,
      canVerify,
      dueText,
      taskIcon
    }
  }
}
</script>
