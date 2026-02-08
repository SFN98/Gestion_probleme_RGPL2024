<?php
/**
 * Consultation du ticket par numéro (5.3) — statut et réponse/solution
 */

$ticketId = trim($_GET['ticketId'] ?? $_POST['ticketId'] ?? '');
$ticket = null;
$notFound = false;
$scripts = ['ticket-check.js'];

if ($ticketId !== '') {
    $ticket = TicketService::getById($ticketId);
    $notFound = ($ticket === null);
}
