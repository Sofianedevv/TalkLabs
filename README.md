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
Copyright © 2024 [Arthur Brouard, Sid-Ahmed ainsi que Sofiane Chadili]