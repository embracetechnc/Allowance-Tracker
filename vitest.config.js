import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    coverage: {
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.spec.js',
      ],
    },
    setupFiles: ['./tests/setup.js'],
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
});


