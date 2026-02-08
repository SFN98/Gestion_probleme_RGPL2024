<?php
/**
 * Dashboard contrôleur (5.5) — liste tickets, KPI, FAQ, bibliothèque PDF
 */

AuthService::requireAuth();

$currentUser = AuthService::getCurrentUser();
$username = $currentUser['username'] ?? '';

// Actions POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'take':
            $ticketId = $_POST['ticketId'] ?? '';
            $result = TicketService::takeTicket($ticketId, $username);
            echo json_encode($result);
            exit;
            
        case 'release':
            $ticketId = $_POST['ticketId'] ?? '';
            $success = TicketService::releaseTicket($ticketId, $username);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'resolve':
            $ticketId = $_POST['ticketId'] ?? '';
            $response = $_POST['response'] ?? '';
            $success = TicketService::markResolved($ticketId, $response, $username);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'reassign':
            $ticketId = $_POST['ticketId'] ?? '';
            $newAssignee = trim($_POST['newAssignee'] ?? '');
            $success = TicketService::reassignTicket($ticketId, $newAssignee, $username);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'faq_create':
            $data = [
                'title' => $_POST['title'] ?? '',
                'solution' => $_POST['solution'] ?? '',
                'status' => $_POST['status'] ?? 'resolved',
            ];
            $id = FaqService::create($data);
            echo json_encode(['success' => true, 'id' => $id]);
            exit;
            
        case 'faq_update':
            $id = $_POST['id'] ?? '';
            $data = [
                'title' => $_POST['title'] ?? '',
                'solution' => $_POST['solution'] ?? '',
                'status' => $_POST['status'] ?? 'resolved',
            ];
            $success = FaqService::update($id, $data);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'faq_delete':
            $id = $_POST['id'] ?? '';
            $success = FaqService::delete($id);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'pdf_create':
            $keywords = json_decode($_POST['keywords'] ?? '[]', true);
            if (!is_array($keywords)) {
                $keywords = [];
            }
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'fileUrl' => $_POST['fileUrl'] ?? '',
                'keywords' => $keywords,
            ];
            $id = PdfLibraryService::create($data);
            echo json_encode(['success' => true, 'id' => $id]);
            exit;
            
        case 'pdf_update':
            $id = $_POST['id'] ?? '';
            $keywords = json_decode($_POST['keywords'] ?? '[]', true);
            if (!is_array($keywords)) {
                $keywords = [];
            }
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'fileUrl' => $_POST['fileUrl'] ?? '',
                'keywords' => $keywords,
            ];
            $success = PdfLibraryService::update($id, $data);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'pdf_delete':
            $id = $_POST['id'] ?? '';
            $success = PdfLibraryService::delete($id);
            echo json_encode(['success' => $success]);
            exit;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action inconnue']);
            exit;
    }
}

// GET : affichage dashboard
$filters = [
    'status' => $_GET['status'] ?? '',
    'province' => $_GET['province'] ?? '',
    'type' => $_GET['type'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'search' => $_GET['search'] ?? '',
];

$tickets = TicketService::getFiltered($filters);
$faq = FaqService::getAll();
$pdfLibrary = PdfLibraryService::getAll();

// Calcul des KPI
$pdo = getDb();
if ($pdo !== null) {
    $kpis = [
        'total' => (int) $pdo->query('SELECT COUNT(*) FROM tickets')->fetchColumn(),
        'open' => (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'open'")->fetchColumn(),
        'progress' => (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'progress'")->fetchColumn(),
        'resolved' => (int) $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'resolved'")->fetchColumn(),
    ];
} else {
    $kpis = [
        'total' => count($tickets),
        'open' => count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'open')),
        'progress' => count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'progress')),
        'resolved' => count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'resolved')),
    ];
}

$scripts = ['dashboard.js'];
