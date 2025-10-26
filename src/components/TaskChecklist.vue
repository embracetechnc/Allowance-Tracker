<template>
  <div class="space-y-4">
    <!-- Room Cleaning Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center mb-4">
        <RoomCleaningAnimation class="w-6 h-6 text-indigo-600 mr-2" />
        <h3 class="text-lg font-medium text-gray-900">Clean Room</h3>
      </div>
      <div class="space-y-2">
        <label 
          v-for="(item, index) in roomTasks" 
          :key="index"
          class="flex items-start"
        >
          <div class="flex items-center h-5">
            <input
              type="checkbox"
              :checked="item.completed"
              @change="toggleTask('room', index)"
              :disabled="loading || isSubmitted"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3">
            <span 
              class="text-sm text-gray-700"
              :class="{ 'line-through text-gray-400': item.completed }"
            >
              {{ item.text }}
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- Bathroom Cleaning Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center mb-4">
        <BathroomCleaningAnimation class="w-6 h-6 text-indigo-600 mr-2" />
        <h3 class="text-lg font-medium text-gray-900">Clean Bathroom</h3>
      </div>
      <div class="space-y-2">
        <label 
          v-for="(item, index) in bathroomTasks" 
          :key="index"
          class="flex items-start"
        >
          <div class="flex items-center h-5">
            <input
              type="checkbox"
              :checked="item.completed"
              @change="toggleTask('bathroom', index)"
              :disabled="loading || isSubmitted"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3">
            <span 
              class="text-sm text-gray-700"
              :class="{ 'line-through text-gray-400': item.completed }"
            >
              {{ item.text }}
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- Kitchen Cleaning Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center mb-4">
        <KitchenCleaningAnimation class="w-6 h-6 text-indigo-600 mr-2" />
        <h3 class="text-lg font-medium text-gray-900">Clean Kitchen</h3>
      </div>
      <div class="space-y-2">
        <label 
          v-for="(item, index) in kitchenTasks" 
          :key="index"
          class="flex items-start"
        >
          <div class="flex items-center h-5">
            <input
              type="checkbox"
              :checked="item.completed"
              @change="toggleTask('kitchen', index)"
              :disabled="loading || isSubmitted"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3">
            <span 
              class="text-sm text-gray-700"
              :class="{ 'line-through text-gray-400': item.completed }"
            >
              {{ item.text }}
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- Laundry Section -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center mb-4">
        <LaundryAnimation class="w-6 h-6 text-indigo-600 mr-2" />
        <h3 class="text-lg font-medium text-gray-900">Laundry</h3>
      </div>
      <div class="space-y-2">
        <label 
          v-for="(item, index) in laundryTasks" 
          :key="index"
          class="flex items-start"
        >
          <div class="flex items-center h-5">
            <input
              type="checkbox"
              :checked="item.completed"
              @change="toggleTask('laundry', index)"
              :disabled="loading || isSubmitted"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            >
          </div>
          <div class="ml-3">
            <span 
              class="text-sm text-gray-700"
              :class="{ 'line-through text-gray-400': item.completed }"
            >
              {{ item.text }}
            </span>
          </div>
        </label>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="mt-6 flex justify-end">
      <button
        @click="submitTasks"
        :disabled="!canSubmit || loading || isSubmitted"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <template v-if="!loading">
          {{ isSubmitted ? 'Tasks Submitted' : 'Submit All Tasks' }}
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
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import RoomCleaningAnimation from './animations/RoomCleaningAnimation.vue'
import BathroomCleaningAnimation from './animations/BathroomCleaningAnimation.vue'
import KitchenCleaningAnimation from './animations/KitchenCleaningAnimation.vue'
import LaundryAnimation from './animations/LaundryAnimation.vue'

export default {
  name: 'TaskChecklist',

  components: {
    RoomCleaningAnimation,
    BathroomCleaningAnimation,
    KitchenCleaningAnimation,
    LaundryAnimation
  },

  props: {
    loading: {
      type: Boolean,
      default: false
    }
  },

  emits: ['submit'],

  setup(props, { emit }) {
    const isSubmitted = ref(false)

    const roomTasks = ref([
      { text: 'Empty trash', completed: false },
      { text: 'Make bed', completed: false },
      { text: 'Clean closet', completed: false },
      { text: 'Remove food items', completed: false }
    ])

    const bathroomTasks = ref([
      { text: 'Clean toilet', completed: false },
      { text: 'Clean sink', completed: false },
      { text: 'Clean tub/shower', completed: false },
      { text: 'Sweep/mop floor', completed: false },
      { text: 'Empty trash', completed: false }
    ])

    const kitchenTasks = ref([
      { text: 'Wash dishes', completed: false },
      { text: 'Wipe counters', completed: false },
      { text: 'Clean sink', completed: false }
    ])

    const laundryTasks = ref([
      { text: 'Sort clothes', completed: false },
      { text: 'Wash load', completed: false },
      { text: 'Dry load', completed: false },
      { text: 'Fold and put away', completed: false }
    ])

    const toggleTask = (section, index) => {
      if (isSubmitted.value) return

      const tasks = {
        room: roomTasks,
        bathroom: bathroomTasks,
        kitchen: kitchenTasks,
        laundry: laundryTasks
      }

      tasks[section].value[index].completed = !tasks[section].value[index].completed
    }

    const isTaskGroupComplete = (tasks) => {
      return tasks.value.every(task => task.completed)
    }

    const canSubmit = computed(() => {
      return isTaskGroupComplete(roomTasks) ||
             isTaskGroupComplete(bathroomTasks) ||
             isTaskGroupComplete(kitchenTasks) ||
             isTaskGroupComplete(laundryTasks)
    })

    const submitTasks = async () => {
      if (!canSubmit.value || props.loading || isSubmitted.value) return

      const completedTasks = {
        room_cleaning: isTaskGroupComplete(roomTasks),
        bathroom_cleaning: isTaskGroupComplete(bathroomTasks),
        kitchen_cleaning: isTaskGroupComplete(kitchenTasks),
        laundry: isTaskGroupComplete(laundryTasks)
      }

      emit('submit', completedTasks)
      isSubmitted.value = true
    }

    return {
      roomTasks,
      bathroomTasks,
      kitchenTasks,
      laundryTasks,
      toggleTask,
      submitTasks,
      canSubmit,
      isSubmitted
    }
  }
}
</script>



