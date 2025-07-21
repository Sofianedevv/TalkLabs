#!/bin/bash

# Script de déploiement local pour TalkLabs
echo "🚀 Démarrage du déploiement local TalkLabs..."

# Configuration
COMPOSE_FILE="compose.dev.yaml"
TEST_URL="http://localhost"

# Fonction pour exécuter docker-compose avec le fichier d'environnement
function dc() {
    docker-compose --env-file .env.dev -f $COMPOSE_FILE $@
}

# Arrêter et nettoyer les conteneurs existants
echo "🛑 Arrêt et nettoyage des conteneurs existants..."
dc down
docker container prune -f
docker image prune -a -f
docker network prune -f
docker volume prune -f

# Supprimer spécifiquement les volumes utilisés par Vue.js pour forcer une reconstruction
echo "🧹 Suppression des volumes Vue.js..."
docker volume rm $(docker volume ls -q | grep vue_build) 2>/dev/null

# Construire et démarrer les conteneurs sans utiliser le cache et forcer la recréation
echo "🔨 Construction et démarrage des conteneurs sans cache et en forçant la recréation..."
dc build --no-cache --pull --build-arg CACHE_BUST=$(date +%s)
dc up -d --force-recreate --build

echo "📦 Construction des images Docker (build + pull + no-cache)..."
docker-compose --env-file .env.prod.vps -f $COMPOSE_FILE build --no-cache --pull --build-arg CACHE_BUST=$(date +%s)

# Suppression des volumes Vue.js si besoin
echo "🧹 Suppression des volumes Vue.js..."
docker volume rm $(docker volume ls -q | grep vue_build)

echo "▶️ Démarrage des nouveaux conteneurs (force-recreate + build)..."
docker-compose --env-file .env.prod.vps -f $COMPOSE_FILE up -d --force-recreate --build

# Vérification des extensions PHP
echo "🔍 Vérification des extensions PHP..."
dc exec -T php php -m | grep -E "(pdo|pgsql)" || echo "⚠️ Extensions PostgreSQL manquantes"

# Vérification de l'état du conteneur PHP
echo "🔍 Vérification de l'état du conteneur PHP..."
dc exec -T php ls -la bin/ || echo "⚠️ Conteneur PHP pas encore prêt"
dc exec -T php ls -la .env.dev* || echo "⚠️ Fichiers .env non trouvés"

# Correction des permissions
echo "🔧 Correction des permissions..."
dc exec -T php chown -R www-data:www-data /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions var/ non modifiées"
dc exec -T php chmod -R 755 /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions var/ non modifiées"
dc exec php chmod -R 755 /var/www/html

# Permissions pour les clés JWT
dc exec -T php chown -R www-data:www-data /var/www/html/config/jwt/ 2>/dev/null || echo "ℹ️ Dossier JWT non trouvé"
dc exec -T php chmod 600 /var/www/html/config/jwt/private.pem 2>/dev/null || echo "ℹ️ Clé privée JWT à générer"
dc exec -T php chmod 644 /var/www/html/config/jwt/public.pem 2>/dev/null || echo "ℹ️ Clé publique JWT à générer"

# Installation des dépendances Composer
echo "📦 Installation des dépendances Composer..."
dc exec -T php composer install --optimize-autoloader || echo "⚠️ Installation Composer échouée"

# Génération des clés JWT
echo "🔐 Génération des clés JWT si nécessaire..."
dc exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=dev || echo "⚠️ Génération clés JWT échouée"

# Attendre que PostgreSQL soit prêt
echo "⏳ Attente de PostgreSQL..."
sleep 5

# Création de la base de données et exécution des migrations
echo "🗄️ Création de la base de données et exécution des migrations..."
dc exec -T php php bin/console doctrine:database:create --if-not-exists --env=dev || echo "⚠️ Création BDD échouée"
dc exec -T php php bin/console doctrine:migrations:diff --env=dev || echo "⚠️ Génération migrations échouée"
dc exec -T php php bin/console doctrine:migrations:migrate --no-interaction --env=dev || echo "⚠️ Migrations échouées"

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
dc exec -T php php bin/console cache:clear --env=dev || echo "⚠️ Nettoyage cache échoué"

# Chargement des données de test
echo "📊 Chargement des données de test..."
dc exec -T php php bin/console hautelook:fixtures:load --no-interaction --env=dev || echo "⚠️ Chargement des fixtures échoué ou pas disponible"

echo "✅ Déploiement local terminé avec succès!"
echo "🌐 L'application est accessible sur $TEST_URL"

# Test de l'application
echo "🔍 Test de l'application..."
sleep 5
if curl -s -f $TEST_URL/ > /dev/null 2>&1; then
    echo "✅ Le site répond correctement"
else
    echo "⚠️ Le site ne répond pas encore, attendez quelques secondes..."
fi

if curl -s -f $TEST_URL/api/get-conversations-public > /dev/null 2>&1; then
    echo "✅ L'API répond correctement"
else
    echo "⚠️ L'API ne répond pas encore, vérifiez que tous les services sont démarrés"
fi

# Affichage des informations utiles
echo ""
echo "📋 État des conteneurs:"
dc ps
echo ""
echo "🔗 URLs utiles:"
echo "   - Site web: $TEST_URL"
echo "   - API: $TEST_URL/api"
echo "   - Documentation API: $TEST_URL/api/doc (si disponible)"
echo ""
echo "📝 Commandes utiles:"
echo "   - Voir les logs: dc logs -f"
echo "   - Logs PHP: dc logs -f php"
echo "   - Logs PostgreSQL: dc logs -f postgres"
echo "   - Arrêter: dc down"
echo "   - Redémarrer: dc restart"
echo "   - Accéder au conteneur PHP: dc exec php bash"