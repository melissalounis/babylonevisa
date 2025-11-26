<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Éviter les inclusions circulaires
if (!defined('HEADER_INCLUDED')) {
    define('HEADER_INCLUDED', true);
    
    // Inclure le fichier de configuration avec gestion d'erreurs
    $config_path = realpath(__DIR__ . '/../config.php');
    if ($config_path && file_exists($config_path)) {
        require_once $config_path;
    } else {
        error_log("Fichier config.php introuvable");
    }
}

// Fonctions helper pour la gestion utilisateur
function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user_display_name() {
    if (isset($_SESSION['user_name']) && !empty(trim($_SESSION['user_name']))) {
        return trim($_SESSION['user_name']);
    }
    return "Mon Compte";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo isset($page_title) ? $page_title : 'Babylone Service'; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary-blue: #2563EB;
      --blue-light: #3B82F6;
      --blue-lighter: #60A5FA;
      --blue-dark: #1D4ED8;
      --blue-darker: #1E40AF;
      --blue-glow: rgba(37, 99, 235, 0.3);
      --hero-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --hero-card: rgba(255, 255, 255, 0.1);
      --hero-border: rgba(255, 255, 255, 0.2);
      --hero-text: #FFFFFF;
      --hero-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      --transition: all 0.3s ease;
      --border-radius: 16px;
      --border-radius-sm: 12px;
      --border-radius-lg: 20px;
    }

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: transparent !important;
      color: #333;
      padding-top: 0;
    }

    .loading-bar {
      position: fixed;
      top: 0;
      left: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-blue), var(--blue-light));
      width: 0%;
      z-index: 9999;
      transition: width 0.4s ease;
    }

    /* ===== HEADER TRANSPARENT AU DÉPART ===== */
    .navbar {
      background: transparent !important;
      backdrop-filter: blur(0px);
      padding: 25px 0;
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
      transition: var(--transition);
      border-bottom: 1px solid transparent;
    }

    /* Texte blanc quand le header est transparent */
    .navbar:not(.scrolled) .logo-text,
    .navbar:not(.scrolled) .main-menu a,
    .navbar:not(.scrolled) .user-name,
    .navbar:not(.scrolled) .menu-right a,
    .navbar:not(.scrolled) .user-menu,
    .navbar:not(.scrolled) .menu-toggle span {
      color: white !important;
    }

    /* SURVOL SIMPLE - COULEUR BLANCHE */
    .navbar:not(.scrolled) .main-menu a:hover,
    .navbar:not(.scrolled) .menu-right a:hover,
    .navbar:not(.scrolled) .user-menu:hover {
      color: white !important;
      background: rgba(255, 255, 255, 0.1) !important;
      border-color: transparent !important;
      transform: none !important;
    }

    /* ===== HEADER BLEU QUAND ON SCROLL ===== */
    .navbar.scrolled {
      background: linear-gradient(135deg, var(--blue-dark), var(--primary-blue)) !important;
      backdrop-filter: blur(20px);
      padding: 15px 0;
      border-bottom: 1px solid var(--blue-darker);
      box-shadow: 0 8px 32px rgba(30, 64, 175, 0.3);
    }

    /* Texte blanc quand le header est bleu */
    .navbar.scrolled .logo-text,
    .navbar.scrolled .main-menu a,
    .navbar.scrolled .user-name,
    .navbar.scrolled .menu-right a,
    .navbar.scrolled .user-menu,
    .navbar.scrolled .menu-toggle span {
      color: white !important;
    }

    /* SURVOL SIMPLE - COULEUR BLANCHE */
    .navbar.scrolled .main-menu a:hover,
    .navbar.scrolled .menu-right a:hover,
    .navbar.scrolled .user-menu:hover {
      color: white !important;
      background: rgba(255, 255, 255, 0.2) !important;
      border-color: transparent !important;
      transform: none !important;
    }

    .navbar .container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 30px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;
    }

    .logo img {
      height: 50px;
      width: auto;
      border-radius: 12px;
      transition: var(--transition);
    }

    .navbar:not(.scrolled) .logo img {
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .navbar.scrolled .logo img {
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .logo:hover img {
      transform: scale(1.05);
    }

    .logo-text {
      font-weight: 700;
      font-size: 1.5rem;
      transition: var(--transition);
    }

    .navbar:not(.scrolled) .logo-text {
      background: linear-gradient(135deg, white, var(--blue-lighter));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .navbar.scrolled .logo-text {
      background: linear-gradient(135deg, white, #BFDBFE);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* ===== MENU PRINCIPAL ===== */
    .main-menu {
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 1;
      gap: 8px;
      margin: 0 40px;
    }

    .main-menu a {
      text-decoration: none;
      font-weight: 500;
      font-size: 15px;
      transition: var(--transition);
      padding: 14px 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
      background: transparent;
      border: none !important;
      white-space: nowrap;
      border-radius: var(--border-radius-sm);
    }

    /* SURVOL SIMPLE - SANS EFFET DE DÉPLACEMENT */
    .main-menu a:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      border: none !important;
      color: white !important;
    }

    .navbar.scrolled .main-menu a:hover {
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
      color: white !important;
    }

    .main-menu a:hover i {
      color: white !important;
    }

    /* ===== MENU DROIT ===== */
    .menu-right {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;
    }

    .menu-right a {
      text-decoration: none;
      font-weight: 500;
      font-size: 15px;
      transition: var(--transition);
      padding: 14px 24px;
      display: flex;
      align-items: center;
      gap: 10px;
      position: relative;
      background: transparent;
      border: none !important;
      white-space: nowrap;
      border-radius: var(--border-radius-sm);
    }

    /* SURVOL SIMPLE - SANS EFFET DE DÉPLACEMENT */
    .menu-right a:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      border: none !important;
      color: white !important;
    }

    .navbar.scrolled .menu-right a:hover {
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
      color: white !important;
    }

    .menu-right a:hover i {
      color: white !important;
    }

    .menu-right a.cta-button {
      font-weight: 600;
      transition: var(--transition);
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
    }

    .menu-right a.cta-button:hover {
      background: rgba(255, 255, 255, 0.3) !important;
      border: none !important;
      color: white !important;
    }

    /* ===== DROPDOWN ===== */
    .user-menu {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      cursor: pointer;
      padding: 14px 24px;
      background: transparent;
      border: none !important;
      white-space: nowrap;
      border-radius: var(--border-radius-sm);
      transition: var(--transition);
      position: relative;
    }

    /* SURVOL SIMPLE - SANS EFFET DE DÉPLACEMENT */
    .user-menu:hover {
      background: rgba(255, 255, 255, 0.15) !important;
      border: none !important;
      color: white !important;
    }

    .navbar.scrolled .user-menu {
      background: rgba(255, 255, 255, 0.1) !important;
    }

    .navbar.scrolled .user-menu:hover {
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
      color: white !important;
    }

    .user-name {
      font-weight: 500;
      font-size: 15px;
    }

    .notification-badge {
      position: absolute;
      top: -6px;
      right: -6px;
      background: #EF4444;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: bold;
      border: 2px solid;
      animation: pulse-red 2s infinite;
    }

    .navbar:not(.scrolled) .notification-badge {
      border-color: transparent;
    }

    .navbar.scrolled .notification-badge {
      border-color: var(--blue-dark);
    }

    @keyframes pulse-red {
      0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
      70% { transform: scale(1.05); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
      100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .dropdown {
      position: relative;
    }

    /* DROPDOWN AVEC STYLE HERO - PLUS D'ARRIÈRE-PLAN BLANC */
    .dropdown-content {
      position: absolute;
      top: 100%;
      right: 0;
      background: var(--hero-bg) !important;
      backdrop-filter: blur(20px);
      min-width: 320px;
      border-radius: var(--border-radius-lg);
      box-shadow: var(--hero-shadow);
      padding: 0;
      margin-top: 20px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px) scale(0.95);
      transition: var(--transition);
      z-index: 1000;
      border: 1px solid var(--hero-border);
      overflow: hidden;
    }

    .dropdown:hover .dropdown-content {
      opacity: 1;
      visibility: visible;
      transform: translateY(0) scale(1);
    }

    /* HEADER DU DROPDOWN TRANSPARENT */
    .dropdown-header {
      padding: 24px;
      background: transparent !important;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid var(--hero-border);
    }

    .dropdown-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
    }

    .dropdown-header .user-info {
      display: flex;
      align-items: center;
      gap: 16px;
      position: relative;
      z-index: 2;
    }

    .dropdown-header .user-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 32px;
      border: 3px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
    }

    .dropdown-header .user-details {
      flex: 1;
    }

    .dropdown-header .user-details .user-display-name {
      font-weight: 700;
      color: var(--hero-text) !important;
      font-size: 18px;
      margin-bottom: 6px;
      text-align: left;
    }

    .dropdown-header .user-details .user-email {
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 14px;
      text-align: left;
      margin-bottom: 8px;
    }

    .dropdown-header .user-details .user-status {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      color: white;
    }

    /* ITEMS DU DROPDOWN TRANSPARENTS */
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 18px 24px;
      color: var(--hero-text) !important;
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
      border-bottom: 1px solid var(--hero-border);
      position: relative;
      background: transparent !important;
    }

    .dropdown-item:last-child {
      border-bottom: none;
    }

    .dropdown-item:hover {
      background: rgba(255, 255, 255, 0.1) !important;
      color: var(--hero-text) !important;
      padding-left: 28px;
    }

    .dropdown-item:hover i {
      transform: translateX(3px);
      color: rgba(255, 255, 255, 0.9) !important;
    }

    .dropdown-item i {
      width: 20px;
      text-align: center;
      color: rgba(255, 255, 255, 0.8);
      font-size: 18px;
      transition: var(--transition);
    }

    .dropdown-badge {
      margin-left: auto;
      background: rgba(255, 255, 255, 0.3);
      color: var(--hero-text) !important;
      border-radius: 12px;
      padding: 6px 10px;
      font-size: 11px;
      font-weight: 700;
      min-width: 24px;
      text-align: center;
      backdrop-filter: blur(10px);
    }

    .dropdown-divider {
      height: 1px;
      background: var(--hero-border);
      margin: 8px 0;
    }

    /* FOOTER DU DROPDOWN TRANSPARENT */
    .dropdown-footer {
      padding: 20px 24px;
      background: transparent !important;
      border-top: 1px solid var(--hero-border);
    }

    .dropdown-footer .dropdown-item {
      border-bottom: none;
      padding: 14px 0;
      color: var(--hero-text) !important;
      justify-content: center;
      background: rgba(255, 255, 255, 0.2) !important;
      border-radius: var(--border-radius-sm);
      margin-top: 0;
      font-weight: 600;
      backdrop-filter: blur(10px);
    }

    .dropdown-footer .dropdown-item:hover {
      background: rgba(255, 255, 255, 0.3) !important;
      color: var(--hero-text) !important;
      padding-left: 0;
    }

    .menu-toggle {
      display: none;
      flex-direction: column;
      justify-content: center;
      width: 28px;
      height: 28px;
      cursor: pointer;
      gap: 5px;
    }

    .menu-toggle span {
      height: 2px;
      width: 100%;
      border-radius: 2px;
      transition: var(--transition);
    }

    .menu-toggle.active span:nth-child(1) {
      transform: rotate(45deg) translate(6px, 6px);
    }

    .menu-toggle.active span:nth-child(2) {
      opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
      transform: rotate(-45deg) translate(6px, -6px);
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 1200px) {
      .main-menu {
        gap: 6px;
        margin: 0 30px;
      }
      
      .main-menu a {
        font-size: 14px;
        padding: 12px 20px;
      }
    }

    @media (max-width: 992px) {
      .navbar .container {
        padding: 0 20px;
      }
      
      .menu-toggle {
        display: flex;
      }
      
      .main-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, var(--blue-dark), var(--primary-blue)) !important;
        flex-direction: column;
        padding: 20px;
        gap: 12px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-20px);
        transition: var(--transition);
        margin: 0;
        border-top: 1px solid var(--blue-darker);
        box-shadow: 0 8px 32px rgba(30, 64, 175, 0.3);
      }
      
      .main-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }
      
      .main-menu a {
        padding: 16px 20px;
        justify-content: center;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.1) !important;
        border: none !important;
        color: white !important;
      }

      .main-menu a:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        border: none !important;
        color: white !important;
      }
      
      .menu-right {
        gap: 8px;
      }

      .dropdown-content {
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%) translateY(-10px) scale(0.95);
        width: 90%;
        max-width: 340px;
      }

      .dropdown:hover .dropdown-content {
        transform: translateX(-50%) translateY(0) scale(1);
      }
    }

    @media (max-width: 768px) {
      .navbar {
        padding: 20px 0;
      }
      
      .navbar.scrolled {
        padding: 12px 0;
      }
      
      .logo-text {
        font-size: 1.3rem;
      }
      
      .logo img {
        height: 42px;
      }
      
      .main-menu a {
        font-size: 15px;
        padding: 14px 18px;
      }
      
      .menu-right a {
        font-size: 14px;
        padding: 12px 18px;
      }

      .user-menu {
        padding: 12px 18px;
      }
    }

    @media (max-width: 576px) {
      .logo-text {
        display: none;
      }
      
      .user-name {
        display: none;
      }
      
      .menu-right a span {
        display: none;
      }
      
      .menu-right a i {
        font-size: 18px;
      }

      .dropdown-header {
        padding: 20px;
      }

      .dropdown-item {
        padding: 16px 20px;
      }

      .dropdown-footer {
        padding: 16px 20px;
      }
    }

    /* Accessibilité */
    @media (prefers-reduced-motion: reduce) {
      * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
      
      .notification-badge {
        animation: none;
      }
    }
  </style>
</head>
<body>
<!-- Barre de chargement -->
<div class="loading-bar" id="loadingBar"></div>

<header class="navbar">
  <div class="container">
    <!-- Logo -->
    <div class="logo">
      <a href="/babylone/public/index.php">
        <img src="/babylone/public/images/logo.png" alt="Babylone Service">
      </a>
      <span class="logo-text">Babylone Service</span>
    </div>

    <!-- Menu toggle pour mobile -->
    <div class="menu-toggle" id="menuToggle">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <!-- Menu principal -->
    <div class="main-menu" id="mainMenu">
      <a href="/babylone/public/index.php"><i class="fas fa-home"></i> Accueil</a>
      <a href="/babylone/publics/services.php"><i class="fas fa-concierge-bell"></i> Services</a>
      <a href="/babylone/public/réservation hotel.php"><i class="fas fa-hotel"></i> Hôtel</a>
      <a href="/babylone/public/réservation billet.php"><i class="fas fa-plane"></i> Avion</a>
      
      <a href="/babylone/public/travail/demande_cv.php" class="service-cv">
        <i class="fas fa-file-alt"></i> Créer un CV
      </a>
      
      <?php if (function_exists('is_admin') && is_admin()): ?>
        <a href="/babylone/public/admin/index.php"><i class="fas fa-cog"></i> Admin</a>
      <?php endif; ?>
    </div>

    <!-- Menu droit -->
    <div class="menu-right">
      <?php if (!is_user_logged_in()): ?>
        <a href="/babylone/public/client/login.php" class="cta-button">
          <i class="fas fa-sign-in-alt"></i> <span>Connexion</span>
        </a>
        <a href="/babylone/public/client/register.php">
          <i class="fas fa-user-plus"></i> <span>Inscription</span>
        </a>
      <?php else: ?>
        <div class="dropdown">
          <div class="user-menu">
            <i class="fas fa-user-circle"></i>
            <span class="user-name">
              <?php echo htmlspecialchars(get_user_display_name(), ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span class="notification-badge">3</span>
          </div>
          <div class="dropdown-content">
            <!-- En-tête du dropdown - style hero -->
            <div class="dropdown-header">
              <div class="user-info">
                <div class="user-avatar">
                  <?php 
                  $display_name = get_user_display_name();
                  echo strtoupper(substr($display_name, 0, 1)); 
                  ?>
                </div>
                <div class="user-details">
                  <div class="user-display-name"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="user-email"><?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email'], ENT_QUOTES, 'UTF-8') : 'utilisateur@babylone.com'; ?></div>
                </div>
              </div>
            </div>

            <!-- Items du menu - style hero -->
            <a href="/babylone/public/client/profil.php" class="dropdown-item">
              <i class="fas fa-user-edit"></i> Modifier le profil
            </a>
            <a href="/babylone/public/client/messages.php" class="dropdown-item">
              <i class="fas fa-comments"></i> Messages
              <span class="dropdown-badge">3</span>
            </a>
            <a href="/babylone/public/client/demandes.php" class="dropdown-item">
              <i class="fas fa-cog"></i> Demandes
            </a>

            <div class="dropdown-footer">
              <a href="/babylone/public/client/logout.php" class="dropdown-item" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</header>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const menuToggle = document.getElementById('menuToggle');
      const mainMenu = document.getElementById('mainMenu');
      const loadingBar = document.getElementById('loadingBar');
      
      // Toggle menu mobile avec animation hamburger
      if (menuToggle && mainMenu) {
          menuToggle.addEventListener('click', function(e) {
              e.stopPropagation();
              mainMenu.classList.toggle('active');
              menuToggle.classList.toggle('active');
          });
      }

      // Animation au scroll - ajoute la classe 'scrolled' quand on scroll
      window.addEventListener('scroll', function() {
          const navbar = document.querySelector('.navbar');
          if (navbar) {
              if (window.scrollY > 50) {
                  navbar.classList.add('scrolled');
              } else {
                  navbar.classList.remove('scrolled');
              }
          }
      });

      // Fermer le menu en cliquant à l'extérieur
      document.addEventListener('click', function(event) {
          if (mainMenu && mainMenu.classList.contains('active')) {
              if (!mainMenu.contains(event.target) && !menuToggle.contains(event.target)) {
                  mainMenu.classList.remove('active');
                  menuToggle.classList.remove('active');
              }
          }
      });

      // Barre de chargement
      if (loadingBar) {
          loadingBar.style.width = '40%';
          setTimeout(() => loadingBar.style.width = '80%', 200);
          setTimeout(() => {
              loadingBar.style.width = '100%';
              setTimeout(() => {
                  loadingBar.style.opacity = '0';
                  setTimeout(() => loadingBar.remove(), 400);
              }, 300);
          }, 400);
      }

      // Amélioration du dropdown pour mobile
      const dropdown = document.querySelector('.dropdown');
      if (dropdown && window.innerWidth <= 992) {
          dropdown.addEventListener('click', function(e) {
              if (e.target.closest('.user-menu')) {
                  e.preventDefault();
                  const dropdownContent = this.querySelector('.dropdown-content');
                  const isActive = dropdownContent.classList.contains('active');
                  
                  // Fermer tous les dropdowns
                  document.querySelectorAll('.dropdown-content.active').forEach(dc => {
                      dc.classList.remove('active');
                  });
                  
                  // Ouvrir le dropdown actuel
                  if (!isActive) {
                      dropdownContent.classList.add('active');
                  }
              }
          });
      }

      // Fermer le dropdown en cliquant à l'extérieur
      document.addEventListener('click', function(event) {
          if (!event.target.closest('.dropdown')) {
              document.querySelectorAll('.dropdown-content.active').forEach(dc => {
                  dc.classList.remove('active');
              });
          }
      });
  });
</script>