import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    manifest: true,
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'assets/app.js'),
      },
    },
    outDir: 'public/build',
    emptyOutDir: true,
  },
  server: {
    origin: 'http://localhost:5173',
  },
});
