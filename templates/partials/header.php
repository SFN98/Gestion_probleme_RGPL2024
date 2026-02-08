<?php
/**
 * Header global du site — navigation, logo, actions utilisateur
 */
$currentUser = $currentUser ?? null;
$isAuthenticated = !empty($currentUser);
$username = $currentUser['username'] ?? '';
$displayName = $currentUser['display_name'] ?? $username;
?>
<header class="site-header">
  <div class="site-header__container">
    <div class="site-header__brand">
      <a href="<?php echo escapeHtml($appBase); ?>/" class="site-header__logo">
        <i class="fas fa-ticket-alt site-header__logo-icon"></i>
        <span class="site-header__logo-text">RGPL 2024</span>
      </a>
    </div>
    <button class="site-header__toggle" aria-label="Menu" aria-expanded="false" id="header-toggle">
      <i class="fas fa-bars"></i>
    </button>
    <nav class="site-header__nav" id="header-nav">
      <?php if ($isAuthenticated): ?>
        <!-- Navigation pour utilisateurs connectés -->
        <a href="<?php echo escapeHtml($appBase); ?>/dashboard" class="site-header__link">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
        <span class="site-header__user">
          <span class="site-header__user-info">
            <i class="fas fa-user-circle"></i>
            <span class="site-header__user-name"><?php echo escapeHtml($displayName); ?></span>
          </span>
          <a href="<?php echo escapeHtml($appBase); ?>/logout" class="btn btn--sm btn--ghost">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
          </a>
        </span>
      <?php else: ?>
        <!-- Navigation pour visiteurs -->
        <a href="<?php echo escapeHtml($appBase); ?>/" class="site-header__link">
          <i class="fas fa-home"></i>
          <span>Accueil</span>
        </a>
        <a href="<?php echo escapeHtml($appBase); ?>/recherche" class="site-header__link">
          <i class="fas fa-search"></i>
          <span>Rechercher</span>
        </a>
        <a href="<?php echo escapeHtml($appBase); ?>/soumettre" class="site-header__link">
          <i class="fas fa-plus-circle"></i>
          <span>Soumettre</span>
        </a>
        <a href="<?php echo escapeHtml($appBase); ?>/ticket" class="site-header__link">
          <i class="fas fa-ticket-alt"></i>
          <span>Mon ticket</span>
        </a>
        <a href="<?php echo escapeHtml($appBase); ?>/login" class="btn btn--sm btn--primary">
          <i class="fas fa-sign-in-alt"></i>
          <span>Connexion</span>
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>
