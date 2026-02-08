<?php
/** Dashboard contrôleur (5.5) */
$tickets = $tickets ?? [];
$faq = $faq ?? [];
$pdfLibrary = $pdfLibrary ?? [];
$kpis = $kpis ?? ['total' => 0, 'open' => 0, 'progress' => 0, 'resolved' => 0];
$filters = $filters ?? [];
$currentUser = $currentUser ?? null;
$username = $currentUser['username'] ?? '';
$problemTypes = ProvinceHelper::getProblemTypes();
?>
<header class="page-header">
  <h1>
    <i class="fas fa-tachometer-alt"></i>
    <span>Dashboard Administrateur</span>
  </h1>
</header>

<!-- KPI -->
<div class="kpis">
  <div class="kpi">
    <div class="label">
      <i class="fas fa-ticket-alt"></i>
      <span>Total</span>
    </div>
    <div class="value"><?php echo $kpis['total']; ?></div>
  </div>
  <div class="kpi red">
    <div class="label">
      <i class="fas fa-circle"></i>
      <span>Ouverts</span>
    </div>
    <div class="value"><?php echo $kpis['open']; ?></div>
  </div>
  <div class="kpi orange">
    <div class="label">
      <i class="fas fa-hourglass-half"></i>
      <span>En cours</span>
    </div>
    <div class="value"><?php echo $kpis['progress']; ?></div>
  </div>
  <div class="kpi green">
    <div class="label">
      <i class="fas fa-check-circle"></i>
      <span>Résolus</span>
    </div>
    <div class="value"><?php echo $kpis['resolved']; ?></div>
  </div>
</div>

<!-- Filtres -->
<div class="card">
  <form method="get" action="<?php echo escapeHtml($appBase); ?>/dashboard" class="search-row">
    <input type="search" name="search" placeholder="Rechercher (ID, titre, login...)" value="<?php echo escapeHtml($filters['search'] ?? ''); ?>">
    <select name="status">
      <option value="">Tous les statuts</option>
      <option value="open"<?php echo ($filters['status'] ?? '') === 'open' ? ' selected' : ''; ?>>Ouvert</option>
      <option value="progress"<?php echo ($filters['status'] ?? '') === 'progress' ? ' selected' : ''; ?>>En cours</option>
      <option value="resolved"<?php echo ($filters['status'] ?? '') === 'resolved' ? ' selected' : ''; ?>>Résolu</option>
    </select>
    <select name="priority">
      <option value="">Toutes priorités</option>
      <option value="P1"<?php echo ($filters['priority'] ?? '') === 'P1' ? ' selected' : ''; ?>>P1</option>
      <option value="P2"<?php echo ($filters['priority'] ?? '') === 'P2' ? ' selected' : ''; ?>>P2</option>
      <option value="P3"<?php echo ($filters['priority'] ?? '') === 'P3' ? ' selected' : ''; ?>>P3</option>
      <option value="P4"<?php echo ($filters['priority'] ?? '') === 'P4' ? ' selected' : ''; ?>>P4</option>
    </select>
    <button type="submit" class="btn btn--primary">Filtrer</button>
    <a href="<?php echo escapeHtml($appBase); ?>/dashboard" class="btn btn--ghost">Réinitialiser</a>
  </form>
</div>

<div class="layout">
  <!-- Liste tickets -->
  <div>
    <div class="card">
      <h2>Liste des tickets</h2>
      <?php if (empty($tickets)): ?>
        <p class="no-results">Aucun ticket trouvé.</p>
      <?php else: ?>
        <table id="tickets-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Province</th>
              <th>Occ.</th>
              <th>Priorité</th>
              <th>Assigné</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tickets as $t): ?>
              <tr data-ticket-id="<?php echo escapeHtml($t['id']); ?>">
                <td data-label="ID"><?php echo escapeHtml($t['id']); ?></td>
                <td data-label="Titre"><?php echo escapeHtml($t['title']); ?></td>
                <td data-label="Province"><?php echo escapeHtml($t['province']); ?></td>
                <td data-label="Occurrences"><?php echo $t['occurrences']; ?></td>
                <td data-label="Priorité"><span class="badge badge--<?php echo strtolower($t['priority']); ?>"><?php echo escapeHtml($t['priority']); ?></span></td>
                <td data-label="Assigné"><?php echo escapeHtml($t['assignee'] ?: 'Non assigné'); ?></td>
                <td data-label="Statut">
                  <?php
                  $statusLabels = ['open' => 'Ouvert', 'progress' => 'En cours', 'resolved' => 'Résolu'];
                  $status = $t['status'] ?? 'open';
                  $label = $statusLabels[$status] ?? $status;
                  $class = $status === 'resolved' ? 'status-resolved' : ($status === 'open' ? 'status-open' : '');
                  ?>
                  <span class="<?php echo $class; ?>"><?php echo escapeHtml($label); ?></span>
                </td>
                <td class="actions" data-label="Actions">
                  <?php if ($status === 'open'): ?>
                    <button class="btn btn--sm btn--primary" onclick="takeTicket('<?php echo escapeHtml($t['id']); ?>')">Prendre</button>
                  <?php elseif ($status === 'progress' && $t['assignee'] === $username): ?>
                    <button class="btn btn--sm btn--ghost" onclick="releaseTicket('<?php echo escapeHtml($t['id']); ?>')">Libérer</button>
                    <button class="btn btn--sm btn--secondary" onclick="showResolveModal('<?php echo escapeHtml($t['id']); ?>')">Résoudre</button>
                  <?php elseif ($status === 'progress'): ?>
                    <button class="btn btn--sm btn--ghost" onclick="showReassignModal('<?php echo escapeHtml($t['id']); ?>', '<?php echo escapeHtml($t['assignee']); ?>')">Réaffecter</button>
                  <?php endif; ?>
                  <button class="btn btn--sm btn--ghost" onclick="showTicketDetail('<?php echo escapeHtml($t['id']); ?>')">Voir</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!empty($pagination)): ?>
          <div class="pagination" style="margin-top: var(--space-4); display: flex; justify-content: space-between; align-items: center;">
            <div>
              Page <?php echo $pagination['page']; ?> sur <?php echo $pagination['totalPages']; ?> (<?php echo $pagination['total']; ?> tickets au total)
            </div>
            <div>
              <?php if ($pagination['page'] > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['page'] - 1])); ?>" class="btn btn--sm btn--ghost">
                  <i class="fas fa-arrow-left"></i>
                  <span>Précédent</span>
                </a>
              <?php endif; ?>
              <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['page'] + 1])); ?>" class="btn btn--sm btn--ghost">
                  <span>Suivant</span>
                  <i class="fas fa-arrow-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Panneau latéral -->
  <div>
    <!-- Gestion FAQ -->
    <div class="card card--spaced">
      <div class="card__header">
        <h3>FAQ</h3>
        <button class="btn btn--sm btn--primary" onclick="showFaqModal()">Ajouter</button>
      </div>
      <?php if (empty($faq)): ?>
        <p class="muted">Aucune entrée FAQ.</p>
      <?php else: ?>
        <ul class="result-list">
          <?php foreach ($faq as $f): ?>
            <li>
              <strong><?php echo escapeHtml($f['title']); ?></strong>
              <p><?php echo escapeHtml(substr($f['solution'], 0, 100)); ?>...</p>
              <div class="header-actions" style="margin-top: var(--space-2);">
                <button class="btn btn--sm btn--ghost" onclick="showFaqModal('<?php echo escapeHtml($f['id']); ?>')">Modifier</button>
                <button class="btn btn--sm btn--danger" onclick="deleteFaq('<?php echo escapeHtml($f['id']); ?>')">Supprimer</button>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Bibliothèque PDF -->
    <div class="card">
      <div class="card__header">
        <h3>Bibliothèque PDF</h3>
        <button class="btn btn--sm btn--primary" onclick="showPdfModal()">Ajouter</button>
      </div>
      <?php if (empty($pdfLibrary)): ?>
        <p class="muted">Aucun document PDF.</p>
      <?php else: ?>
        <ul class="result-list">
          <?php foreach ($pdfLibrary as $p): ?>
            <li>
              <strong><?php echo escapeHtml($p['title']); ?></strong>
              <?php if (!empty($p['description'])): ?>
                <p><?php echo escapeHtml(substr($p['description'], 0, 100)); ?>...</p>
              <?php endif; ?>
              <?php if (!empty($p['fileUrl'])): ?>
                <p><a href="<?php echo escapeHtml($baseUrl . $p['fileUrl']); ?>" target="_blank" class="btn btn--sm btn--primary">Télécharger</a></p>
              <?php endif; ?>
              <div class="header-actions" style="margin-top: var(--space-2);">
                <button class="btn btn--sm btn--ghost" onclick="showPdfModal('<?php echo escapeHtml($p['id']); ?>')">Modifier</button>
                <button class="btn btn--sm btn--danger" onclick="deletePdf('<?php echo escapeHtml($p['id']); ?>')">Supprimer</button>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modales -->
<!-- Détail ticket -->
<div id="modal-ticket-detail" class="modal-back" style="display: none;">
  <div class="modal">
    <div class="modal__header">
      <h2>Détail du ticket</h2>
      <button onclick="closeModal('modal-ticket-detail')" class="btn btn--ghost">✕</button>
    </div>
    <div class="modal__body" id="ticket-detail-content">
      <!-- Rempli par JS -->
    </div>
  </div>
</div>

<!-- Réaffectation -->
<div id="modal-reassign" class="modal-back" style="display: none;">
  <div class="modal">
    <div class="modal__header">
      <h2>Réaffecter le ticket</h2>
      <button onclick="closeModal('modal-reassign')" class="btn btn--ghost">✕</button>
    </div>
    <div class="modal__body">
      <form id="form-reassign">
        <input type="hidden" id="reassign-ticket-id">
        <div class="form-group">
          <label for="reassign-new-assignee">Nouvel assigné</label>
          <input type="text" id="reassign-new-assignee" required>
        </div>
        <div class="modal__actions">
          <button type="button" onclick="closeModal('modal-reassign')" class="btn btn--ghost">Annuler</button>
          <button type="submit" class="btn btn--primary">Réaffecter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Marquer résolu -->
<div id="modal-resolve" class="modal-back" style="display: none;">
  <div class="modal">
    <div class="modal__header">
      <h2>Marquer comme résolu</h2>
      <button onclick="closeModal('modal-resolve')" class="btn btn--ghost">✕</button>
    </div>
    <div class="modal__body">
      <form id="form-resolve">
        <input type="hidden" id="resolve-ticket-id">
        <div class="form-group">
          <label for="resolve-solution-type">Type de solution *</label>
          <select id="resolve-solution-type" required onchange="toggleSolutionFields()">
            <option value="">-- Sélectionner un type --</option>
            <option value="text">Texte (explication du processus)</option>
            <option value="pdf">PDF (document avec les étapes)</option>
            <option value="video">Vidéo explicative</option>
          </select>
        </div>
        <div class="form-group" id="resolve-text-group" style="display: none;">
          <label for="resolve-response">Explication du processus *</label>
          <textarea id="resolve-response" rows="6" placeholder="Décrivez étape par étape comment résoudre le problème..."></textarea>
        </div>
        <div class="form-group" id="resolve-pdf-group" style="display: none;">
          <label for="resolve-pdf-file">Document PDF *</label>
          <input type="file" id="resolve-pdf-file" accept=".pdf" onchange="handlePdfFileSelect(this)">
          <small class="form-hint">Téléversez un document PDF contenant les étapes de résolution</small>
          <div id="resolve-pdf-preview" style="margin-top: var(--space-2); display: none;">
            <span id="resolve-pdf-name"></span>
            <button type="button" class="btn btn--sm btn--ghost" onclick="clearPdfFile()">Supprimer</button>
          </div>
        </div>
        <div class="form-group" id="resolve-video-group" style="display: none;">
          <label for="resolve-video-file">Vidéo explicative *</label>
          <input type="file" id="resolve-video-file" accept="video/*" onchange="handleVideoFileSelect(this)">
          <small class="form-hint">Téléversez une vidéo explicative (MP4, WebM, etc.)</small>
          <div id="resolve-video-preview" style="margin-top: var(--space-2); display: none;">
            <span id="resolve-video-name"></span>
            <button type="button" class="btn btn--sm btn--ghost" onclick="clearVideoFile()">Supprimer</button>
          </div>
        </div>
        <div class="modal__actions">
          <button type="button" onclick="closeModal('modal-resolve')" class="btn btn--ghost">Annuler</button>
          <button type="submit" class="btn btn--primary">Marquer résolu</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CRUD FAQ -->
<div id="modal-faq" class="modal-back" style="display: none;">
  <div class="modal">
    <div class="modal__header">
      <h2 id="modal-faq-title">Ajouter une FAQ</h2>
      <button onclick="closeModal('modal-faq')" class="btn btn--ghost">✕</button>
    </div>
    <div class="modal__body">
      <form id="form-faq">
        <input type="hidden" id="faq-id">
        <div class="form-group">
          <label for="faq-title">Titre / Catégorie *</label>
          <input type="text" id="faq-title" placeholder="Ex: Connexion, Synchronisation, Erreur..." required>
          <small class="form-hint">Catégorie ou titre de classification</small>
        </div>
        <div class="form-group">
          <label for="faq-question">Question *</label>
          <textarea id="faq-question" rows="3" placeholder="Ex: Comment résoudre un problème de connexion ?" required></textarea>
        </div>
        <div class="form-group">
          <label for="faq-solution">Réponse / Solution *</label>
          <textarea id="faq-solution" rows="5" placeholder="Ex: Vérifier la connexion réseau et relancer l'application..." required></textarea>
        </div>
        <div class="form-group">
          <label for="faq-status">Statut</label>
          <select id="faq-status">
            <option value="resolved">Résolu</option>
            <option value="workaround">Contournement</option>
            <option value="known">Connu</option>
          </select>
        </div>
        <div class="modal__actions">
          <button type="button" onclick="closeModal('modal-faq')" class="btn btn--ghost">Annuler</button>
          <button type="submit" class="btn btn--primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CRUD PDF -->
<div id="modal-pdf" class="modal-back" style="display: none;">
  <div class="modal">
    <div class="modal__header">
      <h2 id="modal-pdf-title">Ajouter un document PDF</h2>
      <button onclick="closeModal('modal-pdf')" class="btn btn--ghost">✕</button>
    </div>
    <div class="modal__body">
      <form id="form-pdf" enctype="multipart/form-data">
        <input type="hidden" id="pdf-id">
        <div class="form-group">
          <label for="pdf-title">Titre *</label>
          <input type="text" id="pdf-title" required>
        </div>
        <div class="form-group">
          <label for="pdf-description">Description</label>
          <textarea id="pdf-description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="pdf-file">Fichier PDF *</label>
          <input type="file" id="pdf-file" name="pdf_file" accept="application/pdf">
          <small class="muted">Maximum 20 Mo. <span id="pdf-current-file"></span></small>
        </div>
        <div class="form-group">
          <label for="pdf-keywords">Mots clés (séparés par des virgules)</label>
          <input type="text" id="pdf-keywords" placeholder="ex. synchronisation, accès">
        </div>
        <div class="modal__actions">
          <button type="button" onclick="closeModal('modal-pdf')" class="btn btn--ghost">Annuler</button>
          <button type="submit" class="btn btn--primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Données globales pour JS
window.ticketsData = <?php echo json_encode($tickets); ?>;
window.faqData = <?php echo json_encode($faq); ?>;
window.pdfData = <?php echo json_encode($pdfLibrary); ?>;
window.currentUsername = <?php echo json_encode($username); ?>;
window.appBase = <?php echo json_encode($appBase); ?>;
window.baseUrl = <?php echo json_encode($baseUrl); ?>;
</script>
