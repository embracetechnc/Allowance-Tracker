<template>
  <div class="bg-blue-600 text-white">
    <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between flex-wrap">
        <div class="w-0 flex-1 flex items-center min-w-0">
          <span class="flex p-2">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
              />
            </svg>
          </span>
          <div class="ml-3 font-medium truncate">
            <span class="md:hidden">{{ verse.reference }}</span>
            <span class="hidden md:inline">
              "{{ verse.text }}" - {{ verse.reference }}
            </span>
          </div>
        </div>
        <div class="flex-shrink-0 w-full flex justify-center mt-2 sm:mt-0 sm:w-auto">
          <button
            @click="refreshVerse"
            class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50"
          >
            Refresh
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'

export default {
  name: 'BibleVerse',
  setup() {
    const verse = ref({
      text: '',
      reference: ''
    })

    const fetchVerse = async () => {
      try {
        const response = await axios.get('/api/bible/verse-of-the-day')
        verse.value = {
          text: response.data.verse,
          reference: response.data.reference
        }
      } catch (error) {
        console.error('Error fetching Bible verse:', error)
        verse.value = {
          text: 'Train up a child in the way he should go; even when he is old he will not depart from it.',
          reference: 'Proverbs 22:6'
        }
      }
    }

    const refreshVerse = () => {
      fetchVerse()
    }

    onMounted(() => {
      fetchVerse()
    })

    return {
      verse,
      refreshVerse
    }
  }
}
</script>
