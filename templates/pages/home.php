<?php
/** Page d'accueil agent (5.0) — trois options */
?>
<header class="page-header">
  <h1>Plateforme de gestion des problèmes</h1>
  <p class="subtitle">Choisissez une option :</p>
</header>

<ul class="home-actions">
  <li>
    <a href="<?php echo escapeHtml($appBase); ?>/recherche" class="card">
      <h3>Rechercher une solution</h3>
      <p>Consulter les tickets résolus, la FAQ et la bibliothèque PDF par mots clés.</p>
    </a>
  </li>
  <li>
    <a href="<?php echo escapeHtml($appBase); ?>/soumettre" class="card">
      <h3>Soumettre un problème</h3>
      <p>Déclarer un nouveau problème et obtenir un numéro de ticket.</p>
    </a>
  </li>
  <li>
    <a href="<?php echo escapeHtml($appBase); ?>/ticket" class="card">
      <h3>Consulter mon ticket</h3>
      <p>Vérifier le statut et la réponse associée à votre ticket.</p>
    </a>
  </li>
</ul>
