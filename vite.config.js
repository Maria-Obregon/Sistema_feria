// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: { transformAssetUrls: { base: null, includeAbsolute: false } },
    }),
  ],
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
 server: {
  hmr: { host: 'localhost' },
  proxy: {
    '^/api': {
      target: 'http://localhost',
      changeOrigin: true,
      rewrite: (p) => p.replace(/^\/api/, '/Laravel/feria/Sistema_feria/public/api'),
    },
    '^/sanctum': {
      target: 'http://localhost',
      changeOrigin: true,
      rewrite: (p) => p.replace(/^\/sanctum/, '/Laravel/feria/Sistema_feria/public/sanctum'),
    },
  },
},

})
