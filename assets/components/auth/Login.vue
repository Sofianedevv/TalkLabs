<template>
  <div class="login-container">
    <h1>Connexion</h1>
    <form @submit.prevent="login">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit" class="btn-login">Se connecter</button>
    </form>
    <p class="register-link">
      Pas encore de compte ? <a href="#" @click.prevent="goToRegister">S'inscrire</a>
    </p>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      email: '',
      password: ''
    }
  },
  methods: {
    login() {
      // Logique de connexion à implémenter
      console.log('Tentative de connexion avec:', this.email);
    },
    goToRegister() {
      // Navigation vers la page d'inscription
      if (this.$parent && this.$parent.navigate) {
        this.$parent.navigate('register');
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  background-color: #1a1a24;
  border-radius: 10px;
}

h1 {
  color: #23CE6B;
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #ccc;
}

input {
  width: 100%;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #333;
  background-color: #2a2a36;
  color: white;
}

.btn-login {
  width: 100%;
  padding: 10px;
  background-color: #23CE6B;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  margin-top: 10px;
}

.btn-login:hover {
  background-color: #1eae5a;
}

.register-link {
  text-align: center;
  margin-top: 20px;
  color: #ccc;
}

.register-link a {
  color: #23CE6B;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}
</style> 