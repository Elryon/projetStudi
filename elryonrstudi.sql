-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : elryonrstudi.mysql.db
-- Généré le : mer. 23 sep. 2026 à 22:49
-- Version du serveur : 8.4.11-11
-- Version de PHP : 8.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `elryonrstudi`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` binary(16) NOT NULL,
  `note` smallint NOT NULL,
  `contenu` longtext NOT NULL,
  `validated` tinyint(1) NOT NULL,
  `commande_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` binary(16) NOT NULL,
  `date_commande` date NOT NULL,
  `date_livraison` datetime NOT NULL,
  `prix_total` decimal(6,2) NOT NULL,
  `nombre_personne` int NOT NULL,
  `pret_materiel` tinyint(1) NOT NULL,
  `materiel_rendu` tinyint(1) NOT NULL,
  `adresse` longtext NOT NULL,
  `motif_annulation` varchar(255) DEFAULT NULL,
  `mode_contact` varchar(50) DEFAULT NULL,
  `prix_livraison` decimal(6,2) DEFAULT NULL,
  `user_id` binary(16) NOT NULL,
  `menu_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `date_commande`, `date_livraison`, `prix_total`, `nombre_personne`, `pret_materiel`, `materiel_rendu`, `adresse`, `motif_annulation`, `mode_contact`, `prix_livraison`, `user_id`, `menu_id`) VALUES
(0x01a0cfa4cebb765a89fc99605c7ae842, '2026-09-23', '2026-10-01 12:27:00', 656.31, 20, 1, 0, '29 Rue de Rivoli, Paris', NULL, NULL, 350.31, 0x01a0cf83d4ed77e3a6d64d6f2bbb0093, 0x01a0cfa3bf8f720d8270db61dbddebd7),
(0x01a0cfaa2e9e762898ca243c5e791c2d, '2026-09-23', '2026-10-14 11:30:00', 518.36, 11, 0, 0, '10 rue de rivoli, Paris', NULL, NULL, 350.06, 0x01a0cfa911fc7efbb766d6f6e1dd9004, 0x01a0cfa3bf8f720d8270db61dbddebd7);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260923162504', '2026-09-23 19:22:29', 258);

-- --------------------------------------------------------

--
-- Structure de la table `horaires`
--

CREATE TABLE `horaires` (
  `id` int NOT NULL,
  `lundi` varchar(255) NOT NULL,
  `mardi` varchar(255) NOT NULL,
  `mercredi` varchar(255) NOT NULL,
  `jeudi` varchar(255) NOT NULL,
  `vendredi` varchar(255) NOT NULL,
  `samedi` varchar(255) NOT NULL,
  `dimanche` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `horaires`
--

INSERT INTO `horaires` (`id`, `lundi`, `mardi`, `mercredi`, `jeudi`, `vendredi`, `samedi`, `dimanche`) VALUES
(1, 'Fermé', '8h-19h', '8h-19h', '8h-19h', '8h-19h', '8h-22h', 'Fermé');

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE `menu` (
  `id` binary(16) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `personne_min` int NOT NULL,
  `prix_personne` decimal(5,2) NOT NULL,
  `description` longtext NOT NULL,
  `quantite_restante` int NOT NULL,
  `regime` varchar(255) NOT NULL,
  `conditions` longtext,
  `theme_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`id`, `titre`, `personne_min`, `prix_personne`, `description`, `quantite_restante`, `regime`, `conditions`, `theme_id`) VALUES
(0x01a0cfa3bf8f720d8270db61dbddebd7, 'Menu 1', 1, 19.00, 'Cassoulet et tarte tatin', 29, 'aucun', 'aucunes', 1),
(0x01a0cfb6ed9c731996398bbc5ec753ba, 'Menu 2', 1, 14.00, 'Galette complète et île flottante', 29, 'aucun', 'aucunes', 1),
(0x01a0cfb8ec287f09b293d03e53841664, 'Menu 3', 1, 15.00, 'Gratin dauphinois et tiramisu', 29, 'vegetarien', 'aucunes', 1),
(0x01a0cfbb432b7488b6ebea182353848f, 'Menu 4', 1, 14.00, 'Ratatouille et mousse au chocolat', 26, 'vegetarien', 'aucunes', 1),
(0x01a0cfbc7e7a77a5967c67cdf6b5e151, 'Menu 5', 1, 16.00, 'Quiche lorraine et tartelette au citron', 29, 'aucun', 'aucunes', 1),
(0x01a0cfe6fa3f7142b732611d1877d59c, 'Menu 6', 1, 13.00, 'Tomates farcies au haché végétal et smoothie banane ananas coco', 29, 'vegan', 'aucunes', 1),
(0x01a0cfe7d47779178f96e983ff97aab7, 'Menu 7', 1, 18.00, 'Bouillabaisse et riz au lait', 29, 'poisson', 'aucunes', 1);

-- --------------------------------------------------------

--
-- Structure de la table `menu_plats`
--

CREATE TABLE `menu_plats` (
  `menu_id` binary(16) NOT NULL,
  `plats_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `menu_plats`
--

INSERT INTO `menu_plats` (`menu_id`, `plats_id`) VALUES
(0x01a0cfa3bf8f720d8270db61dbddebd7, 0x01a0cfa1860a797eb5e3cf3f4a72ea37),
(0x01a0cfa3bf8f720d8270db61dbddebd7, 0x01a0cfb40e4d7204a43a748dbac4c86a),
(0x01a0cfb6ed9c731996398bbc5ec753ba, 0x01a0cfa96cf27d338ffdd1450cfca9ec),
(0x01a0cfb6ed9c731996398bbc5ec753ba, 0x01a0cfb24c5a754a9e58dc68ecb49220),
(0x01a0cfb8ec287f09b293d03e53841664, 0x01a0cfa64d447c49a36414a58548e486),
(0x01a0cfb8ec287f09b293d03e53841664, 0x01a0cfae6b1478eb9961232ce8b7eff5),
(0x01a0cfbb432b7488b6ebea182353848f, 0x01a0cfa24dad7db69161364a79cee402),
(0x01a0cfbb432b7488b6ebea182353848f, 0x01a0cfac2bff76d5971f14026e81b325),
(0x01a0cfbc7e7a77a5967c67cdf6b5e151, 0x01a0cfa46f8e724b832c3d9b3ab9606c),
(0x01a0cfbc7e7a77a5967c67cdf6b5e151, 0x01a0cfb0729c7e8abb9b3c0e0b839a79),
(0x01a0cfe6fa3f7142b732611d1877d59c, 0x01a0cfe1548570998ae8efc5ab3696e0),
(0x01a0cfe6fa3f7142b732611d1877d59c, 0x01a0cfe3d024701c8f3b7a37f1951f63),
(0x01a0cfe7d47779178f96e983ff97aab7, 0x01a0cfdf4a6676f89124d9abcdaad97d),
(0x01a0cfe7d47779178f96e983ff97aab7, 0x01a0cfe603197687887b1fb09335885c);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plats`
--

CREATE TABLE `plats` (
  `id` binary(16) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `allergenes` longtext,
  `disponible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `plats`
--

INSERT INTO `plats` (`id`, `nom`, `photo`, `allergenes`, `disponible`) VALUES
(0x01a0cfa1860a797eb5e3cf3f4a72ea37, 'Cassoulet', 'cassoulet-omnicuiseur-recette-6ab421286f2a0.jpg', 'Fèves', 1),
(0x01a0cfa24dad7db69161364a79cee402, 'Ratatouille', 'plat-gourmand-de-ratatouille-a-lhuile-dolive-6ab4215b89400.webp', NULL, 1),
(0x01a0cfa46f8e724b832c3d9b3ab9606c, 'Quiche Lorraine', 'gros-plan-sur-une-savoureuse-tranche-de-quiche-lorraine-avec-laitue-sur-une-assiette-6ab421e74b089.webp', 'Gluten, Lactose, Oeuf', 1),
(0x01a0cfa64d447c49a36414a58548e486, 'Gratin dauphinois', 'istockphoto-1476560113-612x612-6ab4226192df8.jpg', 'Lactose', 1),
(0x01a0cfa96cf27d338ffdd1450cfca9ec, 'Galette complète', 'breton-galette-et-dautres-plats-prepares-ensemble-colore-6ab4232e4df64.webp', 'Oeuf, Lactose', 1),
(0x01a0cfac2bff76d5971f14026e81b325, 'Mousse au chocolat', 'api-thumb-450-6ab423e2495e1.webp', 'Oeuf', 1),
(0x01a0cfae6b1478eb9961232ce8b7eff5, 'Tiramisu', 'tiramisu-on-a-plate-6ab424757f4ed.webp', 'Gluten, Lactose, Oeuf', 1),
(0x01a0cfb0729c7e8abb9b3c0e0b839a79, 'Tartelette au citron', 'tarte-au-citron-6ab424fa7f245.webp', 'Gluten, Lactose, Oeuf', 1),
(0x01a0cfb24c5a754a9e58dc68ecb49220, 'Île flottante', 'dessert-de-lile-flottante-6ab42573c3041.webp', 'Oeuf, Lactose', 1),
(0x01a0cfb40e4d7204a43a748dbac4c86a, 'Tarte tatin', 'homemade-tarte-tatin-6ab425e6f0ebe.webp', 'Gluten, Lactose, Oeuf', 1),
(0x01a0cfdf4a6676f89124d9abcdaad97d, 'Bouillabaisse', 'bouillabaisse-poisson-ragout-de-francais-vue-rapprochee-francaise-recette-383741271-6ab430f86646d.webp', 'Fruit de mer, Gluten', 1),
(0x01a0cfe1548570998ae8efc5ab3696e0, 'Tomates farcies au haché végétal', 'tomates-farcies-au-riz-fromage-et-chanterelles-6ab4317e13f34.webp', NULL, 1),
(0x01a0cfe3d024701c8f3b7a37f1951f63, 'Smoothie banane ananas coco', 'smoothie-a-la-banane-6ab43220c3c3a.webp', NULL, 1),
(0x01a0cfe603197687887b1fb09335885c, 'Riz au lait', 'riz-au-lait-avec-la-noix-de-muscade-25440066-6ab432b0e0517.webp', 'Lactose', 1);

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `user_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut_commande`
--

CREATE TABLE `statut_commande` (
  `id` int NOT NULL,
  `date` datetime NOT NULL,
  `statut` varchar(255) NOT NULL,
  `commande_id` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `statut_commande`
--

INSERT INTO `statut_commande` (`id`, `date`, `statut`, `commande_id`) VALUES
(1, '2026-09-23 21:01:19', 'attente', 0x01a0cfa4cebb765a89fc99605c7ae842),
(2, '2026-09-23 21:07:11', 'attente', 0x01a0cfaa2e9e762898ca243c5e791c2d);

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

CREATE TABLE `theme` (
  `id` int NOT NULL,
  `theme` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `theme`
--

INSERT INTO `theme` (`id`, `theme`) VALUES
(1, 'Aucun');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` binary(16) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `roles` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `prenom`, `nom`, `telephone`, `adresse`, `roles`) VALUES
(0x01a0cf83d4ed77e3a6d64d6f2bbb0093, 'xavier.julia7@orange.fr', '$2y$13$iFJkLmbSGE7ec06DZN43Ze6BedqEgA.7/bBIs/b.s0/arDdXAwryi', 'Xavier', 'JULIA', '0688425001', NULL, '[\"ROLE_ADMIN\"]'),
(0x01a0cf995c437b1b8477cc1e137368c6, 'jean-ploye@traiteur.fr', '$2y$13$l5szb9EKBx9g45eun1lqIe/a9IGoiJ4igwJBs5j.tZedm5F3RAYau', 'Jean', 'PLOYE', NULL, NULL, '[\"ROLE_EMPLOYE\"]'),
(0x01a0cfa79b667210a6da6a121cb36a29, 'jose-spere@traiteur.fr', '$2y$13$Nk26A.ege.hOIuv99kgzB.gEfTvxGUDhyvB0ESkLKQGz2DUCYB3Oi', 'José', 'SPERE', NULL, NULL, '[\"ROLE_ADMIN\"]'),
(0x01a0cfa911fc7efbb766d6f6e1dd9004, 'cust-homer@client-exemple.fr', '$2y$13$p7HAmZ.lFmLVhThlk4aO9eOdgoHlmkUkFw8zl2jH.CRIcAEAN2.72', 'Homer', 'CUST', '0606060606', '10 rue de Rivoli, Paris', '[\"ROLE_USER\"]');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8F91ABF082EA2E54` (`commande_id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6EEAA67DA76ED395` (`user_id`),
  ADD KEY `IDX_6EEAA67DCCD7E912` (`menu_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `horaires`
--
ALTER TABLE `horaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7D053A9359027487` (`theme_id`);

--
-- Index pour la table `menu_plats`
--
ALTER TABLE `menu_plats`
  ADD PRIMARY KEY (`menu_id`,`plats_id`),
  ADD KEY `IDX_14E6416DCCD7E912` (`menu_id`),
  ADD KEY `IDX_14E6416DAA14E1C8` (`plats_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `plats`
--
ALTER TABLE `plats`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Index pour la table `statut_commande`
--
ALTER TABLE `statut_commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_11EC1ADC82EA2E54` (`commande_id`);

--
-- Index pour la table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `horaires`
--
ALTER TABLE `horaires`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `statut_commande`
--
ALTER TABLE `statut_commande`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `theme`
--
ALTER TABLE `theme`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `FK_8F91ABF082EA2E54` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `FK_6EEAA67DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_6EEAA67DCCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);

--
-- Contraintes pour la table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `FK_7D053A9359027487` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`);

--
-- Contraintes pour la table `menu_plats`
--
ALTER TABLE `menu_plats`
  ADD CONSTRAINT `FK_14E6416DAA14E1C8` FOREIGN KEY (`plats_id`) REFERENCES `plats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_14E6416DCCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `statut_commande`
--
ALTER TABLE `statut_commande`
  ADD CONSTRAINT `FK_11EC1ADC82EA2E54` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
