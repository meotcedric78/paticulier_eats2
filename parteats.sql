
-- Déchargement des données de la table `achat`
--

INSERT INTO `achat` (`id`, `commande_id`, `prix`, `quantite`) VALUES
(1, 2, 25, 1),
(2, 2, 45, 1),
(3, 2, 20, 1),
(4, 3, 25, 1);

-- --------------------------------------------------------


--

INSERT INTO `categorie` (`id`, `nom`) VALUES
(4, 'Entrée (salade)'),
(5, 'Plat'),
(6, 'Dessert');


--

INSERT INTO `commande` (`id`, `user_id`, `date`, `date_retrait`, `total`) VALUES
(1, 4, '2021-05-19', NULL, 0),
(2, 4, '2021-05-19', NULL, 90),
(3, 4, '2021-05-19', NULL, 25);

-- --------------------------------------------------------





INSERT INTO `produit` (`id`, `achat_id`, `categorie_id`, `nom`, `prix`, `description`, `image`, `commission`, `cuisinier_id`) VALUES
(9, NULL, 6, 'Gateau Framboise Speculoos', 25, 'tres bon gateau', '20210517232532-60a2df4cbff80-IMG_20210511_124738.png', 5, 1),
(10, NULL, 6, 'Gateau multi fruit', 45, 'tres tres bon gateau 2', '20210517232833-60a2e001303bb-IMG_20210511_124609.png', 5, 1),
(11, NULL, 5, 'riz crevette', 20, 'riz parfumé crevette', '20210518112128-60a3871849684-IMG_20210511_124811.png', 5, 4),
(12, NULL, 6, 'gateau', 45, 'tres tres bon gateau 3', '20210518115738-60a38f92bc0ad-IMG_20210511_124609.png', 5, 4),
(13, NULL, 5, 'spaghetti escalope', 15, 'repas tres bon 2efefefefeff', '20210518141616-60a3b0103a950-IMG_20210511_124822.png', 5, 2),
(14, NULL, 5, 'couscous', 100, 'repas tres bon', '20210518153823-60a3c34f6be98-IMG_20210511_112607.png', 5, 6);

-- --------------------------------------------------------


--

INSERT INTO `produit_sous_categorie` (`produit_id`, `sous_categorie_id`) VALUES
(9, 1),
(10, 2),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(14, 2);

-- --------------------------------------------------------


--

INSERT INTO `sous_categorie` (`id`, `nom`) VALUES
(1, 'vegan'),
(2, 'Halal');



INSERT INTO `user` (`id`, `username`, `password`, `email`, `nom`, `prenom`, `birthday`, `roles`, `adresse`, `ville`, `cp`, `majorite`) VALUES
(1, 'mp', '$2y$13$43JzLXSIq8bG.N0tIMj8z.VKBLS9tdoLxvWVIV8Sq1YpWoWPeGPM6', 'mp@hotmail.fr', 'mendy', 'patrick', '1920-11-21', '[\"ROLE_ADMIN\"]', '16 rue du plateau', 'aubergenville', 78410, 0),
(2, 'mc', '$2y$13$6UXoV6Q4vWKw0sJa3YMHi.yBIvooreO.rtxYXyqvB5T6hZ9E0MFCi', 'meo78@hotmail.fr', 'meot', 'cedric', '1912-12-07', '[\"ROLE_USER\"]', '16 rue du plateau', 'aubergenville', 78410, 0),
(4, 'mm78', '$2y$13$Ug4mxIjCmJoURTtno9DUQ.vDRqWsXuVlSCRs5bvEjzAlgzSL6vSvG', 'maelys@hotmail.fr', 'meot', 'maelys', '2008-08-10', '[\"ROLE_USER\"]', '4 rue rue', 'les mureaux', 78130, 1),
(5, 'mn78', '$2y$13$GfER/30ZbKVZrWFqTfj/AO340cEfo4fSJy6STfd2DpR07YtJisfzi', 'nessa@hotmail.fr', 'meot', 'nessa', '2014-04-04', '[\"ROLE_USER\"]', '8 rue avenue', 'mantes la jolie', 78200, 1),
(6, 'hafsa', '$2y$13$1Q9k0sR3AVlUzGujicXzHOYugrfmZRl299MXjX/pw9Qj6AaalVJfm', 'hafsa@hotmail.fr', 'hyvernat', 'hafsa', '1918-10-14', '[\"ROLE_USER\"]', 'jijijijij', 'les mureaux', 78130, 1);


