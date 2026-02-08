<?php
/** Recherche par mots clés (5.1) — tickets résolus, FAQ, PDF */
$query = $query ?? '';
$resultsTickets = $resultsTickets ?? [];
$resultsFaq = $resultsFaq ?? [];
$resultsPdf = $resultsPdf ?? [];
?>
<header class="page-header">
  <h1>Rechercher une solution</h1>
  <p class="subtitle">Recherchez dans les tickets résolus, la FAQ et les documents PDF.</p>
</header>

<div class="card search-form-card">
  <form method="get" action="<?php echo escapeHtml($appBase); ?>/recherche">
    <div class="form-group">
      <label for="q">Mots clés</label>
      <input type="search" id="q" name="q" value="<?php echo escapeHtml($query); ?>" placeholder="Ex. synchronisation, accès...">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">
        <i class="fas fa-search"></i>
        <span>Rechercher</span>
      </button>
    </div>
  </form>
</div>

<?php if ($query !== ''): ?>
  <section class="results-section">
    <h2>
      <i class="fas fa-list"></i>
      <span>Résultats</span>
    </h2>

    <div class="card search-results-tickets">
      <h3>
        <i class="fas fa-ticket-alt"></i>
        <span>Tickets résolus</span>
      </h3>
      <?php if (empty($resultsTickets)): ?>
        <p class="muted">Aucun ticket résolu trouvé.</p>
      <?php else: ?>
        <ul class="result-list">
          <?php foreach ($resultsTickets as $t): ?>
            <li>
              <strong><?php echo escapeHtml($t['title'] ?? ''); ?></strong>
              <p><?php echo escapeHtml($t['desc'] ?? ''); ?></p>
              <?php if (!empty($t['response'])): ?>
                <p><em>Solution :</em> <?php echo escapeHtml($t['response']); ?></p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="card search-results-faq">
      <h3>
        <i class="fas fa-question-circle"></i>
        <span>FAQ</span>
      </h3>
      <?php if (empty($resultsFaq)): ?>
        <p class="muted">Aucune entrée FAQ trouvée.</p>
      <?php else: ?>
        <ul class="result-list">
          <?php foreach ($resultsFaq as $f): ?>
            <li>
              <strong><?php echo escapeHtml($f['title'] ?? ''); ?></strong>
              <p><?php echo escapeHtml($f['solution'] ?? ''); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="card search-results-pdf">
      <h3>
        <i class="fas fa-file-pdf"></i>
        <span>Documents PDF</span>
      </h3>
      <?php if (empty($resultsPdf)): ?>
        <p class="muted">Aucun document PDF trouvé.</p>
      <?php else: ?>
        <ul class="result-list">
          <?php foreach ($resultsPdf as $p): ?>
            <li>
              <strong><?php echo escapeHtml($p['title'] ?? ''); ?></strong>
              <?php if (!empty($p['description'])): ?>
                <p><?php echo escapeHtml($p['description']); ?></p>
              <?php endif; ?>
              <?php if (!empty($p['fileUrl'])): ?>
                <p><a href="<?php echo escapeHtml($baseUrl . $p['fileUrl']); ?>" target="_blank" rel="noopener" class="btn btn--primary btn--sm">
                  <i class="fas fa-file-pdf"></i>
                  <span>Télécharger le PDF</span>
                </a></p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>

<footer class="page-footer">
  <a href="<?php echo escapeHtml($appBase); ?>/">← Retour à l'accueil</a>
</footer>
