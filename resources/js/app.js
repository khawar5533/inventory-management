import './bootstrap'
import { createApp } from 'vue'
import App from './App.vue'

const appDiv = document.getElementById('app')
if (appDiv) {
  const app = createApp(App)
  // Register global $baseUrl from window.baseUrl
  app.config.globalProperties.$baseUrl = window.baseUrl
  app.mount('#app')
}

