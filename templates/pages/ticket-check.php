<?php
/** Consultation du ticket (5.3) — saisie numéro + affichage statut / réponse */
$ticketId = $ticketId ?? '';
$ticket = $ticket ?? null;
$notFound = $notFound ?? false;
?>
<header class="page-header">
  <h1>Consulter mon ticket</h1>
  <p class="subtitle">Saisissez votre numéro de ticket pour voir son statut et la réponse éventuelle.</p>
</header>

<div class="card">
  <form method="get" action="<?php echo escapeHtml($appBase); ?>/ticket" id="form-ticket-check">
    <div class="form-group">
      <label for="ticketId">
        <i class="fas fa-ticket-alt"></i>
        <span>Numéro de ticket</span>
      </label>
      <input type="text" id="ticketId" name="ticketId" value="<?php echo escapeHtml($ticketId); ?>" placeholder="Ex. TABC123" required>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">
        <i class="fas fa-search"></i>
        <span>Vérifier</span>
      </button>
    </div>
  </form>
</div>

<?php if ($ticketId !== ''): ?>
  <?php if ($notFound): ?>
    <div class="alert alert--error" role="alert">
      Aucun ticket trouvé pour ce numéro.
    </div>
  <?php else: ?>
    <section class="ticket-result">
      <div class="card">
        <h2>
          <i class="fas fa-ticket-alt"></i>
          <span>Ticket <?php echo escapeHtml($ticket['id'] ?? ''); ?></span>
        </h2>
        <p data-status="<?php echo escapeHtml($ticket['status'] ?? 'open'); ?>">
          <strong>
            <?php
            $status = $ticket['status'] ?? 'open';
            $statusIcons = ['open' => 'fa-circle', 'progress' => 'fa-hourglass-half', 'resolved' => 'fa-check-circle'];
            $statusIcon = $statusIcons[$status] ?? 'fa-circle';
            ?>
            <i class="fas <?php echo $statusIcon; ?>"></i>
            <span>Statut :</span>
          </strong>
          <?php
          $statusLabel = ['open' => 'Ouvert', 'progress' => 'En cours de traitement', 'resolved' => 'Résolu'][$status] ?? $status;
          echo escapeHtml($statusLabel);
          ?>
        </p>
        <p><strong>Titre :</strong> <?php echo escapeHtml($ticket['title'] ?? ''); ?></p>
        <?php if (($ticket['status'] ?? '') === 'resolved' && ($ticket['response'] ?? '') !== ''): ?>
          <p><strong>Réponse / solution :</strong></p>
          <div class="ticket-response"><?php echo nl2br(escapeHtml($ticket['response'])); ?></div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>
<?php endif; ?>

<footer class="page-footer">
  <a href="<?php echo escapeHtml($appBase); ?>/">← Retour à l'accueil</a>
</footer>
