# projetStudi – Déploiement en local

Je vais partir du principe que la personne souhaitant déployer l'application en local a déjà un environnement en place (LAMP, WAMP, XAMPP, Laragon…).
Une fois les fichiers placés dans leur espace (`www` pour la plupart de ces stacks), il suffit de suivre les étapes ci-dessous.

---

## 1. Prérequis

| Outil | Version conseillée |
|---|---|
| PHP | 8.2 ou supérieur |
| Composer | 2.x |
| MySQL / MariaDB | 8.x / 10.x |
| Extension PHP MongoDB | activée dans le `php.ini` |
| Compte MongoDB Atlas (ou MongoDB local) | pour les statistiques |
| Symfony CLI | facultatif, pour le serveur local |

**Extensions PHP nécessaires :** `pdo_mysql`, `intl`, `mbstring`, `ctype`, `iconv`, `mongodb`.

> **Activer MongoDB :** dans le `php.ini` de votre stack, décommentez (ou ajoutez) la ligne
> `extension=mongodb`, puis redémarrez le serveur. Vérifiez avec `php -m | grep mongodb`
> (sous Windows : `php -m | findstr mongodb`).

---

## 2. Récupérer le projet

```bash
git clone git@github.com:Elryon/projetStudi.git
cd projetStudi
```

(Ou dézippez l'archive directement dans le dossier `www`.)

---

## 3. Installer les dépendances

```bash
composer install
```

---

## 4. Configurer l'environnement

Créez un fichier `.env.local` à la racine (il n'est pas versionné) et renseignez vos propres valeurs :

```dotenv
APP_ENV=dev
APP_SECRET=une_chaine_aleatoire

# Base relationnelle (MySQL / MariaDB)
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/projetstudi?serverVersion=8.0&charset=utf8mb4"

# MongoDB (statistiques)
MONGODB_URL="mongodb+srv://utilisateur:motdepasse@cluster.mongodb.net"
MONGODB_DB=projetstudi_stats

# Envoi d'e-mails (réinitialisation de mot de passe)
# En local, "null://null" désactive l'envoi ; utilisez Mailtrap/Mailpit pour tester
MAILER_DSN=null://null
```

> Adaptez `serverVersion` à votre version de MySQL/MariaDB (ex. `10.11.2-MariaDB`).

---

## 5. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Côté MongoDB :

```bash
php bin/console doctrine:mongodb:schema:create
```

## 6. Compiler les assets

Le projet utilise AssetMapper et `symfonycasts/sass-bundle` (Bootstrap personnalisé en Sass) :

```bash
php bin/console importmap:install
php bin/console sass:build
```

Pendant le développement, pour recompiler automatiquement le Sass :

```bash
php bin/console sass:build --watch
```

---

## 7. Lancer l'application

**Avec la Symfony CLI :**

```bash
symfony serve
```

Puis ouvrez <https://127.0.0.1:8000>.

**Avec votre stack (Apache) :** faites pointer le virtual host vers le dossier `public/` du projet, puis ouvrez l'URL correspondante (ex. `http://projetstudi.test` avec Laragon).

---

## 8. Compte administrateur

<!-- À compléter : identifiants de démo fournis par les fixtures, ou commande pour créer un admin -->

---

## Dépannage

| Problème | Solution |
|---|---|
| `Class "MongoDB\Driver\Manager" not found` | L'extension `mongodb` n'est pas activée dans le `php.ini` (étape 1). |
| Styles absents | Relancez `php bin/console sass:build`. |
| Erreur de connexion MySQL | Vérifiez `DATABASE_URL` dans `.env.local`. |
| Erreur de connexion MongoDB Atlas | Autorisez votre IP dans *Network Access* sur Atlas. |
| Calcul de livraison indisponible | L'application interroge `api-adresse.data.gouv.fr` et OSRM : une connexion internet est nécessaire. |
| Comportement étrange après modification | `php bin/console cache:clear` |