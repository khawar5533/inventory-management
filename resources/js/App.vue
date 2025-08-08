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
import ShowPermission from './components/ShowPermission.vue'
import UserPermission from './components/User-Permission.vue'
// include location
import AddLocation from './components/Add-Location.vue'
import AddFloor from './components/Add-Floor.vue'
import AddRoom from './components/Add-Room.vue'
import Rack from './components/Rack.vue'
import Box from './components/Box.vue'
import Category from './components/Category.vue'
import Product from './components/Product.vue'
import ProductLot from './components/ProductLot.vue'
import GetItems from './components/GetItems.vue'
import CheckOut from './components/CheckOut.vue'
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
    ShowPermission,
    UserPermission,
    AddLocation,
    AddFloor,
    AddRoom,
    Rack,
    Box,
    Category,
    Product,
    ProductLot,
    GetItems,
    CheckOut,
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
