<template>
  <div class="space-y-6">
    <!-- Task Categories Management -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Task Categories</h2>
      
      <div class="mt-4">
        <!-- Category List -->
        <div class="space-y-4">
          <div 
            v-for="category in categories" 
            :key="category.id"
            class="flex items-center justify-between"
          >
            <div class="flex items-center">
              <span 
                class="w-3 h-3 rounded-full"
                :style="{ backgroundColor: category.color }"
              ></span>
              <span class="ml-3 text-sm text-gray-900">{{ category.name }}</span>
            </div>
            
            <div class="flex items-center space-x-2">
              <button
                @click="editCategory(category)"
                class="text-indigo-600 hover:text-indigo-900"
              >
                Edit
              </button>
              <button
                @click="deleteCategory(category.id)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Add Category Form -->
        <form @submit.prevent="addCategory" class="mt-6">
          <div class="flex items-center space-x-4">
            <input
              v-model="newCategory.name"
              type="text"
              placeholder="Category name"
              class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <input
              v-model="newCategory.color"
              type="color"
              class="h-8 w-8 rounded-md border-gray-300"
            />
            <button
              type="submit"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Add
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Task Requirements -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Task Requirements</h2>

      <div class="mt-4 space-y-4">
        <!-- Minimum Tasks Per Week -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Minimum Tasks Per Week
          </label>
          <input
            v-model.number="requirements.minTasksPerWeek"
            type="number"
            min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Required Categories -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Required Categories
          </label>
          <div class="mt-2 space-y-2">
            <div 
              v-for="category in categories" 
              :key="category.id"
              class="flex items-center"
            >
              <input
                :id="'required-' + category.id"
                v-model="requirements.requiredCategories"
                :value="category.id"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label :for="'required-' + category.id" class="ml-3 text-sm text-gray-700">
                {{ category.name }}
              </label>
            </div>
          </div>
        </div>

        <!-- Points Requirements -->
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Minimum Points Per Week
          </label>
          <input
            v-model.number="requirements.minPointsPerWeek"
            type="number"
            min="0"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Task Instructions Management -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Task Instructions</h2>

      <div class="mt-4">
        <!-- Rich Text Editor -->
        <div class="prose prose-sm">
          <div 
            ref="editor"
            class="min-h-[200px] p-4 border rounded-md"
            contenteditable="true"
            @input="updateInstructions"
          ></div>
        </div>

        <!-- Upload Video -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700">
            Instruction Video
          </label>
          <div class="mt-1 flex items-center">
            <input
              ref="videoInput"
              type="file"
              accept="video/*"
              class="hidden"
              @change="handleVideoUpload"
            />
            <button
              @click="triggerVideoUpload"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Upload Video
            </button>
            <span v-if="videoFile" class="ml-3 text-sm text-gray-500">
              {{ videoFile.name }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Changes -->
    <div class="flex justify-end">
      <button
        @click="saveChanges"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
      >
        Save Changes
      </button>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';

export default {
  name: 'ParentControls',

  setup() {
    const categories = ref([
      { id: 1, name: 'Chores', color: '#4F46E5' },
      { id: 2, name: 'Homework', color: '#059669' },
      { id: 3, name: 'Extra Credit', color: '#DC2626' }
    ]);

    const newCategory = reactive({
      name: '',
      color: '#4F46E5'
    });

    const requirements = reactive({
      minTasksPerWeek: 5,
      requiredCategories: [],
      minPointsPerWeek: 20
    });

    const editor = ref(null);
    const videoInput = ref(null);
    const videoFile = ref(null);
    const instructions = ref('');

    const addCategory = () => {
      if (newCategory.name.trim()) {
        categories.value.push({
          id: Date.now(),
          name: newCategory.name.trim(),
          color: newCategory.color
        });
        newCategory.name = '';
      }
    };

    const editCategory = (category) => {
      // Implement edit functionality
      console.log('Edit category:', category);
    };

    const deleteCategory = (categoryId) => {
      categories.value = categories.value.filter(cat => cat.id !== categoryId);
    };

    const updateInstructions = () => {
      if (editor.value) {
        instructions.value = editor.value.innerHTML;
      }
    };

    const triggerVideoUpload = () => {
      videoInput.value.click();
    };

    const handleVideoUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        videoFile.value = file;
      }
    };

    const saveChanges = async () => {
      try {
        // Prepare data
        const data = {
          categories: categories.value,
          requirements: requirements,
          instructions: instructions.value,
          videoFile: videoFile.value
        };

        // Send to backend
        console.log('Saving changes:', data);
        // await axios.post('/api/parent/settings', data);

        // Show success message
        alert('Changes saved successfully!');
      } catch (error) {
        console.error('Failed to save changes:', error);
        alert('Failed to save changes. Please try again.');
      }
    };

    return {
      categories,
      newCategory,
      requirements,
      editor,
      videoInput,
      videoFile,
      addCategory,
      editCategory,
      deleteCategory,
      updateInstructions,
      triggerVideoUpload,
      handleVideoUpload,
      saveChanges
    };
  }
};
</script>


