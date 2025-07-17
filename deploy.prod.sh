#!/bin/bash

# Script de déploiement pour TalkLabs
set -e

echo "🚀 Déploiement de TalkLabs..."

# Détecter le contexte Docker actuel
DOCKER_CONTEXT=$(docker context show)
echo "📍 Contexte Docker actuel : $DOCKER_CONTEXT"

# Utiliser le fichier .env approprié selon le contexte
if [ "$DOCKER_CONTEXT" = "default" ]; then
    echo "📝 Déploiement en local - utilisation de .env.dev"
    if [ -f ".env.dev" ]; then
        cp .env.dev .env
    fi
    COMPOSE_FILE="compose.yaml"
    TEST_URL="http://localhost:8080"
else
    echo "📝 Déploiement sur VPS - utilisation de .env.prod.vps"
    if [ -f ".env.prod.vps" ]; then
        cp .env.prod.vps .env
    fi
    COMPOSE_FILE="compose.prod.yaml"
    TEST_URL="https://talklab.fr"
fi

# Construire et démarrer les services
echo "📦 Construction des images Docker..."
docker-compose -f $COMPOSE_FILE build --no-cache

echo "🔄 Arrêt des anciens conteneurs..."
docker-compose -f $COMPOSE_FILE down

echo "▶️ Démarrage des nouveaux conteneurs..."
docker-compose -f $COMPOSE_FILE up -d

echo "⏳ Attente que les services soient prêts..."
sleep 10

# Vérifier que le conteneur PHP est prêt et que les fichiers sont bien copiés
echo "🔍 Vérification de l'état du conteneur..."
docker-compose -f $COMPOSE_FILE exec -T php ls -la bin/
echo "🔍 Contenu du fichier .env..."
docker-compose -f $COMPOSE_FILE exec -T php head -5 .env

# Corriger les permissions si nécessaire
echo "🔧 Correction des permissions de cache et JWT..."
docker-compose -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/
docker-compose -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/
# Permissions pour les clés JWT
docker-compose -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/config/jwt/ 2>/dev/null || echo "ℹ️  Dossier JWT non trouvé, génération des clés..."
docker-compose -f $COMPOSE_FILE exec -T php chmod 600 /var/www/html/config/jwt/private.pem 2>/dev/null || echo "ℹ️  Clé privée JWT à générer"
docker-compose -f $COMPOSE_FILE exec -T php chmod 644 /var/www/html/config/jwt/public.pem 2>/dev/null || echo "ℹ️  Clé publique JWT à générer"

# Générer les clés JWT si nécessaire
echo "🔐 Génération des clés JWT si nécessaire..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=prod

# Créer la base de données si elle n'existe pas
echo "🗄️  Création de la base de données si nécessaire..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:database:create --if-not-exists --env=prod

# Générer les migrations automatiquement
echo "🔧 Génération des migrations à partir des entités..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:diff --env=prod

# Diagnostic et réparation des migrations
echo "🔍 Diagnostic de l'état des migrations..."
MIGRATION_STATUS=$(docker-compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:status --env=prod 2>/dev/null | grep "Executed" | tail -1 | awk '{print $4}')
TABLE_COUNT=$(docker-compose -f $COMPOSE_FILE exec -T postgres psql -U postgres -d TalkLabs -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public' AND table_name != 'doctrine_migration_versions';" 2>/dev/null | tr -d ' ')

echo "📊 Migrations exécutées: $MIGRATION_STATUS"
echo "📊 Tables présentes: $TABLE_COUNT"

if [ "$MIGRATION_STATUS" != "0" ] && [ "$TABLE_COUNT" = "0" ]; then
    echo "⚠️  Incohérence détectée: migrations marquées comme exécutées mais tables absentes"
    echo "🔧 Reset de l'état des migrations..."
    
    # Supprimer les entrées de migrations fantômes
    docker-compose -f $COMPOSE_FILE exec -T postgres psql -U postgres -d TalkLabs -c "DELETE FROM doctrine_migration_versions;" 2>/dev/null || echo "Table migrations non trouvée"
    
    echo "✅ État des migrations nettoyé"
fi

# Exécuter les migrations
echo "🔧 Exécution des migrations..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Corriger les permissions avant le cache
echo "🔐 Correction des permissions..."
docker-compose -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/
docker-compose -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/

# Vider le cache
echo "🧹 Nettoyage du cache..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console cache:clear --env=prod

# Corriger les permissions du cache généré
echo "🔐 Correction finale des permissions cache..."
docker-compose -f $COMPOSE_FILE exec -T php chown -R www-data:www-data /var/www/html/var/cache/
docker-compose -f $COMPOSE_FILE exec -T php chmod -R 755 /var/www/html/var/cache/

# Charger les données de test (fixtures)
echo "📊 Chargement des données de test..."
docker-compose -f $COMPOSE_FILE exec -T php php bin/console hautelook:fixtures:load --no-interaction --env=prod || echo "⚠️  Chargement des fixtures échoué ou pas disponible"

echo "✅ Déploiement terminé avec succès!"
echo "🌐 L'application est accessible sur $TEST_URL"

# Test de l'application
echo "🔍 Test de l'application..."
if curl -s -f $TEST_URL/ > /dev/null; then
    echo "✅ Le site répond correctement"
else
    echo "⚠️  Le site ne répond pas"
fi

echo "🔍 Test de l'API..."
if curl -s -f $TEST_URL/api/get-conversations-public > /dev/null; then
    echo "✅ L'API répond correctement"
else
    echo "⚠️  L'API ne répond pas"
fi
