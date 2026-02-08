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
      <label for="ticketId">Numéro de ticket</label>
      <input type="text" id="ticketId" name="ticketId" value="<?php echo escapeHtml($ticketId); ?>" placeholder="Ex. TABC123" required>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn--primary">Vérifier</button>
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
        <h2>Ticket <?php echo escapeHtml($ticket['id'] ?? ''); ?></h2>
        <p><strong>Statut :</strong>
          <?php
          $status = $ticket['status'] ?? 'open';
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
