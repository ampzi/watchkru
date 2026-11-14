import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => ({
  plugins: [react(), tailwindcss()],
  // The static build lives at /<repo>/ on GitHub Pages and is loaded through
  // relative URLs, so the same output works under any repository name.
  base: mode === 'static' ? './' : '/',
  server: {
    port: 1258,
    strictPort: true,
  },
  preview: {
    port: 1258,
    strictPort: true,
  },
}))
