import './bootstrap'
import { createApp } from 'vue'
import App from './App.vue'

const appDiv = document.getElementById('app')
if (appDiv) {
  createApp(App).mount('#app')
}

