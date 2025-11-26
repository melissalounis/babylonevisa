-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 22 nov. 2025 à 22:27
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `babylone_service`
--

-- --------------------------------------------------------

--
-- Structure de la table `attestation_province`
--

CREATE TABLE `attestation_province` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `province` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `passeport_path` varchar(500) NOT NULL,
  `preuve_fonds_path` text DEFAULT NULL,
  `photos_identite_path` varchar(500) NOT NULL,
  `releves_bancaires_path` varchar(500) NOT NULL,
  `lettre_acceptation_path` text DEFAULT NULL,
  `autres_documents_path` varchar(500) DEFAULT NULL,
  `statut` enum('nouveau','en_traitement','approuve','refuse') DEFAULT 'nouveau',
  `notes_admin` text DEFAULT NULL,
  `date_soumission` datetime NOT NULL DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `attestation_province`
--

INSERT INTO `attestation_province` (`id`, `user_id`, `nom_complet`, `email`, `telephone`, `province`, `date_naissance`, `adresse`, `ville`, `code_postal`, `pays`, `passeport_path`, `preuve_fonds_path`, `photos_identite_path`, `releves_bancaires_path`, `lettre_acceptation_path`, `autres_documents_path`, `statut`, `notes_admin`, `date_soumission`, `date_traitement`, `created_at`, `updated_at`) VALUES
(1, 6, 'chahid melissa', 'melissalounis551@gmail.com', '0556603313', 'Québec', '2025-09-19', '', '', '5678', 'Canada', '68d5ad2e8a415_casier_judiciere.pdf', NULL, '68d5ad2e8b2ad_Capture_d_____cran_2025-09-09_000543.jpg', '68d5ad2e8c0a6_casier_judiciere.pdf', '68d5ad2e8c497_casier_judiciere.pdf', '68d5ad2e8c88d_casier_judiciere.pdf', 'nouveau', NULL, '2025-09-25 21:59:26', NULL, '2025-09-25 20:59:26', NULL),
(2, 6, 'chahid melissa', 'kami@gmail.com', '0556603313', 'Ontario', '2003-05-05', 'vfvfvv', 'sdfghjk', '5678', 'Canada', '[\"68f4121bdb891_Capture_d_____cran_2025-09-09_000543.jpg\"]', NULL, '', '', NULL, NULL, 'nouveau', NULL, '2025-10-18 23:18:03', NULL, '2025-10-18 22:18:03', NULL),
(3, 6, 'chahid melissa', 'kami@gmail.com', '0556603313', 'Ontario', '2003-05-05', '', 'sdfghjk', '5678', 'Canada', '[\"68fb83d6ed430_BABYLONE_SERVICES.docx\"]', NULL, '', '', NULL, NULL, 'nouveau', NULL, '2025-10-24 14:49:10', NULL, '2025-10-24 13:49:10', NULL);

--
-- Déclencheurs `attestation_province`
--
DELIMITER $$
CREATE TRIGGER `before_insert_attestation_province` BEFORE INSERT ON `attestation_province` FOR EACH ROW BEGIN
    IF NEW.date_soumission IS NULL THEN
        SET NEW.date_soumission = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `client_profiles`
--

CREATE TABLE `client_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `situation_familiale` enum('célibataire','marié','divorcé','veuf') DEFAULT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `telephone` varchar(60) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaires_demandes`
--

CREATE TABLE `commentaires_demandes` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `commentaire` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0,
  `date_lecture` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `phone`, `subject`, `message`, `date_envoi`, `lu`, `date_lecture`) VALUES
(3, NULL, 'melissa lounis', 'melissalounis551@gmail.com', '0556603313', 'visa', 'zsxdcfghjbklm,;.', '2025-11-13 19:44:59', 0, NULL),
(4, NULL, 'melissa lounis', 'lounismelissa534@gmail.com', '0556603313', 'visa', 'DEFR', '2025-11-21 19:52:06', 0, NULL),
(5, NULL, 'melissa', 'kami@gmail.com', '0556603313', 'visa', 'erthy', '2025-11-21 20:12:28', 1, '2025-11-21 21:12:39');

-- --------------------------------------------------------

--
-- Structure de la table `demandes`
--

CREATE TABLE `demandes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visa_type` varchar(100) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `passeport` varchar(100) DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `photo_identite_path` varchar(500) DEFAULT NULL,
  `passeport_pdf_path` varchar(500) DEFAULT NULL,
  `reservation_hotel_path` varchar(500) DEFAULT NULL,
  `billet_avion_path` varchar(500) DEFAULT NULL,
  `assurance_voyage_path` varchar(500) DEFAULT NULL,
  `justificatif_ressources_path` varchar(500) DEFAULT NULL,
  `lettre_invitation_path` varchar(500) DEFAULT NULL,
  `programme_sejour_path` varchar(500) DEFAULT NULL,
  `attestation_employeur_path` varchar(500) DEFAULT NULL,
  `releves_notes_path` varchar(500) DEFAULT NULL,
  `diplome_path` varchar(500) DEFAULT NULL,
  `cv_path` varchar(500) DEFAULT NULL,
  `lettre_motivation_path` varchar(500) DEFAULT NULL,
  `test_linguistique_path` varchar(500) DEFAULT NULL,
  `niveau_etude` varchar(50) DEFAULT NULL,
  `universite_souhaitee` varchar(255) DEFAULT NULL,
  `programme_etude` varchar(255) DEFAULT NULL,
  `status` enum('nouveau','en_traitement','accepte','refuse') DEFAULT 'nouveau',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes`
--

INSERT INTO `demandes` (`id`, `user_id`, `visa_type`, `nom_complet`, `email`, `telephone`, `date_naissance`, `passeport`, `date_expiration`, `photo_identite_path`, `passeport_pdf_path`, `reservation_hotel_path`, `billet_avion_path`, `assurance_voyage_path`, `justificatif_ressources_path`, `lettre_invitation_path`, `programme_sejour_path`, `attestation_employeur_path`, `releves_notes_path`, `diplome_path`, `cv_path`, `lettre_motivation_path`, `test_linguistique_path`, `niveau_etude`, `universite_souhaitee`, `programme_etude`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 6, 'Admission Canada', 'chahid melissa', 'kami@gmail.com', '0556603313', '2005-07-06', 'Algérie', NULL, '/canada/6/68d51cd80f243_casier judiciere.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nouveau', 'Niveau: licence\nProgramme: informatique\nUniversité: ', '2025-09-25 10:43:36', NULL),
(2, 6, 'Admission Canada', 'chahid melissa', 'kami@gmail.com', '0556603313', '2005-07-06', 'Algérie', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'licence', '', 'informatique', 'nouveau', 'Niveau: licence\nProgramme: informatique\nUniversité: \nDomaine: informatique', '2025-09-25 10:48:05', NULL),
(3, NULL, 'Attestation Province Québec', 'chahid melissa', 'melissalounis551@gmail.com', '0556603313', '2025-09-19', NULL, NULL, '68d5a9bb1dc9f_Capture d’écran 2025-09-09 000543.jpg', '68d5a9bb1d002_casier judiciere.pdf', NULL, NULL, NULL, '68d5a9bb1e2d3_casier judiciere.pdf', '68d5a9bb1e8a1_casier judiciere.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nouveau', NULL, '2025-09-25 20:44:43', NULL),
(4, 6, 'Admission Canada', 'chahid melissa', 'kami@gmail.com', '0556603313', '2025-09-27', 'Algérie', NULL, NULL, NULL, NULL, NULL, NULL, '/canada/6/68dbd96b78343_casier judiciere.pdf', NULL, NULL, NULL, NULL, NULL, '/canada/6/68dbd96b77d34_casier judiciere.pdf', '/canada/6/68dbd96b780f6_casier judiciere.pdf', NULL, 'licence', '', 'informatique', 'nouveau', 'Niveau: licence\nProgramme: informatique\nUniversité: \nDomaine: informatique', '2025-09-30 13:21:47', NULL),
(5, 6, 'Admission Canada', 'chahid melissa', 'kami@gmail.com', '0556603313', '2025-09-27', 'Algérie', NULL, NULL, NULL, NULL, NULL, NULL, '/canada/6/68dbe0c0a8e2f_casier judiciere.pdf', NULL, NULL, NULL, NULL, NULL, '/canada/6/68dbe0c0a8753_casier judiciere.pdf', '/canada/6/68dbe0c0a89d0_casier judiciere.pdf', '/canada/6/68dbe0c0a8c01_casier judiciere.pdf', 'licence', 'ygg', 'informatique', 'nouveau', 'Niveau: licence\nProgramme: informatique\nUniversité: ygg\nDomaine: informatique', '2025-09-30 13:53:04', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_belgique`
--

CREATE TABLE `demandes_belgique` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `naissance` date NOT NULL,
  `nationalite` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `niveau_etude` varchar(20) NOT NULL,
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_belgique`
--

INSERT INTO `demandes_belgique` (`id`, `user_id`, `nom`, `naissance`, `nationalite`, `email`, `niveau_etude`, `statut`, `date_soumission`, `date_modification`) VALUES
(3, 6, 'chahid melissa', '2004-02-04', 'jjjj', 'kami@gmail.com', 'l2', 'en_attente', '2025-09-29 22:18:56', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_belgique_fichiers`
--

CREATE TABLE `demandes_belgique_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_belgique_fichiers`
--

INSERT INTO `demandes_belgique_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 3, 'lettre_admission', '68db05d0e0d63_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(2, 3, 'photo', '68db05d0e2450_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(3, 3, 'certificat_medical', '68db05d0e32af_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(4, 3, 'casier_judiciaire', '68db05d0e58a6_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(5, 3, 'releve_bac', '68db05d0e6741_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(6, 3, 'diplome_bac', '68db05d0e72fc_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(7, 3, 'releve_l1', '68db05d0e7f8c_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56'),
(8, 3, 'certificat_scolarite', '68db05d0e8f2b_Capture d’écran 2025-09-09 000543.jpg', '2025-09-29 22:18:56');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_billets_avion`
--

CREATE TABLE `demandes_billets_avion` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `numero_dossier` varchar(50) DEFAULT NULL,
  `email_contact` varchar(255) NOT NULL,
  `telephone_contact` varchar(50) DEFAULT NULL,
  `type_vol` enum('aller_simple','aller_retour') NOT NULL,
  `pays_depart` varchar(100) NOT NULL,
  `ville_depart` varchar(100) NOT NULL,
  `pays_arrivee` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  `date_depart` date NOT NULL,
  `date_retour` date DEFAULT NULL,
  `classe` varchar(50) NOT NULL,
  `compagnie_preferee` varchar(100) DEFAULT NULL,
  `baggage_main` varchar(50) DEFAULT NULL,
  `baggage_soute` varchar(50) DEFAULT NULL,
  `commentaires` text DEFAULT NULL,
  `statut` varchar(50) DEFAULT 'nouveau',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_billets_avion`
--

INSERT INTO `demandes_billets_avion` (`id`, `user_email`, `numero_dossier`, `email_contact`, `telephone_contact`, `type_vol`, `pays_depart`, `ville_depart`, `pays_arrivee`, `ville_arrivee`, `date_depart`, `date_retour`, `classe`, `compagnie_preferee`, `baggage_main`, `baggage_soute`, `commentaires`, `statut`, `date_soumission`, `date_creation`) VALUES
(1, 'kami@gmail.com', 'BILLET20251024191028397', 'kami@gmail.com', '+213556603313', 'aller_simple', 'Algérie', 'sdfghjk', 'Algérie', 'sdfghjk', '2026-03-31', NULL, 'economique', 'ff', '1_piece', 'aucun', '', 'nouveau', '2025-10-24 18:10:28', '2025-11-22 09:19:55'),
(2, 'kami@gmail.com', 'BILLET20251024193351135', 'kami@gmail.com', '+213556603313', 'aller_simple', 'Algérie', 'sdfghjk', 'Algérie', 'sdfghjk', '2026-03-31', NULL, 'economique', 'ff', '1_piece', 'aucun', '', 'annule', '2025-10-24 18:33:51', '2025-11-22 09:19:55'),
(3, 'kami@gmail.com', 'BILLET20251122114831158', 'kami@gmail.com', '0556603313', 'aller_simple', 'Algérie', 'sdfghjk', 'Algérie', 'sdfghjk', '2026-04-04', NULL, 'economique', '', '1_piece', 'aucun', '', 'en_traitement', '2025-11-22 11:48:31', '2025-11-22 11:48:31'),
(4, 'kami@gmail.com', 'BILLET20251122123459322', 'kami@gmail.com', '0556603313', 'aller_simple', 'Algérie', 'sdfghjk', 'Algérie', 'sdfghjk', '2026-04-04', NULL, 'economique', 'ff', '1_piece', 'aucun', '', 'nouveau', '2025-11-22 12:34:59', '2025-11-22 12:34:59');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_bourse_fichiers`
--

CREATE TABLE `demandes_bourse_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_bourse_fichiers`
--

INSERT INTO `demandes_bourse_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'releves_notes', '6921a02fd9acb_Arezki (5).pdf', '2025-11-22 12:36:15'),
(2, 1, 'diplomes', '6921a02fda881_Arezki (3).pdf', '2025-11-22 12:36:15'),
(3, 1, 'lettres_recommandation', '6921a02fdb47d_Arezki (4).pdf', '2025-11-22 12:36:15'),
(4, 1, 'passeport', '6921a02fdcb3a_Arezki (3).pdf', '2025-11-22 12:36:15'),
(5, 1, 'photo_identite', '6921a02fde0ba_logo.png', '2025-11-22 12:36:15');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_bourse_italie`
--

CREATE TABLE `demandes_bourse_italie` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type_bourse` varchar(50) NOT NULL,
  `niveau_etudes` varchar(50) NOT NULL,
  `domaine_etudes` varchar(255) NOT NULL,
  `universite_choisie` varchar(255) NOT NULL,
  `programme` varchar(255) NOT NULL,
  `duree_etudes` varchar(50) NOT NULL,
  `moyenne` decimal(4,2) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(255) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tests_italien` varchar(50) NOT NULL,
  `tests_anglais` varchar(50) NOT NULL,
  `consentement` tinyint(1) NOT NULL,
  `newsletter` tinyint(1) NOT NULL,
  `statut` enum('en_attente','acceptee','refusee') DEFAULT 'en_attente',
  `notes_admin` text DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_bourse_italie`
--

INSERT INTO `demandes_bourse_italie` (`id`, `user_id`, `type_bourse`, `niveau_etudes`, `domaine_etudes`, `universite_choisie`, `programme`, `duree_etudes`, `moyenne`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `tests_italien`, `tests_anglais`, `consentement`, `newsletter`, `statut`, `notes_admin`, `date_soumission`, `date_traitement`) VALUES
(1, 6, 'merite', 'licence2', 'informatique', 'ergthyjh', 'tt', '3', 18.53, 'melissa', 'admin', '2001-03-31', 'jjjj', 'ffff', 'rtedt', '0556603313', 'kami@gmail.com', 'non', 'non', 1, 1, 'en_attente', NULL, '2025-11-22 12:36:15', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_bourse_statuts`
--

CREATE TABLE `demandes_bourse_statuts` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `statut` enum('en_attente','en_cours','approuvee','refusee') NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_changement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_campus_france`
--

CREATE TABLE `demandes_campus_france` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pays_etudes` varchar(100) DEFAULT NULL,
  `niveau_etudes` varchar(50) DEFAULT NULL,
  `domaine_etudes` varchar(100) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `num_passeport` varchar(50) DEFAULT NULL,
  `date_delivrance` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `niveau_francais` varchar(50) DEFAULT NULL,
  `tests_francais` varchar(50) DEFAULT NULL,
  `score_test` varchar(50) DEFAULT NULL,
  `test_anglais` varchar(50) DEFAULT NULL,
  `score_anglais` varchar(50) DEFAULT NULL,
  `boite_pastel` varchar(10) DEFAULT NULL,
  `email_pastel` varchar(100) DEFAULT NULL,
  `mdp_pastel` varchar(100) DEFAULT NULL,
  `releves_annees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`releves_annees`)),
  `autres_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`autres_documents`)),
  `statut` varchar(20) DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_campus_france`
--

INSERT INTO `demandes_campus_france` (`id`, `user_id`, `pays_etudes`, `niveau_etudes`, `domaine_etudes`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `num_passeport`, `date_delivrance`, `date_expiration`, `niveau_francais`, `tests_francais`, `score_test`, `test_anglais`, `score_anglais`, `boite_pastel`, `email_pastel`, `mdp_pastel`, `releves_annees`, `autres_documents`, `statut`, `created_at`, `date_creation`, `date_modification`, `date_soumission`) VALUES
(1, 6, 'France', 'inge', 'de', 'melissa', 'admin', '2002-03-31', 'ffff', 'jkjkkj', 'fv', '0556603313', 'kami@gmail.com', '4456566666', '2024-03-31', '2027-02-22', '', 'non', '', 'non', '', 'non', '', '', '{\"1\":{\"annee\":\"Ann\\u00e9e du Bac\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"2\":{\"annee\":\"1\\u00e8re ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"3\":{\"annee\":\"2\\u00e8me ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"jj\"}}', '[]', 'en_attente', '2025-11-03 21:28:27', '2025-11-03 22:28:27', NULL, '2025-11-03 21:28:27'),
(2, 6, 'France', 'inge', 'de', 'melissa', 'admin', '2002-03-31', 'ffff', 'jkjkkj', 'fv', '0556603313', 'kami@gmail.com', '4456566666', '2024-03-31', '2027-02-22', '', 'non', '', 'non', '', 'non', '', '', '{\"1\":{\"annee\":\"Ann\\u00e9e du Bac\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"2\":{\"annee\":\"1\\u00e8re ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"3\":{\"annee\":\"2\\u00e8me ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"jj\"}}', '[]', 'en_attente', '2025-11-03 21:37:45', '2025-11-03 22:37:45', NULL, '2025-11-03 21:37:45'),
(3, 6, 'France', 'inge', 'de', 'melissa', 'admin', '2002-03-31', 'ffff', 'jkjkkj', 'fv', '0556603313', 'kami@gmail.com', '4456566666', '2024-03-31', '2027-02-22', '', 'non', '', 'non', '', 'non', '', '', '{\"1\":{\"annee\":\"Ann\\u00e9e du Bac\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"2\":{\"annee\":\"1\\u00e8re ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"3\":{\"annee\":\"2\\u00e8me ann\\u00e9e ing\\u00e9nieur\",\"moyenne\":\"14\",\"mention\":\"jj\"}}', '[]', 'en_attente', '2025-11-03 21:43:54', '2025-11-03 22:43:54', NULL, '2025-11-03 21:43:54');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_campus_france_fichiers`
--

CREATE TABLE `demandes_campus_france_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `nom_original` varchar(255) NOT NULL,
  `taille_fichier` int(11) DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demandes_campus_france_fichiers`
--

INSERT INTO `demandes_campus_france_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `nom_original`, `taille_fichier`, `date_upload`) VALUES
(1, 3, 'copie_passeport', '6909221a4f838_casier judiciere.pdf', '', NULL, '2025-11-03 21:43:54'),
(2, 3, 'photo_identite', '6909221a50cd3_Capture.PNG', '', NULL, '2025-11-03 21:43:54'),
(3, 3, 'certificat_scolarite', '6909221a5223f_BABYLONE SERVICES2.docx', '', NULL, '2025-11-03 21:43:54'),
(4, 3, 'lettre_motivation', '6909221a541f3_BABYLONE SERVICES2.docx', '', NULL, '2025-11-03 21:43:54'),
(5, 3, 'cv', '6909221a5510d_BABYLONE SERVICES2.docx', '', NULL, '2025-11-03 21:43:54'),
(6, 3, 'releve_annee_1', '6909221a56d11_casier judiciere.pdf', '', NULL, '2025-11-03 21:43:54'),
(7, 3, 'releve_annee_2', '6909221a57a55_casier judiciere.pdf', '', NULL, '2025-11-03 21:43:54'),
(8, 3, 'releve_annee_3', '6909221a587af_casier judiciere.pdf', '', NULL, '2025-11-03 21:43:54');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_canada`
--

CREATE TABLE `demandes_canada` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `nom_famille` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `naissance` date DEFAULT NULL,
  `nationalite` varchar(50) DEFAULT NULL,
  `pays_residence` varchar(50) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `niveau_etude` enum('bac','l1','l2','l3','master','doctorat') NOT NULL,
  `etablissement_origine` varchar(200) DEFAULT NULL,
  `diplome_obtenu` varchar(200) DEFAULT NULL,
  `moyenne_generale` decimal(4,2) DEFAULT NULL,
  `domaine_etudes` varchar(100) DEFAULT NULL,
  `universite_canada` varchar(200) DEFAULT NULL,
  `programme_canada` varchar(200) DEFAULT NULL,
  `ville_etude` varchar(100) DEFAULT NULL,
  `province_etude` varchar(100) DEFAULT NULL,
  `duree_etudes` int(11) DEFAULT NULL,
  `date_debut_etudes` date DEFAULT NULL,
  `langue_maternelle` varchar(50) DEFAULT NULL,
  `test_linguistique` enum('ielts','toefl','tef','tcf','autre','aucun') DEFAULT NULL,
  `score_linguistique` decimal(4,1) DEFAULT NULL,
  `date_test_linguistique` date DEFAULT NULL,
  `source_financement` enum('personnel','familial','bourse','pret','autre') DEFAULT NULL,
  `montant_financement` decimal(10,2) DEFAULT NULL,
  `preuve_financement` tinyint(1) DEFAULT 0,
  `statut` enum('en_attente','en_cours','approuve','refuse') DEFAULT 'en_attente',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL,
  `date_traitement` datetime DEFAULT NULL,
  `commentaires_agent` text DEFAULT NULL,
  `notes_internes` text DEFAULT NULL,
  `passeport_valide` tinyint(1) DEFAULT 0,
  `date_expiration_passeport` date DEFAULT NULL,
  `casier_judiciaire` tinyint(1) DEFAULT 0,
  `certificat_medical` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déclencheurs `demandes_canada`
--
DELIMITER $$
CREATE TRIGGER `after_demandes_canada_status_change` AFTER UPDATE ON `demandes_canada` FOR EACH ROW BEGIN
    IF OLD.statut != NEW.statut THEN
        INSERT INTO demandes_canada_historique 
        (demande_id, ancien_statut, nouveau_statut, agent_id)
        VALUES 
        (NEW.id, OLD.statut, NEW.statut, NULL); -- agent_id peut être NULL pour les changements automatiques
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_demandes_canada_update` BEFORE UPDATE ON `demandes_canada` FOR EACH ROW BEGIN
    SET NEW.date_modification = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_canada_fichiers`
--

CREATE TABLE `demandes_canada_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` enum('passeport','photo','lettre_motivation','releve_notes','diplome','test_linguistique','cv','lettre_recommandation','preuve_finance','casier_judiciaire','certificat_medical','lettre_admission','autres') NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille_fichier` int(11) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_canada_historique`
--

CREATE TABLE `demandes_canada_historique` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `ancien_statut` enum('en_attente','en_cours','approuve','refuse') DEFAULT NULL,
  `nouveau_statut` enum('en_attente','en_cours','approuve','refuse') NOT NULL,
  `date_changement` datetime DEFAULT current_timestamp(),
  `agent_id` int(11) DEFAULT NULL,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_caq`
--

CREATE TABLE `demandes_caq` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `pays_origine` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays_residence` varchar(100) DEFAULT NULL,
  `etablissement_quebec` varchar(255) DEFAULT NULL,
  `programme_etudes` varchar(255) DEFAULT NULL,
  `duree_etudes` varchar(50) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `passeport_path` varchar(500) NOT NULL,
  `photos_identite_path` varchar(500) NOT NULL,
  `releves_bancaires_path` varchar(500) NOT NULL,
  `lettre_acceptation_path` varchar(500) NOT NULL,
  `preuve_fonds_path` text DEFAULT NULL,
  `diplomes_path` varchar(500) DEFAULT NULL,
  `test_francais_path` varchar(500) DEFAULT NULL,
  `autres_documents_path` varchar(500) DEFAULT NULL,
  `statut` enum('nouveau','en_traitement','approuve','refuse') DEFAULT 'nouveau',
  `notes_admin` text DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_caq`
--

INSERT INTO `demandes_caq` (`id`, `user_id`, `nom_complet`, `email`, `telephone`, `date_naissance`, `pays_origine`, `adresse`, `ville`, `code_postal`, `pays_residence`, `etablissement_quebec`, `programme_etudes`, `duree_etudes`, `date_debut`, `passeport_path`, `photos_identite_path`, `releves_bancaires_path`, `lettre_acceptation_path`, `preuve_fonds_path`, `diplomes_path`, `test_francais_path`, `autres_documents_path`, `statut`, `notes_admin`, `date_soumission`, `date_traitement`, `created_at`, `updated_at`) VALUES
(1, 6, 'chahid melissa', 'kami@gmail.com', '0556603313', '2025-09-20', 'Algérie', 'vfvfvv', 'sdfghjk', '5678', 'Algérie', 'ummto', 'madgf', '6 mois', '2025-09-27', '68d5b40862c95_casier_judiciere.pdf', '68d5b40862fe1_Capture_d_____cran_2025-09-09_000543.jpg', '68d5b40863484_casier_judiciere.pdf', '68d5b40863a83_casier_judiciere.pdf', NULL, '68d5b408641e2_casier_judiciere.pdf', '68d5b40864896_casier_judiciere.pdf', '68d5b40864e21_casier_judiciere.pdf', 'nouveau', NULL, '2025-09-25 22:28:40', NULL, '2025-09-25 21:28:40', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_contrat_travail`
--

CREATE TABLE `demandes_contrat_travail` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom_complet` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `domaine_competence` varchar(100) DEFAULT NULL,
  `niveau_etude` varchar(50) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `pays_recherche` varchar(100) DEFAULT NULL,
  `type_contrat` varchar(50) DEFAULT NULL,
  `competences` text DEFAULT NULL,
  `langues` text DEFAULT NULL,
  `a_cv` enum('oui','non') DEFAULT NULL,
  `cv` varchar(255) DEFAULT NULL,
  `lettre_motivation` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','en_cours','approuvee','rejetee') DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_contrat_travail`
--

INSERT INTO `demandes_contrat_travail` (`id`, `user_id`, `nom_complet`, `email`, `telephone`, `domaine_competence`, `niveau_etude`, `experience`, `pays_recherche`, `type_contrat`, `competences`, `langues`, `a_cv`, `cv`, `lettre_motivation`, `statut`, `date_soumission`, `notes_admin`) VALUES
(1, 6, 'chahid melissa', 'melissalounis551@gmail.com', '+213556603313', 'informatique', 'sans_diplome', 3, 'france', 'cdi', '', '', 'non', NULL, NULL, 'en_attente', '2025-10-14 10:27:19', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_court_sejour`
--

CREATE TABLE `demandes_court_sejour` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visa_type` varchar(50) NOT NULL,
  `pays_destination` varchar(100) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(255) NOT NULL,
  `etat_civil` varchar(50) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `profession` varchar(255) NOT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passeport` varchar(100) NOT NULL,
  `pays_delivrance` varchar(100) NOT NULL,
  `date_delivrance` date NOT NULL,
  `date_expiration` date NOT NULL,
  `a_deja_visa` enum('oui','non') DEFAULT 'non',
  `nb_visas` int(11) DEFAULT 0,
  `details_voyages` text DEFAULT NULL,
  `nom_hote` varchar(255) DEFAULT NULL,
  `adresse_hote` text DEFAULT NULL,
  `telephone_hote` varchar(50) DEFAULT NULL,
  `email_hote` varchar(255) DEFAULT NULL,
  `lien_parente` varchar(100) DEFAULT NULL,
  `entreprise_origine` varchar(255) DEFAULT NULL,
  `poste` varchar(255) DEFAULT NULL,
  `adresse_entreprise` text DEFAULT NULL,
  `tel_entreprise` varchar(50) DEFAULT NULL,
  `email_entreprise` varchar(255) DEFAULT NULL,
  `entreprise_destination` varchar(255) DEFAULT NULL,
  `adresse_entreprise_destination` text DEFAULT NULL,
  `contact_destination` varchar(255) DEFAULT NULL,
  `tel_contact_destination` varchar(50) DEFAULT NULL,
  `objet_mission` text DEFAULT NULL,
  `debut_mission` date DEFAULT NULL,
  `fin_mission` date DEFAULT NULL,
  `date_arrivee` date DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `hebergement_type` varchar(50) DEFAULT NULL,
  `itineraire` text DEFAULT NULL,
  `statut` enum('en_attente','en_cours','approuve','refuse') DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_maj` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demandes_court_sejour`
--

INSERT INTO `demandes_court_sejour` (`id`, `user_id`, `visa_type`, `pays_destination`, `nom_complet`, `date_naissance`, `lieu_naissance`, `etat_civil`, `nationalite`, `profession`, `adresse`, `telephone`, `email`, `passeport`, `pays_delivrance`, `date_delivrance`, `date_expiration`, `a_deja_visa`, `nb_visas`, `details_voyages`, `nom_hote`, `adresse_hote`, `telephone_hote`, `email_hote`, `lien_parente`, `entreprise_origine`, `poste`, `adresse_entreprise`, `tel_entreprise`, `email_entreprise`, `entreprise_destination`, `adresse_entreprise_destination`, `contact_destination`, `tel_contact_destination`, `objet_mission`, `debut_mission`, `fin_mission`, `date_arrivee`, `date_depart`, `hebergement_type`, `itineraire`, `statut`, `date_creation`, `date_maj`) VALUES
(1, 6, 'tourisme', 'Algérie', 'chahid melissa', '2004-10-14', 'ttttt', 'celibataire', 'jkjkkj', 'ffffffff', 'vfvfvv', '0556603313', 'kami@gmail.com', '4567890', 'Canada', '2023-02-04', '2033-02-04', 'non', 0, '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en_attente', '2025-10-10 22:20:17', '2025-10-10 22:20:17'),
(2, 6, 'tourisme', 'Algérie', 'chahid melissa', '2004-10-14', 'ttttt', 'marie', 'jkjkkj', 'ffffffff', 'vfvfvv', '0556603313', 'kami@gmail.com', '4567890', 'Canada', '2023-02-04', '2033-02-04', 'non', 0, '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'en_attente', '2025-10-18 00:26:28', '2025-10-18 00:26:28');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_court_sejour_fichiers`
--

CREATE TABLE `demandes_court_sejour_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demandes_court_sejour_fichiers`
--

INSERT INTO `demandes_court_sejour_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'copie_passeport', '68e97891d31d8_casier judiciere.pdf', '2025-10-10 22:20:17'),
(2, 1, 'billet_avion', '68e97891d4eef_casier judiciere.pdf', '2025-10-10 22:20:17'),
(3, 1, 'documents_travail', '68e97891d5e97_casier judiciere.pdf', '2025-10-10 22:20:17'),
(4, 2, 'copie_passeport', '68f2d0a44df79_casier judiciere.pdf', '2025-10-18 00:26:28'),
(5, 2, 'billet_avion', '68f2d0a44ed30_casier judiciere.pdf', '2025-10-18 00:26:28');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_creation_cv`
--

CREATE TABLE `demandes_creation_cv` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `adresse` text DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `situation_familiale` varchar(50) DEFAULT NULL,
  `formations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`formations`)),
  `experiences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`experiences`)),
  `competences_techniques` text DEFAULT NULL,
  `competences_linguistiques` text DEFAULT NULL,
  `competences_interpersonnelles` text DEFAULT NULL,
  `centres_interet` text DEFAULT NULL,
  `statut` enum('en_traitement','traitee','annulee') DEFAULT 'en_traitement',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_creation_cv`
--

INSERT INTO `demandes_creation_cv` (`id`, `user_id`, `nom_complet`, `email`, `telephone`, `adresse`, `date_naissance`, `nationalite`, `situation_familiale`, `formations`, `experiences`, `competences_techniques`, `competences_linguistiques`, `competences_interpersonnelles`, `centres_interet`, `statut`, `date_creation`, `notes_admin`) VALUES
(1, 6, 'chahid melissa', 'kami@gmail.com', '0556603313', 'vfvfvv', '2002-10-17', 'jkjkkj', 'celibataire', '[{\"diplome\":\"yuk\",\"etablissement\":\"jj\",\"annee_obtention\":\"2007\",\"description\":\"dgfh\"}]', '[{\"poste\":\"chgvjbknkm\",\"entreprise\":\"gvjhbjnm\",\"date_debut\":\"2025-10-17\",\"date_fin\":\"2025-10-15\",\"description\":\"cgvjhbkn\"}]', 'jgvhbkjn', 'hbjknkm,', 'tfghjkjnkm', 'hkjklml,', 'traitee', '2025-10-14 10:55:16', '');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_ecoles_privees`
--

CREATE TABLE `demandes_ecoles_privees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `situation_familiale` varchar(50) DEFAULT NULL,
  `profession_candidat` varchar(100) DEFAULT NULL,
  `categorie_socio_pro` varchar(50) DEFAULT NULL,
  `niveau_etudes` varchar(50) DEFAULT NULL,
  `domaine_etudes` varchar(100) DEFAULT NULL,
  `budget_etudes` varchar(20) DEFAULT NULL,
  `session_formation` varchar(50) DEFAULT NULL,
  `type_piece_identite` varchar(50) DEFAULT NULL,
  `num_piece_identite` varchar(100) DEFAULT NULL,
  `date_delivrance_piece` date DEFAULT NULL,
  `date_expiration_piece` date DEFAULT NULL,
  `nom_pere` varchar(100) DEFAULT NULL,
  `prenom_pere` varchar(100) DEFAULT NULL,
  `profession_pere` varchar(100) DEFAULT NULL,
  `employeur_pere` varchar(100) DEFAULT NULL,
  `csp_pere` varchar(50) DEFAULT NULL,
  `nom_mere` varchar(100) DEFAULT NULL,
  `prenom_mere` varchar(100) DEFAULT NULL,
  `profession_mere` varchar(100) DEFAULT NULL,
  `employeur_mere` varchar(100) DEFAULT NULL,
  `csp_mere` varchar(50) DEFAULT NULL,
  `a_garant_france` enum('oui','non') DEFAULT 'non',
  `nom_garant` varchar(100) DEFAULT NULL,
  `prenom_garant` varchar(100) DEFAULT NULL,
  `adresse_garant` text DEFAULT NULL,
  `telephone_garant` varchar(20) DEFAULT NULL,
  `email_garant` varchar(150) DEFAULT NULL,
  `lien_parente_garant` varchar(100) DEFAULT NULL,
  `tests_francais` varchar(50) DEFAULT 'non',
  `score_test` varchar(50) DEFAULT NULL,
  `test_anglais` varchar(50) DEFAULT 'non',
  `score_anglais` varchar(50) DEFAULT NULL,
  `boite_pastel` enum('oui','non') DEFAULT 'non',
  `email_pastel` varchar(150) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_ecoles_privees_fichiers`
--

CREATE TABLE `demandes_ecoles_privees_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_equivalences`
--

CREATE TABLE `demandes_equivalences` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `universite_origine` varchar(200) NOT NULL,
  `diplome_origine` varchar(200) NOT NULL,
  `filiere_demandee` varchar(200) NOT NULL,
  `documents` varchar(500) DEFAULT NULL,
  `fichiers_joints` varchar(500) DEFAULT NULL,
  `statut` enum('en_attente','approuvee','rejetee') DEFAULT 'en_attente',
  `date_demande` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_equivalences`
--

INSERT INTO `demandes_equivalences` (`id`, `nom`, `prenom`, `email`, `telephone`, `universite_origine`, `diplome_origine`, `filiere_demandee`, `documents`, `fichiers_joints`, `statut`, `date_demande`, `notes`) VALUES
(1, 'melissa', 'admin', 'kami@gmail.com', '0556603313', 'fff', 'fff', 'fff', 'Diplôme: casier judiciere.pdf; Relevé de notes: casier judiciere.pdf', NULL, 'en_attente', '2025-10-31 13:15:07', '');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etablissements_non_connectes`
--

CREATE TABLE `demandes_etablissements_non_connectes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pays_etudes` varchar(100) NOT NULL,
  `niveau_etudes` varchar(50) NOT NULL,
  `domaine_etudes` varchar(100) NOT NULL,
  `nom_formation` varchar(200) DEFAULT NULL,
  `etablissement` varchar(200) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `duree_etudes` varchar(50) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `num_passeport` varchar(50) NOT NULL,
  `date_delivrance` date NOT NULL,
  `date_expiration` date NOT NULL,
  `niveau_francais` varchar(50) DEFAULT NULL,
  `tests_francais` varchar(20) DEFAULT 'non',
  `score_test` varchar(50) DEFAULT NULL,
  `dernier_diplome` varchar(200) NOT NULL,
  `etablissement_origine` varchar(200) NOT NULL,
  `moyenne_derniere_annee` decimal(5,2) DEFAULT NULL,
  `releves_annees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`releves_annees`)),
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_etablissements_non_connectes`
--

INSERT INTO `demandes_etablissements_non_connectes` (`id`, `user_id`, `pays_etudes`, `niveau_etudes`, `domaine_etudes`, `nom_formation`, `etablissement`, `date_debut`, `duree_etudes`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `num_passeport`, `date_delivrance`, `date_expiration`, `niveau_francais`, `tests_francais`, `score_test`, `dernier_diplome`, `etablissement_origine`, `moyenne_derniere_annee`, `releves_annees`, `statut`, `date_creation`, `date_modification`) VALUES
(1, 6, 'France', 'licence2', 'informatique', 'Babylone', 'ummto', '2025-09-26', '2ans', 'melissa', 'chahid', '2025-09-26', 'ttttt', 'jkjkkj', 'vfvfvv', '0556603313', 'kami@gmail.com', '4567890', '2025-09-26', '2025-09-26', 'avance', 'non', '', 'bac', 'lycee', 15.00, '{\"1\":{\"annee\":\"Ann\\u00e9e du Bac\",\"moyenne\":\"14\",\"mention\":\"bien\"},\"2\":{\"annee\":\"Licence 1\",\"moyenne\":\"14\",\"mention\":\"bien\"}}', 'en_attente', '2025-09-28 12:43:55', '2025-09-28 12:43:55');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etablissements_non_connectes_fichiers`
--

CREATE TABLE `demandes_etablissements_non_connectes_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_etablissements_non_connectes_fichiers`
--

INSERT INTO `demandes_etablissements_non_connectes_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'copie_passeport', '68d92d8b95ce1_casier judiciere.pdf', '2025-09-28 12:43:55'),
(2, 1, 'diplomes', '68d92d8b977b7_casier judiciere.pdf', '2025-09-28 12:43:55'),
(3, 1, 'releves_notes', '68d92d8b99066_casier judiciere.pdf', '2025-09-28 12:43:55'),
(4, 1, 'releve_annee_1', '68d92d8b9a65f_casier judiciere.pdf', '2025-09-28 12:43:55'),
(5, 1, 'releve_annee_2', '68d92d8b9b7b9_casier judiciere.pdf', '2025-09-28 12:43:55');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etudes_bulgarie`
--

CREATE TABLE `demandes_etudes_bulgarie` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `programme` enum('anglais','preparatoire') DEFAULT NULL,
  `niveau_etude` enum('l1','l2','l3','m1','m2') DEFAULT NULL,
  `passeport` varchar(255) DEFAULT NULL,
  `justificatif_financier` varchar(255) DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `documents_supplementaires` text DEFAULT NULL,
  `test_en` text DEFAULT NULL,
  `certificat_scolarite` text DEFAULT NULL,
  `certificat_medical` text DEFAULT NULL,
  `casier_judiciaire` text DEFAULT NULL,
  `releves_lycee` text DEFAULT NULL,
  `releves_l1` text DEFAULT NULL,
  `releves_l1_l2` text DEFAULT NULL,
  `releves_licence` text DEFAULT NULL,
  `releves_m1` text DEFAULT NULL,
  `demande_test` tinyint(1) DEFAULT 0,
  `statut` enum('nouveau','en_cours','approuve','refuse') DEFAULT 'nouveau',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_etudes_bulgarie`
--

INSERT INTO `demandes_etudes_bulgarie` (`id`, `nom_complet`, `email`, `telephone`, `programme`, `niveau_etude`, `passeport`, `justificatif_financier`, `photos`, `documents_supplementaires`, `test_en`, `certificat_scolarite`, `certificat_medical`, `casier_judiciaire`, `releves_lycee`, `releves_l1`, `releves_l1_l2`, `releves_licence`, `releves_m1`, `demande_test`, `statut`, `date_soumission`, `date_modification`) VALUES
(1, 'chahid melissa', 'kami@gmail.com', '0556603313', 'preparatoire', 'l2', '6908a1a3b82a0_BABYLONE SERVICES2.docx', '6908a1a3b885f_BABYLONE SERVICES2.docx', '6908a1a3b8c73_BABYLONE SERVICES2.docx', '', '', '6908a1a3b8f92_BABYLONE SERVICES2.docx', '6908a1a3b91fc_BABYLONE SERVICES2.docx', '6908a1a3b94fa_BABYLONE SERVICES2.docx', NULL, NULL, NULL, NULL, NULL, 1, 'nouveau', '2025-11-03 12:35:47', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etudes_canada`
--

CREATE TABLE `demandes_etudes_canada` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `province` enum('quebec','ontario','colombie_britannique','alberta','manitoba','saskatchewan','nouvelle_ecosse','nouveau_brunswick','terre_neuve','ile_du_prince_edouard') DEFAULT NULL,
  `niveau_etude` enum('bac','dec','dep','aec','maitrise','phd','technique','langue') DEFAULT NULL,
  `passeport` varchar(255) DEFAULT NULL,
  `acte_naissance` varchar(255) DEFAULT NULL,
  `test_langue` varchar(255) DEFAULT NULL,
  `cv` varchar(255) DEFAULT NULL,
  `releve_notes` varchar(255) DEFAULT NULL,
  `diplome_fin_etudes` varchar(255) DEFAULT NULL,
  `releve_bac` varchar(255) DEFAULT NULL,
  `diplome_bac` varchar(255) DEFAULT NULL,
  `releves_universitaires` varchar(255) DEFAULT NULL,
  `releve_maitrise` varchar(255) DEFAULT NULL,
  `diplome_maitrise` varchar(255) DEFAULT NULL,
  `projet_recherche` varchar(255) DEFAULT NULL,
  `cv_academique` varchar(255) DEFAULT NULL,
  `certificat_scolarite` varchar(255) DEFAULT NULL,
  `attestation_province` varchar(255) DEFAULT NULL,
  `documents_supplementaires` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_supplementaires`)),
  `statut` enum('nouveau','en_cours','approuve','refuse') DEFAULT 'nouveau',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_etudes_canada`
--

INSERT INTO `demandes_etudes_canada` (`id`, `nom_complet`, `date_naissance`, `nationalite`, `email`, `telephone`, `province`, `niveau_etude`, `passeport`, `acte_naissance`, `test_langue`, `cv`, `releve_notes`, `diplome_fin_etudes`, `releve_bac`, `diplome_bac`, `releves_universitaires`, `releve_maitrise`, `diplome_maitrise`, `projet_recherche`, `cv_academique`, `certificat_scolarite`, `attestation_province`, `documents_supplementaires`, `statut`, `date_soumission`, `date_modification`) VALUES
(1, 'chahid melissa', '2002-03-31', 'jkjkkj', 'kami@gmail.com', NULL, 'terre_neuve', 'bac', '6908815ba1cda_9968f6d5ab869c78.pdf', '6908815ba233e_f01b5ea82843755d.pdf', NULL, NULL, '6908815ba29fd_9b1b2245cd20e9c4.pdf', '6908815ba3efb_2b696016beca517f.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '6908815ba47dd_fc08f6b4377046f9.pdf', NULL, NULL, 'nouveau', '2025-11-03 10:18:03', NULL),
(2, 'chahid melissa', '2002-03-31', 'jkjkkj', 'kami@gmail.com', NULL, 'terre_neuve', 'bac', '690881cf443a0_67a288c4518bf5b7.pdf', '690881cf449e4_4a45726296229aec.pdf', NULL, NULL, '690881cf4574f_87a85fa1d5822a20.pdf', '690881cf45f74_9524bd9c5ad09a32.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '690881cf4655f_37559767a17e4f9f.pdf', NULL, NULL, 'nouveau', '2025-11-03 10:19:59', NULL),
(3, 'chahid melissa', '2002-03-31', 'jkjkkj', 'kami@gmail.com', NULL, 'terre_neuve', 'bac', '6908877605e5c_f5c0d60f4e24d502.pdf', '6908877606647_d65641162cf63357.pdf', NULL, NULL, '69088776074e1_a27a217635756721.pdf', '6908877607e10_090909faf465ea44.pdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '69088776084db_95715dde0605a859.pdf', NULL, NULL, 'nouveau', '2025-11-03 10:44:06', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etudes_espagne`
--

CREATE TABLE `demandes_etudes_espagne` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `niveau_etude` varchar(50) NOT NULL,
  `universite_souhaitee` varchar(255) DEFAULT NULL,
  `programme_etude` varchar(255) DEFAULT NULL,
  `type_service` varchar(50) DEFAULT 'admission',
  `lettre_admission` varchar(500) DEFAULT NULL,
  `photo` varchar(500) DEFAULT NULL,
  `certificat_medical` varchar(500) DEFAULT NULL,
  `casier_judiciaire` varchar(500) DEFAULT NULL,
  `releve_2nde` varchar(500) DEFAULT NULL,
  `releve_1ere` varchar(500) DEFAULT NULL,
  `releve_terminale` varchar(500) DEFAULT NULL,
  `releve_bac` varchar(500) DEFAULT NULL,
  `diplome_bac` varchar(500) DEFAULT NULL,
  `releve_l1` varchar(500) DEFAULT NULL,
  `releve_l2` varchar(500) DEFAULT NULL,
  `releve_l3` varchar(500) DEFAULT NULL,
  `diplome_licence` varchar(500) DEFAULT NULL,
  `certificat_scolarite` varchar(500) DEFAULT NULL,
  `statut` enum('nouveau','en_traitement','approuve','refuse') DEFAULT 'nouveau',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `notes_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demandes_etudes_espagne`
--

INSERT INTO `demandes_etudes_espagne` (`id`, `nom_complet`, `date_naissance`, `nationalite`, `email`, `niveau_etude`, `universite_souhaitee`, `programme_etude`, `type_service`, `lettre_admission`, `photo`, `certificat_medical`, `casier_judiciaire`, `releve_2nde`, `releve_1ere`, `releve_terminale`, `releve_bac`, `diplome_bac`, `releve_l1`, `releve_l2`, `releve_l3`, `diplome_licence`, `certificat_scolarite`, `statut`, `date_soumission`, `date_traitement`, `notes_admin`) VALUES
(1, 'chahid melissa', '2025-09-26', 'jjjj', 'kami@gmail.com', 'l2', '', 'madgf', 'admission', NULL, '68d5bab5948d9_Capture_d_____cran_2025_09_09_000543.jpg', '68d5bab594dbb_casier_judiciere.pdf', '68d5bab595451_casier_judiciere.pdf', NULL, NULL, NULL, '68d5bab595ce1_casier_judiciere.pdf', '68d5bab5960e7_casier_judiciere.pdf', '68d5bab59651b_casier_judiciere.pdf', NULL, NULL, NULL, '68d5bab59695f_casier_judiciere.pdf', 'nouveau', '2025-09-25 22:57:09', NULL, NULL),
(2, 'chahid melissa', '2025-09-26', 'jjjj', 'kami@gmail.com', 'l2', '', 'madgf', 'admission', NULL, '68d5c3ce7d858_Capture_d_____cran_2025_09_09_000543.jpg', '68d5c3ce7e143_casier_judiciere.pdf', '68d5c3ce7ec88_casier_judiciere.pdf', NULL, NULL, NULL, '68d5c3ce7f274_casier_judiciere.pdf', '68d5c3ce7f780_casier_judiciere.pdf', '68d5c3ce7fabc_casier_judiciere.pdf', NULL, NULL, NULL, '68d5c3ce7fe38_casier_judiciere.pdf', 'nouveau', '2025-09-25 23:35:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_etudes_roumanie`
--

CREATE TABLE `demandes_etudes_roumanie` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `specialite` varchar(255) NOT NULL,
  `programme_langue` varchar(50) NOT NULL,
  `certificat_type` varchar(50) DEFAULT NULL,
  `certificat_score` varchar(50) DEFAULT NULL,
  `certificat_file` varchar(500) DEFAULT NULL,
  `releve_2nde` varchar(500) DEFAULT NULL,
  `releve_1ere` varchar(500) DEFAULT NULL,
  `releve_terminale` varchar(500) DEFAULT NULL,
  `releve_bac` varchar(500) DEFAULT NULL,
  `diplome_bac` varchar(500) DEFAULT NULL,
  `releve_l1` varchar(500) DEFAULT NULL,
  `releve_l2` varchar(500) DEFAULT NULL,
  `releve_l3` varchar(500) DEFAULT NULL,
  `diplome_licence` varchar(500) DEFAULT NULL,
  `certificat_scolarite` varchar(500) DEFAULT NULL,
  `niveau_etude` varchar(50) NOT NULL,
  `statut` enum('nouveau','en_traitement','approuve','refuse') DEFAULT 'nouveau',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `notes_admin` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demandes_etudes_roumanie`
--

INSERT INTO `demandes_etudes_roumanie` (`id`, `nom_complet`, `email`, `telephone`, `specialite`, `programme_langue`, `certificat_type`, `certificat_score`, `certificat_file`, `releve_2nde`, `releve_1ere`, `releve_terminale`, `releve_bac`, `diplome_bac`, `releve_l1`, `releve_l2`, `releve_l3`, `diplome_licence`, `certificat_scolarite`, `niveau_etude`, `statut`, `date_soumission`, `date_traitement`, `notes_admin`, `commentaire`) VALUES
(1, 'melissa', 'kami@gmail.com', '0556603313', 'bb', 'roumain', '', '', NULL, NULL, NULL, NULL, '68d5d49a1d8f4_casier judiciere.pdf', '68d5d49a1dbfa_casier judiciere.pdf', NULL, NULL, NULL, NULL, '68d5d49a20930_casier judiciere.pdf', 'l2', 'nouveau', '2025-09-26 00:47:38', NULL, NULL, NULL),
(2, 'melissa', 'kami@gmail.com', '0556603313', 'bb', 'roumain', '', '', NULL, NULL, NULL, NULL, '68daf3f6d30ef_casier judiciere.pdf', '68daf3f6d35ec_casier judiciere.pdf', NULL, NULL, NULL, NULL, '68daf3f6d3a61_casier judiciere.pdf', 'l2', 'nouveau', '2025-09-29 22:02:46', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_green_card`
--

CREATE TABLE `demandes_green_card` (
  `id` int(11) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `adresse` text NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays_residence` varchar(100) NOT NULL,
  `situation_familiale` enum('celibataire','marie','divorce','veuf') NOT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `profession` varchar(100) DEFAULT NULL,
  `employeur` varchar(100) DEFAULT NULL,
  `revenu_annuel` decimal(10,2) DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `statut` enum('en_attente','en_cours','complet','approuve','refuse') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_green_card`
--

INSERT INTO `demandes_green_card` (`id`, `reference`, `nom`, `prenom`, `date_naissance`, `nationalite`, `email`, `telephone`, `adresse`, `ville`, `code_postal`, `pays_residence`, `situation_familiale`, `nombre_enfants`, `profession`, `employeur`, `revenu_annuel`, `date_soumission`, `statut`) VALUES
(1, 'GC-20251005-68E24A330B4A7', 'melissa', 'chahid', '2002-10-02', 'hhh', 'melissalounis551@gmail.com', '0556603313', 'kami@gmail.com', 'sdfghjk', '5678', 'Canada', 'celibataire', 0, 'hhhh', 'chahid', 3.00, '2025-10-05 11:36:35', 'en_attente'),
(2, 'GC-20251005-68E24A689A301', 'melissa', 'chahid', '2002-10-02', 'hhh', 'melissalounis551@gmail.com', '0556603313', 'kami@gmail.com', 'sdfghjk', '5678', 'Canada', 'celibataire', 0, 'hhhh', 'chahid', 3.00, '2025-10-05 11:37:28', 'en_attente'),
(3, 'GC-20251005-68E24A9DAFCE4', 'melissa', 'chahid', '2002-10-02', 'hhh', 'melissalounis551@gmail.com', '0556603313', 'kami@gmail.com', 'sdfghjk', '5678', 'Canada', 'celibataire', 0, 'hhhh', 'chahid', 3.00, '2025-10-05 11:38:21', 'en_attente');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_italie`
--

CREATE TABLE `demandes_italie` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pays_etudes` varchar(100) DEFAULT 'Italie',
  `niveau_etudes` varchar(50) NOT NULL,
  `domaine_etudes` varchar(200) NOT NULL,
  `nom_formation` varchar(300) NOT NULL,
  `etablissement` varchar(300) NOT NULL,
  `ville_etablissement` varchar(200) NOT NULL,
  `date_debut` date NOT NULL,
  `duree_etudes` varchar(50) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(200) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `num_passeport` varchar(100) NOT NULL,
  `tests_italien` varchar(50) DEFAULT 'non',
  `tests_anglais` varchar(50) DEFAULT 'non',
  `releves_annees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`releves_annees`)),
  `statut` enum('en_attente','en_traitement','approuvee','refusee') DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_demande` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_maj` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_italie`
--

INSERT INTO `demandes_italie` (`id`, `user_id`, `pays_etudes`, `niveau_etudes`, `domaine_etudes`, `nom_formation`, `etablissement`, `ville_etablissement`, `date_debut`, `duree_etudes`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `num_passeport`, `tests_italien`, `tests_anglais`, `releves_annees`, `statut`, `date_creation`, `date_modification`, `date_demande`, `date_maj`) VALUES
(1, 6, 'Italie', 'licence2', 'informatique', 'chahid', 'ummto', 'sdfghjk', '2025-09-27', '2 ans', 'melissa', 'chahid', '2009-09-24', 'jjjj', 'jjjj', 'vfvfvv', '0556603313', 'kami@gmail.com', '4567890', 'cils', 'non', '[\"Baccalaur\\u00e9at\",\"Licence 1\"]', 'en_attente', '2025-09-26 16:59:47', '2025-09-26 16:59:47', '2025-09-27 13:17:16', '2025-09-27 13:18:48');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_italie_fichiers`
--

CREATE TABLE `demandes_italie_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_italie_fichiers`
--

INSERT INTO `demandes_italie_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'copie_passeport', '68d6b87367e01_casier judiciere.pdf', '2025-09-26 16:59:47'),
(2, 1, 'releve_annee_1', '68d6b87369515_casier judiciere.pdf', '2025-09-26 16:59:47'),
(3, 1, 'releve_annee_2', '68d6b8736a131_casier judiciere.pdf', '2025-09-26 16:59:47'),
(4, 1, 'diplome_1', '68d6b8736b5c7_casier judiciere.pdf', '2025-09-26 16:59:47'),
(5, 1, 'certificat_scolarite', '68d6b8736d019_casier judiciere.pdf', '2025-09-26 16:59:47'),
(6, 1, 'attestation_italien', '68d6b8736db8a_casier judiciere.pdf', '2025-09-26 16:59:47');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_luxembourg`
--

CREATE TABLE `demandes_luxembourg` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `programme` varchar(50) NOT NULL,
  `niveau` varchar(50) NOT NULL,
  `motivation` text DEFAULT NULL,
  `statut` enum('en_attente','en_traitement','approuvee','rejetee') DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_luxembourg`
--

INSERT INTO `demandes_luxembourg` (`id`, `user_id`, `nom`, `email`, `telephone`, `nationalite`, `programme`, `niveau`, `motivation`, `statut`, `date_creation`, `date_modification`) VALUES
(1, 6, 'chahid melissa', 'melissalounis551@gmail.com', '0556603313', 'jkjkkj', 'francais', 'bachelor', '', 'en_attente', '2025-09-27 17:59:12', '2025-09-27 17:59:12'),
(2, 6, 'chahid melissa', 'melissalounis551@gmail.com', '0556603313', 'jkjkkj', 'francais', 'bachelor', '', 'en_attente', '2025-09-27 18:22:37', '2025-09-27 18:22:37');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_luxembourg_fichiers`
--

CREATE TABLE `demandes_luxembourg_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `nom_original` varchar(255) DEFAULT NULL,
  `taille_fichier` bigint(20) DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_luxembourg_fichiers`
--

INSERT INTO `demandes_luxembourg_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `nom_original`, `taille_fichier`, `date_upload`) VALUES
(1, 1, 'passeport', '68d825f02080d_BABYLONE SERVICES2.docx', NULL, NULL, '2025-09-27 17:59:12'),
(2, 1, 'justificatif', '68d825f022367_BABYLONE SERVICES2.docx', NULL, NULL, '2025-09-27 17:59:12'),
(3, 1, 'photo', '68d825f023270_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(4, 1, 'test_francais', '68d825f026ab1_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(5, 1, 'test_anglais', '68d825f027a40_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(6, 1, 'releves_lycee', '68d825f028b79_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(7, 1, 'releve_bac', '68d825f029c07_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(8, 1, 'diplome_bac', '68d825f02ac38_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 17:59:12'),
(9, 2, 'test_francais', '68d82b6d17d10_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(10, 2, 'test_anglais', '68d82b6d1978c_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(11, 2, 'passeport', '68d82b6d1abd0_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(12, 2, 'justificatif', '68d82b6d1bd40_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(13, 2, 'photo', '68d82b6d1df5e_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(14, 2, 'releves_lycee', '68d82b6d1ee08_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(15, 2, 'releve_bac', '68d82b6d1fef9_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37'),
(16, 2, 'diplome_bac', '68d82b6d2191d_Capture d’écran 2025-09-09 000543.jpg', NULL, NULL, '2025-09-27 18:22:37');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_parcoursup`
--

CREATE TABLE `demandes_parcoursup` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `niveau_etudes` varchar(50) NOT NULL,
  `domaine_etudes` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `nationalite` varchar(50) NOT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `type_piece_identite` varchar(50) NOT NULL,
  `num_piece_identite` varchar(50) NOT NULL,
  `date_delivrance_piece` date NOT NULL,
  `date_expiration_piece` date NOT NULL,
  `situation_familiale` varchar(50) NOT NULL,
  `profession_candidat` varchar(100) NOT NULL,
  `categorie_socio_pro` varchar(50) NOT NULL,
  `nom_pere` varchar(100) NOT NULL,
  `prenom_pere` varchar(100) NOT NULL,
  `profession_pere` varchar(100) NOT NULL,
  `employeur_pere` varchar(100) NOT NULL,
  `csp_pere` varchar(50) NOT NULL,
  `nom_mere` varchar(100) NOT NULL,
  `prenom_mere` varchar(100) NOT NULL,
  `profession_mere` varchar(100) NOT NULL,
  `employeur_mere` varchar(100) NOT NULL,
  `csp_mere` varchar(50) NOT NULL,
  `a_garant_france` enum('oui','non') NOT NULL,
  `nom_garant` varchar(100) DEFAULT NULL,
  `prenom_garant` varchar(100) DEFAULT NULL,
  `adresse_garant` text DEFAULT NULL,
  `telephone_garant` varchar(20) DEFAULT NULL,
  `email_garant` varchar(100) DEFAULT NULL,
  `lien_parente_garant` varchar(50) DEFAULT NULL,
  `tests_francais` varchar(50) DEFAULT 'non',
  `score_test` varchar(50) DEFAULT NULL,
  `test_anglais` varchar(50) DEFAULT 'non',
  `score_anglais` varchar(50) DEFAULT NULL,
  `boite_pastel` enum('oui','non') DEFAULT 'non',
  `email_pastel` varchar(100) DEFAULT NULL,
  `statut` enum('en_attente','en_cours','approuve','refuse','termine','annule') DEFAULT 'en_attente',
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_parcoursup`
--

INSERT INTO `demandes_parcoursup` (`id`, `user_id`, `niveau_etudes`, `domaine_etudes`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `type_piece_identite`, `num_piece_identite`, `date_delivrance_piece`, `date_expiration_piece`, `situation_familiale`, `profession_candidat`, `categorie_socio_pro`, `nom_pere`, `prenom_pere`, `profession_pere`, `employeur_pere`, `csp_pere`, `nom_mere`, `prenom_mere`, `profession_mere`, `employeur_mere`, `csp_mere`, `a_garant_france`, `nom_garant`, `prenom_garant`, `adresse_garant`, `telephone_garant`, `email_garant`, `lien_parente_garant`, `tests_francais`, `score_test`, `test_anglais`, `score_anglais`, `boite_pastel`, `email_pastel`, `statut`, `date_soumission`, `date_modification`, `date_creation`) VALUES
(1, 6, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', '', '', '', '', '', '', 'non', '', 'non', '', 'non', '', 'en_attente', '2025-11-10 20:19:03', '2025-11-10 20:19:03', '2025-11-10 21:49:14'),
(2, 6, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', '', '', '', '', '', '', 'non', '', 'non', '', 'non', '', 'en_attente', '2025-11-10 20:21:26', '2025-11-10 20:21:26', '2025-11-10 21:49:14'),
(3, 6, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', '', '', '', '', '', '', 'non', '', 'non', '', 'non', '', 'en_attente', '2025-11-10 20:26:33', '2025-11-10 20:26:33', '2025-11-10 21:49:14'),
(6, 6, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'passeport', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', '', '', '', '', '', '', 'non', '', 'non', '', 'non', '', 'en_attente', '2025-11-10 21:55:18', '2025-11-10 21:55:18', '2025-11-10 21:55:18'),
(7, 6, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', '', '', '', '', '', '', 'non', '', 'non', '', 'non', '', 'en_attente', '2025-11-10 21:56:43', '2025-11-10 21:56:43', '2025-11-10 21:56:43'),
(9, 1, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'agriculteur', 'non', NULL, NULL, NULL, NULL, NULL, NULL, 'non', NULL, 'non', NULL, 'non', NULL, 'en_attente', '2025-11-10 22:47:01', '2025-11-10 22:47:01', '2025-11-10 22:47:01'),
(10, 1, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'ouvrier', 'non', NULL, NULL, NULL, NULL, NULL, NULL, 'non', NULL, 'non', NULL, 'non', NULL, 'en_attente', '2025-11-10 22:48:10', '2025-11-10 22:48:10', '2025-11-10 22:48:10'),
(11, 1, 'licence1', 'informatique', 'melissa', 'admin', '2003-11-07', 'ttttt', 'defe', 'fv', '0556603313', 'kami@gmail.com', 'cni', 'ss', '2025-11-07', '2025-11-13', 'celibataire', 'frdfgdfgtgt', 'agriculteur', 'melissa', 'admin', 'fff', 'ff', 'agriculteur', 'ff', 'ff', 'vv', 'vv', 'ouvrier', 'non', NULL, NULL, NULL, NULL, NULL, NULL, 'non', NULL, 'non', NULL, 'non', NULL, 'en_attente', '2025-11-10 23:07:43', '2025-11-10 23:07:43', '2025-11-10 23:07:43');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_parcoursup_annees`
--

CREATE TABLE `demandes_parcoursup_annees` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `annee_etude` varchar(100) NOT NULL,
  `moyenne` varchar(20) DEFAULT NULL,
  `mention` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_parcoursup_annees`
--

INSERT INTO `demandes_parcoursup_annees` (`id`, `demande_id`, `annee_etude`, `moyenne`, `mention`) VALUES
(1, 1, 'Année du Bac', '14', 'v'),
(2, 2, 'Année du Bac', '14', 'v'),
(3, 3, 'Année du Bac', '14', 'v');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_parcoursup_documents`
--

CREATE TABLE `demandes_parcoursup_documents` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_document` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_parcoursup_fichiers`
--

CREATE TABLE `demandes_parcoursup_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(50) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_parcoursup_fichiers`
--

INSERT INTO `demandes_parcoursup_fichiers` (`id`, `demande_id`, `type_fichier`, `nom_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'photo_identite', 'Capture.PNG', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_1_photo_identite_1762802343.PNG', '2025-11-10 20:19:03'),
(2, 1, 'copie_piece_identite', 'casier judiciere.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_1_copie_piece_identite_1762802343.pdf', '2025-11-10 20:19:03'),
(3, 1, 'certificat_scolarite', 'BABYLONE SERVICES2.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_1_certificat_scolarite_1762802343.docx', '2025-11-10 20:19:03'),
(4, 1, 'releve_notes_annee_1', 'casier judiciere.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_1_releve_notes_annee_1_1762802343.pdf', '2025-11-10 20:19:03'),
(5, 2, 'photo_identite', 'Capture.PNG', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_2_photo_identite_1762802486.PNG', '2025-11-10 20:21:26'),
(6, 2, 'copie_piece_identite', 'casier judiciere.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_2_copie_piece_identite_1762802486.pdf', '2025-11-10 20:21:26'),
(7, 2, 'certificat_scolarite', 'BABYLONE SERVICES2.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_2_certificat_scolarite_1762802486.docx', '2025-11-10 20:21:26'),
(8, 2, 'releve_notes_annee_1', 'casier judiciere.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/uploads/parcoursup/parcoursup_2_releve_notes_annee_1_1762802486.pdf', '2025-11-10 20:21:26'),
(9, 3, 'photo_identite', 'Capture.PNG', 'uploads/parcoursup/photo_identite_3_1762802793_908e040f44e6.png', '2025-11-10 20:26:33'),
(10, 3, 'copie_piece_identite', 'casier_judiciere.pdf', 'uploads/parcoursup/copie_piece_identite_3_1762802793_1b9c1c11f84e.pdf', '2025-11-10 20:26:33'),
(11, 3, 'certificat_scolarite', 'BABYLONE_SERVICES2.docx', 'uploads/parcoursup/certificat_scolarite_3_1762802793_b364f7aef84d.docx', '2025-11-10 20:26:33'),
(12, 3, 'releve_notes_annee_1', 'casier_judiciere.pdf', 'uploads/parcoursup/releve_notes_annee_1_3_1762802793_3497fb239d2a.pdf', '2025-11-10 20:26:33');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_paris_saclay`
--

CREATE TABLE `demandes_paris_saclay` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pays_etudes` varchar(100) DEFAULT NULL,
  `niveau_etudes` varchar(50) DEFAULT NULL,
  `domaine_etudes` varchar(100) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `num_passeport` varchar(50) DEFAULT NULL,
  `date_delivrance` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `niveau_francais` varchar(50) DEFAULT NULL,
  `tests_francais` varchar(50) DEFAULT NULL,
  `score_test` varchar(50) DEFAULT NULL,
  `test_anglais` varchar(50) DEFAULT NULL,
  `score_anglais` varchar(50) DEFAULT NULL,
  `boite_pastel` varchar(10) DEFAULT NULL,
  `email_pastel` varchar(100) DEFAULT NULL,
  `mdp_pastel` varchar(100) DEFAULT NULL,
  `releves_annees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`releves_annees`)),
  `autres_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`autres_documents`)),
  `statut` varchar(20) DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_regroupement_familial`
--

CREATE TABLE `demandes_regroupement_familial` (
  `id` int(11) NOT NULL,
  `numero_dossier` varchar(50) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `nom_famille` varchar(255) NOT NULL,
  `lien_parente` varchar(50) NOT NULL,
  `adresse_famille` text NOT NULL,
  `commentaire` text DEFAULT NULL,
  `passeport` varchar(255) DEFAULT NULL,
  `titre_sejour` varchar(255) DEFAULT NULL,
  `acte_mariage` varchar(255) DEFAULT NULL,
  `preuves_liens` text DEFAULT NULL,
  `justificatif_logement` varchar(255) DEFAULT NULL,
  `ressources` varchar(255) DEFAULT NULL,
  `paiement` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','validé','rejeté') DEFAULT 'en_attente',
  `raison_rejet` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_traitement` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_regroupement_familial`
--

INSERT INTO `demandes_regroupement_familial` (`id`, `numero_dossier`, `nom_complet`, `date_naissance`, `nationalite`, `email`, `telephone`, `nom_famille`, `lien_parente`, `adresse_famille`, `commentaire`, `passeport`, `titre_sejour`, `acte_mariage`, `preuves_liens`, `justificatif_logement`, `ressources`, `paiement`, `statut`, `raison_rejet`, `date_soumission`, `date_traitement`) VALUES
(1, 'FR-2025-9164', 'admin melissa', '2002-07-07', 'ffff', 'kami@gmail.com', '0556603313', 'melissa lounis', 'conjoint', 'rtedt', NULL, '6922124b50970.pdf', '6922124b50d5c.pdf', '6922124b51216.pdf', '6922124b52320.png', '6922124b517b3.pdf', '6922124b51b91.pdf', '6922124b51f54.pdf', 'validé', NULL, '2025-11-22 19:43:07', '2025-11-22 21:01:29');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_reservation`
--

CREATE TABLE `demandes_reservation` (
  `id` int(11) NOT NULL,
  `numero_dossier` varchar(20) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `civilite` varchar(10) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `numero_passeport` varchar(50) NOT NULL,
  `date_expiration_passeport` date NOT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `heure_arrivee_prevue` time DEFAULT NULL,
  `moyen_transport` varchar(50) DEFAULT NULL,
  `numero_vol_train` varchar(100) DEFAULT NULL,
  `type_hebergement` varchar(50) DEFAULT NULL,
  `categorie_chambre` varchar(50) DEFAULT NULL,
  `nombre_adultes` int(11) NOT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `ages_enfants` varchar(255) DEFAULT NULL,
  `demandes_speciales` text DEFAULT NULL,
  `raison_sejour` varchar(100) DEFAULT NULL,
  `precisions_financement` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'en_attente',
  `prix_estime` decimal(10,2) DEFAULT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `date_traitement` datetime DEFAULT NULL,
  `notes_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_reservation`
--

INSERT INTO `demandes_reservation` (`id`, `numero_dossier`, `date_creation`, `civilite`, `nom`, `prenom`, `email`, `telephone`, `nationalite`, `numero_passeport`, `date_expiration_passeport`, `date_arrivee`, `date_depart`, `heure_arrivee_prevue`, `moyen_transport`, `numero_vol_train`, `type_hebergement`, `categorie_chambre`, `nombre_adultes`, `nombre_enfants`, `ages_enfants`, `demandes_speciales`, `raison_sejour`, `precisions_financement`, `status`, `prix_estime`, `date_soumission`, `date_traitement`, `notes_admin`) VALUES
(1, 'DOS202405150001', '2025-10-24 19:56:35', 'M', 'DUPONT', 'Jean', 'jean.dupont@email.com', '0123456789', 'Française', 'AB123456', '2025-12-31', '2024-06-10', '2024-06-17', NULL, NULL, NULL, 'hotel', 'superieure', 2, 1, NULL, NULL, NULL, NULL, 'en_attente', 850.00, '2025-11-22 15:56:18', NULL, NULL),
(2, 'DOS202405150002', '2025-10-24 19:56:35', 'Mme', 'MARTIN', 'Sophie', 'sophie.martin@email.com', '0123456790', 'Française', 'CD789012', '2026-05-15', '2024-07-01', '2024-07-08', NULL, NULL, NULL, 'appartement', 'familiale', 4, 2, NULL, NULL, NULL, NULL, 'confirmee', 1200.00, '2025-11-22 15:56:18', NULL, NULL),
(3, 'DOS202510244093', '2025-10-24 21:56:35', 'M', 'melissa', 'chahid', 'kami@gmail.com', '0556603313', 'jkjkkj', '4456566666', '2026-02-05', '2025-10-24', '2027-06-06', '23:56:00', 'avion', '345678', 'hotel', 'standard', 1, 0, '', '', 'tourisme', '', 'annulee', 59000.00, '2025-11-22 15:56:18', '2025-11-22 16:02:50', NULL),
(4, 'DOS202511047175', '2025-11-04 21:22:03', 'M', 'melissa', 'admin', 'kami@gmail.com', '0556603313', 'jkjkkj', '4456566666', '2002-07-07', '2027-03-31', '2028-03-31', '22:22:00', 'avion', '222222', 'hotel', 'standard', 1, 0, '', '', 'tourisme', '', 'confirmee', 36600.00, '2025-11-22 15:56:18', '2025-11-22 16:02:41', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_suisse`
--

CREATE TABLE `demandes_suisse` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pays_etudes` varchar(100) DEFAULT 'Suisse',
  `niveau_etudes` varchar(50) DEFAULT NULL,
  `domaine_etudes` varchar(100) DEFAULT NULL,
  `nom_formation` varchar(200) DEFAULT NULL,
  `etablissement` varchar(200) DEFAULT NULL,
  `ville_etablissement` varchar(100) DEFAULT NULL,
  `date_debut_etudes` date DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `duree_etudes` varchar(50) DEFAULT NULL,
  `langue_formation` varchar(50) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `num_passeport` varchar(50) DEFAULT NULL,
  `type_passeport` varchar(50) DEFAULT NULL,
  `langue_maternelle` varchar(50) DEFAULT NULL,
  `tests_allemand` varchar(50) DEFAULT 'non',
  `tests_francais` varchar(50) DEFAULT 'non',
  `tests_anglais` varchar(50) DEFAULT 'non',
  `tests_italien` varchar(50) DEFAULT 'non',
  `releves_annees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`releves_annees`)),
  `statut` enum('en_attente','en_traitement','approuvee','rejetee') DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_suisse`
--

INSERT INTO `demandes_suisse` (`id`, `user_id`, `pays_etudes`, `niveau_etudes`, `domaine_etudes`, `nom_formation`, `etablissement`, `ville_etablissement`, `date_debut_etudes`, `date_debut`, `duree_etudes`, `langue_formation`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `adresse`, `telephone`, `email`, `num_passeport`, `type_passeport`, `langue_maternelle`, `tests_allemand`, `tests_francais`, `tests_anglais`, `tests_italien`, `releves_annees`, `statut`, `date_creation`, `date_modification`) VALUES
(1, 6, 'Suisse', 'master1', 'informatique', 'chahid', 'ummto', 'sdfghjk', NULL, '2025-09-21', '1.5 ans', 'allemand', 'melissa', 'chahid', '2025-09-14', 'jjjj', 'jjjj', 'vfvfvv', '0556603313', 'kami@gmail.com', '4567890', 'ordinaire', 'llll', 'non', 'non', 'non', 'non', '[\"Baccalaur\\u00e9at\",\"Licence 1\",\"Licence 2\",\"Licence 3\"]', 'en_attente', '2025-09-27 13:50:30', '2025-09-27 13:50:30');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_suisse_fichiers`
--

CREATE TABLE `demandes_suisse_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `type_fichier` varchar(100) DEFAULT NULL,
  `nom_fichier_original` varchar(255) DEFAULT NULL,
  `chemin_fichier` varchar(255) DEFAULT NULL,
  `taille_fichier` bigint(20) DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_suisse_fichiers`
--

INSERT INTO `demandes_suisse_fichiers` (`id`, `demande_id`, `type_fichier`, `nom_fichier_original`, `chemin_fichier`, `taille_fichier`, `date_upload`) VALUES
(1, 1, 'copie_passeport', NULL, '68d7eba63bb67_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(2, 1, 'releve_annee_1', NULL, '68d7eba63da65_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(3, 1, 'releve_annee_2', NULL, '68d7eba63e97c_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(4, 1, 'releve_annee_3', NULL, '68d7eba6400b7_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(5, 1, 'releve_annee_4', NULL, '68d7eba6410d2_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(6, 1, 'diplome_1', NULL, '68d7eba642850_casier judiciere.pdf', NULL, '2025-09-27 13:50:30'),
(7, 1, 'diplome_2', NULL, '68d7eba643778_casier judiciere.pdf', NULL, '2025-09-27 13:50:30');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_turquie`
--

CREATE TABLE `demandes_turquie` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialite` varchar(200) DEFAULT NULL,
  `programme_langue` varchar(50) DEFAULT NULL,
  `certificat_type` varchar(50) DEFAULT NULL,
  `certificat_score` varchar(50) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('en_attente','en_traitement','approuvee','rejetee') DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `passeport` varchar(255) DEFAULT NULL,
  `diplomes` varchar(255) DEFAULT NULL,
  `releves_notes` varchar(255) DEFAULT NULL,
  `photo_identite` varchar(255) DEFAULT NULL,
  `certificat_langue` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_turquie`
--

INSERT INTO `demandes_turquie` (`id`, `user_id`, `specialite`, `programme_langue`, `certificat_type`, `certificat_score`, `nom`, `prenom`, `date_naissance`, `nationalite`, `email`, `telephone`, `niveau`, `commentaire`, `statut`, `date_creation`, `date_modification`, `passeport`, `diplomes`, `releves_notes`, `photo_identite`, `certificat_langue`) VALUES
(1, 6, 'bb', 'turc', '', '', 'melissa', 'chahid', '2001-05-05', 'defe', 'kami@gmail.com', '0556603313', 'l1', '', 'en_attente', '2025-09-27 15:22:20', '2025-09-27 15:22:20', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demandes_turquie_fichiers`
--

CREATE TABLE `demandes_turquie_fichiers` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `type_fichier` varchar(100) DEFAULT NULL,
  `chemin_fichier` varchar(255) DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_turquie_fichiers`
--

INSERT INTO `demandes_turquie_fichiers` (`id`, `demande_id`, `type_fichier`, `chemin_fichier`, `date_upload`) VALUES
(1, 1, 'releve_2nde', '68d8012c45d78_casier judiciere.pdf', '2025-09-27 15:22:20'),
(2, 1, 'releve_1ere', '68d8012c4710e_casier judiciere.pdf', '2025-09-27 15:22:20'),
(3, 1, 'releve_terminale', '68d8012c484f2_casier judiciere.pdf', '2025-09-27 15:22:20'),
(4, 1, 'releve_bac', '68d8012c49f01_casier judiciere.pdf', '2025-09-27 15:22:20'),
(5, 1, 'diplome_bac', '68d8012c4ae87_casier judiciere.pdf', '2025-09-27 15:22:20'),
(6, 1, 'certificat_scolarite', '68d8012c4c204_casier judiciere.pdf', '2025-09-27 15:22:20');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_visa`
--

CREATE TABLE `demandes_visa` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `nom_famille` varchar(255) NOT NULL,
  `lien_parente` varchar(50) NOT NULL,
  `adresse_famille` text NOT NULL,
  `commentaire` text DEFAULT NULL,
  `passeport` varchar(255) DEFAULT NULL,
  `titre_sejour` varchar(255) DEFAULT NULL,
  `acte_mariage` varchar(255) DEFAULT NULL,
  `justificatif_logement` varchar(255) DEFAULT NULL,
  `ressources` varchar(255) DEFAULT NULL,
  `recu_paiement` varchar(255) DEFAULT NULL,
  `avis_favorable` varchar(255) DEFAULT NULL,
  `preuves_liens` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_visa`
--

INSERT INTO `demandes_visa` (`id`, `nom_complet`, `date_naissance`, `nationalite`, `email`, `telephone`, `nom_famille`, `lien_parente`, `adresse_famille`, `commentaire`, `passeport`, `titre_sejour`, `acte_mariage`, `justificatif_logement`, `ressources`, `recu_paiement`, `avis_favorable`, `preuves_liens`, `date_soumission`) VALUES
(1, 'chahid melissa', '2002-02-22', 'ffff', 'kami@gmail.com', '0556603313', 'chahid melissa', 'conjoint', 'vfvfvv', 'cc', '68efe2a9ddcae_casier judiciere.pdf', '68efe2a9de1cb_casier judiciere.pdf', '68efe2a9de6c7_casier judiciere.pdf', '68efe2a9def2f_casier judiciere.pdf', '68efe2a9df38f_casier judiciere.pdf', '68efe2a9df669_casier judiciere.pdf', '68efe2a9df919_casier judiciere.pdf', '68efe2a9dfcb7_casier judiciere.pdf', '2025-10-15 18:06:33'),
(2, 'chahid melissa', '2002-02-22', 'ffff', 'kami@gmail.com', '0556603313', 'chahid melissa', 'conjoint', 'vfvfvv', 'cc', '68efe362da302_casier judiciere.pdf', '68efe362da5e6_casier judiciere.pdf', '68efe362da99a_casier judiciere.pdf', '68efe362dac1d_casier judiciere.pdf', '68efe362dae93_casier judiciere.pdf', '68efe362db10b_casier judiciere.pdf', '68efe362db367_casier judiciere.pdf', '68efe362db5e3_casier judiciere.pdf', '2025-10-15 18:09:38');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_visa_travail`
--

CREATE TABLE `demandes_visa_travail` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `passeport` varchar(100) NOT NULL,
  `date_delivrance` date NOT NULL,
  `date_expiration` date NOT NULL,
  `employeur` varchar(255) NOT NULL,
  `adresse_employeur` text NOT NULL,
  `type_contrat` varchar(50) NOT NULL,
  `duree_sejour` int(11) NOT NULL,
  `photo_identite` varchar(255) DEFAULT NULL,
  `copie_passeport` varchar(255) DEFAULT NULL,
  `contrat_travail` varchar(255) DEFAULT NULL,
  `attestation_employeur` varchar(255) DEFAULT NULL,
  `logement` varchar(255) DEFAULT NULL,
  `assurance` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT 0,
  `statut` enum('en_attente','approuvee','rejetee') DEFAULT 'en_attente',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_traitement` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_visa_travail`
--

INSERT INTO `demandes_visa_travail` (`id`, `nom_complet`, `date_naissance`, `nationalite`, `email`, `telephone`, `passeport`, `date_delivrance`, `date_expiration`, `employeur`, `adresse_employeur`, `type_contrat`, `duree_sejour`, `photo_identite`, `copie_passeport`, `contrat_travail`, `attestation_employeur`, `logement`, `assurance`, `user_id`, `statut`, `date_soumission`, `date_traitement`, `notes`, `created_at`) VALUES
(1, 'chahid melissa', '2005-09-18', 'defe', 'kami@gmail.com', '0556603313', '4567890', '2025-09-19', '2033-01-29', 'chahid', 'to', 'CDI', 3, 'BABYLONE SERVICES2.docx', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 6, 'en_attente', '2025-09-29 19:34:20', NULL, NULL, NULL),
(2, 'chahid melissa', '2005-09-18', 'defe', 'kami@gmail.com', '0556603313', '4567890', '2025-09-19', '2033-01-29', 'chahid', 'to', 'CDI', 3, 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 'Capture d’écran 2025-09-09 000543.jpg', 6, 'en_attente', '2025-09-29 19:35:26', NULL, NULL, NULL),
(3, 'chahid melissa', '2005-10-17', 'jkjkkj', 'kami@gmail.com', '0556603313', '4567890', '2025-10-10', '2026-10-10', 'chahid melissa', 'vfvfvv', 'CDD', 3, 'Capture d’écran 2025-09-09 000543.jpg', '', '', '', '', '', 6, 'en_attente', '2025-10-12 19:08:54', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demande_etudes_`
--

CREATE TABLE `demande_etudes_` (
  `id` int(11) NOT NULL,
  `nom_famille` varchar(100) NOT NULL,
  `prenoms` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `pays_naissance` varchar(100) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `sexe` enum('M','F') NOT NULL,
  `statut_matrimonial` varchar(50) NOT NULL,
  `adresse` text NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays_residence` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `niveau_etudes` varchar(100) NOT NULL,
  `domaine_etudes` varchar(100) NOT NULL,
  `etablissement_souhaite` varchar(100) DEFAULT NULL,
  `duree_etudes` varchar(50) NOT NULL,
  `date_debut` date NOT NULL,
  `budget_estime` decimal(10,2) DEFAULT NULL,
  `numero_dossier` varchar(50) NOT NULL,
  `date_soumission` datetime DEFAULT current_timestamp(),
  `statut` enum('en_attente','accepté','refusé') DEFAULT 'en_attente',
  `date_traitement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_etude_canada`
--

CREATE TABLE `demande_etude_canada` (
  `id` int(11) NOT NULL,
  `nom_famille` varchar(100) NOT NULL,
  `prenoms` varchar(100) NOT NULL,
  `nom_naissance` varchar(100) DEFAULT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `pays_naissance` varchar(100) NOT NULL,
  `nationalite_actuelle` varchar(100) NOT NULL,
  `autre_nationalite` varchar(100) DEFAULT NULL,
  `sexe` enum('M','F') NOT NULL,
  `statut_matrimonial` enum('Célibataire','Marié(e)','Conjoint(e) de fait','Divorcé(e)','Séparé(e)','Veuf(ve)') NOT NULL,
  `adresse_rue` varchar(255) NOT NULL,
  `adresse_ville` varchar(100) NOT NULL,
  `adresse_province` varchar(100) NOT NULL,
  `adresse_code_postal` varchar(20) NOT NULL,
  `adresse_pays` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `telephone_autre` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `passeport_numero` varchar(50) NOT NULL,
  `passeport_pays` varchar(100) NOT NULL,
  `passeport_date_emission` date NOT NULL,
  `passeport_date_expiration` date NOT NULL,
  `nom_institution` varchar(255) NOT NULL,
  `dli_numero` varchar(20) NOT NULL,
  `adresse_institution` varchar(255) NOT NULL,
  `niveau_etudes` varchar(100) NOT NULL,
  `domaine_etudes` varchar(100) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `frais_scolarite` decimal(12,2) NOT NULL,
  `frais_hebergement` decimal(12,2) NOT NULL,
  `frais_divers` decimal(12,2) NOT NULL,
  `total_frais` decimal(12,2) NOT NULL,
  `fonds_disponibles` decimal(12,2) NOT NULL,
  `type_financement` enum('Épargne personnelle','Famille','Prêt étudiant','Bourse','Combinaison') NOT NULL,
  `preuve_fonds` varchar(3) NOT NULL,
  `niveau_etudes_precedent` varchar(100) NOT NULL,
  `institution_precedente` varchar(255) DEFAULT NULL,
  `pays_institution` varchar(100) DEFAULT NULL,
  `annee_obtention` year(4) DEFAULT NULL,
  `test_langue` enum('IELTS','TOEFL','TFI','DALF','Autre','Aucun') DEFAULT NULL,
  `score_langue` varchar(10) DEFAULT NULL,
  `date_test` date DEFAULT NULL,
  `conjoint_pays` varchar(100) DEFAULT NULL,
  `conjoint_statut` varchar(50) DEFAULT NULL,
  `enfants_nombre` int(11) DEFAULT 0,
  `refus_immigration` enum('Oui','Non') NOT NULL,
  `details_refus` text DEFAULT NULL,
  `permis_sejour_pays` varchar(100) DEFAULT NULL,
  `lettre_acceptation` enum('Oui','Non') NOT NULL,
  `attestation_provinciale` enum('Oui','Non') NOT NULL,
  `certificat_medical` enum('Oui','Non') NOT NULL,
  `casier_judiciaire` enum('Oui','Non') NOT NULL,
  `projet_etudes` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','soumis','traite') DEFAULT 'en_attente',
  `numero_dossier` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_permis_etudes`
--

CREATE TABLE `demande_permis_etudes` (
  `id` int(11) NOT NULL,
  `nom_famille` varchar(100) NOT NULL,
  `prenoms` varchar(100) NOT NULL,
  `nom_naissance` varchar(100) DEFAULT NULL,
  `sexe` enum('M','F','U','X') NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `pays_naissance` varchar(100) NOT NULL,
  `nationalite_actuelle` varchar(100) NOT NULL,
  `pays_residence` varchar(100) NOT NULL,
  `statut_residence` enum('Citizen','Permanent resident','Visitor','Worker','Student','Other','Protected Person','Refugee Claimant') NOT NULL,
  `statut_matrimonial` enum('Annulled Marriage','Common-Law','Divorced','Married','Separated','Single','Widowed') NOT NULL,
  `adresse_rue` varchar(255) NOT NULL,
  `adresse_ville` varchar(100) NOT NULL,
  `adresse_province` varchar(100) NOT NULL,
  `adresse_code_postal` varchar(20) NOT NULL,
  `adresse_pays` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `passeport_numero` varchar(50) NOT NULL,
  `passeport_pays` varchar(100) NOT NULL,
  `passeport_date_emission` date NOT NULL,
  `passeport_date_expiration` date NOT NULL,
  `nom_institution` varchar(255) NOT NULL,
  `dli_numero` varchar(20) NOT NULL,
  `niveau_etudes` varchar(100) NOT NULL,
  `domaine_etudes` varchar(100) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `frais_scolarite` decimal(12,2) NOT NULL,
  `frais_hebergement` decimal(12,2) NOT NULL,
  `frais_divers` decimal(12,2) NOT NULL,
  `total_frais` decimal(12,2) NOT NULL,
  `fonds_disponibles` decimal(12,2) NOT NULL,
  `type_financement` enum('Épargne personnelle','Famille','Prêt étudiant','Bourse','Combinaison') NOT NULL,
  `attestation_provinciale` enum('Oui','Non') NOT NULL,
  `lettre_acceptation` enum('Oui','Non') NOT NULL,
  `certificat_medical` enum('Oui','Non') NOT NULL,
  `casier_judiciaire` enum('Oui','Non') NOT NULL,
  `projet_etudes` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `numero_dossier` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_visa`
--

CREATE TABLE `demande_visa` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `pays_residence` varchar(100) DEFAULT NULL,
  `passeport` varchar(50) DEFAULT NULL,
  `acceptation_universitaire` varchar(3) DEFAULT NULL,
  `preuve_fonds` varchar(3) DEFAULT NULL,
  `niveau_etudes_precedent` varchar(100) DEFAULT NULL,
  `langue_test` varchar(50) DEFAULT NULL,
  `score_langue` varchar(10) DEFAULT NULL,
  `projet_etudes` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demande_visa_court_sejour`
--

CREATE TABLE `demande_visa_court_sejour` (
  `id` int(11) NOT NULL,
  `nom_famille` varchar(100) NOT NULL,
  `prenoms` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) NOT NULL,
  `pays_naissance` varchar(100) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `sexe` enum('M','F') NOT NULL,
  `statut_matrimonial` varchar(50) NOT NULL,
  `adresse` text NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays_residence` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passeport_numero` varchar(50) NOT NULL,
  `passeport_pays` varchar(100) NOT NULL,
  `passeport_date_emission` date NOT NULL,
  `passeport_date_expiration` date NOT NULL,
  `but_visite` varchar(100) NOT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `duree_sejour` varchar(50) NOT NULL,
  `hebergement_type` varchar(50) NOT NULL,
  `hebergement_adresse` text DEFAULT NULL,
  `personne_contact_nom` varchar(100) DEFAULT NULL,
  `personne_contact_telephone` varchar(20) DEFAULT NULL,
  `personne_contact_relation` varchar(50) DEFAULT NULL,
  `employeur_nom` varchar(100) DEFAULT NULL,
  `employeur_adresse` text DEFAULT NULL,
  `employeur_telephone` varchar(20) DEFAULT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `situation_professionnelle` varchar(50) NOT NULL,
  `refus_visa_precedent` enum('Oui','Non') NOT NULL,
  `details_refus` text DEFAULT NULL,
  `maladies_graves` enum('Oui','Non') NOT NULL,
  `details_maladies` text DEFAULT NULL,
  `condamnations_judiciaires` enum('Oui','Non') NOT NULL,
  `details_condamnations` text DEFAULT NULL,
  `service_militaire` enum('Oui','Non') NOT NULL,
  `details_service_militaire` text DEFAULT NULL,
  `documents_passeport` varchar(255) DEFAULT NULL,
  `documents_photo` varchar(255) DEFAULT NULL,
  `documents_fonds` varchar(255) DEFAULT NULL,
  `documents_vol` varchar(255) DEFAULT NULL,
  `documents_hebergement` varchar(255) DEFAULT NULL,
  `documents_conjoint` varchar(255) DEFAULT NULL,
  `documents_enfants` varchar(255) DEFAULT NULL,
  `documents_autres` varchar(255) DEFAULT NULL,
  `conjoint_nom` varchar(100) DEFAULT NULL,
  `conjoint_prenoms` varchar(100) DEFAULT NULL,
  `conjoint_date_naissance` date DEFAULT NULL,
  `conjoint_lieu_naissance` varchar(100) DEFAULT NULL,
  `conjoint_nationalite` varchar(100) DEFAULT NULL,
  `conjoint_profession` varchar(100) DEFAULT NULL,
  `conjoint_employeur` varchar(100) DEFAULT NULL,
  `conjoint_experience_professionnelle` text DEFAULT NULL,
  `conjoint_niveau_etudes` varchar(50) DEFAULT NULL,
  `conjoint_etablissement_etudes` varchar(100) DEFAULT NULL,
  `enfants_informations` text DEFAULT NULL,
  `numero_dossier` varchar(50) NOT NULL,
  `statut` enum('en_attente','en_cours','approuve','refuse') DEFAULT 'en_attente',
  `commentaire_admin` text DEFAULT NULL,
  `date_soumission` datetime NOT NULL,
  `date_traitement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `demande_visa_court_sejour`
--

INSERT INTO `demande_visa_court_sejour` (`id`, `nom_famille`, `prenoms`, `date_naissance`, `lieu_naissance`, `pays_naissance`, `nationalite`, `sexe`, `statut_matrimonial`, `adresse`, `ville`, `code_postal`, `pays_residence`, `telephone`, `email`, `passeport_numero`, `passeport_pays`, `passeport_date_emission`, `passeport_date_expiration`, `but_visite`, `date_arrivee`, `date_depart`, `duree_sejour`, `hebergement_type`, `hebergement_adresse`, `personne_contact_nom`, `personne_contact_telephone`, `personne_contact_relation`, `employeur_nom`, `employeur_adresse`, `employeur_telephone`, `profession`, `situation_professionnelle`, `refus_visa_precedent`, `details_refus`, `maladies_graves`, `details_maladies`, `condamnations_judiciaires`, `details_condamnations`, `service_militaire`, `details_service_militaire`, `documents_passeport`, `documents_photo`, `documents_fonds`, `documents_vol`, `documents_hebergement`, `documents_conjoint`, `documents_enfants`, `documents_autres`, `conjoint_nom`, `conjoint_prenoms`, `conjoint_date_naissance`, `conjoint_lieu_naissance`, `conjoint_nationalite`, `conjoint_profession`, `conjoint_employeur`, `conjoint_experience_professionnelle`, `conjoint_niveau_etudes`, `conjoint_etablissement_etudes`, `enfants_informations`, `numero_dossier`, `statut`, `commentaire_admin`, `date_soumission`, `date_traitement`) VALUES
(1, 'Dupont', 'Jean', '1985-05-15', 'Paris', 'France', 'Française', 'M', 'Célibataire', '123 Avenue des Champs-Élysées', 'Paris', '75008', 'France', '+33123456789', 'jean.dupont@email.com', '12AB34567', 'France', '2020-01-15', '2030-01-15', 'Tourisme', '2024-06-01', '2024-06-15', '2 semaines', 'Hôtel', 'Hôtel Plaza, Montréal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Employé', 'Non', NULL, 'Non', NULL, 'Non', NULL, 'Non', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'VCS-20240115-0001', '', NULL, '2025-10-19 21:00:36', '2025-10-19 23:39:39'),
(3, 'Bernard', 'Pierre', '1978-12-03', 'Marseille', 'France', 'Française', 'M', 'Divorcé(e)', '789 Boulevard Saint-Charles', 'Marseille', '13008', 'France', '+33456789013', 'pierre.bernard@email.com', '56EF78901', 'France', '2021-06-10', '2031-06-10', 'Affaires', '2024-05-01', '2024-05-07', '1 semaine', 'Hôtel', 'Business Hotel, Vancouver', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Employé', 'Oui', NULL, 'Non', NULL, 'Non', NULL, 'Non', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'VCS-20240115-0003', 'approuve', NULL, '2025-10-19 21:00:36', NULL),
(4, 'melissa', 'chahid', '2007-03-31', 'ffff', 'Algérie', 'jkjkkj', 'F', 'Célibataire', 'vfvfvv', 'sdfghjk', '5678', 'Algérie', '0556603313', 'melissalounis551@gmail.com', '3453423f', 'vvdf', '2023-03-31', '2029-03-31', 'Tourisme', '2025-10-23', '2028-11-19', '38 mois', 'Hôtel', 'vfvfvv', NULL, NULL, NULL, NULL, NULL, NULL, 'rffe', 'Employé', 'Non', NULL, 'Non', NULL, 'Non', NULL, 'Non', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'VCS-20251019-3286', 'refuse', NULL, '2025-10-19 23:34:55', '2025-10-19 23:39:51'),
(5, 'melissa', 'chahid', '2007-03-31', 'ffff', 'Algérie', 'jkjkkj', 'F', 'Célibataire', 'vfvfvv', 'sdfghjk', '5678', 'Algérie', '0556603313', 'melissalounis551@gmail.com', '3453423f', 'vvdf', '2023-03-31', '2029-03-31', 'Tourisme', '2025-10-23', '2028-11-19', '38 mois', 'Hôtel', 'vfvfvv', 'chahid melissa', '0556603313', 'ferfr', 'rferfrfer', 'rfefe', '03444444', 'rffe', 'Employé', 'Non', '', 'Non', '', 'Non', '', 'Non', '', 'documents/VCS-20251020-1162_passeport.pdf', 'documents/VCS-20251020-1162_photo_identite.jpg', 'documents/VCS-20251020-1162_justificatif_fonds.pdf', '', 'documents/VCS-20251020-1162_hebergement_preuve.pdf', '', '', 'documents/VCS-20251020-1162_autres_documents.pdf', '', '', '0000-00-00', '', '', '', '', '', '', '', '', 'VCS-20251020-1162', 'en_attente', NULL, '2025-10-20 00:28:40', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demande_visa_imm5257`
--

CREATE TABLE `demande_visa_imm5257` (
  `id` int(11) NOT NULL,
  `nom_famille` varchar(100) DEFAULT NULL,
  `prenoms` varchar(100) DEFAULT NULL,
  `nom_naissance` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `pays_naissance` varchar(100) DEFAULT NULL,
  `nationalite_actuelle` varchar(100) DEFAULT NULL,
  `autre_nationalite` varchar(100) DEFAULT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `statut_matrimonial` enum('Célibataire','Marié(e)','Conjoint(e) de fait','Divorcé(e)','Séparé(e)','Veuf(ve)') DEFAULT NULL,
  `statut_residence` varchar(100) DEFAULT NULL,
  `adresse_rue` varchar(255) DEFAULT NULL,
  `adresse_ville` varchar(100) DEFAULT NULL,
  `adresse_province` varchar(100) DEFAULT NULL,
  `adresse_code_postal` varchar(20) DEFAULT NULL,
  `adresse_pays` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `telephone_autre` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `passeport_numero` varchar(50) DEFAULT NULL,
  `passeport_pays` varchar(100) DEFAULT NULL,
  `passeport_date_emission` date DEFAULT NULL,
  `passeport_date_expiration` date DEFAULT NULL,
  `but_visite` enum('Tourisme','Visite familiale','Affaires','Transit','Autre') DEFAULT NULL,
  `duree_sejour` varchar(50) DEFAULT NULL,
  `fonds_disponibles` decimal(12,2) DEFAULT NULL,
  `personne_visitee` varchar(100) DEFAULT NULL,
  `relation_personne` varchar(100) DEFAULT NULL,
  `adresse_canada` varchar(255) DEFAULT NULL,
  `education_niveau` varchar(100) DEFAULT NULL,
  `emploi_actuel` varchar(100) DEFAULT NULL,
  `employeur` varchar(100) DEFAULT NULL,
  `ville_employeur` varchar(100) DEFAULT NULL,
  `pays_employeur` varchar(100) DEFAULT NULL,
  `date_debut_emploi` date DEFAULT NULL,
  `sante_probleme` enum('Oui','Non') DEFAULT NULL,
  `sante_details` text DEFAULT NULL,
  `refus_visa_pays` varchar(100) DEFAULT NULL,
  `refus_details` text DEFAULT NULL,
  `expulsion_pays` varchar(100) DEFAULT NULL,
  `expulsion_details` text DEFAULT NULL,
  `condamnation_criminelle` enum('Oui','Non') DEFAULT NULL,
  `condamnation_details` text DEFAULT NULL,
  `service_militaire` enum('Oui','Non') DEFAULT NULL,
  `service_details` text DEFAULT NULL,
  `organisation_membre` enum('Oui','Non') DEFAULT NULL,
  `organisation_details` text DEFAULT NULL,
  `informations_supplementaires` text DEFAULT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','soumis','traite') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `type_document` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `verifie` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents_demande`
--

CREATE TABLE `documents_demande` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin` varchar(500) NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents_demande`
--

INSERT INTO `documents_demande` (`id`, `demande_id`, `nom_fichier`, `chemin`, `type_document`, `date_upload`) VALUES
(1, 1, 'Capture d’écran 2025-09-09 000543.jpg', 'uploads/demandes/1/68de6751aaedd_Capture d’écran 2025-09-09 000543.jpg', 'green_card', '2025-10-02 12:51:45');

-- --------------------------------------------------------

--
-- Structure de la table `documents_immigration`
--

CREATE TABLE `documents_immigration` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `type_document` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille` int(11) NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents_immigration`
--

INSERT INTO `documents_immigration` (`id`, `evaluation_id`, `type_document`, `nom_fichier`, `chemin_fichier`, `taille`, `date_upload`) VALUES
(1, 22, 'passeport', 'Attestation d\'acces au deuxième cycle d\'étude.docx', 'uploads/68fa8ed4966c4_Attestation_d_acces_au_deuxi__me_cycle_d___tude.docx', 19470, '2025-10-23 21:23:48'),
(2, 23, 'passeport', 'BABYLONE SERVICES2.docx', 'uploads/68fa93913fa92_BABYLONE_SERVICES2.docx', 27208, '2025-10-23 21:44:01'),
(3, 24, 'passeport', 'Attestation d\'acces au deuxième cycle d\'étude.docx', 'uploads/1761254020_passeport_68fa9a84a7a94.docx', 19470, '2025-10-23 22:13:40'),
(4, 24, 'naissance', 'BABYLONE SERVICES2.docx', 'uploads/1761254020_naissance_68fa9a84a9140.docx', 27208, '2025-10-23 22:13:40');

-- --------------------------------------------------------

--
-- Structure de la table `documents_rendez_vous`
--

CREATE TABLE `documents_rendez_vous` (
  `id` int(11) NOT NULL,
  `rendez_vous_id` int(11) DEFAULT NULL,
  `nom_original` varchar(255) DEFAULT NULL,
  `chemin_fichier` varchar(500) DEFAULT NULL,
  `type_document` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etude_belgique`
--

CREATE TABLE `etude_belgique` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `naissance` date NOT NULL,
  `nationalite` varchar(50) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `niveau_etude` varchar(20) NOT NULL,
  `equivalence_bac` enum('oui','non') DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `passport` varchar(255) NOT NULL,
  `document_equivalence` varchar(255) DEFAULT NULL,
  `releve_2nde` varchar(255) DEFAULT NULL,
  `releve_1ere` varchar(255) DEFAULT NULL,
  `releve_terminale` varchar(255) DEFAULT NULL,
  `releve_bac` varchar(255) DEFAULT NULL,
  `diplome_bac` varchar(255) DEFAULT NULL,
  `releve_l1` varchar(255) DEFAULT NULL,
  `releve_l2` varchar(255) DEFAULT NULL,
  `releve_l3` varchar(255) DEFAULT NULL,
  `diplome_licence` varchar(255) DEFAULT NULL,
  `certificat_scolarite` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etude_belgique`
--

INSERT INTO `etude_belgique` (`id`, `nom`, `naissance`, `nationalite`, `telephone`, `email`, `adresse`, `niveau_etude`, `equivalence_bac`, `photo`, `passport`, `document_equivalence`, `releve_2nde`, `releve_1ere`, `releve_terminale`, `releve_bac`, `diplome_bac`, `releve_l1`, `releve_l2`, `releve_l3`, `diplome_licence`, `certificat_scolarite`, `status`, `created_at`, `updated_at`) VALUES
(1, 'chahid melissa', '2002-02-22', 'jkjkkj', '0556603313', 'kami@gmail.com', 'fv', 'l1', 'non', '6907ec3b1e003_BABYLONE SERVICES.docx', '6907ec3b1e491_BABYLONE SERVICES2.docx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-11-02 23:41:47', '2025-11-02 23:41:47'),
(2, 'chahid melissa', '2002-02-22', 'jkjkkj', '0556603313', 'kami@gmail.com', 'fv', 'l1', 'non', '6907ec7498523_BABYLONE SERVICES.docx', '6907ec74988df_BABYLONE SERVICES2.docx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2025-11-02 23:42:44', '2025-11-02 23:42:44');

-- --------------------------------------------------------

--
-- Structure de la table `evaluations_immigration`
--

CREATE TABLE `evaluations_immigration` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `education` varchar(150) DEFAULT NULL,
  `diplome` varchar(150) NOT NULL,
  `experience` int(11) NOT NULL,
  `langue` varchar(100) NOT NULL,
  `english_level` varchar(50) NOT NULL,
  `french_level` varchar(50) NOT NULL,
  `situation_familiale` enum('celibataire','marie','divorce','veuf') NOT NULL DEFAULT 'celibataire',
  `enfants` int(11) NOT NULL DEFAULT 0,
  `province` varchar(100) DEFAULT NULL,
  `offre_emploi` enum('oui','non') NOT NULL DEFAULT 'non',
  `famille_canada` enum('oui','non') NOT NULL DEFAULT 'non',
  `programme` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `eligible` tinyint(1) NOT NULL DEFAULT 0,
  `pays_cible` varchar(100) DEFAULT 'Canada',
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','en_cours','accepte','refuse') DEFAULT 'en_attente',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evaluations_immigration`
--

INSERT INTO `evaluations_immigration` (`id`, `user_id`, `nom`, `prenom`, `email`, `telephone`, `age`, `education`, `diplome`, `experience`, `langue`, `english_level`, `french_level`, `situation_familiale`, `enfants`, `province`, `offre_emploi`, `famille_canada`, `programme`, `score`, `eligible`, `pays_cible`, `date_soumission`, `statut`, `notes`) VALUES
(1, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 4, '', '10', '9', 'celibataire', 0, 'ontario', 'oui', 'oui', 'express', 100, 1, 'Canada', '2025-09-30 23:06:25', 'en_attente', NULL),
(2, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 18, 'post-secondary', '', 4, '', '8', '7', 'celibataire', 0, 'ontario', 'oui', 'oui', 'express', 94, 1, 'Canada', '2025-10-01 08:17:40', 'en_attente', NULL),
(3, 0, 'chahid melissa', '', 'kami@gmail.com', '', 22, 'post-secondary', '', 4, '', '9', '9', 'celibataire', 0, 'ontario', 'non', 'non', 'express', 104, 1, 'Canada', '2025-10-01 09:23:07', 'en_attente', NULL),
(4, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 4, '', '9', '8', 'celibataire', 0, 'ontario', 'oui', 'non', 'express', 97, 1, 'Canada', '2025-10-01 09:28:07', 'en_attente', NULL),
(5, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 4, '', '9', '9', 'celibataire', 0, 'ontario', 'oui', 'non', 'express', 100, 1, 'Canada', '2025-10-01 09:29:08', 'en_attente', NULL),
(6, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 2, '', '4', '9', 'celibataire', 0, 'quantero', 'oui', 'non', 'express', 62, 0, 'Canada', '2025-10-01 10:41:26', 'en_attente', NULL),
(7, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 2, '', '4', '9', 'celibataire', 0, 'quantero', 'oui', 'non', 'express', 62, 0, 'Canada', '2025-10-01 10:43:48', 'en_attente', NULL),
(8, 0, 'chahid', '', 'cl051@vmsindustrie.net', '', 18, 'master', '', 2, '', '4', '9', 'celibataire', 0, 'quantero', 'oui', 'non', 'express', 70, 1, 'Canada', '2025-10-01 10:48:08', 'en_attente', NULL),
(9, 0, 'chahid melissa', '', 'kami@gmail.com', '', 18, 'secondary', '', 2, '', '4', '9', 'celibataire', 0, 'quantero', 'oui', 'non', 'express', 62, 0, 'Canada', '2025-10-01 10:50:08', 'en_attente', NULL),
(10, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 34, 'master', '', 4, '', '4', '9', 'celibataire', 0, 'quantero', 'oui', 'oui', 'express', 72, 1, 'Canada', '2025-10-01 10:51:20', 'en_attente', NULL),
(11, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 34, 'master', '', 4, '', '4', '9', 'marie', 2, 'quantero', 'oui', 'oui', 'express', 72, 1, 'Canada', '2025-10-01 11:16:16', 'en_attente', NULL),
(12, 0, 'chahid melissa', '', 'kami@gmail.com', '', 21, 'master', '', 4, '', '6', '8', 'celibataire', 0, 'Quebec', 'oui', 'oui', 'arrima', 63, 1, 'Canada', '2025-10-01 11:38:52', 'en_attente', NULL),
(13, 0, 'chahid melissa', '', 'kami@gmail.com', '', 21, 'master', '', 4, '', '6', '8', 'celibataire', 1, 'Quebec', 'oui', 'oui', 'arrima', 65, 1, 'Canada', '2025-10-01 11:49:26', 'en_attente', NULL),
(14, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 34, 'master', '', 4, '', '4', '9', 'celibataire', 2, 'quantero', 'oui', 'oui', 'express', 72, 1, 'Canada', '2025-10-01 14:29:02', 'en_attente', NULL),
(15, 0, 'chahid melissa', '', 'kami@gmail.com', '', 21, 'master', '', 4, '', '6', '8', 'celibataire', 1, 'Quebec', 'oui', 'oui', 'arrima', 65, 1, 'Canada', '2025-10-01 14:39:38', 'en_attente', NULL),
(16, 0, 'chahid melissa', '', 'kami@gmail.com', '', 21, 'secondary', '', 0, '', '6', '5', 'celibataire', 0, 'Quebec', 'non', 'oui', 'arrima', 21, 0, 'Canada', '2025-10-01 14:41:32', 'en_attente', NULL),
(17, 0, 'chahid melissa', '', 'kami@gmail.com', '', 21, 'secondary', '', 4, '', '6', '8', 'celibataire', 1, 'Quebec', 'oui', 'oui', 'arrima', 55, 1, 'Canada', '2025-10-01 14:44:04', 'en_attente', NULL),
(18, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 34, 'master', '', 4, '', '4', '9', 'marie', 2, 'quantero', 'oui', 'oui', 'express', 72, 1, 'Canada', '2025-10-01 15:04:11', 'en_attente', NULL),
(19, 0, 'chahid melissa', '', 'melissalounis551@gmail.com', '', 34, 'secondary', '', 0, '', '4', '8', 'celibataire', 0, 'quantero', 'non', 'non', 'express', 49, 0, 'Canada', '2025-10-01 15:06:07', 'en_attente', NULL),
(20, 0, 'chahid melissa', '', 'kami@gmail.com', '', 33, 'secondary', '', 6, '', '5', '9', 'celibataire', 0, 'quantero', 'oui', 'oui', 'express', 66, 0, 'Canada', '2025-10-23 18:55:11', 'en_attente', NULL),
(21, 0, 'chahid melissa', '', 'kami@gmail.com', '', 33, 'phd', '', 6, '', '5', '9', 'marie', 3, 'quantero', 'oui', 'oui', 'express', 76, 1, 'Canada', '2025-10-23 18:55:53', 'en_attente', NULL),
(22, 0, 'chahid melissa', '', 'kami@gmail.com', '', 33, 'master', '', 6, '', '5', '9', 'celibataire', 3, 'quantero', 'oui', 'oui', 'express', 74, 1, 'Canada', '2025-10-23 19:46:38', 'en_attente', NULL),
(23, 0, 'chahid melissa', '', 'kami@gmail.com', '', 33, 'master', '', 6, '', '5', '9', 'celibataire', 3, 'quantero', 'oui', 'oui', 'express', 74, 1, 'Canada', '2025-10-23 20:34:49', 'en_attente', NULL),
(24, 0, 'chahid melissa', '', 'kami@gmail.com', '', 33, 'master', '', 6, '', '5', '9', 'celibataire', 3, 'Quebec', 'oui', 'oui', 'arrima', 69, 1, 'Canada', '2025-10-23 20:46:33', 'en_attente', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_demandes`
--

CREATE TABLE `fichiers_demandes` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `type_fichier` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin` text NOT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fichiers_demandes`
--

INSERT INTO `fichiers_demandes` (`id`, `demande_id`, `type_fichier`, `nom_fichier`, `chemin`, `date_upload`) VALUES
(1, 6, 'photo_identite', 'photo_identite_691251369d90c.PNG', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/6/photo_identite_691251369d90c.PNG', '2025-11-10 21:55:18'),
(2, 6, 'piece_identite', 'piece_identite_691251369e67e.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/6/piece_identite_691251369e67e.pdf', '2025-11-10 21:55:18'),
(3, 6, 'certificat_scolarite', 'certificat_scolarite_691251369eb9d.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/6/certificat_scolarite_691251369eb9d.docx', '2025-11-10 21:55:18'),
(4, 7, 'photo_identite', 'photo_identite_6912518b87dc6.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/7/photo_identite_6912518b87dc6.jpg', '2025-11-10 21:56:43'),
(5, 7, 'piece_identite', 'piece_identite_6912518b88396.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/7/piece_identite_6912518b88396.jpg', '2025-11-10 21:56:43'),
(6, 7, 'lettre_motivation', 'lettre_motivation_6912518b89680.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/7/lettre_motivation_6912518b89680.docx', '2025-11-10 21:56:43'),
(7, 7, 'certificat_scolarite', 'certificat_scolarite_6912518b89da1.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/7/certificat_scolarite_6912518b89da1.docx', '2025-11-10 21:56:43'),
(8, 9, 'photo_identite', 'photo_identite_69125d5508d59.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/9/photo_identite_69125d5508d59.jpg', '2025-11-10 22:47:01'),
(9, 9, 'piece_identite', 'piece_identite_69125d550a4ac.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/9/piece_identite_69125d550a4ac.jpg', '2025-11-10 22:47:01'),
(10, 9, 'lettre_motivation', 'lettre_motivation_69125d550ac0d.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/9/lettre_motivation_69125d550ac0d.docx', '2025-11-10 22:47:01'),
(11, 9, 'certificat_scolarite', 'certificat_scolarite_69125d550da43.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/9/certificat_scolarite_69125d550da43.docx', '2025-11-10 22:47:01'),
(12, 9, 'releve_notes_1', 'releve_notes_1_69125d550e3d3.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/9/releve_notes_1_69125d550e3d3.pdf', '2025-11-10 22:47:01'),
(13, 10, 'photo_identite', 'photo_identite_69125d9abdf0a.PNG', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/10/photo_identite_69125d9abdf0a.PNG', '2025-11-10 22:48:10'),
(14, 10, 'piece_identite', 'piece_identite_69125d9abea75.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/10/piece_identite_69125d9abea75.pdf', '2025-11-10 22:48:10'),
(15, 10, 'certificat_scolarite', 'certificat_scolarite_69125d9ac18b9.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/10/certificat_scolarite_69125d9ac18b9.docx', '2025-11-10 22:48:10'),
(16, 10, 'releve_notes_1', 'releve_notes_1_69125d9ac251c.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/10/releve_notes_1_69125d9ac251c.jpg', '2025-11-10 22:48:10'),
(17, 11, 'photo_identite', 'photo_identite_6912622faaf52.PNG', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/11/photo_identite_6912622faaf52.PNG', '2025-11-10 23:07:43'),
(18, 11, 'piece_identite', 'piece_identite_6912622fae801.pdf', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/11/piece_identite_6912622fae801.pdf', '2025-11-10 23:07:43'),
(19, 11, 'certificat_scolarite', 'certificat_scolarite_6912622fb353c.docx', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/11/certificat_scolarite_6912622fb353c.docx', '2025-11-10 23:07:43'),
(20, 11, 'releve_notes_1', 'releve_notes_1_6912622fb9e0f.jpg', 'C:\\xampp\\htdocs\\babylone\\public\\france\\etudes/../uploads/parcoursup/11/releve_notes_1_6912622fb9e0f.jpg', '2025-11-10 23:07:43');

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_paiements`
--

CREATE TABLE `fichiers_paiements` (
  `id` int(11) NOT NULL,
  `paiement_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `type_fichier` varchar(50) DEFAULT NULL,
  `taille_fichier` int(11) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `garants`
--

CREATE TABLE `garants` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `lien_familial` varchar(100) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `immigration_evaluations`
--

CREATE TABLE `immigration_evaluations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `programme` enum('express','arrima') NOT NULL,
  `age` int(11) NOT NULL,
  `education` enum('secondary','post-secondary','bachelor','master','phd') DEFAULT NULL,
  `experience` int(11) NOT NULL,
  `niveau_anglais` int(11) DEFAULT NULL,
  `niveau_francais` int(11) DEFAULT NULL,
  `score_total` int(11) NOT NULL,
  `eligible` tinyint(1) DEFAULT 0,
  `date_evaluation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `langue_tests`
--

CREATE TABLE `langue_tests` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `adresse` text NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `pays` varchar(50) NOT NULL,
  `type_piece` varchar(50) NOT NULL,
  `numero_piece` varchar(100) NOT NULL,
  `date_emission_piece` date NOT NULL,
  `date_expiration_piece` date NOT NULL,
  `fichier_piece` varchar(255) DEFAULT NULL,
  `fichier_passeport` varchar(255) DEFAULT NULL,
  `type_test` varchar(50) NOT NULL,
  `date_rendezvous` date NOT NULL,
  `heure_rendezvous` time NOT NULL,
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `langue_tests`
--

INSERT INTO `langue_tests` (`id`, `nom`, `prenom`, `email`, `telephone`, `adresse`, `ville`, `code_postal`, `pays`, `type_piece`, `numero_piece`, `date_emission_piece`, `date_expiration_piece`, `fichier_piece`, `fichier_passeport`, `type_test`, `date_rendezvous`, `heure_rendezvous`, `statut`, `date_creation`) VALUES
(1, 'melissa', 'chahid', 'kami@gmail.com', '0556603313', 'vfvfvv', 'sdfghjk', '5678', 'algerie', 'passeport', '23456789', '2025-10-10', '2035-07-06', '68eac1f6e9cdd_piece.pdf', '68eac1f6ebb58_passeport.pdf', 'tef_canada', '2025-10-25', '01:30:00', 'en_attente', '2025-10-11 21:45:42');

-- --------------------------------------------------------

--
-- Structure de la table `membres_famille`
--

CREATE TABLE `membres_famille` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `relation` enum('conjoint','enfant') DEFAULT NULL,
  `type_document` varchar(50) DEFAULT NULL,
  `chemin_document` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `fichier_nom` varchar(255) DEFAULT NULL,
  `fichier_chemin` varchar(500) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` varchar(20) DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `reference`, `nom`, `prenom`, `email`, `telephone`, `montant`, `service`, `message`, `fichier_nom`, `fichier_chemin`, `date_creation`, `statut`) VALUES
(1, 'REF-20251122-220044-6314', 'melissa', 'admin', 'kami@gmail.com', '+213556603313', 222222.00, 'standard', '', 'REF-20251122-220044-6314.pdf', 'uploads/REF-20251122-220044-6314.pdf', '2025-11-22 22:00:44', 'confirme');

-- --------------------------------------------------------

--
-- Structure de la table `passagers_billets`
--

CREATE TABLE `passagers_billets` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `civilite` varchar(10) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `numero_passeport` varchar(100) NOT NULL,
  `expiration_passeport` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `passagers_billets`
--

INSERT INTO `passagers_billets` (`id`, `demande_id`, `civilite`, `nom`, `prenom`, `date_naissance`, `numero_passeport`, `expiration_passeport`, `nationalite`, `created_at`) VALUES
(1, 1, 'Mme', 'melissa', 'chahid', '2002-10-22', '4456566666', '2027-05-05', 'jkjkkj', '2025-10-24 15:52:41'),
(2, 2, 'Mme', 'melissa', 'chahid', '2002-10-22', '4456566666', '2027-05-05', 'jkjkkj', '2025-10-24 15:55:46'),
(3, 3, 'M', 'melissa', 'chahid', '2002-02-22', '4456566666', '2027-05-05', 'jkjkkj', '2025-10-24 16:21:42'),
(4, 1, 'M', 'melissa', 'chahid', '2002-02-22', '4456566666', '2027-05-05', 'jkjkkj', '2025-10-24 17:10:28'),
(5, 2, 'M', 'melissa', 'chahid', '2002-02-22', '4456566666', '2027-05-05', 'jkjkkj', '2025-10-24 17:33:51'),
(6, 3, 'M', 'melissa', 'admin', '2003-03-31', '4456566666', '2027-02-22', 'ffff', '2025-11-22 10:48:31'),
(7, 4, 'M', 'melissa', 'admin', '2003-03-31', '4456566666', '2027-02-22', 'ffff', '2025-11-22 11:34:59');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(1, 'melissalounis551@gmail.com', '0c915d1f828183327dd9aa8cc8fed9966972000e82e25a837649e5db4f381116', '2025-11-11 20:49:51', 0, '2025-11-11 18:49:51');

-- --------------------------------------------------------

--
-- Structure de la table `pays`
--

CREATE TABLE `pays` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `type_visa` enum('schengen','national') NOT NULL,
  `site_officiel` varchar(255) DEFAULT NULL,
  `delai_traitement` int(11) DEFAULT 15,
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pays`
--

INSERT INTO `pays` (`id`, `nom`, `code`, `type_visa`, `site_officiel`, `delai_traitement`, `actif`) VALUES
(1, 'France', 'FR', 'schengen', 'https://france-visas.gouv.fr', 10, 1),
(2, 'Canada', 'CA', 'national', 'https://www.canada.ca/fr/immigration-refugies-citoyennete.html', 20, 1),
(3, 'Allemagne', 'DE', 'schengen', 'https://www.auswaertiges-amt.de/fr', 12, 1),
(4, 'Belgique', 'BE', 'schengen', 'https://dofi.ibz.be', 15, 1),
(5, 'Espagne', 'ES', 'schengen', 'https://www.spain-visas.com', 14, 1),
(6, 'Italie', 'IT', 'schengen', 'https://vistoperitalia.esteri.it', 13, 1),
(7, 'Suisse', 'CH', 'schengen', 'https://www.sem.admin.ch', 11, 1),
(8, 'Luxembourg', 'LU', 'schengen', 'https://guichet.public.lu', 16, 1),
(9, 'Malte', 'MT', 'schengen', 'https://identitymalta.com/visas', 18, 1),
(10, 'Roumanie', 'RO', 'national', 'https://www.mae.ro', 25, 1),
(11, 'Bulgarie', 'BG', 'national', 'https://www.mfa.bg', 22, 1),
(12, 'Irlande', 'IE', 'national', 'https://www.irishimmigration.ie', 20, 1);

-- --------------------------------------------------------

--
-- Structure de la table `programmes_canada`
--

CREATE TABLE `programmes_canada` (
  `id` int(11) NOT NULL,
  `universite_id` int(11) NOT NULL,
  `nom_programme` varchar(200) NOT NULL,
  `niveau` enum('bac','l1','l2','l3','master','doctorat') NOT NULL,
  `domaine` varchar(100) NOT NULL,
  `duree_mois` int(11) NOT NULL,
  `frais_scolarite` decimal(10,2) DEFAULT NULL,
  `langue_enseignement` enum('francais','anglais','bilingue') NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `programmes_canada`
--

INSERT INTO `programmes_canada` (`id`, `universite_id`, `nom_programme`, `niveau`, `domaine`, `duree_mois`, `frais_scolarite`, `langue_enseignement`, `description`) VALUES
(1, 1, 'Baccalauréat en informatique', 'bac', 'Informatique', 36, 15000.00, 'francais', NULL),
(2, 1, 'Maîtrise en génie électrique', 'master', 'Génie', 24, 18000.00, 'francais', NULL),
(3, 2, 'Bachelor of Science in Computer Science', 'bac', 'Informatique', 36, 25000.00, 'anglais', NULL),
(4, 2, 'Master of Business Administration', 'master', 'Gestion', 20, 45000.00, 'anglais', NULL),
(5, 3, 'Baccalauréat en administration', 'bac', 'Gestion', 36, 12000.00, 'francais', NULL),
(6, 4, 'Bachelor of Engineering', 'bac', 'Génie', 48, 35000.00, 'anglais', NULL),
(7, 5, 'Master of Data Science', 'master', 'Informatique', 24, 28000.00, 'anglais', NULL),
(8, 6, 'PhD in Psychology', 'doctorat', 'Psychologie', 48, 12000.00, 'anglais', NULL),
(9, 7, 'Baccalauréat bilingue en commerce', 'bac', 'Gestion', 36, 16000.00, 'bilingue', NULL),
(10, 8, 'Master of Public Health', 'master', 'Santé', 24, 22000.00, 'anglais', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous_tcf`
--

CREATE TABLE `rendezvous_tcf` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `type_piece` varchar(50) NOT NULL,
  `numero_piece` varchar(100) NOT NULL,
  `date_emission_piece` date NOT NULL,
  `date_expiration_piece` date NOT NULL,
  `type_test` varchar(50) NOT NULL,
  `date_naissance` date NOT NULL,
  `date_rendezvous` date NOT NULL,
  `heure_rendezvous` time NOT NULL,
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `lieu_naissance` varchar(100) NOT NULL,
  `pays_naissance` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendezvous_tcf`
--

INSERT INTO `rendezvous_tcf` (`id`, `nom`, `prenom`, `email`, `telephone`, `type_piece`, `numero_piece`, `date_emission_piece`, `date_expiration_piece`, `type_test`, `date_naissance`, `date_rendezvous`, `heure_rendezvous`, `statut`, `date_creation`, `lieu_naissance`, `pays_naissance`) VALUES
(1, 'melissa', 'chahid', 'kami@gmail.com', '0556603313', 'passeport', '23456789', '2025-09-06', '2027-06-20', 'tcf_tp', '2025-09-27', '2027-05-31', '03:07:00', 'en_attente', '2025-09-28 01:05:06', 'jjjj', 'france');

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

CREATE TABLE `rendez_vous` (
  `id` int(11) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `pays_destination` varchar(100) NOT NULL,
  `type_demande` enum('premiere_demande','renouvellement') NOT NULL,
  `type_client` enum('individuel','famille','groupe') NOT NULL,
  `nombre_personnes` int(11) DEFAULT 1,
  `motif_voyage` varchar(100) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `adresse` text NOT NULL,
  `type_hebergement` varchar(100) NOT NULL,
  `adresse_hebergement` text NOT NULL,
  `date_arrivee` date NOT NULL,
  `date_depart` date NOT NULL,
  `statut` enum('en_attente','confirme','annule') DEFAULT 'en_attente',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_maj` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendez_vous`
--

INSERT INTO `rendez_vous` (`id`, `reference`, `pays_destination`, `type_demande`, `type_client`, `nombre_personnes`, `motif_voyage`, `nom`, `prenom`, `date_naissance`, `nationalite`, `email`, `telephone`, `adresse`, `type_hebergement`, `adresse_hebergement`, `date_arrivee`, `date_depart`, `statut`, `date_creation`, `date_maj`) VALUES
(1, 'RDV-20251004-165952-866F02', 'france', 'premiere_demande', 'individuel', 1, 'tourisme', 'melissa', 'chahid', '2001-10-24', 'hhh', 'kami@gmail.com', '0556603313', 'vfvfvv', 'hotel', 'vfvfvv', '2026-03-23', '2026-12-24', 'confirme', '2025-10-04 15:59:52', '2025-10-10 21:58:09');

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous_biometrie`
--

CREATE TABLE `rendez_vous_biometrie` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `numero_passeport` varchar(100) NOT NULL,
  `personnes_supplementaires` text DEFAULT NULL,
  `passeport_path` varchar(255) NOT NULL,
  `lettre_biometrie_path` varchar(255) NOT NULL,
  `statut` enum('nouveau','confirme','annule','termine') DEFAULT 'nouveau',
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendez_vous_biometrie`
--

INSERT INTO `rendez_vous_biometrie` (`id`, `nom_complet`, `email`, `telephone`, `nationalite`, `date_naissance`, `numero_passeport`, `personnes_supplementaires`, `passeport_path`, `lettre_biometrie_path`, `statut`, `date_creation`) VALUES
(1, 'chahid melissa', 'kami@gmail.com', '+213556603313', 'jkjkkj', '2002-06-06', '4567890', NULL, '68f414c53ab50_passeport_casier_judiciere.pdf', '68f414c53ae4b_lettre_biometrie_casier_judiciere.pdf', 'nouveau', '2025-10-18 23:29:25'),
(2, 'chahid melissa', 'kami@gmail.com', '+213556603313', 'jkjkkj', '2002-06-06', '4567890', NULL, '68f419a9d8f8d_passeport_casier_judiciere.pdf', '68f419a9d9495_lettre_biometrie_casier_judiciere.pdf', 'nouveau', '2025-10-18 23:50:17'),
(3, 'chahid melissa', 'kami@gmail.com', '+213556603313', 'jkjkkj', '2002-06-06', '4567890', NULL, '68f41a87d4dfe_passeport_casier_judiciere.pdf', '68f41a87d50a1_lettre_biometrie_casier_judiciere.pdf', 'nouveau', '2025-10-18 23:53:59'),
(4, 'chahid melissa', 'kami@gmail.com', '+213556603313', 'jkjkkj', '2002-06-06', '4567890', NULL, '68f41bbee15c2_passeport_casier_judiciere.pdf', '68f41bbee196c_lettre_biometrie_casier_judiciere.pdf', 'nouveau', '2025-10-18 23:59:10');

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous_personnes_supp`
--

CREATE TABLE `rendez_vous_personnes_supp` (
  `id` int(11) NOT NULL,
  `rendez_vous_id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `numero_passeport` varchar(50) NOT NULL,
  `passeport_path` varchar(500) DEFAULT NULL,
  `lettre_biometrie_path` varchar(500) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `titre`, `slug`, `description`, `created_at`) VALUES
(1, 'Visa France', 'campusfrance.php', 'Accompagnement complet pour demande de visa France, campus france ,parcoursup ,contrat de travail', '2025-08-22 01:23:35'),
(2, 'Visa Turquie', 'turquie.php', 'Assistance pour la préparation des documents de visa Turquie.', '2025-08-22 01:23:35'),
(3, 'Assurance Voyage', 'assurance.php', 'Couverture médicale et assurance voyage internationale.', '2025-08-22 01:23:35'),
(4, 'Hébergement étudiant', 'hebergement.php', 'Aide à la recherche de logement pour étudiants.', '2025-08-22 01:23:35'),
(5, 'Admission Canada', 'canada.php', 'Demande d\'admission dans les universités et collèges canadiens', '2025-09-25 10:33:47');

-- --------------------------------------------------------

--
-- Structure de la table `study_applications`
--

CREATE TABLE `study_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_fullname` varchar(200) NOT NULL,
  `dob` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `passport_number` varchar(100) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `intended_school` varchar(255) DEFAULT NULL,
  `program_name` varchar(255) DEFAULT NULL,
  `program_start` date DEFAULT NULL,
  `acceptance_letter_file` varchar(255) DEFAULT NULL,
  `passport_file` varchar(255) DEFAULT NULL,
  `photo_file` varchar(255) DEFAULT NULL,
  `proof_of_funds_file` varchar(255) DEFAULT NULL,
  `other_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`other_documents`)),
  `status` varchar(50) DEFAULT 'collected',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `suivi_demandes_luxembourg`
--

CREATE TABLE `suivi_demandes_luxembourg` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `statut` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `administrateur` varchar(100) DEFAULT NULL,
  `date_suivi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `suivi_demandes_suisse`
--

CREATE TABLE `suivi_demandes_suisse` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `statut` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_suivi` timestamp NOT NULL DEFAULT current_timestamp(),
  `administrateur` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `suivi_demandes_turquie`
--

CREATE TABLE `suivi_demandes_turquie` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) DEFAULT NULL,
  `statut` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_suivi` timestamp NOT NULL DEFAULT current_timestamp(),
  `administrateur` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `types_visa`
--

CREATE TABLE `types_visa` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duree_sejour` int(11) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `types_visa`
--

INSERT INTO `types_visa` (`id`, `nom`, `description`, `duree_sejour`, `prix`) VALUES
(1, 'Tourisme', 'Visa pour visites touristiques', 90, 80.00),
(2, 'Affaires', 'Visa pour voyages d affaires', 90, 80.00),
(3, 'Étudiant', 'Visa pour études', 365, 50.00),
(4, 'Familial', 'Visite familiale', 90, 80.00),
(5, 'Travail', 'Visa de travail', 365, 100.00),
(6, 'Transit', 'Visa de transit aéroportuaire', 2, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `universites_canada`
--

CREATE TABLE `universites_canada` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `province` varchar(50) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `type_etablissement` enum('universite','college','cegep','institut') DEFAULT NULL,
  `site_web` varchar(200) DEFAULT NULL,
  `statut` enum('public','prive') DEFAULT 'public',
  `date_creation` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `universites_canada`
--

INSERT INTO `universites_canada` (`id`, `nom`, `province`, `ville`, `type_etablissement`, `site_web`, `statut`, `date_creation`) VALUES
(1, 'Université de Montréal', 'Québec', 'Montréal', 'universite', 'https://umontreal.ca', 'public', NULL),
(2, 'Université McGill', 'Québec', 'Montréal', 'universite', 'https://mcgill.ca', 'public', NULL),
(3, 'Université Laval', 'Québec', 'Québec', 'universite', 'https://ulaval.ca', 'public', NULL),
(4, 'Université de Toronto', 'Ontario', 'Toronto', 'universite', 'https://utoronto.ca', 'public', NULL),
(5, 'University of British Columbia', 'Colombie-Britannique', 'Vancouver', 'universite', 'https://ubc.ca', 'public', NULL),
(6, 'University of Alberta', 'Alberta', 'Edmonton', 'universite', 'https://ualberta.ca', 'public', NULL),
(7, 'Université d\'Ottawa', 'Ontario', 'Ottawa', 'universite', 'https://uottawa.ca', 'public', NULL),
(8, 'McMaster University', 'Ontario', 'Hamilton', 'universite', 'https://mcmaster.ca', 'public', NULL),
(9, 'University of Calgary', 'Alberta', 'Calgary', 'universite', 'https://ucalgary.ca', 'public', NULL),
(10, 'Université du Québec à Montréal', 'Québec', 'Montréal', 'universite', 'https://uqam.ca', 'public', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nom` varchar(255) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `created_at`, `nom`, `telephone`) VALUES
(6, 'kami', 'kami@gmail.com', NULL, '$2y$10$cjHuu8t7dLvXtCT6fJwIvuUFEBejuMzxnUsfXlytwv7DZmOjA1F9i', '2025-09-14 11:23:52', NULL, NULL),
(7, 'Melissa', 'melissalounis551@gmail.com', '+213556603313', '$2y$10$RE2u.pc5fSfjca94bp6eIOXYlOVPUgRhW4HTs2jFfFR81x7i.dlz2', '2025-09-22 11:44:13', NULL, NULL),
(8, 'wassila', 'cl051@vmsindustrie.net', '+213556603313', '$2y$10$xjdmV4RMurqdEt94m3JFzOjYvxmLp1nclPs6Tiu79/v/qI2mROMEO', '2025-09-22 12:33:04', NULL, NULL),
(9, 'melissa lounis', 'lounismelissa534@gmail.com', '+213556603313', '$2y$10$gzNyO/XOk5YsUqyxNcQ6Kuy88NnR.4LURDN7v0p0J9osLIWfyjcFG', '2025-11-08 20:14:22', NULL, NULL),
(10, 'melissa lounis', 'lounimeli3@gmail.com', '+213556603313', '$2y$10$3rMWS5UM5MpWZD7OiRH5lO/7tZQpecTjyuGYNZ1cc1iJrQsm5nG.q', '2025-11-08 21:15:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_documents`
--

CREATE TABLE `user_documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type_document` varchar(100) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','approuvé','rejeté') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `role` enum('admin','agent') DEFAULT 'agent',
  `actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `mot_de_passe`, `nom`, `role`, `actif`, `date_creation`) VALUES
(1, 'admin@visasystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin', 1, '2025-09-28 22:18:00');

-- --------------------------------------------------------

--
-- Structure de la table `visa_travail_france`
--

CREATE TABLE `visa_travail_france` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `nationalite` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `passeport` varchar(100) NOT NULL,
  `date_delivrance` date NOT NULL,
  `date_expiration` date NOT NULL,
  `employeur` varchar(255) NOT NULL,
  `adresse_employeur` text NOT NULL,
  `type_contrat` varchar(50) NOT NULL,
  `duree_sejour` int(11) NOT NULL,
  `photo_identite` varchar(255) NOT NULL,
  `copie_passeport` varchar(255) NOT NULL,
  `contrat_travail` varchar(255) NOT NULL,
  `attestation_employeur` varchar(255) NOT NULL,
  `logement` varchar(255) NOT NULL,
  `assurance` varchar(255) NOT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('En attente','Approuvée','Rejetée') DEFAULT 'En attente',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vw_demandes_canada_complete`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vw_demandes_canada_complete` (
`id` int(11)
,`user_id` int(11)
,`nom` varchar(100)
,`prenom` varchar(100)
,`nom_famille` varchar(100)
,`email` varchar(150)
,`telephone` varchar(20)
,`naissance` date
,`nationalite` varchar(50)
,`pays_residence` varchar(50)
,`adresse` text
,`ville` varchar(100)
,`code_postal` varchar(20)
,`niveau_etude` enum('bac','l1','l2','l3','master','doctorat')
,`etablissement_origine` varchar(200)
,`diplome_obtenu` varchar(200)
,`moyenne_generale` decimal(4,2)
,`domaine_etudes` varchar(100)
,`universite_canada` varchar(200)
,`programme_canada` varchar(200)
,`ville_etude` varchar(100)
,`province_etude` varchar(100)
,`duree_etudes` int(11)
,`date_debut_etudes` date
,`langue_maternelle` varchar(50)
,`test_linguistique` enum('ielts','toefl','tef','tcf','autre','aucun')
,`score_linguistique` decimal(4,1)
,`date_test_linguistique` date
,`source_financement` enum('personnel','familial','bourse','pret','autre')
,`montant_financement` decimal(10,2)
,`preuve_financement` tinyint(1)
,`statut` enum('en_attente','en_cours','approuve','refuse')
,`date_soumission` datetime
,`date_modification` datetime
,`date_traitement` datetime
,`commentaires_agent` text
,`notes_internes` text
,`passeport_valide` tinyint(1)
,`date_expiration_passeport` date
,`casier_judiciaire` tinyint(1)
,`certificat_medical` tinyint(1)
,`universite_nom` varchar(200)
,`universite_province` varchar(50)
,`universite_ville` varchar(100)
,`nom_programme` varchar(200)
,`programme_domaine` varchar(100)
,`nombre_fichiers` bigint(21)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vw_stats_canada`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vw_stats_canada` (
`statut` enum('en_attente','en_cours','approuve','refuse')
,`nombre_demandes` bigint(21)
,`duree_moyenne_jours` decimal(10,4)
,`premiere_demande` datetime
,`derniere_demande` datetime
);

-- --------------------------------------------------------

--
-- Structure de la vue `vw_demandes_canada_complete`
--
DROP TABLE IF EXISTS `vw_demandes_canada_complete`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_demandes_canada_complete`  AS SELECT `dc`.`id` AS `id`, `dc`.`user_id` AS `user_id`, `dc`.`nom` AS `nom`, `dc`.`prenom` AS `prenom`, `dc`.`nom_famille` AS `nom_famille`, `dc`.`email` AS `email`, `dc`.`telephone` AS `telephone`, `dc`.`naissance` AS `naissance`, `dc`.`nationalite` AS `nationalite`, `dc`.`pays_residence` AS `pays_residence`, `dc`.`adresse` AS `adresse`, `dc`.`ville` AS `ville`, `dc`.`code_postal` AS `code_postal`, `dc`.`niveau_etude` AS `niveau_etude`, `dc`.`etablissement_origine` AS `etablissement_origine`, `dc`.`diplome_obtenu` AS `diplome_obtenu`, `dc`.`moyenne_generale` AS `moyenne_generale`, `dc`.`domaine_etudes` AS `domaine_etudes`, `dc`.`universite_canada` AS `universite_canada`, `dc`.`programme_canada` AS `programme_canada`, `dc`.`ville_etude` AS `ville_etude`, `dc`.`province_etude` AS `province_etude`, `dc`.`duree_etudes` AS `duree_etudes`, `dc`.`date_debut_etudes` AS `date_debut_etudes`, `dc`.`langue_maternelle` AS `langue_maternelle`, `dc`.`test_linguistique` AS `test_linguistique`, `dc`.`score_linguistique` AS `score_linguistique`, `dc`.`date_test_linguistique` AS `date_test_linguistique`, `dc`.`source_financement` AS `source_financement`, `dc`.`montant_financement` AS `montant_financement`, `dc`.`preuve_financement` AS `preuve_financement`, `dc`.`statut` AS `statut`, `dc`.`date_soumission` AS `date_soumission`, `dc`.`date_modification` AS `date_modification`, `dc`.`date_traitement` AS `date_traitement`, `dc`.`commentaires_agent` AS `commentaires_agent`, `dc`.`notes_internes` AS `notes_internes`, `dc`.`passeport_valide` AS `passeport_valide`, `dc`.`date_expiration_passeport` AS `date_expiration_passeport`, `dc`.`casier_judiciaire` AS `casier_judiciaire`, `dc`.`certificat_medical` AS `certificat_medical`, `u`.`nom` AS `universite_nom`, `u`.`province` AS `universite_province`, `u`.`ville` AS `universite_ville`, `p`.`nom_programme` AS `nom_programme`, `p`.`domaine` AS `programme_domaine`, count(`df`.`id`) AS `nombre_fichiers` FROM (((`demandes_canada` `dc` left join `universites_canada` `u` on(`dc`.`universite_canada` = `u`.`nom`)) left join `programmes_canada` `p` on(`dc`.`programme_canada` = `p`.`nom_programme`)) left join `demandes_canada_fichiers` `df` on(`dc`.`id` = `df`.`demande_id`)) GROUP BY `dc`.`id` ;

-- --------------------------------------------------------

--
-- Structure de la vue `vw_stats_canada`
--
DROP TABLE IF EXISTS `vw_stats_canada`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_stats_canada`  AS SELECT `demandes_canada`.`statut` AS `statut`, count(0) AS `nombre_demandes`, avg(to_days(coalesce(`demandes_canada`.`date_traitement`,current_timestamp())) - to_days(`demandes_canada`.`date_soumission`)) AS `duree_moyenne_jours`, min(`demandes_canada`.`date_soumission`) AS `premiere_demande`, max(`demandes_canada`.`date_soumission`) AS `derniere_demande` FROM `demandes_canada` GROUP BY `demandes_canada`.`statut` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `attestation_province`
--
ALTER TABLE `attestation_province`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `statut` (`statut`),
  ADD KEY `date_soumission` (`date_soumission`);

--
-- Index pour la table `client_profiles`
--
ALTER TABLE `client_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `commentaires_demandes`
--
ALTER TABLE `commentaires_demandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes`
--
ALTER TABLE `demandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `demandes_belgique`
--
ALTER TABLE `demandes_belgique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_belgique_fichiers`
--
ALTER TABLE `demandes_belgique_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_billets_avion`
--
ALTER TABLE `demandes_billets_avion`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_bourse_fichiers`
--
ALTER TABLE `demandes_bourse_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_bourse_italie`
--
ALTER TABLE `demandes_bourse_italie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_bourse_statuts`
--
ALTER TABLE `demandes_bourse_statuts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_statuts_demande` (`demande_id`);

--
-- Index pour la table `demandes_campus_france`
--
ALTER TABLE `demandes_campus_france`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_campus_france_fichiers`
--
ALTER TABLE `demandes_campus_france_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demande_id` (`demande_id`),
  ADD KEY `idx_type_fichier` (`type_fichier`);

--
-- Index pour la table `demandes_canada`
--
ALTER TABLE `demandes_canada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_soumission` (`date_soumission`);

--
-- Index pour la table `demandes_canada_fichiers`
--
ALTER TABLE `demandes_canada_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demande_id` (`demande_id`),
  ADD KEY `idx_type_fichier` (`type_fichier`);

--
-- Index pour la table `demandes_canada_historique`
--
ALTER TABLE `demandes_canada_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demande_id` (`demande_id`),
  ADD KEY `idx_date_changement` (`date_changement`);

--
-- Index pour la table `demandes_caq`
--
ALTER TABLE `demandes_caq`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `statut` (`statut`),
  ADD KEY `date_soumission` (`date_soumission`);

--
-- Index pour la table `demandes_contrat_travail`
--
ALTER TABLE `demandes_contrat_travail`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_court_sejour`
--
ALTER TABLE `demandes_court_sejour`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_creation` (`date_creation`);

--
-- Index pour la table `demandes_court_sejour_fichiers`
--
ALTER TABLE `demandes_court_sejour_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demande_id` (`demande_id`),
  ADD KEY `idx_type_fichier` (`type_fichier`);

--
-- Index pour la table `demandes_creation_cv`
--
ALTER TABLE `demandes_creation_cv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_ecoles_privees`
--
ALTER TABLE `demandes_ecoles_privees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_date_creation` (`date_creation`);

--
-- Index pour la table `demandes_ecoles_privees_fichiers`
--
ALTER TABLE `demandes_ecoles_privees_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_demande_id` (`demande_id`);

--
-- Index pour la table `demandes_equivalences`
--
ALTER TABLE `demandes_equivalences`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_etablissements_non_connectes`
--
ALTER TABLE `demandes_etablissements_non_connectes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_etablissements_non_connectes_fichiers`
--
ALTER TABLE `demandes_etablissements_non_connectes_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_etudes_bulgarie`
--
ALTER TABLE `demandes_etudes_bulgarie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_etudes_canada`
--
ALTER TABLE `demandes_etudes_canada`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_etudes_espagne`
--
ALTER TABLE `demandes_etudes_espagne`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `statut` (`statut`),
  ADD KEY `date_soumission` (`date_soumission`);

--
-- Index pour la table `demandes_etudes_roumanie`
--
ALTER TABLE `demandes_etudes_roumanie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `statut` (`statut`),
  ADD KEY `date_soumission` (`date_soumission`);

--
-- Index pour la table `demandes_green_card`
--
ALTER TABLE `demandes_green_card`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Index pour la table `demandes_italie`
--
ALTER TABLE `demandes_italie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_italie_fichiers`
--
ALTER TABLE `demandes_italie_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_luxembourg`
--
ALTER TABLE `demandes_luxembourg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_luxembourg_fichiers`
--
ALTER TABLE `demandes_luxembourg_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_parcoursup`
--
ALTER TABLE `demandes_parcoursup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_parcoursup_annees`
--
ALTER TABLE `demandes_parcoursup_annees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_parcoursup_documents`
--
ALTER TABLE `demandes_parcoursup_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_parcoursup_fichiers`
--
ALTER TABLE `demandes_parcoursup_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_paris_saclay`
--
ALTER TABLE `demandes_paris_saclay`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_regroupement_familial`
--
ALTER TABLE `demandes_regroupement_familial`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_dossier` (`numero_dossier`);

--
-- Index pour la table `demandes_reservation`
--
ALTER TABLE `demandes_reservation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_dossier` (`numero_dossier`);

--
-- Index pour la table `demandes_suisse`
--
ALTER TABLE `demandes_suisse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_suisse_fichiers`
--
ALTER TABLE `demandes_suisse_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_turquie`
--
ALTER TABLE `demandes_turquie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `demandes_turquie_fichiers`
--
ALTER TABLE `demandes_turquie_fichiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `demandes_visa`
--
ALTER TABLE `demandes_visa`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_visa_travail`
--
ALTER TABLE `demandes_visa_travail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_soumission` (`date_soumission`);

--
-- Index pour la table `demande_etudes_`
--
ALTER TABLE `demande_etudes_`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_dossier` (`numero_dossier`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_soumission` (`date_soumission`),
  ADD KEY `idx_nom_prenom` (`nom_famille`,`prenoms`);

--
-- Index pour la table `demande_etude_canada`
--
ALTER TABLE `demande_etude_canada`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demande_permis_etudes`
--
ALTER TABLE `demande_permis_etudes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demande_visa`
--
ALTER TABLE `demande_visa`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demande_visa_court_sejour`
--
ALTER TABLE `demande_visa_court_sejour`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_dossier` (`numero_dossier`),
  ADD KEY `idx_numero_dossier` (`numero_dossier`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_soumission` (`date_soumission`),
  ADD KEY `idx_nom_prenom` (`nom_famille`,`prenoms`);

--
-- Index pour la table `demande_visa_imm5257`
--
ALTER TABLE `demande_visa_imm5257`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `documents_demande`
--
ALTER TABLE `documents_demande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `documents_immigration`
--
ALTER TABLE `documents_immigration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`);

--
-- Index pour la table `documents_rendez_vous`
--
ALTER TABLE `documents_rendez_vous`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rendez_vous_id` (`rendez_vous_id`);

--
-- Index pour la table `etude_belgique`
--
ALTER TABLE `etude_belgique`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `evaluations_immigration`
--
ALTER TABLE `evaluations_immigration`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fichiers_demandes`
--
ALTER TABLE `fichiers_demandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `fichiers_paiements`
--
ALTER TABLE `fichiers_paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paiement_id` (`paiement_id`);

--
-- Index pour la table `garants`
--
ALTER TABLE `garants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `immigration_evaluations`
--
ALTER TABLE `immigration_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `langue_tests`
--
ALTER TABLE `langue_tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email_date` (`email`,`date_rendezvous`);

--
-- Index pour la table `membres_famille`
--
ALTER TABLE `membres_famille`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Index pour la table `passagers_billets`
--
ALTER TABLE `passagers_billets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Index pour la table `pays`
--
ALTER TABLE `pays`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `programmes_canada`
--
ALTER TABLE `programmes_canada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `universite_id` (`universite_id`),
  ADD KEY `idx_niveau` (`niveau`),
  ADD KEY `idx_domaine` (`domaine`);

--
-- Index pour la table `rendezvous_tcf`
--
ALTER TABLE `rendezvous_tcf`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Index pour la table `rendez_vous_biometrie`
--
ALTER TABLE `rendez_vous_biometrie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_creation` (`date_creation`);

--
-- Index pour la table `rendez_vous_personnes_supp`
--
ALTER TABLE `rendez_vous_personnes_supp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rendez_vous_id` (`rendez_vous_id`);

--
-- Index pour la table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `study_applications`
--
ALTER TABLE `study_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `suivi_demandes_luxembourg`
--
ALTER TABLE `suivi_demandes_luxembourg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `suivi_demandes_suisse`
--
ALTER TABLE `suivi_demandes_suisse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `suivi_demandes_turquie`
--
ALTER TABLE `suivi_demandes_turquie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demande_id` (`demande_id`);

--
-- Index pour la table `types_visa`
--
ALTER TABLE `types_visa`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `universites_canada`
--
ALTER TABLE `universites_canada`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_ville` (`ville`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_documents`
--
ALTER TABLE `user_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `visa_travail_france`
--
ALTER TABLE `visa_travail_france`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `attestation_province`
--
ALTER TABLE `attestation_province`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `client_profiles`
--
ALTER TABLE `client_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commentaires_demandes`
--
ALTER TABLE `commentaires_demandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes`
--
ALTER TABLE `demandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes_belgique`
--
ALTER TABLE `demandes_belgique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demandes_belgique_fichiers`
--
ALTER TABLE `demandes_belgique_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `demandes_billets_avion`
--
ALTER TABLE `demandes_billets_avion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `demandes_bourse_fichiers`
--
ALTER TABLE `demandes_bourse_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes_bourse_italie`
--
ALTER TABLE `demandes_bourse_italie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_bourse_statuts`
--
ALTER TABLE `demandes_bourse_statuts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_campus_france`
--
ALTER TABLE `demandes_campus_france`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demandes_campus_france_fichiers`
--
ALTER TABLE `demandes_campus_france_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `demandes_canada`
--
ALTER TABLE `demandes_canada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_canada_fichiers`
--
ALTER TABLE `demandes_canada_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_canada_historique`
--
ALTER TABLE `demandes_canada_historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_caq`
--
ALTER TABLE `demandes_caq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_contrat_travail`
--
ALTER TABLE `demandes_contrat_travail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_court_sejour`
--
ALTER TABLE `demandes_court_sejour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `demandes_court_sejour_fichiers`
--
ALTER TABLE `demandes_court_sejour_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes_creation_cv`
--
ALTER TABLE `demandes_creation_cv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_ecoles_privees`
--
ALTER TABLE `demandes_ecoles_privees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_ecoles_privees_fichiers`
--
ALTER TABLE `demandes_ecoles_privees_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_equivalences`
--
ALTER TABLE `demandes_equivalences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_etablissements_non_connectes`
--
ALTER TABLE `demandes_etablissements_non_connectes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_etablissements_non_connectes_fichiers`
--
ALTER TABLE `demandes_etablissements_non_connectes_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes_etudes_bulgarie`
--
ALTER TABLE `demandes_etudes_bulgarie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_etudes_canada`
--
ALTER TABLE `demandes_etudes_canada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demandes_etudes_espagne`
--
ALTER TABLE `demandes_etudes_espagne`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `demandes_etudes_roumanie`
--
ALTER TABLE `demandes_etudes_roumanie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `demandes_green_card`
--
ALTER TABLE `demandes_green_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demandes_italie`
--
ALTER TABLE `demandes_italie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_italie_fichiers`
--
ALTER TABLE `demandes_italie_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `demandes_luxembourg`
--
ALTER TABLE `demandes_luxembourg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `demandes_luxembourg_fichiers`
--
ALTER TABLE `demandes_luxembourg_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `demandes_parcoursup`
--
ALTER TABLE `demandes_parcoursup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `demandes_parcoursup_annees`
--
ALTER TABLE `demandes_parcoursup_annees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demandes_parcoursup_documents`
--
ALTER TABLE `demandes_parcoursup_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_parcoursup_fichiers`
--
ALTER TABLE `demandes_parcoursup_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `demandes_paris_saclay`
--
ALTER TABLE `demandes_paris_saclay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_regroupement_familial`
--
ALTER TABLE `demandes_regroupement_familial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_reservation`
--
ALTER TABLE `demandes_reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `demandes_suisse`
--
ALTER TABLE `demandes_suisse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_suisse_fichiers`
--
ALTER TABLE `demandes_suisse_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `demandes_turquie`
--
ALTER TABLE `demandes_turquie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demandes_turquie_fichiers`
--
ALTER TABLE `demandes_turquie_fichiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `demandes_visa`
--
ALTER TABLE `demandes_visa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `demandes_visa_travail`
--
ALTER TABLE `demandes_visa_travail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `demande_etudes_`
--
ALTER TABLE `demande_etudes_`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demande_etude_canada`
--
ALTER TABLE `demande_etude_canada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demande_permis_etudes`
--
ALTER TABLE `demande_permis_etudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demande_visa`
--
ALTER TABLE `demande_visa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demande_visa_court_sejour`
--
ALTER TABLE `demande_visa_court_sejour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demande_visa_imm5257`
--
ALTER TABLE `demande_visa_imm5257`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents_demande`
--
ALTER TABLE `documents_demande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `documents_immigration`
--
ALTER TABLE `documents_immigration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `documents_rendez_vous`
--
ALTER TABLE `documents_rendez_vous`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `etude_belgique`
--
ALTER TABLE `etude_belgique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `evaluations_immigration`
--
ALTER TABLE `evaluations_immigration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `fichiers_demandes`
--
ALTER TABLE `fichiers_demandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `fichiers_paiements`
--
ALTER TABLE `fichiers_paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `garants`
--
ALTER TABLE `garants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `immigration_evaluations`
--
ALTER TABLE `immigration_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `langue_tests`
--
ALTER TABLE `langue_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `membres_famille`
--
ALTER TABLE `membres_famille`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `passagers_billets`
--
ALTER TABLE `passagers_billets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `pays`
--
ALTER TABLE `pays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `programmes_canada`
--
ALTER TABLE `programmes_canada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `rendezvous_tcf`
--
ALTER TABLE `rendezvous_tcf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `rendez_vous_biometrie`
--
ALTER TABLE `rendez_vous_biometrie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `rendez_vous_personnes_supp`
--
ALTER TABLE `rendez_vous_personnes_supp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `study_applications`
--
ALTER TABLE `study_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `suivi_demandes_luxembourg`
--
ALTER TABLE `suivi_demandes_luxembourg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `suivi_demandes_suisse`
--
ALTER TABLE `suivi_demandes_suisse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `suivi_demandes_turquie`
--
ALTER TABLE `suivi_demandes_turquie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `types_visa`
--
ALTER TABLE `types_visa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `universites_canada`
--
ALTER TABLE `universites_canada`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user_documents`
--
ALTER TABLE `user_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `visa_travail_france`
--
ALTER TABLE `visa_travail_france`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `attestation_province`
--
ALTER TABLE `attestation_province`
  ADD CONSTRAINT `attestation_province_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `client_profiles`
--
ALTER TABLE `client_profiles`
  ADD CONSTRAINT `client_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commentaires_demandes`
--
ALTER TABLE `commentaires_demandes`
  ADD CONSTRAINT `commentaires_demandes_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_luxembourg` (`id`),
  ADD CONSTRAINT `commentaires_demandes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `demandes`
--
ALTER TABLE `demandes`
  ADD CONSTRAINT `demandes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `demandes_belgique`
--
ALTER TABLE `demandes_belgique`
  ADD CONSTRAINT `demandes_belgique_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_belgique_fichiers`
--
ALTER TABLE `demandes_belgique_fichiers`
  ADD CONSTRAINT `demandes_belgique_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_belgique` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_bourse_fichiers`
--
ALTER TABLE `demandes_bourse_fichiers`
  ADD CONSTRAINT `demandes_bourse_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_bourse_italie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_bourse_statuts`
--
ALTER TABLE `demandes_bourse_statuts`
  ADD CONSTRAINT `demandes_bourse_statuts_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_bourse_italie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_campus_france_fichiers`
--
ALTER TABLE `demandes_campus_france_fichiers`
  ADD CONSTRAINT `demandes_campus_france_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_campus_france` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_canada`
--
ALTER TABLE `demandes_canada`
  ADD CONSTRAINT `demandes_canada_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_canada_fichiers`
--
ALTER TABLE `demandes_canada_fichiers`
  ADD CONSTRAINT `demandes_canada_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_canada` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_canada_historique`
--
ALTER TABLE `demandes_canada_historique`
  ADD CONSTRAINT `demandes_canada_historique_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_canada` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_caq`
--
ALTER TABLE `demandes_caq`
  ADD CONSTRAINT `demandes_caq_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `demandes_court_sejour_fichiers`
--
ALTER TABLE `demandes_court_sejour_fichiers`
  ADD CONSTRAINT `demandes_court_sejour_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_court_sejour` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_creation_cv`
--
ALTER TABLE `demandes_creation_cv`
  ADD CONSTRAINT `demandes_creation_cv_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_ecoles_privees_fichiers`
--
ALTER TABLE `demandes_ecoles_privees_fichiers`
  ADD CONSTRAINT `demandes_ecoles_privees_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_ecoles_privees` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_etablissements_non_connectes_fichiers`
--
ALTER TABLE `demandes_etablissements_non_connectes_fichiers`
  ADD CONSTRAINT `demandes_etablissements_non_connectes_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_etablissements_non_connectes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_italie`
--
ALTER TABLE `demandes_italie`
  ADD CONSTRAINT `demandes_italie_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_italie_fichiers`
--
ALTER TABLE `demandes_italie_fichiers`
  ADD CONSTRAINT `demandes_italie_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_italie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_luxembourg`
--
ALTER TABLE `demandes_luxembourg`
  ADD CONSTRAINT `demandes_luxembourg_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_luxembourg_fichiers`
--
ALTER TABLE `demandes_luxembourg_fichiers`
  ADD CONSTRAINT `demandes_luxembourg_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_luxembourg` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_parcoursup`
--
ALTER TABLE `demandes_parcoursup`
  ADD CONSTRAINT `demandes_parcoursup_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_parcoursup_annees`
--
ALTER TABLE `demandes_parcoursup_annees`
  ADD CONSTRAINT `demandes_parcoursup_annees_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_parcoursup` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_parcoursup_documents`
--
ALTER TABLE `demandes_parcoursup_documents`
  ADD CONSTRAINT `demandes_parcoursup_documents_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_parcoursup` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_parcoursup_fichiers`
--
ALTER TABLE `demandes_parcoursup_fichiers`
  ADD CONSTRAINT `demandes_parcoursup_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_parcoursup` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_suisse`
--
ALTER TABLE `demandes_suisse`
  ADD CONSTRAINT `demandes_suisse_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_suisse_fichiers`
--
ALTER TABLE `demandes_suisse_fichiers`
  ADD CONSTRAINT `demandes_suisse_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_suisse` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_turquie`
--
ALTER TABLE `demandes_turquie`
  ADD CONSTRAINT `demandes_turquie_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_turquie_fichiers`
--
ALTER TABLE `demandes_turquie_fichiers`
  ADD CONSTRAINT `demandes_turquie_fichiers_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_turquie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_visa` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_demande`
--
ALTER TABLE `documents_demande`
  ADD CONSTRAINT `documents_demande_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_green_card` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_immigration`
--
ALTER TABLE `documents_immigration`
  ADD CONSTRAINT `documents_immigration_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations_immigration` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_rendez_vous`
--
ALTER TABLE `documents_rendez_vous`
  ADD CONSTRAINT `documents_rendez_vous_ibfk_1` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`);

--
-- Contraintes pour la table `fichiers_demandes`
--
ALTER TABLE `fichiers_demandes`
  ADD CONSTRAINT `fichiers_demandes_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_parcoursup` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fichiers_paiements`
--
ALTER TABLE `fichiers_paiements`
  ADD CONSTRAINT `fichiers_paiements_ibfk_1` FOREIGN KEY (`paiement_id`) REFERENCES `paiements` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `garants`
--
ALTER TABLE `garants`
  ADD CONSTRAINT `garants_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_visa` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `immigration_evaluations`
--
ALTER TABLE `immigration_evaluations`
  ADD CONSTRAINT `immigration_evaluations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `membres_famille`
--
ALTER TABLE `membres_famille`
  ADD CONSTRAINT `membres_famille_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_green_card` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `passagers_billets`
--
ALTER TABLE `passagers_billets`
  ADD CONSTRAINT `passagers_billets_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_billets_avion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `programmes_canada`
--
ALTER TABLE `programmes_canada`
  ADD CONSTRAINT `programmes_canada_ibfk_1` FOREIGN KEY (`universite_id`) REFERENCES `universites_canada` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rendez_vous_personnes_supp`
--
ALTER TABLE `rendez_vous_personnes_supp`
  ADD CONSTRAINT `rendez_vous_personnes_supp_ibfk_1` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous_biometrie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `study_applications`
--
ALTER TABLE `study_applications`
  ADD CONSTRAINT `study_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `suivi_demandes_luxembourg`
--
ALTER TABLE `suivi_demandes_luxembourg`
  ADD CONSTRAINT `suivi_demandes_luxembourg_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_luxembourg` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `suivi_demandes_suisse`
--
ALTER TABLE `suivi_demandes_suisse`
  ADD CONSTRAINT `suivi_demandes_suisse_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_suisse` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `suivi_demandes_turquie`
--
ALTER TABLE `suivi_demandes_turquie`
  ADD CONSTRAINT `suivi_demandes_turquie_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_turquie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_documents`
--
ALTER TABLE `user_documents`
  ADD CONSTRAINT `user_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
