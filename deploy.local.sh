#!/bin/bash

# Script de déploiement local simplifié pour TalkLabs
echo "🚀 Démarrage du déploiement local TalkLabs..."

COMPOSE_FILE="compose.yaml"
TEST_URL="http://localhost"

# Arrêt et nettoyage des conteneurs
docker-compose --env-file .env.local -f $COMPOSE_FILE down

# Construction et démarrage des conteneurs
docker-compose --env-file .env.local -f $COMPOSE_FILE up -d --build

# Attente du démarrage des services
sleep 10

# Installation des dépendances
# Création et migration de la base de données
docker-compose --env-file .env.local -f $COMPOSE_FILE exec -T php php bin/console doctrine:database:create --if-not-exists --env=dev
docker-compose --env-file .env.local -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:diff --env=dev
docker-compose --env-file .env.local -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:migrate --no-interaction --env=dev

# Chargement des fixtures
docker-compose --env-file .env.local -f $COMPOSE_FILE exec -T php php bin/console hautelook:fixtures:load --no-interaction --env=dev

# Nettoyage du cache
docker-compose --env-file .env.local -f $COMPOSE_FILE exec -T php php bin/console cache:clear --env=dev

echo "✅ Déploiement local terminé !"
echo "🌐 L'application est accessible sur $TEST_URL"

# État des conteneurs
docker-compose --env-file .env.local -f $COMPOSE_FILE ps

# URLs utiles
echo "Site web: $TEST_URL"
echo "API: $TEST_URL/api"