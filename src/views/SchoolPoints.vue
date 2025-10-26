<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Bible Verse Banner -->
    <BibleVerseBanner />

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-semibold text-gray-900">School Points</h1>
          <p class="mt-1 text-sm text-gray-500">
            Track and manage behavior points for your children
          </p>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <!-- Points Input Section (Parents Only) -->
          <div v-if="isParent">
            <ParentPointInput 
              :children="children"
              @points-updated="handlePointsUpdated"
            />
          </div>

          <!-- Points Display Section -->
          <div v-if="isParent || isChild">
            <PointsDisplay 
              :user-id="userId"
              :is-parent="isParent"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div 
      v-if="loading"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-xl">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import BibleVerseBanner from '../components/BibleVerseBanner.vue';
import ParentPointInput from '../components/ParentPointInput.vue';
import PointsDisplay from '../components/PointsDisplay.vue';

export default {
  name: 'SchoolPoints',

  components: {
    BibleVerseBanner,
    ParentPointInput,
    PointsDisplay
  },

  setup() {
    const store = useStore();
    const loading = ref(true);

    // Computed properties
    const user = computed(() => store.state.auth.user);
    const isParent = computed(() => user.value?.role === 'parent');
    const isChild = computed(() => user.value?.role === 'child');
    const userId = computed(() => user.value?.id);

    // For parent users, get list of children
    const children = ref([]);

    const fetchChildren = async () => {
      if (isParent.value) {
        try {
          const response = await fetch('/api/users/children', {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
          });
          const data = await response.json();
          if (data.success) {
            children.value = data.children;
          }
        } catch (error) {
          console.error('Error fetching children:', error);
        }
      }
    };

    const handlePointsUpdated = () => {
      // Refresh points display
      if (isParent.value) {
        store.dispatch('points/fetchWeeklyPoints');
      }
    };

    onMounted(async () => {
      try {
        await fetchChildren();
        if (isChild.value) {
          await store.dispatch('points/fetchWeeklyPoints', { userId: userId.value });
        }
      } finally {
        loading.value = false;
      }
    });

    return {
      loading,
      isParent,
      isChild,
      userId,
      children,
      handlePointsUpdated
    };
  }
};
</script>
