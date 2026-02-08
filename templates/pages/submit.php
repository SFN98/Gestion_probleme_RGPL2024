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
    <p><strong>Votre problème a été enregistré.</strong></p>
    <p>Numéro de ticket : <strong><?php echo escapeHtml($ticketId); ?></strong></p>
    <div class="actions-links">
      <a href="<?php echo escapeHtml($appBase); ?>/ticket">Consulter l'état de mon ticket</a>
      <a href="<?php echo escapeHtml($appBase); ?>/soumettre" class="btn--ghost">Soumettre un autre problème</a>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <form method="post" action="<?php echo escapeHtml($appBase); ?>/soumettre" id="form-submit" class="submit-form">
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
      <div class="form-actions">
        <a href="<?php echo escapeHtml($appBase); ?>/" class="btn btn--ghost">Annuler</a>
        <button type="submit" class="btn btn--primary">Soumettre le problème</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<footer class="page-footer">
  <a href="<?php echo escapeHtml($appBase); ?>/">← Retour à l'accueil</a>
</footer>
