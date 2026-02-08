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
      <button type="submit" class="btn btn--primary">Rechercher</button>
    </div>
  </form>
</div>

<?php if ($query !== ''): ?>
  <section class="results-section">
    <h2>Résultats</h2>

    <div class="card search-results-tickets">
      <h3>Tickets résolus</h3>
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
      <h3>FAQ</h3>
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
      <h3>Documents PDF</h3>
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
                <p><a href="<?php echo escapeHtml($baseUrl . $p['fileUrl']); ?>" target="_blank" rel="noopener" class="btn btn--primary btn--sm">Télécharger le PDF</a></p>
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
