<template>
  <div>
    <!-- Afficher uniquement Register lorsque currentPage est 'register' -->
    <Register v-if="currentPage === 'register'" />
    
    <!-- Afficher la structure normale de l'application pour les autres pages -->
    <div v-else class="app-container" :class="{ 'sidebar-collapsed-content': isSidebarCollapsed }">
      <Sidebar v-if="currentPage === 'home'" @sidebar-toggle="handleSidebarToggle" />
      <main class="main-content">
        <component :is="currentComponent"></component>
      </main>
    </div>
  </div>
</template>

<script>
// Import des composants
import Sidebar from './Sidebar.vue';
import Home from './Home.vue';
import Register from './Register.vue';
import Login from './Login.vue';

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
      currentPage: 'home' // 'home', 'register', 'login', etc.
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
    // Vérifier si une page est définie dans la variable globale window.currentPage
    if (window.currentPage) {
      this.currentPage = window.currentPage;
    } else {
      // Vérifier si l'URL contient un paramètre de page
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
  overflow: hidden; /* Empêche le défilement horizontal */
  transition: all 0.3s ease;
}

.main-content {
  flex: 1;
  padding: 25px 30px;
  overflow-y: auto; /* Permet de faire défiler le contenu principal si nécessaire */
  height: 100vh; /* Utilise 100% de la hauteur de la fenêtre */
  position: relative;
  transition: margin-left 0.3s ease;
  display: flex;
  justify-content: center; /* Centre horizontalement le contenu */
}

.main-content > * {
  width: 100%;
  max-width: 1200px; /* Définit une largeur maximale pour le contenu */
  margin: 0 auto; /* Centre le contenu si sa largeur est inférieure à max-width */
}

.sidebar-collapsed-content {
  margin-left: 80px; /* Ajustez cette valeur pour correspondre à la largeur de votre sidebar réduite */
}

/* Add button in bottom right */
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

/* Ajout de marges aux boutons d'authentification */
.auth-buttons {
  margin: 0 15px; /* Ajoute une marge horizontale de 15px */
}

.auth-button {
  margin: 0 8px; /* Ajoute un espacement entre les boutons */
  padding: 8px 16px; /* Assure un bon padding interne */
}

/* Pour les écrans plus petits, augmenter les marges */
@media (max-width: 768px) {
  .auth-buttons {
    margin: 0 10px;
  }
}

.header {
  padding: 0 20px; /* Augmente le padding horizontal de l'en-tête */
}

.nav-buttons {
  margin-right: 15px; /* Ajoute une marge à droite du conteneur de boutons */
}

.nav-button {
  margin-left: 10px; /* Espace entre les boutons */
  padding: 8px 16px; /* Padding interne confortable */
}

/* Pour les écrans plus petits */
@media (max-width: 768px) {
  .header {
    padding: 0 15px;
  }
  
  .nav-buttons {
    margin-right: 10px;
  }
}
</style> 