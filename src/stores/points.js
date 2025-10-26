import { defineStore } from 'pinia'

export const usePointsStore = defineStore('points', {
  state: () => ({
    weeklyPoints: [],
    categories: [],
    loading: false,
    error: null
  }),

  getters: {
    totalPoints: (state) => {
      return state.weeklyPoints.reduce((sum, point) => sum + point.points, 0)
    },
    allowanceDeduction: (state) => {
      const total = state.weeklyPoints.reduce((sum, point) => sum + point.points, 0)
      return total > 3 ? 5 : 0
    },
    pointsByDate: (state) => {
      const grouped = {}
      state.weeklyPoints.forEach(point => {
        const date = point.created_at.split('T')[0]
        if (!grouped[date]) {
          grouped[date] = []
        }
        grouped[date].push(point)
      })
      return grouped
    }
  },

  actions: {
    async fetchWeeklyPoints({ userId }) {
      this.loading = true
      try {
        const response = await fetch(`/api/points/weekly/${userId}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        const data = await response.json()
        if (data.success) {
          this.weeklyPoints = data.points
        } else {
          throw new Error(data.message)
        }
      } catch (error) {
        this.error = error.message
        console.error('Error fetching points:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchCategories() {
      try {
        const response = await fetch('/api/points/categories', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        const data = await response.json()
        if (data.success) {
          this.categories = data.categories
        } else {
          throw new Error(data.message)
        }
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    }
  }
})


