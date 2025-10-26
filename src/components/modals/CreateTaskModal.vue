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
              <form @submit.prevent="handleSubmit">
                <div class="space-y-4">
                  <!-- Task Name -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 text-left">
                      Task Name
                    </label>
                    <div class="mt-1">
                      <input
                        type="text"
                        id="name"
                        name="name"
                        v-model="form.name"
                        required
                        data-test="task-name"
                        :class="getFieldStateClasses('name', errors, touched)"
                        placeholder="Enter task name"
                        @blur="handleFieldBlur('name')"
                        @input="handleFieldInput('name')"
                      />
                      <div class="mt-1" v-if="touched.name">
                        <p :class="['text-sm', getValidationMessage('name', errors, validationSchema).color]">
                          {{ getValidationMessage('name', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Description -->
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 text-left">
                      Description
                    </label>
                    <div class="mt-1">
                      <textarea
                        id="description"
                        name="description"
                        v-model="form.description"
                        rows="3"
                        data-test="task-description"
                        :class="getFieldStateClasses('description', errors, touched)"
                        placeholder="Enter task description"
                        @blur="handleFieldBlur('description')"
                        @input="handleFieldInput('description')"
                      ></textarea>
                      <div class="mt-1" v-if="touched.description">
                        <p :class="['text-sm', getValidationMessage('description', errors, validationSchema).color]">
                          {{ getValidationMessage('description', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Category -->
                  <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 text-left">
                      Category
                    </label>
                    <div class="mt-1">
                      <select
                        id="category"
                        name="category"
                        v-model="form.category"
                        required
                        data-test="task-category"
                        :class="getFieldStateClasses('category', errors, touched)"
                        @blur="handleFieldBlur('category')"
                        @change="handleFieldInput('category')"
                      >
                        <option value="">Select Category</option>
                        <option value="chores">Chores</option>
                        <option value="homework">Homework</option>
                        <option value="extra_credit">Extra Credit</option>
                        <option value="behavior">Behavior</option>
                      </select>
                      <div class="mt-1" v-if="touched.category">
                        <p :class="['text-sm', getValidationMessage('category', errors, validationSchema).color]">
                          {{ getValidationMessage('category', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Points -->
                  <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 text-left">
                      Points
                    </label>
                    <div class="mt-1">
                      <input
                        type="number"
                        id="points"
                        name="points"
                        v-model.number="form.points"
                        required
                        data-test="task-points"
                        min="0"
                        max="100"
                        step="1"
                        :class="getFieldStateClasses('points', errors, touched)"
                        @blur="handleFieldBlur('points')"
                        @input="handleFieldInput('points')"
                      />
                      <div class="mt-1" v-if="touched.points">
                        <p :class="['text-sm', getValidationMessage('points', errors, validationSchema).color]">
                          {{ getValidationMessage('points', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Recurring Task -->
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
                      <p class="text-gray-500">This task repeats on a schedule</p>
                    </div>
                  </div>

                  <!-- Recurrence Pattern (if recurring) -->
                  <div v-if="form.is_recurring">
                    <label for="recurrence_pattern" class="block text-sm font-medium text-gray-700 text-left">
                      Recurrence Pattern
                    </label>
                    <div class="mt-1">
                      <select
                        id="recurrence_pattern"
                        name="recurrence_pattern"
                        v-model="form.recurrence_pattern"
                        required
                        :class="getFieldStateClasses('recurrence_pattern', errors, touched)"
                        @blur="handleFieldBlur('recurrence_pattern')"
                        @change="handleFieldInput('recurrence_pattern')"
                      >
                        <option value="">Select Pattern</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                      </select>
                      <div class="mt-1" v-if="touched.recurrence_pattern">
                        <p :class="['text-sm', getValidationMessage('recurrence_pattern', errors, validationSchema).color]">
                          {{ getValidationMessage('recurrence_pattern', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Assign To -->
                  <div>
                    <label for="child_id" class="block text-sm font-medium text-gray-700 text-left">
                      Assign To
                    </label>
                    <div class="mt-1">
                      <select
                        id="child_id"
                        name="child_id"
                        v-model="form.child_id"
                        required
                        data-test="task-child"
                        :class="getFieldStateClasses('child_id', errors, touched)"
                        @blur="handleFieldBlur('child_id')"
                        @change="handleFieldInput('child_id')"
                      >
                        <option value="">Select Child</option>
                        <option v-for="child in children" :key="child.id" :value="child.id">
                          {{ child.name }}
                        </option>
                      </select>
                      <div class="mt-1" v-if="touched.child_id">
                        <p :class="['text-sm', getValidationMessage('child_id', errors, validationSchema).color]">
                          {{ getValidationMessage('child_id', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Due Date -->
                  <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 text-left">
                      Due Date
                    </label>
                    <div class="mt-1">
                      <input
                        type="datetime-local"
                        id="due_date"
                        name="due_date"
                        v-model="form.due_date"
                        required
                        data-test="task-due-date"
                        :class="getFieldStateClasses('due_date', errors, touched)"
                        @blur="handleFieldBlur('due_date')"
                        @input="handleFieldInput('due_date')"
                      />
                      <div class="mt-1" v-if="touched.due_date">
                        <p :class="['text-sm', getValidationMessage('due_date', errors, validationSchema).color]">
                          {{ getValidationMessage('due_date', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Error Message -->
                  <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ error }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                      type="submit"
                      class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                      :disabled="loading"
                      data-test="submit-task"
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
import { ref, reactive } from 'vue';
import { fieldRequirements, validateField, validateForm, getFieldStateClasses, getValidationMessage } from '@/utils/validation';

export default {
  name: 'CreateTaskModal',
  props: {
    children: {
      type: Array,
      required: true
    }
  },
  emits: ['close', 'create-task'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const touched = reactive({});
    const errors = reactive({});
    
    const form = reactive({
      name: '',
      description: '',
      category: '',
      points: 0,
      is_recurring: false,
      recurrence_pattern: '',
      child_id: '',
      due_date: ''
    });

    const validationSchema = {
      name: fieldRequirements.taskName,
      description: fieldRequirements.taskDescription,
      points: fieldRequirements.points,
      category: {
        required: true,
        message: 'Please select a category',
        hint: 'Choose the most appropriate category for this task'
      },
      child_id: {
        required: true,
        message: 'Please select a child',
        hint: 'Choose which child will be responsible for this task'
      },
      due_date: {
        required: true,
        message: 'Please set a due date',
        hint: 'When should this task be completed?'
      },
      recurrence_pattern: {
        required: (form) => form.is_recurring,
        message: 'Please select a recurrence pattern',
        hint: 'How often should this task repeat?'
      }
    };

    const validateField = (fieldName, value) => {
      touched[fieldName] = true;
      const result = validateField(value, validationSchema[fieldName]);
      if (!result.isValid) {
        errors[fieldName] = result.errors;
      } else {
        delete errors[fieldName];
      }
      return result.isValid;
    };

    const handleFieldBlur = (fieldName) => {
      validateField(fieldName, form[fieldName]);
    };

    const handleFieldInput = (fieldName) => {
      if (touched[fieldName]) {
        validateField(fieldName, form[fieldName]);
      }
    };

    const handleSubmit = async () => {
      try {
        // Mark all fields as touched
        Object.keys(validationSchema).forEach(field => {
          touched[field] = true;
        });

        // Validate all fields
        const { isValid, errors: validationErrors } = validateForm(form, validationSchema);
        
        if (!isValid) {
          Object.assign(errors, validationErrors);
          error.value = 'Please fix the errors before submitting';
          return;
        }

        loading.value = true;
        error.value = '';

        const taskData = {
          id: Date.now(), // Temporary ID
          name: form.name.trim(),
          description: form.description.trim(),
          category: form.category,
          points: Number(form.points),
          is_recurring: form.is_recurring,
          recurrence_pattern: form.is_recurring ? form.recurrence_pattern : null,
          child_id: form.child_id,
          due_date: form.due_date,
          status: 'assigned'
        };

        console.log('Creating task:', taskData);
        emit('create-task', taskData);
        emit('close');
      } catch (err) {
        console.error('Failed to create task:', err);
        error.value = err.message || 'Failed to create task. Please try again.';
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