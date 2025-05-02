<template>
  <div class="home-container">
    <header class="top-header">
      <div class="welcome-message">Bienvenue sur <span class="username">TalkLabs</span></div>
      <div class="search-container">
        <div class="search-bar" :class="{ 'search-bar-open': isSearchOpen }">
          <input type="text" placeholder="Rechercher..." v-model="searchQuery" />
          <button class="search-button" @click="toggleSearch">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.1333 24L13.7333 15.6C13.0667 16.1333 12.3 16.5556 11.4333 16.8667C10.5667 17.1778 9.64445 17.3333 8.66667 17.3333C6.24444 17.3333 4.19444 16.4944 2.51667 14.8167C0.838889 13.1389 0 11.0889 0 8.66667C0 6.24444 0.838889 4.19444 2.51667 2.51667C4.19444 0.838889 6.24444 0 8.66667 0C11.0889 0 13.1389 0.838889 14.8167 2.51667C16.4944 4.19444 17.3333 6.24444 17.3333 8.66667C17.3333 9.64445 17.1778 10.5667 16.8667 11.4333C16.5556 12.3 16.1333 13.0667 15.6 13.7333L24 22.1333L22.1333 24ZM8.66667 14.6667C10.3333 14.6667 11.75 14.0833 12.9167 12.9167C14.0833 11.75 14.6667 10.3333 14.6667 8.66667C14.6667 7 14.0833 5.58333 12.9167 4.41667C11.75 3.25 10.3333 2.66667 8.66667 2.66667C7 2.66667 5.58333 3.25 4.41667 4.41667C3.25 5.58333 2.66667 7 2.66667 8.66667C2.66667 10.3333 3.25 11.75 4.41667 12.9167C5.58333 14.0833 7 14.6667 8.66667 14.6667Z" fill="#121212"/>
            </svg>
          </button>
        </div>
        <button v-if="!isSearchOpen" class="search-icon-button" @click="toggleSearch">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.1333 24L13.7333 15.6C13.0667 16.1333 12.3 16.5556 11.4333 16.8667C10.5667 17.1778 9.64445 17.3333 8.66667 17.3333C6.24444 17.3333 4.19444 16.4944 2.51667 14.8167C0.838889 13.1389 0 11.0889 0 8.66667C0 6.24444 0.838889 4.19444 2.51667 2.51667C4.19444 0.838889 6.24444 0 8.66667 0C11.0889 0 13.1389 0.838889 14.8167 2.51667C16.4944 4.19444 17.3333 6.24444 17.3333 8.66667C17.3333 9.64445 17.1778 10.5667 16.8667 11.4333C16.5556 12.3 16.1333 13.0667 15.6 13.7333L24 22.1333L22.1333 24ZM8.66667 14.6667C10.3333 14.6667 11.75 14.0833 12.9167 12.9167C14.0833 11.75 14.6667 10.3333 14.6667 8.66667C14.6667 7 14.0833 5.58333 12.9167 4.41667C11.75 3.25 10.3333 2.66667 8.66667 2.66667C7 2.66667 5.58333 3.25 4.41667 4.41667C3.25 5.58333 2.66667 7 2.66667 8.66667C2.66667 10.3333 3.25 11.75 4.41667 12.9167C5.58333 14.0833 7 14.6667 8.66667 14.6667Z" fill="#121212"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- Content Sections -->
    <section class="content-section">
      <h2 class="section-title">Recommandations</h2>
      <div class="card-grid">
        <Card 
          v-for="i in 4" 
          :key="'rec-'+i"
          :image-url="sampleImage"
          title="Une dispute qui tourne au vinaigre"
          author="Sid-Ahmed"
          :tags="sampleTags"
        />
      </div>
    </section>

    <section class="content-section">
      <h2 class="section-title">Populaire</h2>
      <div class="card-grid">
        <Card 
          v-for="i in 4" 
          :key="'pop-'+i"
          :image-url="sampleImage"
          title="Une dispute qui tourne au vinaigre"
          author="Sid-Ahmed"
          :tags="sampleTags"
        />
      </div>
    </section>
  </div>
</template>

<script>
import Card from '../ui/Card.vue'

export default {
  name: 'Home',
  components: {
    Card
  },
  data() {
    return {
      isSearchOpen: false,
      searchQuery: '',
      sampleImage: 'https://picsum.photos/350/236',
      sampleTags: [
        { text: 'NOV', color: 'yellow' },
        { text: 'SFF', color: 'green' },
        { text: 'APO', color: 'pink' },
        { text: 'CYB', color: 'blue' }
      ]
    }
  },
  methods: {
    toggleSearch() {
      this.isSearchOpen = !this.isSearchOpen;
      if (this.isSearchOpen) {
        // Focus sur l'input de recherche après l'ouverture
        this.$nextTick(() => {
          const searchInput = document.querySelector('.search-bar input');
          if (searchInput) searchInput.focus();
        });
      }
    }
  }
}
</script>

<style scoped>
.home-container {
  padding-top: 30px;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.top-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 0 15px;
}

.welcome-message {
  font-size: 18px;
  color: white;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.username {
  font-weight: 600;
  color: #23CE6B;
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon-button {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #23CE6B;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(35, 206, 107, 0.3);
}

.search-icon-button:hover {
  background-color: #1eae5a;
  transform: scale(1.05);
}

.search-bar {
  display: flex;
  align-items: center;
  width: 0;
  overflow: hidden;
  transition: all 0.3s ease;
  opacity: 0;
  position: absolute;
  right: 0;
  border-radius: 24px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-bar-open {
  width: 300px;
  opacity: 1;
  background-color: white;
}

.search-bar input {
  flex: 1;
  padding: 12px 16px;
  border: none;
  border-radius: 24px 0 0 24px;
  font-size: 14px;
  outline: none;
  background-color: white;
}

.search-button {
  background-color: #23CE6B;
  border: none;
  border-radius: 0 24px 24px 0;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.search-button:hover {
  background-color: #1eae5a;
}

.search-button svg {
  width: 16px;
  height: 16px;
}

.search-button svg path {
  fill: #121212;
}

.section-title {
  font-size: 24px;
  font-weight: 600;
  color: #23CE6B;
  margin-bottom: 20px;
  margin-top: 40px;
  padding: 0 15px;
}

.section-title:first-of-type {
  margin-top: 10px;
}

.card-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 40px;
  padding: 0 15px;
}

/* Responsive adjustments */
@media (max-width: 1400px) {
  .card-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1050px) {
  .card-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 700px) {
  .card-grid {
    grid-template-columns: 1fr;
  }
  
  .top-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .search-bar-open {
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 10px 15px;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    border-radius: 0;
  }
  
  .search-bar-open input {
    border-radius: 24px 0 0 24px;
  }
  
  .search-bar-open .search-button {
    border-radius: 0 24px 24px 0;
  }
  
  .top-header {
    position: relative;
  }
}
</style> 