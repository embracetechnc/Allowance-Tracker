<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <div class="relative bg-white w-full sm:max-w-lg mx-4 sm:mx-auto rounded-lg shadow-xl overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
            Add New Child
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-500 focus:outline-none"
          >
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Content -->
        <div class="px-4 pt-5 pb-4 sm:p-6">
          <form class="space-y-4">
                  <!-- Name Field -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 text-left">
                      Name
                    </label>
                    <div class="mt-1">
                      <input
                        type="text"
                        id="name"
                        name="name"
                        v-model="form.name"
                        required
                        :class="getFieldStateClasses('name', errors, touched)"
                        placeholder="Enter child's name"
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

                  <!-- Email Field -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 text-left">
                      Email
                    </label>
                    <div class="mt-1">
                      <input
                        type="email"
                        id="email"
                        name="email"
                        v-model="form.email"
                        required
                        :class="getFieldStateClasses('email', errors, touched)"
                        placeholder="Enter email address"
                        @blur="handleFieldBlur('email')"
                        @input="handleFieldInput('email')"
                      />
                      <div class="mt-1" v-if="touched.email">
                        <p :class="['text-sm', getValidationMessage('email', errors, validationSchema).color]">
                          {{ getValidationMessage('email', errors, validationSchema).message }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Weekly Allowance Field -->
                  <div>
                    <label for="weekly_allowance" class="block text-sm font-medium text-gray-700 text-left">
                      Weekly Allowance Rate
                    </label>
                    <div class="mt-1">
                      <input
                        type="number"
                        id="weekly_allowance"
                        name="weekly_allowance_rate"
                        v-model.number="form.weekly_allowance_rate"
                        required
                        min="0"
                        max="1000"
                        step="0.01"
                        :class="getFieldStateClasses('weekly_allowance_rate', errors, touched)"
                        placeholder="0.00"
                        @blur="handleFieldBlur('weekly_allowance_rate')"
                        @input="handleFieldInput('weekly_allowance_rate')"
                      />
                      <div class="mt-1" v-if="touched.weekly_allowance_rate">
                        <p :class="['text-sm', getValidationMessage('weekly_allowance_rate', errors, validationSchema).color]">
                          {{ getValidationMessage('weekly_allowance_rate', errors, validationSchema).message }}
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

          </form>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="handleSubmit"
            class="w-full sm:w-auto sm:ml-3 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
            :disabled="loading"
          >
            {{ loading ? 'Adding...' : 'Add Child' }}
          </button>
          <button
            type="button"
            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
            @click="$emit('close')"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import axios from 'axios';
import { fieldRequirements, validateField, validateForm, getFieldStateClasses, getValidationMessage } from '@/utils/validation';

export default {
  name: 'AddChildModal',
  emits: ['close', 'add-child'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const touched = reactive({});
    const errors = reactive({});
    
    const form = reactive({
      name: '',
      email: '',
      weekly_allowance_rate: 0
    });

    const validationSchema = {
      name: fieldRequirements.name,
      email: fieldRequirements.email,
      weekly_allowance_rate: {
        required: true,
        min: 0,
        max: 1000,
        message: 'Weekly allowance rate should be between $0 and $1000',
        hint: 'Set the base weekly allowance amount'
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

        const childData = {
          name: form.name.trim(),
          email: form.email.trim(),
          weekly_allowance_rate: Number(form.weekly_allowance_rate)
        };

        console.log('Submitting child data:', childData);
        
        // Emit the event to parent component
        emit('add-child', childData);
        emit('close');
      } catch (err) {
        console.error('Failed to add child:', err);
        error.value = err.message || 'Failed to add child. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      errors,
      touched,
      validationSchema,
      handleSubmit,
      handleFieldBlur,
      handleFieldInput,
      getFieldStateClasses,
      getValidationMessage
    };
  }
};
</script>