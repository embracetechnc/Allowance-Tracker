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
    <!-- Sink -->
    <path
      d="M4 14C4 13.4477 4.44772 13 5 13H19C19.5523 13 20 13.4477 20 14V20C20 20.5523 19.5523 21 19 21H5C4.44772 21 4 20.5523 4 20V14Z"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Faucet -->
    <path
      d="M12 13V8M12 8C12 6.34315 13.3431 5 15 5H16M12 8C12 6.34315 10.6569 5 9 5H8"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      :class="{ 'animate-draw': isAnimating }"
    />

    <!-- Water Drops -->
    <path
      v-for="(drop, index) in waterDrops"
      :key="index"
      :d="drop.path"
      :fill="color"
      class="opacity-0"
      :class="{ 'animate-water-drop': isAnimating }"
      :style="{ animationDelay: `${index * 200}ms` }"
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
  name: 'BathroomCleaningAnimation',

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
    
    // Water drops paths
    const waterDrops = ref([
      { path: 'M12 9L11 11M12 9L13 11' },
      { path: 'M12 11L11 13M12 11L13 13' },
      { path: 'M12 13L11 15M12 13L13 15' }
    ])

    // Sparkle positions
    const sparkles = ref([
      { x: 7, y: 16 },
      { x: 12, y: 18 },
      { x: 17, y: 16 },
      { x: 9, y: 14 },
      { x: 15, y: 14 }
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
      waterDrops,
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

.animate-water-drop {
  animation: waterDrop 1s ease-in forwards;
}

@keyframes draw {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes waterDrop {
  0% {
    opacity: 1;
    transform: translateY(0);
  }
  100% {
    opacity: 0;
    transform: translateY(10px);
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
