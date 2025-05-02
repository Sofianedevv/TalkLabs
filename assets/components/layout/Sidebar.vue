<template>
  <div class="sidebar" :class="{ 'sidebar-collapsed': isCollapsed }">
    <!-- Bouton pour ouvrir/fermer la sidebar -->
    <div class="toggle-button" @click="toggleSidebar">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 6H20M4 12H20M4 18H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-if="isCollapsed"/>
        <path d="M6 18L18 6M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-if="!isCollapsed"/>
      </svg>
    </div>
    
    <div class="logo">
      <img :src="logoUrl" alt="TalkLabs Logo" />
    </div>
    <nav class="menu">
      <div class="menu-item active" @click="navigateTo('/')">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 24H7.5V15H16.5V24H21V10.5L12 3.75L3 10.5V24ZM0 27V9L12 0L24 9V27H13.5V18H10.5V27H0Z" fill="white"/>
        </svg>
        <span>Accueil</span>
      </div>
      <div class="menu-item" @click="navigateTo('/categories')">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.5 11L12 2L17.5 11H6.5ZM17.5 22C16.25 22 15.1875 21.5625 14.3125 20.6875C13.4375 19.8125 13 18.75 13 17.5C13 16.25 13.4375 15.1875 14.3125 14.3125C15.1875 13.4375 16.25 13 17.5 13C18.75 13 19.8125 13.4375 20.6875 14.3125C21.5625 15.1875 22 16.25 22 17.5C22 18.75 21.5625 19.8125 20.6875 20.6875C19.8125 21.5625 18.75 22 17.5 22ZM3 21.5V13.5H11V21.5H3ZM17.5 20C18.2 20 18.7917 19.7583 19.275 19.275C19.7583 18.7917 20 18.2 20 17.5C20 16.8 19.7583 16.2083 19.275 15.725C18.7917 15.2417 18.2 15 17.5 15C16.8 15 16.2083 15.2417 15.725 15.725C15.2417 16.2083 15 16.8 15 17.5C15 18.2 15.2417 18.7917 15.725 19.275C16.2083 19.7583 16.8 20 17.5 20ZM5 19.5H9V15.5H5V19.5ZM10.05 9H13.95L12 5.85L10.05 9Z" fill="#E3E3E3"/>
</svg>

        <span>Catégories</span>
      </div>
      <div class="menu-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3 21V3H21V21H3ZM18 17H6V18.5H18V17ZM6 15.5H18V14H6V15.5ZM6 12H18V6H6V12Z" fill="#E3E3E3"/>
</svg>

        <span>Mes créations</span>
      </div>
      <div class="menu-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3 20V5C3 4.45 3.19583 3.97917 3.5875 3.5875C3.97917 3.19583 4.45 3 5 3H17C17.55 3 18.0208 3.19583 18.4125 3.5875C18.8042 3.97917 19 4.45 19 5V10.075C18.8333 10.0417 18.6667 10.0208 18.5 10.0125C18.3333 10.0042 18.1667 10 18 10C17.8333 10 17.6667 10.0042 17.5 10.0125C17.3333 10.0208 17.1667 10.0417 17 10.075V5H5V15H12.075C12.0417 15.1667 12.0208 15.3333 12.0125 15.5C12.0042 15.6667 12 15.8333 12 16C12 16.1667 12.0042 16.3333 12.0125 16.5C12.0208 16.6667 12.0417 16.8333 12.075 17H6L3 20ZM7 9H15V7H7V9ZM7 13H12V11H7V13ZM17 20V17H14V15H17V12H19V15H22V17H19V20H17Z" fill="#E3E3E3"/>
</svg>

        <span>Créer une discussion</span>
      </div>
      <div class="menu-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_128_164)">
<path d="M0 18V16.425C0 15.7083 0.366667 15.125 1.1 14.675C1.83333 14.225 2.8 14 4 14C4.21667 14 4.425 14.0042 4.625 14.0125C4.825 14.0208 5.01667 14.0417 5.2 14.075C4.96667 14.425 4.79167 14.7917 4.675 15.175C4.55833 15.5583 4.5 15.9583 4.5 16.375V18H0ZM6 18V16.375C6 15.8417 6.14583 15.3542 6.4375 14.9125C6.72917 14.4708 7.14167 14.0833 7.675 13.75C8.20833 13.4167 8.84583 13.1667 9.5875 13C10.3292 12.8333 11.1333 12.75 12 12.75C12.8833 12.75 13.6958 12.8333 14.4375 13C15.1792 13.1667 15.8167 13.4167 16.35 13.75C16.8833 14.0833 17.2917 14.4708 17.575 14.9125C17.8583 15.3542 18 15.8417 18 16.375V18H6ZM19.5 18V16.375C19.5 15.9417 19.4458 15.5333 19.3375 15.15C19.2292 14.7667 19.0667 14.4083 18.85 14.075C19.0333 14.0417 19.2208 14.0208 19.4125 14.0125C19.6042 14.0042 19.8 14 20 14C21.2 14 22.1667 14.2208 22.9 14.6625C23.6333 15.1042 24 15.6917 24 16.425V18H19.5ZM8.125 16H15.9C15.7333 15.6667 15.2708 15.375 14.5125 15.125C13.7542 14.875 12.9167 14.75 12 14.75C11.0833 14.75 10.2458 14.875 9.4875 15.125C8.72917 15.375 8.275 15.6667 8.125 16ZM4 13C3.45 13 2.97917 12.8042 2.5875 12.4125C2.19583 12.0208 2 11.55 2 11C2 10.4333 2.19583 9.95833 2.5875 9.575C2.97917 9.19167 3.45 9 4 9C4.56667 9 5.04167 9.19167 5.425 9.575C5.80833 9.95833 6 10.4333 6 11C6 11.55 5.80833 12.0208 5.425 12.4125C5.04167 12.8042 4.56667 13 4 13ZM20 13C19.45 13 18.9792 12.8042 18.5875 12.4125C18.1958 12.0208 18 11.55 18 11C18 10.4333 18.1958 9.95833 18.5875 9.575C18.9792 9.19167 19.45 9 20 9C20.5667 9 21.0417 9.19167 21.425 9.575C21.8083 9.95833 22 10.4333 22 11C22 11.55 21.8083 12.0208 21.425 12.4125C21.0417 12.8042 20.5667 13 20 13ZM12 12C11.1667 12 10.4583 11.7083 9.875 11.125C9.29167 10.5417 9 9.83333 9 9C9 8.15 9.29167 7.4375 9.875 6.8625C10.4583 6.2875 11.1667 6 12 6C12.85 6 13.5625 6.2875 14.1375 6.8625C14.7125 7.4375 15 8.15 15 9C15 9.83333 14.7125 10.5417 14.1375 11.125C13.5625 11.7083 12.85 12 12 12ZM12 10C12.2833 10 12.5208 9.90417 12.7125 9.7125C12.9042 9.52083 13 9.28333 13 9C13 8.71667 12.9042 8.47917 12.7125 8.2875C12.5208 8.09583 12.2833 8 12 8C11.7167 8 11.4792 8.09583 11.2875 8.2875C11.0958 8.47917 11 8.71667 11 9C11 9.28333 11.0958 9.52083 11.2875 9.7125C11.4792 9.90417 11.7167 10 12 10Z" fill="#E3E3E3"/>
</g>
<defs>
<clipPath id="clip0_128_164">
<rect width="24" height="24" fill="white"/>
</clipPath>
</defs>
</svg>

        <span>Forum</span>
      </div>
      <div class="menu-item">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M14 13C13.1667 13 12.4583 12.7083 11.875 12.125C11.2917 11.5417 11 10.8333 11 10C11 9.16667 11.2917 8.45833 11.875 7.875C12.4583 7.29167 13.1667 7 14 7C14.8333 7 15.5417 7.29167 16.125 7.875C16.7083 8.45833 17 9.16667 17 10C17 10.8333 16.7083 11.5417 16.125 12.125C15.5417 12.7083 14.8333 13 14 13ZM7 16C6.45 16 5.97917 15.8042 5.5875 15.4125C5.19583 15.0208 5 14.55 5 14V6C5 5.45 5.19583 4.97917 5.5875 4.5875C5.97917 4.19583 6.45 4 7 4H21C21.55 4 22.0208 4.19583 22.4125 4.5875C22.8042 4.97917 23 5.45 23 6V14C23 14.55 22.8042 15.0208 22.4125 15.4125C22.0208 15.8042 21.55 16 21 16H7ZM9 14H19C19 13.45 19.1958 12.9792 19.5875 12.5875C19.9792 12.1958 20.45 12 21 12V8C20.45 8 19.9792 7.80417 19.5875 7.4125C19.1958 7.02083 19 6.55 19 6H9C9 6.55 8.80417 7.02083 8.4125 7.4125C8.02083 7.80417 7.55 8 7 8V12C7.55 12 8.02083 12.1958 8.4125 12.5875C8.80417 12.9792 9 13.45 9 14ZM20 20H3C2.45 20 1.97917 19.8042 1.5875 19.4125C1.19583 19.0208 1 18.55 1 18V7H3V18H20V20Z" fill="#E3E3E3"/>
</svg>

        <span>Tarification</span>
      </div>
    </nav>
    <div class="auth-buttons">
      <button class="btn-signup" @click="navigateTo('/register')">S'inscrire</button>
      <button class="btn-login" @click="navigateTo('/login')">Se connecter</button>
    </div>
  </div>
</template>

<script>
import logoUrl from '../../images/logo.svg';

export default {
  name: 'Sidebar',
  data() {
    return {
      logoUrl: logoUrl,
      isCollapsed: false
    }
  },
  methods: {
    toggleSidebar() {
      console.log('toggleSidebar called');
      this.isCollapsed = !this.isCollapsed;
      console.log('isCollapsed:', this.isCollapsed);
      // Émettre un événement pour informer le composant parent
      this.$emit('sidebar-toggle', this.isCollapsed);
    },
    navigateTo(path) {
      console.log('navigateTo called with path:', path);
      window.location.href = path;
    }
  },
  mounted() {
    console.log('Sidebar component mounted');
  }
}
</script>

<style scoped>
.sidebar {
  width: 305px;
  height: 100vh;
  background-color: #1A192C;
  padding: 20px 15px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: sticky;
  top: 0;
  overflow-y: auto;
  transition: width 0.3s ease;
}

.sidebar-collapsed {
  width: 80px;
}

.toggle-button {
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
  z-index: 10;
  background-color: rgba(26, 25, 44, 0.8); 
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-collapsed .logo img {
  width: 50px;
  height: 50px;
}

.sidebar-collapsed .menu-item span,
.sidebar-collapsed .auth-buttons {
  display: none;
}

.sidebar-collapsed .menu-item {
  display: flex;
  justify-content: center;
  padding: 10px 0;
}

.sidebar-collapsed .menu-item svg {
  margin-left: 0;
}

.logo {
  margin-top: 40px; 
  margin-bottom: 30px;
  display: flex;
  justify-content: center;
}

.logo img {
  width: 70px;
  height: 70px;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
  overflow-y: auto;
  margin-bottom: 20px;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  gap: 12px;
}

.menu-item span {
  font-size: 14px;
  font-weight: 600;
}

.menu-item.active {
  background-color: #2a2a36;
}

.menu-item.active span {
  color: #23CE6B;
}

.menu-item:hover {
  background-color: #2a2a36;
}

.menu-item svg {
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 8px;
}

.auth-buttons {
  margin: 15px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 0 15px;
  box-sizing: border-box;
}

.btn-signup, .btn-login {
  padding: 12px 15px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  width: 90%;
  max-width: 250px;
  text-align: center;
}

.btn-signup {
  background-color: #23CE6B;
  color: white;
}

.btn-login {
  background-color: transparent;
  border: 1px solid #23CE6B;
  color: #23CE6B;
}


.sidebar-collapsed .auth-buttons {
  display: none;
}

.sidebar-buttons {
  margin: 15px 0;
}

.sidebar-button {
  margin-bottom: 10px;
  padding: 10px 15px;
  width: calc(100% - 30px);
  margin-left: 15px;
  margin-right: 15px;
}
</style> 