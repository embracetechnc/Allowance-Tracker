import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html', 'lcov'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.spec.js',
        '**/*.cy.js',
        'cypress/',
        'coverage/',
        '.nyc_output/',
        'vite.config.js',
        'vitest.config.js',
        'vitest.coverage.config.js',
        'cypress.config.js'
      ],
      include: [
        'src/**/*.{js,vue}',
      ],
      all: true,
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80,
      reportOnFailure: true,
      reportsDirectory: './coverage/unit'
    }
  },
  resolve: {
    alias: {
      '@': '/src'
    }
  }
});