<?php
/**
 * Recherche par mots clés (5.1) — tickets résolus, FAQ, PDF
 */

$query = trim($_GET['q'] ?? '');
$resultsTickets = [];
$resultsFaq = [];
$resultsPdf = [];
$scripts = ['search.js'];

if ($query !== '') {
    $resultsTickets = TicketService::getResolvedForSearch($query);
    $resultsFaq = FaqService::searchByKeywords($query);
    $resultsPdf = PdfLibraryService::searchByKeywords($query);
}
