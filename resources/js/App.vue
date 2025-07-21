<template>
  <Sidebar />
  <div class="main">
    <Header />
    <main class="content">
     <!-- Dynamic Content Loaded Here -->
      <component :is="currentComponent" />
    </main>
    <Footer />
    </div>
</template>

<script>
import Sidebar from './components/Sidebar.vue' // or '@/components/Header.vue' if using alias
import Header from './components/Header.vue' // or '@/components/Header.vue' if using alias
import Content from './components/Content.vue' // or '@/components/Content.vue' if using alias
import Footer from './components/Footer.vue' // or '@/components/Footer.vue' if using alias

export default {
  name: 'App',
  components: {
    Sidebar,
    Header,
     Content,
     Footer
  },
  data() {
    return {
      currentComponent: 'Content' // 👈 Load Content.vue by default
    }
  }
}
</script>
