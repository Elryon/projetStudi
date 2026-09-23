Le choix de Symfony s'est rapidement fait car contenant tous les outils nécessaire (donc plus interessant que du PHP brut à mon gout), le confort offert par ce framework, et un confort personnel sur PHP et Symfony
Le support de twig me convenait, je n'ai donc pas cherché à ajouter un framework orienté front en surcouche.
Bootstrap apporte une facilité et rapidité pour obtenir des rendu visuel de base et du CSS custom pour des éléments souhaités plus poussés.
N'ayant pas une grande habitude des DB NoSQL, MongoDB a été choisi par absence de connaissance sur d'autre db plus qu'autre chose. La version Atlas gratuite en ligne m'a semblé suffisante et plus pratique au moment de la mise en prod que l'emploi d'une version locale.

Environnement:
 Windows 11, Largon, PHP-8.1.10, MySQL 8.0.30, MongoDB 4.0.3

1. CONTEXTE ET ENVIRONNEMENT CIBLE
------------------------------------------------------------
- Projet local : Symfony 6.4, développé sous Windows avec Laragon
  (chemin local : C:\laragon\www\studi\ecf)
- Hébergement : OVH mutualisé (accès SSH, PHP 8.4)
- Répertoire distant : ~/studi_project
- Transfert de fichiers : FileZilla (FTP/SFTP) pointant sur le sous-domaine
- Base de données : MySQL fournie par OVH (instance dédiée à l'hébergement)
- Base secondaire : MongoDB Atlas (cluster cloud, utilisé pour les
  statistiques applicatives — service StatService / document CommandeStat)
- Envoi d'e-mails : SMTP OVH (ssl0.ovh.net, ports 587/465)
- Gestion des assets front : Symfony AssetMapper + SymfonyCasts SassBundle
  (compilation Sass -> CSS, sans serveur de dev en production)
- Versionning : dépôt Git hébergé sur GitHub
 
 
2. DEROULEMENT NORMAL DU DEPLOIEMENT (ETAPES ATTENDUES)
------------------------------------------------------------
1. Connexion SSH au serveur OVH.
2. Récupération du code source sur le serveur (git clone / git pull,
   ou transfert via FileZilla selon les cas).
3. Installation des dépendances PHP :
       php composer.phar install --no-dev --optimize-autoloader
   (pas de binaire "composer" global sur ce mutualisé OVH — utilisation
   du fichier composer.phar téléchargé manuellement via :
       curl -sS https://getcomposer.org/installer | php)
4. Configuration de l'environnement de production :
   - .env.local avec APP_ENV=prod, APP_DEBUG=0
   - DATABASE_URL pointant vers la base MySQL OVH
   - MAILER_DSN pointant vers le SMTP OVH
   - URI MongoDB Atlas pour la connexion ODM
5. Création du schéma de base de données et application des migrations :
       php bin/console doctrine:database:create
       php bin/console doctrine:migrations:migrate
6. Compilation des assets front :
       php bin/console sass:build
       php bin/console asset-map:compile
7. Vidage et réchauffement du cache de production :
       php bin/console cache:clear --env=prod
8. Vérification de l'accès public au site (page d'accueil, formulaires,
   espace admin) et des logs de production.
 
Cette séquence constitue la trame "normale" d'un déploiement Symfony sur
hébergement mutualisé. Dans la pratique, chacune de ces étapes a révélé un
problème spécifique à l'environnement OVH, détaillé ci-dessous dans l'ordre
chronologique où ils ont été rencontrés et résolus.
 
 
3. PROBLEMES RENCONTRES ET RESOLUTIONS
------------------------------------------------------------
 
3.1 Composer absent du PATH
    Problème : "composer : commande introuvable" en SSH.
    Cause : pas de Composer installé globalement sur ce mutualisé.
    Solution : téléchargement de composer.phar directement sur le serveur
    et utilisation systématique de "php composer.phar <commande>".
 
3.2 Conflit de pré-requis PHP pour MongoDB
    Problème : composer install refuse de continuer car
    doctrine/mongodb-odm et mongodb/mongodb exigent l'extension
    ext-mongodb en version ^1.21 ou ^2.0, alors que le serveur ne dispose
    que de la version 1.15.3.
    Solution : ajout du flag --ignore-platform-req=ext-mongodb à
    l'installation. Confirmé sans risque car MongoDB est réellement
    utilisé par l'application (statistiques), pas une dépendance morte.
 
3.3 cache:clear en échec après install --no-dev
    Problème : erreur ClassNotFoundError sur DebugBundle lors du
    cache:clear post-installation.
    Cause : le script d'installation composer s'exécutait avec un
    environnement encore positionné sur "dev" (voir point 3.6), alors que
    --no-dev avait supprimé les paquets de développement.
    Solution : résolu définitivement une fois APP_ENV correctement fixé à
    "prod" (voir 3.6).
 
3.4 Mauvaise adresse de serveur MySQL
    Problème : erreur DNS "Name or service not known" sur le nom d'hôte
    de la base de données, à deux reprises avec deux noms différents.
    Cause : confusion entre deux champs du panneau OVH — le nom interne
    du cluster ("mysql097.eu002...") n'est pas le nom d'hôte de
    connexion ; le bon champ est "Adresse du serveur"
    (elryonrstudi.mysql.db).
    Solution : correction de DATABASE_URL avec la bonne adresse.
 
3.5 Environnement de production resté en mode "dev"
    Problème (root cause majeure, découverte tardive) : .env.local
    contenait encore APP_ENV=dev. Le site fonctionnait donc en
    environnement de développement depuis le début de la mise en ligne
    (constaté via un fichier var/log/dev.log de 12 Mo).
    Solution : correction en APP_ENV=prod et APP_DEBUG=0. Ce changement a
    également résolu indirectement le point 3.3.
 
3.6 Racine du site publiquement exposée (faille de sécurité)
    Problème : l'intégralité du dossier projet (dont .env, .env.local,
    .git/, vendor/) était listée et accessible publiquement ("Index of
    /"), le panneau OVH ne permettant pas de changer directement le
    document root vers le sous-dossier public/.
    Solution : ajout d'un fichier .htaccess à la racine du projet qui
    redirige toutes les requêtes vers public/ via mod_rewrite, complété
    par des règles "Require all denied" sur les fichiers sensibles
    (.env*, .lock, .yaml, etc.) en dehors de public/.
 
3.7 Assets CSS/JS absents (404) en production
    Problème : aucune feuille de style ni script chargé sur le site en
    ligne.
    Cause : AssetMapper nécessite une compilation explicite en
    production (pas de serveur de développement qui sert les fichiers à
    la volée).
    Solution : exécution de "sass:build" puis "asset-map:compile" pour
    générer et versionner les assets statiques.
 
3.8 Echec de compilation Sass (mauvais binaire)
    Problème : sass:build échoue avec "invalid option: --source-map".
    Cause : le bundle Symfony détectait et utilisait le binaire "sass"
    Ruby déjà présent sur le serveur (/usr/bin/sass), alors qu'il attend
    Dart Sass et ses options.
    Solution : exécution ponctuelle de sass:build avec un PATH restreint
    pour masquer le binaire système et forcer le téléchargement du vrai
    binaire Dart Sass dans var/dart-sass/, puis configuration définitive
    du chemin de ce binaire dans symfonycasts_sass.yaml pour que les
    prochaines exécutions n'aient plus besoin de cette manipulation.
 
3.9 Sessions PHP natives non persistées / jeton CSRF invalide
    Problème (chaîne de débogage la plus longue) : erreur "Le jeton CSRF
    est invalide" bloquant le formulaire d'inscription.
    Diagnostic en plusieurs étapes :
      a) Les fichiers de session natifs PHP n'étaient jamais créés sur le
         serveur malgré une configuration correcte (save_path, pas de
         restriction open_basedir) — probable restriction spécifique au
         pool PHP-FPM de cet hébergement.
      b) Contournement : implémentation d'un gestionnaire de session
         personnalisé (App\Session\AppFileSessionHandler) écrivant les
         sessions via de simples file_put_contents/file_get_contents,
         déclaré comme handler_id dans framework.yaml.
 
3.10 Connexion MongoDB Atlas refusée (TLS)
    Problème : l'IP sortante du serveur OVH n'était pas autorisée dans la
    liste blanche réseau ("Network Access") d'Atlas, provoquant un rejet
    au niveau TLS plutôt qu'un simple refus réseau.
    Difficulté annexe : impossible de déterminer l'IP sortante exacte
    depuis le shell SSH (les commandes vers des services externes de
    type "quelle est mon IP" étaient bloquées), alors que le process
    web (PHP-FPM) parvenait bien à atteindre l'extérieur.
    Solution : ajout temporaire d'une autorisation large (0.0.0.0/0) côté
    Atlas pour débloquer la connexion. A remplacer, en amont, par une
    entrée plus restreinte une fois l'IP sortante réelle identifiée
    (panneau OVH ou historique de connexions Atlas).