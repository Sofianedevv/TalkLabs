# Talk-lab

Mise en place d'un site-web permettant de simuler de fausse discussion sur
les messageries les plus connues du moment

## Installation
**Projet actuel BACK**
git clone https://github.com/Sofianedevv/TalkLabs.git
**Projet actuel FRONT**
git clone https://github.com/LuzBoger/talk-lab-front.git

- cd talklabs
- git checkout dev

puis tapper les commandes suivantes :
````bash
# Installation des composants de symfony
docker compose exec php composer install
#Installation de la structure de la BDD
docker compose exec php bin/console make:migration
docker compose exec php bin/console d:m:m

````
Commande pour charger les données :
````bash
docker compose exec php bin/console hautelook:fixtures:load
````

Commande pour mettre en place l'authentification via Token JWT
````bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
````

Générer une clé de chiffrement AES-256 à placer dans le .env le 2FA
````bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
````
### Variable D'environnement Model
````
APP_SECRET=****
DATABASE_URL="pgsql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@database:5432/${POSTGRES_DB}?serverVersion=16&charset=utf8"
POSTGRES_HOST=yourHost
POSTGRES_USER=yourUser
POSTGRES_PASSWORD=yourPassword
POSTGRES_DB=YourNameOfDatabase
LEXIK_JWT_PASSPHRASE=yourSecretSetence
APP_2FA_KEY=cledechiffrementAES-256
MAILER_DSN=null://null
````

### Commande docker importante 
````
docker system prune -a --volumes -f
docker compose exec php php bin/console app:create-admin admin@talklabs.com 'Admin User' 'proute'"
chown -R www-data:www-data /var/www/html/public/uploads/avatar/
chmod -R 775 /var/www/html/public/uploads/avatar/

chown -R www-data:www-data /var/www/html/public/uploads/audios/
chmod -R 775 /var/www/html/public/uploads/audios/

chown -R www-data:www-data /var/www/html/public/uploads/images/
chmod -R 775 /var/www/html/public/uploads/images/
````
Copyright © 2024 [Arthur Brouard, Sid-Ahmed ainsi que Sofiane Chadili]