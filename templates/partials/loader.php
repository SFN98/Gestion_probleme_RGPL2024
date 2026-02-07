<?php
/**
 * Composant Loader — Affichage de chargement réutilisable
 *
 * Variables optionnelles (à définir avant l'inclusion) :
 * - $loader_id        string  ID du conteneur (défaut: 'app-loader')
 * - $loader_variant  string  'overlay' | 'inline' (défaut: 'overlay')
 * - $loader_title    string  Titre principal (défaut: 'Chargement')
 * - $loader_subtitle string  Sous-titre (défaut: 'Veuillez patienter...')
 * - $loader_progress bool    Afficher la barre de progression (défaut: true pour overlay, false pour inline)
 * - $loader_logo     string  URL d'une image logo, ou '' pour aucun (défaut: '')
 */
$loader_id        = $loader_id ?? 'app-loader';
$loader_variant   = $loader_variant ?? 'overlay';
$loader_title     = $loader_title ?? 'Chargement';
$loader_subtitle  = $loader_subtitle ?? 'Veuillez patienter...';
$loader_progress  = $loader_progress ?? ($loader_variant === 'overlay');
$loader_logo      = $loader_logo ?? '';
$loader_classes   = 'loader loader--' . $loader_variant;
?>
<div id="<?php echo htmlspecialchars($loader_id); ?>"
     class="<?php echo htmlspecialchars($loader_classes); ?>"
     role="status"
     aria-live="polite"
     aria-label="<?php echo htmlspecialchars($loader_title); ?>">
  <div class="loader__inner">
    <?php if ($loader_logo !== ''): ?>
      <div class="loader__logo">
        <img src="<?php echo htmlspecialchars($loader_logo); ?>" alt="" width="64" height="64">
      </div>
    <?php else: ?>
      <div class="loader__spinner" aria-hidden="true"></div>
    <?php endif; ?>
    <p class="loader__title"><?php echo htmlspecialchars($loader_title); ?></p>
    <p class="loader__subtitle"><?php echo htmlspecialchars($loader_subtitle); ?></p>
    <?php if ($loader_progress): ?>
      <div class="loader__progress">
        <span class="loader__progress-label">Initialisation en cours</span>
        <div class="loader__progress-bar">
          <span class="loader__progress-fill"></span>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
