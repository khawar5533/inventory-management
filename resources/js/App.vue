<template>
  <Sidebar @change-component="setComponent" />
  <div class="main">
    <Header />
    <main class="content">
      <component :is="currentComponent" :key="componentKey" />
    </main>
    <Footer />
  </div>
</template>

<script>
import Sidebar from './components/Sidebar.vue'
import Header from './components/Header.vue'
import Content from './components/Content.vue'
import Register from './components/Register.vue'
import Role from './components/Role.vue'
import Permission from './components/Permission.vue'
import Login from './components/Login.vue'
import UserRole from './components/UserRole.vue'
import UserPermission from './components/User-Permission.vue'
import Footer from './components/Footer.vue'

export default {
  name: 'App',
  components: {
    Sidebar,
    Header,
    Content,
    Register,
    Role,
    Permission,
    UserRole,
    UserPermission,
    Login,
    Footer
  },
  data() {
    return {
      currentComponent: window.defaultComponent || 'Content',
      componentKey: 0
    }
  },
  methods: {
    setComponent(componentName) {
      const base = window.baseUrl || ''

      //Convert component name to lowercase path
      const routePath = componentName.toLowerCase()

      //Build full URL (empty path for Content)
      const newUrl = `${base}/${routePath === 'content' ? 'dashboard' : routePath}`

      //Full page reload
      window.location.href = newUrl
    }
  }
}
</script>
