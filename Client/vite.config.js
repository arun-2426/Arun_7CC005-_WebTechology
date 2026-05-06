import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Pin the dev server to a non-default port (5283) so it doesn't clash
// with the dozens of other apps that grab Vite's default 5173. The API
// CORS allow-list and Sanctum's stateful domains are wired to this port
// in the api/.env file.
export default defineConfig({
  plugins: [react()],
  server: {
    host: '127.0.0.1',
    port: 5283,
    strictPort: true,
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.js'],
  },
})
