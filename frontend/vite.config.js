import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/AMEZIANE_TOURS',
        changeOrigin: true,
      },
      '/generate-pdf.php': {
        target: 'http://localhost/AMEZIANE_TOURS',
        changeOrigin: true,
      }
    }
  }
})
