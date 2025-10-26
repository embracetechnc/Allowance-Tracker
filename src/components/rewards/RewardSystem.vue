<template>
  <div class="space-y-6">
    <!-- Achievements -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Achievements</h2>
      <div class="mt-4 space-y-4">
        <div v-for="achievement in achievements" :key="achievement.id" class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-900">{{ achievement.name }}</h3>
              <p class="text-sm text-gray-500">
            {{ achievement.description }}
            <span v-if="achievement.name === 'Early Bird'"> (+2 points/task)</span>
            <span v-if="achievement.name === 'Perfect Week'"> (+10 points)</span>
          </p>
            </div>
            <div class="flex items-center">
              <div class="w-32 bg-gray-200 rounded-full h-2">
                <div
                  class="bg-indigo-500 h-2 rounded-full"
                  :style="{ width: `${achievement.progress}%` }"
                ></div>
              </div>
              <span class="ml-2 text-sm text-gray-500">{{ achievement.progress }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Streak Calendar -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Task Streak</h2>
      <div class="mt-4">
        <div class="grid grid-cols-7 gap-2">
          <div
            v-for="day in streakDays"
            :key="day.day"
            class="w-8 h-8 flex items-center justify-center rounded-full"
            :class="day.completed ? 'bg-green-500 text-white' : 'bg-gray-100'"
          >
            {{ day.day }}
          </div>
        </div>
        <p class="mt-2 text-sm text-gray-500">
          Complete tasks daily to earn +{{ streakBonus }} points!
        </p>
      </div>
    </div>

    <!-- Special Rewards -->
    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-lg font-medium text-gray-900">Special Rewards</h2>
      <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div
          v-for="reward in specialRewards"
          :key="reward.id"
          class="border rounded-lg p-4"
          :class="availablePoints >= reward.cost ? 'cursor-pointer hover:bg-gray-50' : 'opacity-50 cursor-not-allowed'"
          @click="claimReward(reward)"
        >
          <h3 class="text-sm font-medium text-gray-900">{{ reward.name }}</h3>
          <p class="text-sm text-gray-500">{{ reward.description }}</p>
          <div class="mt-2 flex items-center justify-between">
            <span class="text-sm font-medium text-indigo-600">{{ reward.cost }} points</span>
            <button
              class="text-sm text-white bg-indigo-600 px-3 py-1 rounded-md"
              :disabled="availablePoints < reward.cost"
            >
              Claim
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'RewardSystem',

  props: {
    childId: {
      type: [Number, String],
      required: true
    }
  },

  setup(props, { emit }) {
    const achievements = ref([
      {
        id: 1,
        name: 'Task Master',
        description: 'Complete 50 tasks',
        progress: 75,
        unlocked: false,
        icon: 'StarIcon'
      },
      {
        id: 2,
        name: 'Early Bird',
        description: 'Complete 10 tasks before due date',
        progress: 50,
        unlocked: false,
        icon: 'ClockIcon'
      },
      {
        id: 3,
        name: 'Perfect Week',
        description: 'Complete all tasks in a week',
        progress: 100,
        unlocked: true,
        icon: 'FireIcon'
      }
    ]);

    const streakDays = ref([
      { day: 1, completed: true },
      { day: 2, completed: true },
      { day: 3, completed: false },
      { day: 4, completed: false },
      { day: 5, completed: false },
      { day: 6, completed: false },
      { day: 7, completed: false }
    ]);
    const streakBonus = ref(5);
    const availablePoints = ref(25);

    const specialRewards = ref([
      {
        id: 1,
        name: 'Extra Screen Time',
        description: 'Get 30 minutes of extra screen time',
        cost: 20
      },
      {
        id: 2,
        name: 'Late Bedtime',
        description: 'Stay up 30 minutes later',
        cost: 25
      },
      {
        id: 3,
        name: 'Movie Night',
        description: 'Pick the movie for movie night',
        cost: 30
      }
    ]);

    const claimReward = (reward) => {
      if (availablePoints.value >= reward.cost) {
        availablePoints.value -= reward.cost;
        // Emit event to parent component
        emit('claim-reward', reward);
      }
    };

    return {
      achievements,
      streakDays,
      streakBonus,
      availablePoints,
      specialRewards,
      claimReward
    };
  }
};
</script>