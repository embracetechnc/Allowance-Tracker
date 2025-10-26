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
    <!-- Counter -->
    <path
      d="M4 8C4 7.44772 4.44772 7 5 7H19C19.5523 7 20 7.44772 20 8V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V8Z"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Sink -->
    <path
      d="M8 7V5C8 4.44772 8.44772 4 9 4H15C15.5523 4 16 4.44772 16 5V7"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Dishes -->
    <path
      d="M7 11C7 10.4477 7.44772 10 8 10H16C16.5523 10 17 10.4477 17 11V14C17 14.5523 16.5523 15 16 15H8C7.44772 15 7 14.5523 7 14V11Z"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Bubbles -->
    <circle
      v-for="(bubble, index) in bubbles"
      :key="index"
      :cx="bubble.x"
      :cy="bubble.y"
      :r="bubble.size"
      :fill="color"
      class="opacity-0"
      :class="{ 'animate-bubble': isAnimating }"
      :style="{ 
        animationDelay: `${index * 200}ms`,
        animationDuration: `${bubble.duration}ms`
      }"
    />

    <!-- Sparkles -->
    <circle
      v-for="(sparkle, index) in sparkles"
      :key="`sparkle-${index}`"
      :cx="sparkle.x"
      :cy="sparkle.y"
      :r="0.5"
      :fill="color"
      class="animate-ping"
      :style="{ animationDelay: `${index * 150}ms` }"
    />
  </svg>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'KitchenCleaningAnimation',

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
    
    // Bubble positions and sizes
    const bubbles = ref([
      { x: 9, y: 12, size: 0.8, duration: 1200 },
      { x: 11, y: 13, size: 0.6, duration: 1500 },
      { x: 13, y: 11, size: 0.7, duration: 1300 },
      { x: 15, y: 12, size: 0.5, duration: 1400 },
      { x: 10, y: 14, size: 0.6, duration: 1600 }
    ])

    // Sparkle positions
    const sparkles = ref([
      { x: 8, y: 16 },
      { x: 12, y: 18 },
      { x: 16, y: 16 },
      { x: 10, y: 14 },
      { x: 14, y: 14 }
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
      bubbles,
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

.animate-bubble {
  animation: bubble 1.5s ease-out forwards;
}

@keyframes draw {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes bubble {
  0% {
    opacity: 0;
    transform: translate(0, 0);
  }
  20% {
    opacity: 0.8;
  }
  100% {
    opacity: 0;
    transform: translate(0, -10px);
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



