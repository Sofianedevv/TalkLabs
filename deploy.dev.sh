#!/bin/bash
# Script de déploiement local pour TalkLabs
echo "🚀 Démarrage du déploiement local TalkLabs..."

# Configuration
COMPOSE_FILE="compose.dev.yaml"
TEST_URL="http://localhost"

# Arrêter et nettoyer les conteneurs existants
echo "🛑 Arrêt des conteneurs existants..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE down

# Supprimer les conteneurs arrêtés
echo "🧹 Nettoyage des conteneurs arrêtés..."
docker container prune -f

# Supprimer les images inutilisées
echo "🧹 Nettoyage des images inutilisées..."
docker image prune -a -f

# Supprimer les réseaux inutilisés
echo "🧹 Nettoyage des réseaux inutilisés..."
docker network prune -f

# Supprimer les volumes inutilisés
echo "🧹 Nettoyage des volumes inutilisés..."
docker volume prune -f

# Supprimer spécifiquement les volumes utilisés par Vue.js pour forcer une reconstruction
echo "🧹 Suppression des volumes Vue.js..."
docker volume rm $(docker volume ls -q | grep vue_build)

# Construire et démarrer les conteneurs sans utiliser le cache
echo "🔨 Construction et démarrage des conteneurs sans cache..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE build --no-cache --build-arg CACHE_BUST=$(date +%s)
docker-compose --env-file .env.dev -f $COMPOSE_FILE up -d

# Reconstruire spécifiquement le conteneur Vue.js pour s'assurer qu'il est à jour
echo "🔨 Reconstruction du conteneur Vue.js sans cache..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE build --no-cache --build-arg CACHE_BUST=$(date +%s) vuejs
docker-compose --env-file .env.dev -f $COMPOSE_FILE up -d vuejs
# Diagnostic des extensions PHP
echo "🔍 Vérification des extensions PHP..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php -m | grep -E "(pdo|pgsql)" || echo "⚠️ Extensions PostgreSQL manquantes"

# Vérifier que le conteneur PHP est prêt et que les fichiers sont bien copiés
echo "🔍 Vérification de l'état du conteneur..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php ls -la bin/ || echo "⚠️ Conteneur PHP pas encore prêt"
echo "🔍 Vérification des fichiers de configuration..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php ls -la .env.dev* || echo "⚠️ Fichiers .env non trouvés"

# Corriger les permissions si nécessaire
echo "🔧 Correction des permissions de cache et JWT..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions var/ non modifiées"
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions var/ non modifiées"

# Permissions pour les clés JWT
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/config/jwt/ 2>/dev/null || echo "ℹ️ Dossier JWT non trouvé, génération des clés..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chmod 600 /var/www/html/config/jwt/private.pem 2>/dev/null || echo "ℹ️ Clé privée JWT à générer"
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chmod 644 /var/www/html/config/jwt/public.pem 2>/dev/null || echo "ℹ️ Clé publique JWT à générer"

# Installer les dépendances AVEC les dépendances de développement pour l'environnement dev
echo "📦 Installation des dépendances Composer (avec dev)..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php composer install --optimize-autoloader || echo "⚠️ Installation Composer échouée"

# Générer les clés JWT si nécessaire
echo "🔐 Génération des clés JWT si nécessaire..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=dev || echo "⚠️ Génération clés JWT échouée"

# Attendre que PostgreSQL soit prêt
echo "⏳ Attente de PostgreSQL..."
sleep 5

# Créer la base de données si elle n'existe pas
echo "🗄️ Création de la base de données si nécessaire..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console doctrine:database:create --if-not-exists --env=dev || echo "⚠️ Création BDD échouée"

# Générer les migrations automatiquement
echo "🔧 Génération des migrations à partir des entités..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:diff --env=dev || echo "⚠️ Génération migrations échouée"

# Diagnostic et réparation des migrations
echo "🔍 Diagnostic de l'état des migrations..."
MIGRATION_STATUS=$(docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:status --env=dev 2>/dev/null | grep "Executed" | tail -1 | awk '{print $4}' || echo "0")
TABLE_COUNT=$(docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T postgres psql -U postgres -d TalkLabs -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public' AND table_name != 'doctrine_migration_versions';" 2>/dev/null | tr -d ' ' || echo "0")
echo "📊 Migrations exécutées: $MIGRATION_STATUS"
echo "📊 Tables présentes: $TABLE_COUNT"
if [ "$MIGRATION_STATUS" != "0" ] && [ "$TABLE_COUNT" = "0" ]; then
    echo "⚠️ Incohérence détectée: migrations marquées comme exécutées mais tables absentes"
    echo "🔧 Reset de l'état des migrations..."
    # Supprimer les entrées de migrations fantômes
    docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T postgres psql -U postgres -d TalkLabs -c "DELETE FROM doctrine_migration_versions;" 2>/dev/null || echo "Table migrations non trouvée"
    echo "✅ État des migrations nettoyé"
fi

# Exécuter les migrations
echo "🔧 Exécution des migrations..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:migrate --no-interaction --env=dev || echo "⚠️ Migrations échouées"

# Corriger les permissions avant le cache
echo "🔐 Correction des permissions..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions non modifiées"
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/ 2>/dev/null || echo "⚠️ Permissions non modifiées"

# Vider le cache
echo "🧹 Nettoyage du cache..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console cache:clear --env=dev || echo "⚠️ Nettoyage cache échoué"

# Corriger les permissions du cache généré
echo "🔐 Correction finale des permissions cache..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/cache/ 2>/dev/null || echo "⚠️ Permissions cache non modifiées"
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/cache/ 2>/dev/null || echo "⚠️ Permissions cache non modifiées"

# Charger les données de test (fixtures)
echo "📊 Chargement des données de test..."
docker-compose --env-file .env.dev -f $COMPOSE_FILE exec -T php php bin/console hautelook:fixtures:load --no-interaction --env=dev || echo "⚠️ Chargement des fixtures échoué ou pas disponible"

echo "✅ Déploiement local terminé avec succès!"
echo "🌐 L'application est accessible sur $TEST_URL"

# Test de l'application
echo "🔍 Test de l'application..."
sleep 5  # Attendre un peu que tout soit prêt
if curl -s -f $TEST_URL/ > /dev/null 2>&1; then
    echo "✅ Le site répond correctement"
else
    echo "⚠️ Le site ne répond pas encore, attendez quelques secondes..."
fi

echo "🔍 Test de l'API..."
if curl -s -f $TEST_URL/api/get-conversations-public > /dev/null 2>&1; then
    echo "✅ L'API répond correctement"
else
    echo "⚠️ L'API ne répond pas encore, vérifiez que tous les services sont démarrés"
fi

echo ""
echo "📋 État des conteneurs:"
docker-compose --env-file .env.dev -f $COMPOSE_FILE ps
echo ""
echo "🔗 URLs utiles:"
echo "   - Site web: $TEST_URL"
echo "   - API: $TEST_URL/api"
echo "   - Documentation API: $TEST_URL/api/doc (si disponible)"
echo ""
echo "📝 Commandes utiles:"
echo "   - Voir les logs: docker-compose --env-file .env.dev -f $COMPOSE_FILE logs -f"
echo "   - Logs PHP: docker-compose --env-file .env.dev -f $COMPOSE_FILE logs -f php"
echo "   - Logs PostgreSQL: docker-compose --env-file .env.dev -f $COMPOSE_FILE logs -f postgres"
echo "   - Arrêter: docker-compose --env-file .env.dev -f $COMPOSE_FILE down"
echo "   - Redémarrer: docker-compose --env-file .env.dev -f $COMPOSE_FILE restart"
echo "   - Accéder au conteneur PHP: docker-compose --env-file .env.dev -f $COMPOSE_FILE exec php bash"
