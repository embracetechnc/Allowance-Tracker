<template>
  <div 
    v-if="verse && !dismissed" 
    class="bg-indigo-600 transition-all duration-500"
    :class="{ 'opacity-100': show, 'opacity-0': !show }"
  >
    <div class="max-w-7xl mx-auto py-3 px-3 sm:px-6 lg:px-8">
      <div class="pr-16 sm:text-center sm:px-16">
        <p class="font-medium text-white">
          <span class="inline">{{ verse.text }}</span>
          <span class="block sm:ml-2 sm:inline-block">
            <span class="font-bold">— {{ verse.reference }}</span>
          </span>
        </p>
      </div>
      <div class="absolute inset-y-0 right-0 pt-1 pr-1 flex items-start sm:pt-1 sm:pr-2 sm:items-start">
        <button
          type="button"
          class="flex p-2 rounded-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-white"
          @click="dismiss"
        >
          <span class="sr-only">Dismiss</span>
          <svg
            class="h-6 w-6 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
  name: 'BibleVerseBanner',

  setup() {
    const verse = ref(null)
    const dismissed = ref(false)
    const show = ref(false)

    const fetchDailyVerse = async () => {
      try {
        const response = await fetch('/api/bible/daily')
        const data = await response.json()
        if (data.success) {
          verse.value = data.verse
          // Trigger fade in animation
          setTimeout(() => {
            show.value = true
          }, 100)
        }
      } catch (error) {
        console.error('Error fetching daily verse:', error)
      }
    }

    const dismiss = () => {
      show.value = false
      setTimeout(() => {
        dismissed.value = true
      }, 500) // Match the duration in the transition class
    }

    onMounted(() => {
      fetchDailyVerse()
    })

    return {
      verse,
      dismissed,
      show,
      dismiss
    }
  }
}
</script>