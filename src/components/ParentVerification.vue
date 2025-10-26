<template>
  <div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
      <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">
        <div class="max-w-md mx-auto">
          <div class="divide-y divide-gray-200">
            <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
              <h2 class="text-2xl font-bold mb-8 text-center text-blue-600">Task Verification</h2>
              
              <!-- Pending Tasks List -->
              <div class="space-y-6">
                <div v-for="task in pendingTasks" :key="task.id" class="bg-gray-50 p-6 rounded-lg">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-medium text-gray-900">{{ task.child_name }}</h3>
                      <p class="text-gray-500">{{ formatTaskType(task.task_type) }}</p>
                      <p class="text-sm text-gray-400">Completed: {{ formatDate(task.completed_at) }}</p>
                    </div>
                    <div class="space-x-2">
                      <button
                        @click="verifyTask(task.id, true)"
                        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition"
                      >
                        Approve
                      </button>
                      <button
                        @click="verifyTask(task.id, false)"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition"
                      >
                        Reject
                      </button>
                    </div>
                  </div>
                </div>

                <!-- No Tasks Message -->
                <div v-if="pendingTasks.length === 0" class="text-center text-gray-500">
                  No tasks pending verification
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import moment from 'moment'

export default {
  name: 'ParentVerification',
  setup() {
    const pendingTasks = ref([])

    const fetchPendingTasks = async () => {
      try {
        const response = await axios.get('/api/tasks/pending')
        pendingTasks.value = response.data
      } catch (error) {
        console.error('Error fetching pending tasks:', error)
      }
    }

    const verifyTask = async (taskId, isApproved) => {
      try {
        await axios.post('/api/tasks/verify', {
          task_id: taskId,
          verified: isApproved
        })
        // Remove task from list
        pendingTasks.value = pendingTasks.value.filter(task => task.id !== taskId)
        // Send notification to child
        const task = pendingTasks.value.find(t => t.id === taskId)
        await axios.post('/api/notifications/task-verified', {
          task_id: taskId,
          child_email: task.child_email,
          approved: isApproved
        })
      } catch (error) {
        console.error('Error verifying task:', error)
      }
    }

    const formatTaskType = (type) => {
      return type.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
      ).join(' ')
    }

    const formatDate = (date) => {
      return moment(date).format('MMM D, YYYY h:mm A')
    }

    onMounted(() => {
      fetchPendingTasks()
    })

    return {
      pendingTasks,
      verifyTask,
      formatTaskType,
      formatDate
    }
  }
}
</script>
