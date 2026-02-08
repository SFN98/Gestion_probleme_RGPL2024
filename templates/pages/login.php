<?php
/** Connexion administrateur (5.4) */
$error = $error ?? null;
$redirect = $redirect ?? '/dashboard';
?>
<div class="bg-animation">
  <div class="bg-circle"></div>
  <div class="bg-circle"></div>
  <div class="bg-circle"></div>
</div>

<div class="login-container">
  <div class="login-header">
    <div class="logo">
      <i class="fas fa-lock"></i>
    </div>
    <h1 class="login-title">Connexion Dashboard</h1>
    <p class="login-subtitle">Accès administrateur sécurisé</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert--error" role="alert">
      <?php echo escapeHtml($error); ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo escapeHtml($appBase); ?>/login" id="form-login">
    <input type="hidden" name="redirect" value="<?php echo escapeHtml($redirect); ?>">
    
    <div class="form-group">
      <label for="username">Identifiant</label>
      <input type="text" id="username" name="username" autocomplete="username" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>

    <div class="form-options">
      <label class="checkbox-wrapper">
        <input type="checkbox" id="remember" name="remember" value="1">
        <span>Se souvenir de moi</span>
      </label>
    </div>

    <button type="submit" class="btn-login">
      <i class="fas fa-sign-in-alt"></i>
      <span class="btn-text">Se connecter</span>
    </button>
  </form>

  <div class="login-footer">
    <a href="<?php echo escapeHtml($appBase); ?>/">
      <i class="fas fa-arrow-left"></i>
      <span>Retour à la page de soumission</span>
    </a>
  </div>
</div>
