// Fichier bootstrap.js simplifié sans dépendance à stimulus-bundle
console.log('Bootstrap file loaded');

import { createApp } from 'vue';
import App from './components/App.vue';

// Création de l'application Vue
const app = createApp(App);

// Montage de l'application sur l'élément #app
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, mounting Vue app');
    const appElement = document.getElementById('app');
    if (appElement) {
        console.log('App element found, mounting Vue app');
        app.mount(appElement);
    } else {
        console.error('App element not found');
    }
});
