<template>
  <div class="fixed inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Edit Task
              </h3>
              <div class="mt-4 space-y-4">
                <div>
                  <label for="taskName" class="block text-sm font-medium text-gray-700">Task Name</label>
                  <input 
                    type="text" 
                    id="taskName" 
                    v-model="taskData.name" 
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                  />
                </div>
                <div>
                  <label for="taskDescription" class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea 
                    id="taskDescription" 
                    v-model="taskData.description" 
                    rows="3" 
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  ></textarea>
                </div>
                <div>
                  <label for="taskPoints" class="block text-sm font-medium text-gray-700">Points</label>
                  <input 
                    type="number" 
                    id="taskPoints" 
                    v-model.number="taskData.points" 
                    min="0" 
                    max="100" 
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    required
                  />
                </div>
                <div>
                  <label for="taskDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                  <input 
                    type="date" 
                    id="taskDueDate" 
                    v-model="taskData.due_date" 
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Recurring</label>
                  <div class="mt-2">
                    <label class="inline-flex items-center">
                      <input 
                        type="checkbox" 
                        v-model="taskData.is_recurring" 
                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                      />
                      <span class="ml-2">Make this task recurring</span>
                    </label>
                  </div>
                </div>
                <div v-if="taskData.is_recurring">
                  <label for="recurringFrequency" class="block text-sm font-medium text-gray-700">Frequency</label>
                  <select 
                    id="recurringFrequency" 
                    v-model="taskData.recurring_frequency" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  >
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button 
            type="button" 
            @click="saveChanges" 
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Save Changes
          </button>
          <button 
            type="button" 
            @click="close" 
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';

export default {
  name: 'EditTaskModal',
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['update-task', 'close'],
  setup(props, { emit }) {
    const taskData = reactive({
      id: null,
      name: '',
      description: '',
      points: 0,
      due_date: '',
      is_recurring: false,
      recurring_frequency: 'weekly',
      child_id: null
    });

    onMounted(() => {
      // Copy task data to our reactive object
      Object.keys(taskData).forEach(key => {
        if (props.task[key] !== undefined) {
          taskData[key] = props.task[key];
        }
      });

      // Format date if needed
      if (props.task.due_date && typeof props.task.due_date === 'string') {
        const date = new Date(props.task.due_date);
        if (!isNaN(date.getTime())) {
          taskData.due_date = date.toISOString().split('T')[0];
        }
      }
    });

    const saveChanges = () => {
      if (!taskData.name.trim()) {
        alert('Task name is required');
        return;
      }

      emit('update-task', { ...taskData });
      emit('close');
    };

    const close = () => {
      emit('close');
    };

    return {
      taskData,
      saveChanges,
      close
    };
  }
}
</script>

<style scoped>
/* Any additional component-specific styles */
</style>