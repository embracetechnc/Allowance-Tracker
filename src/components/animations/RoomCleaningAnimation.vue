<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 24 24"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="transform transition-transform duration-700"
    :class="{ 'scale-110': isAnimating }"
  >
    <!-- House Frame -->
    <path
      d="M3 12L5 10M5 10L12 3L19 10M5 10V20C5 20.5523 5.44772 21 6 21H9M19 10L21 12M19 10V20C19 20.5523 18.5523 21 18 21H15M9 21C9.55228 21 10 20.5523 10 20V16C10 15.4477 10.4477 15 11 15H13C13.5523 15 14 15.4477 14 16V20C14 20.5523 14.4477 21 15 21M9 21H15"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Sparkles -->
    <circle
      v-for="(sparkle, index) in sparkles"
      :key="index"
      :cx="sparkle.x"
      :cy="sparkle.y"
      :r="0.5"
      :fill="color"
      class="animate-ping"
      :style="{ animationDelay: `${index * 200}ms` }"
    />
  </svg>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'RoomCleaningAnimation',

  props: {
    size: {
      type: [Number, String],
      default: 24
    },
    color: {
      type: String,
      default: 'currentColor'
    },
    autoplay: {
      type: Boolean,
      default: true
    }
  },

  setup(props) {
    const isAnimating = ref(false)
    const sparkles = ref([
      { x: 8, y: 8 },
      { x: 16, y: 8 },
      { x: 12, y: 12 },
      { x: 8, y: 16 },
      { x: 16, y: 16 }
    ])
    let animationInterval = null

    const startAnimation = () => {
      isAnimating.value = true
      setTimeout(() => {
        isAnimating.value = false
      }, 2000)
    }

    onMounted(() => {
      if (props.autoplay) {
        animationInterval = setInterval(startAnimation, 3000)
      }
    })

    onUnmounted(() => {
      if (animationInterval) {
        clearInterval(animationInterval)
      }
    })

    return {
      isAnimating,
      sparkles,
      startAnimation
    }
  }
}
</script>

<style scoped>
.animate-draw {
  stroke-dasharray: 100;
  stroke-dashoffset: 100;
  animation: draw 1s ease forwards;
}

@keyframes draw {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
