<template>
  <div>
    <Register v-if="currentPage === 'register'" />
    
    <div v-else class="app-container" :class="{ 'sidebar-collapsed-content': isSidebarCollapsed }">
      <Sidebar v-if="currentPage === 'home'" @sidebar-toggle="handleSidebarToggle" />
      <main class="main-content">
        <component :is="currentComponent"></component>
      </main>
    </div>
  </div>
</template>

<script>
import Sidebar from './Sidebar.vue';
import Home from '../pages/Home.vue';
import Register from '../auth/Register.vue';
import Login from '../auth/Login.vue';

export default {
  name: 'App',
  components: {
    Sidebar,
    Home,
    Register,
    Login
  },
  data() {
    return {
      message: 'TalkLabs',
      isSidebarCollapsed: false,
      currentPage: 'home'
    }
  },
  computed: {
    currentComponent() {
      switch(this.currentPage) {
        case 'home':
          return Home;
        case 'register':
          return Register;
        case 'login':
          return Login;
        default:
          return Home;
      }
    }
  },
  methods: {
    handleSidebarToggle(isCollapsed) {
      console.log('App received sidebar-toggle event:', isCollapsed);
      this.isSidebarCollapsed = isCollapsed;
    },
    navigate(page) {
      this.currentPage = page;
    }
  },
  mounted() {
    if (window.currentPage) {
      this.currentPage = window.currentPage;
    } else {
      const urlParams = new URLSearchParams(window.location.search);
      const page = urlParams.get('page');
      if (page) {
        this.currentPage = page;
      }
    }
  }
}
</script>

<style scoped>
.app-container {
  display: flex;
  min-height: 100vh;
  background-color: #11101A;
  color: white;
  font-family: Arial, sans-serif;
  overflow: hidden;
  transition: all 0.3s ease;
}

.main-content {
  flex: 1;
  padding: 25px 30px;
  overflow-y: auto;
  height: 100vh;
  position: relative;
  transition: margin-left 0.3s ease;
  display: flex;
  justify-content: center;
}

.main-content > * {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.sidebar-collapsed-content {
  margin-left: 80px;
}

.add-button {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #23CE6B;
  color: white;
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: none;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.auth-buttons {
  margin: 0 15px;
}

.auth-button {
  margin: 0 8px;
  padding: 8px 16px;
}

@media (max-width: 768px) {
  .auth-buttons {
    margin: 0 10px;
  }
}

.header {
  padding: 0 20px;
}

.nav-buttons {
  margin-right: 15px;
}

.nav-button {
  margin-left: 10px;
  padding: 8px 16px;
}

@media (max-width: 768px) {
  .header {
    padding: 0 15px;
  }
  
  .nav-buttons {
    margin-right: 10px;
  }
}
</style> 