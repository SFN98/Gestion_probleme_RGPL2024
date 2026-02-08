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
      <h3>
        <i class="fas fa-search"></i>
        <span>Rechercher une solution</span>
      </h3>
      <p>Consulter les tickets résolus, la FAQ et la bibliothèque PDF par mots clés.</p>
    </a>
  </li>
  <li>
    <a href="<?php echo escapeHtml($appBase); ?>/soumettre" class="card">
      <h3>
        <i class="fas fa-plus-circle"></i>
        <span>Soumettre un problème</span>
      </h3>
      <p>Déclarer un nouveau problème et obtenir un numéro de ticket.</p>
    </a>
  </li>
  <li>
    <a href="<?php echo escapeHtml($appBase); ?>/ticket" class="card">
      <h3>
        <i class="fas fa-ticket-alt"></i>
        <span>Consulter mon ticket</span>
      </h3>
      <p>Vérifier le statut et la réponse associée à votre ticket.</p>
    </a>
  </li>
</ul>
