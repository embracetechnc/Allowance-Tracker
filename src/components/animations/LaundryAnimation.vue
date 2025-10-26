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
    <!-- Washing Machine -->
    <path
      d="M4 6C4 5.44772 4.44772 5 5 5H19C19.5523 5 20 5.44772 20 6V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V6Z"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Door Window -->
    <circle
      cx="12"
      cy="13"
      r="5"
      :stroke="color"
      stroke-width="2"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Control Panel -->
    <path
      d="M8 8H10M14 8H16"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Spinning Clothes -->
    <path
      v-for="(cloth, index) in clothes"
      :key="index"
      :d="cloth.path"
      :stroke="color"
      stroke-width="1.5"
      fill="none"
      :class="{ 'animate-spin-clothes': isAnimating }"
      :style="{ 
        animationDelay: `${index * 100}ms`,
        transformOrigin: 'center'
      }"
    />

    <!-- Water Drops -->
    <circle
      v-for="(drop, index) in waterDrops"
      :key="`drop-${index}`"
      :cx="drop.x"
      :cy="drop.y"
      :r="0.5"
      :fill="color"
      class="opacity-0"
      :class="{ 'animate-water': isAnimating }"
      :style="{ animationDelay: `${index * 150}ms` }"
    />

    <!-- Bubbles -->
    <circle
      v-for="(bubble, index) in bubbles"
      :key="`bubble-${index}`"
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
  </svg>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
  name: 'LaundryAnimation',

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
    
    // Clothes paths (simplified curves representing clothes)
    const clothes = ref([
      { path: 'M10 11Q12 13 14 11' },
      { path: 'M9 13Q12 15 15 13' },
      { path: 'M10 15Q12 13 14 15' }
    ])

    // Water drop positions
    const waterDrops = ref([
      { x: 8, y: 13 },
      { x: 16, y: 13 },
      { x: 12, y: 9 },
      { x: 12, y: 17 }
    ])

    // Bubble positions and sizes
    const bubbles = ref([
      { x: 9, y: 12, size: 0.8, duration: 1200 },
      { x: 15, y: 12, size: 0.6, duration: 1500 },
      { x: 12, y: 10, size: 0.7, duration: 1300 },
      { x: 12, y: 16, size: 0.5, duration: 1400 }
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
      clothes,
      waterDrops,
      bubbles,
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

.animate-spin-clothes {
  animation: spinClothes 2s ease-in-out infinite;
}

.animate-water {
  animation: water 1s ease-out forwards;
}

.animate-bubble {
  animation: bubble 1.5s ease-out forwards;
}

@keyframes draw {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes spinClothes {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes water {
  0% {
    opacity: 0;
    transform: translate(0, 0);
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    transform: translate(0, 5px);
  }
}

@keyframes bubble {
  0% {
    opacity: 0;
    transform: translate(0, 0) scale(1);
  }
  20% {
    opacity: 0.8;
    transform: translate(0, -2px) scale(1.1);
  }
  100% {
    opacity: 0;
    transform: translate(0, -5px) scale(0.8);
  }
}
</style>



