import { createApp } from 'vue'
import App from './App.vue'

const defaultComponent = document.getElementById('app')?.dataset?.default || 'Content'

createApp(App, {
  defaultComponent,
}).mount('#app')
