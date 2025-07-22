import { createApp } from 'vue'
import Login from './components/Login.vue'

const loginDiv = document.getElementById('login')
if (loginDiv) {
  createApp(Login).mount('#login')
}