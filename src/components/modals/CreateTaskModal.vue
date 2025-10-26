<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Create New Task
            </h3>
            <div class="mt-2">
              <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Task Name
                  </label>
                  <div class="mt-1">
                    <input
                      type="text"
                      id="name"
                      v-model="form.name"
                      required
                      class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    />
                  </div>
                </div>

                <div>
                  <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                  </label>
                  <div class="mt-1">
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    ></textarea>
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">
                      Category
                    </label>
                    <select
                      id="category"
                      v-model="form.category"
                      required
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                      <option value="">Select Category</option>
                      <option value="chores">Chores</option>
                      <option value="homework">Homework</option>
                      <option value="extra_credit">Extra Credit</option>
                      <option value="behavior">Behavior</option>
                    </select>
                  </div>

                  <div>
                    <label for="points" class="block text-sm font-medium text-gray-700">
                      Points
                    </label>
                    <input
                      type="number"
                      id="points"
                      v-model.number="form.points"
                      required
                      min="0"
                      step="0.5"
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    />
                  </div>
                </div>

                <div class="flex items-start">
                  <div class="flex items-center h-5">
                    <input
                      id="is_recurring"
                      type="checkbox"
                      v-model="form.is_recurring"
                      class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label for="is_recurring" class="font-medium text-gray-700">Recurring Task</label>
                    <p class="text-gray-500">Check if this task repeats on a schedule</p>
                  </div>
                </div>

                <div v-if="form.is_recurring">
                  <label for="recurrence_pattern" class="block text-sm font-medium text-gray-700">
                    Recurrence Pattern
                  </label>
                  <select
                    id="recurrence_pattern"
                    v-model="form.recurrence_pattern"
                    required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  >
                    <option value="">Select Pattern</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>
                </div>

                <div v-if="children.length > 0">
                  <label for="child" class="block text-sm font-medium text-gray-700">
                    Assign To
                  </label>
                  <select
                    id="child"
                    v-model="form.child_id"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  >
                    <option value="">Select Child</option>
                    <option v-for="child in children" :key="child.id" :value="child.id">
                      {{ child.name }}
                    </option>
                  </select>
                </div>

                <div v-if="form.child_id">
                  <label for="due_date" class="block text-sm font-medium text-gray-700">
                    Due Date
                  </label>
                  <input
                    type="datetime-local"
                    id="due_date"
                    v-model="form.due_date"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                  />
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                  >
                    {{ loading ? 'Creating...' : 'Create Task' }}
                  </button>
                  <button
                    type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                    @click="$emit('close')"
                  >
                    Cancel
                  </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mt-2 text-sm text-red-600">
                  {{ error }}
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'CreateTaskModal',
  props: {
    children: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'create-task'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const form = ref({
      name: '',
      description: '',
      category: '',
      points: 0,
      is_recurring: false,
      recurrence_pattern: '',
      child_id: '',
      due_date: ''
    });

    const handleSubmit = async () => {
      loading.value = true;
      error.value = '';

      try {
        // Validate required fields
        if (!form.value.name) {
          throw new Error('Task name is required');
        }
        if (!form.value.category) {
          throw new Error('Category is required');
        }
        if (form.value.points <= 0) {
          throw new Error('Points must be greater than 0');
        }
        if (form.value.is_recurring && !form.value.recurrence_pattern) {
          throw new Error('Recurrence pattern is required for recurring tasks');
        }
        if (!form.value.child_id) {
          throw new Error('Please select a child to assign the task');
        }
        if (!form.value.due_date) {
          throw new Error('Due date is required');
        }

        // Create task data
        const taskData = {
          name: form.value.name,
          description: form.value.description,
          category: form.value.category,
          points: Number(form.value.points),
          is_recurring: form.value.is_recurring,
          recurrence_pattern: form.value.is_recurring ? form.value.recurrence_pattern : null,
          child_id: form.value.child_id,
          due_date: form.value.due_date
        };

        emit('create-task', taskData);
      } catch (err) {
        error.value = err.message || 'Failed to create task';
        return; // Don't close modal if there's an error
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      handleSubmit
    };
  }
};
</script>
