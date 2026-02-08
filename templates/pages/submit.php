<?php
/** Soumission de problème (5.2) — formulaire + création ticket */
$problemTypes = $problemTypes ?? [];
$errors = $errors ?? [];
$success = $success ?? false;
$ticketId = $ticketId ?? null;
$province = $province ?? '';
?>
<header class="page-header">
  <h1>Soumettre un problème</h1>
  <p class="subtitle">Renseignez les informations ci-dessous pour enregistrer votre problème.</p>
</header>

<?php if (!empty($errors)): ?>
  <ul class="alert alert--error" role="alert">
    <?php foreach ($errors as $e): ?>
      <li><?php echo escapeHtml($e); ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if ($success && $ticketId): ?>
  <div class="card success-card">
    <i class="fas fa-check-circle success-icon"></i>
    <p><strong>Votre problème a été enregistré.</strong></p>
    <div class="ticket-id-section">
      <p>Numéro de ticket : <strong id="ticket-id-display"><?php echo escapeHtml($ticketId); ?></strong></p>
      <button type="button" class="btn btn--sm btn--primary" onclick="copyTicketId('<?php echo escapeHtml($ticketId); ?>')" id="copy-ticket-btn">
        <i class="fas fa-copy"></i>
        <span>Copier le code</span>
      </button>
    </div>
    <div class="actions-links">
      <a href="<?php echo escapeHtml($appBase); ?>/ticket">
        <i class="fas fa-ticket-alt"></i>
        <span>Consulter l'état de mon ticket</span>
      </a>
      <a href="<?php echo escapeHtml($appBase); ?>/soumettre" class="btn--ghost">
        <i class="fas fa-plus-circle"></i>
        <span>Soumettre un autre problème</span>
      </a>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <form method="post" action="<?php echo escapeHtml($appBase); ?>/soumettre" id="form-submit" class="submit-form" enctype="multipart/form-data">
      <div class="form-group">
        <label for="reporterName">Nom du soumetteur *</label>
        <input type="text" id="reporterName" name="reporterName" required value="<?php echo escapeHtml($_POST['reporterName'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label for="login">Login *</label>
        <input type="text" id="login" name="login" required value="<?php echo escapeHtml($_POST['login'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label for="phone">Téléphone *</label>
        <input type="tel" id="phone" name="phone" required value="<?php echo escapeHtml($_POST['phone'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label for="type">Type de problème *</label>
        <select id="type" name="type" required>
          <option value="">— Choisir —</option>
          <?php foreach ($problemTypes as $t): ?>
            <option value="<?php echo escapeHtml($t); ?>"<?php echo (isset($_POST['type']) && $_POST['type'] === $t) ? ' selected' : ''; ?>><?php echo escapeHtml($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($province !== ''): ?>
      <div class="form-group">
        <label>Province (détectée)</label>
        <span><?php echo escapeHtml($province); ?></span>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label for="title">Titre du problème *</label>
        <input type="text" id="title" name="title" required value="<?php echo escapeHtml($_POST['title'] ?? ''); ?>">
      </div>
      <div class="form-group">
        <label for="desc">Description détaillée *</label>
        <textarea id="desc" name="desc" required rows="5"><?php echo escapeHtml($_POST['desc'] ?? ''); ?></textarea>
      </div>
      <div class="form-group">
        <label for="attachments">
          <span>Pièces jointes (images/vidéos, max 5 Mo par fichier)</span>
          <i class="fas fa-paperclip"></i>
        </label>
        <input type="file" id="attachments" name="attachments[]" multiple accept="image/*,video/*">
        <small class="muted">Formats acceptés : images (JPG, PNG, GIF, WebP), vidéos (MP4, WebM, QuickTime). Maximum 5 Mo par fichier.</small>
        <div id="attachments-preview" class="images-preview" style="margin-top: var(--space-3);"></div>
      </div>
      <div class="form-actions">
        <a href="<?php echo escapeHtml($appBase); ?>/" class="btn btn--ghost">
          <i class="fas fa-times"></i>
          <span>Annuler</span>
        </a>
        <button type="submit" class="btn btn--primary">
          <i class="fas fa-paper-plane"></i>
          <span>Soumettre le problème</span>
        </button>
      </div>
    </form>
  </div>
<?php endif; ?>

<footer class="page-footer">
  <a href="<?php echo escapeHtml($appBase); ?>/">← Retour à l'accueil</a>
</footer>
