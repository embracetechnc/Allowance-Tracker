<template>
  <div class="space-y-6">
    <!-- Task Details Section -->
    <div class="bg-white shadow rounded-lg p-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-medium text-gray-900">{{ task.name }}</h2>
        <span 
          :class="statusClasses[task.status]"
          class="px-2.5 py-0.5 rounded-full text-xs font-medium"
        >
          {{ task.status }}
        </span>
      </div>

      <!-- Task Description -->
      <div class="mt-4">
        <p class="text-sm text-gray-500">{{ task.description }}</p>
      </div>

      <!-- Task Photos -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-900">Task Photos</h3>
        <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
          <div 
            v-for="photo in taskPhotos" 
            :key="photo.id"
            class="relative"
          >
            <img 
              :src="photo.url" 
              :alt="photo.description"
              class="h-24 w-full object-cover rounded-lg"
            />
            <button
              @click="deletePhoto(photo.id)"
              class="absolute top-0 right-0 -mt-2 -mr-2 p-1 rounded-full bg-red-100 text-red-600 hover:bg-red-200"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Upload Photo Button -->
          <div 
            class="relative h-24 rounded-lg border-2 border-dashed border-gray-300 hover:border-indigo-500 cursor-pointer"
            @click="triggerFileUpload"
          >
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept="image/*"
              @change="handleFileUpload"
            />
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Completion Checklist -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-900">Completion Checklist</h3>
        <div class="mt-2 space-y-2">
          <div 
            v-for="item in checklist" 
            :key="item.id"
            class="flex items-center"
          >
            <input
              :id="'checklist-' + item.id"
              v-model="item.completed"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label :for="'checklist-' + item.id" class="ml-3 text-sm text-gray-700">
              {{ item.text }}
            </label>
          </div>
          
          <!-- Add Checklist Item -->
          <div class="flex items-center mt-4">
            <input
              v-model="newChecklistItem"
              type="text"
              placeholder="Add new item..."
              class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @keyup.enter="addChecklistItem"
            />
            <button
              @click="addChecklistItem"
              class="ml-3 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Add
            </button>
          </div>
        </div>
      </div>

      <!-- Time Tracking -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-900">Time Tracking</h3>
        <div class="mt-2">
          <div class="flex items-center space-x-4">
            <span class="text-2xl font-semibold text-gray-900">
              {{ formatTime(elapsedTime) }}
            </span>
            <button
              v-if="!isTracking"
              @click="startTracking"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
            >
              Start
            </button>
            <button
              v-else
              @click="stopTracking"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
            >
              Stop
            </button>
          </div>
          <p class="mt-2 text-sm text-gray-500">
            Estimated time: {{ task.estimatedTime }} minutes
          </p>
        </div>
      </div>

      <!-- Priority Level -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-900">Priority Level</h3>
        <div class="mt-2">
          <select
            v-model="task.priority"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
          </select>
        </div>
      </div>

      <!-- Task Instructions -->
      <div class="mt-6">
        <h3 class="text-sm font-medium text-gray-900">Instructions</h3>
        <div class="mt-2 prose prose-sm text-gray-500">
          <div v-html="task.instructions"></div>
        </div>
        
        <!-- Instruction Video -->
        <div v-if="task.videoUrl" class="mt-4">
          <video
            class="w-full rounded-lg"
            controls
            :src="task.videoUrl"
          ></video>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'EnhancedTaskManager',

  props: {
    task: {
      type: Object,
      required: true
    }
  },

  setup(props) {
    const fileInput = ref(null);
    const taskPhotos = ref([]);
    const checklist = ref([]);
    const newChecklistItem = ref('');
    const elapsedTime = ref(0);
    const isTracking = ref(false);
    let timer = null;

    const statusClasses = {
      pending: 'bg-gray-100 text-gray-800',
      'in-progress': 'bg-yellow-100 text-yellow-800',
      completed: 'bg-green-100 text-green-800'
    };

    // Photo handling
    const triggerFileUpload = () => {
      fileInput.value.click();
    };

    const handleFileUpload = async (event) => {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          taskPhotos.value.push({
            id: Date.now(),
            url: e.target.result,
            description: file.name
          });
        };
        reader.readAsDataURL(file);
      }
    };

    const deletePhoto = (photoId) => {
      taskPhotos.value = taskPhotos.value.filter(photo => photo.id !== photoId);
    };

    // Checklist handling
    const addChecklistItem = () => {
      if (newChecklistItem.value.trim()) {
        checklist.value.push({
          id: Date.now(),
          text: newChecklistItem.value.trim(),
          completed: false
        });
        newChecklistItem.value = '';
      }
    };

    // Time tracking
    const formatTime = (seconds) => {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;
      return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    };

    const startTracking = () => {
      isTracking.value = true;
      timer = setInterval(() => {
        elapsedTime.value++;
      }, 1000);
    };

    const stopTracking = () => {
      isTracking.value = false;
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    };

    return {
      fileInput,
      taskPhotos,
      checklist,
      newChecklistItem,
      elapsedTime,
      isTracking,
      statusClasses,
      triggerFileUpload,
      handleFileUpload,
      deletePhoto,
      addChecklistItem,
      formatTime,
      startTracking,
      stopTracking
    };
  }
};
</script>


