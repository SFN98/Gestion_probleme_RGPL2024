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
            $solutionType = $_POST['solutionType'] ?? 'text';
            $solutionContent = $_POST['solutionContent'] ?? '';
            $solutionFile = '';
            
            // Gérer l'upload de fichier si nécessaire
            if ($solutionType === 'pdf' || $solutionType === 'video') {
                if (isset($_FILES['solutionFile']) && $_FILES['solutionFile']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = MediaStorageService::storeSolutionFile($_FILES['solutionFile'], $ticketId, $solutionType);
                    if (!$uploadResult['success']) {
                        echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                        exit;
                    }
                    $solutionFile = $uploadResult['fileUrl'];
                } else {
                    echo json_encode(['success' => false, 'error' => 'Aucun fichier fourni pour la solution.']);
                    exit;
                }
            }
            
            $success = TicketService::markResolved($ticketId, $solutionType, $solutionContent, $solutionFile, $username);
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
                'category' => $_POST['category'] ?? $_POST['title'] ?? '',
                'question' => $_POST['question'] ?? '',
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
                'category' => $_POST['category'] ?? $_POST['title'] ?? '',
                'question' => $_POST['question'] ?? '',
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
            require_once BASE_PATH . '/src/Services/MediaStorageService.php';
            $keywords = json_decode($_POST['keywords'] ?? '[]', true);
            if (!is_array($keywords)) {
                $keywords = [];
            }
            $fileUrl = '';
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = MediaStorageService::storePdfLibrary($_FILES['pdf_file']);
                if (!$uploadResult['success']) {
                    echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                    exit;
                }
                $fileUrl = $uploadResult['fileUrl'];
            } elseif (!empty($_POST['fileUrl'])) {
                $fileUrl = $_POST['fileUrl'];
            }
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'fileUrl' => $fileUrl,
                'keywords' => $keywords,
            ];
            $id = PdfLibraryService::create($data);
            echo json_encode(['success' => true, 'id' => $id]);
            exit;
            
        case 'pdf_update':
            require_once BASE_PATH . '/src/Services/MediaStorageService.php';
            $id = $_POST['id'] ?? '';
            $existing = PdfLibraryService::getById($id);
            if (!$existing) {
                echo json_encode(['success' => false, 'error' => 'PDF non trouvé']);
                exit;
            }
            $keywords = json_decode($_POST['keywords'] ?? '[]', true);
            if (!is_array($keywords)) {
                $keywords = [];
            }
            $oldFileUrl = $existing['fileUrl'] ?? null;
            $fileUrl = $oldFileUrl;
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = MediaStorageService::storePdfLibrary($_FILES['pdf_file'], $oldFileUrl);
                if (!$uploadResult['success']) {
                    echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                    exit;
                }
                $fileUrl = $uploadResult['fileUrl'];
            } elseif (!empty($_POST['fileUrl'])) {
                $fileUrl = $_POST['fileUrl'];
            }
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'fileUrl' => $fileUrl,
                'keywords' => $keywords,
            ];
            $success = PdfLibraryService::update($id, $data);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'pdf_delete':
            require_once BASE_PATH . '/src/Services/MediaStorageService.php';
            $id = $_POST['id'] ?? '';
            $existing = PdfLibraryService::getById($id);
            if ($existing && !empty($existing['fileUrl'])) {
                MediaStorageService::deleteFile($existing['fileUrl']);
            }
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

$totalTickets = TicketService::countFiltered($filters);
$perPage = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pagination = null;

if ($totalTickets > $perPage) {
    $totalPages = ceil($totalTickets / $perPage);
    $offset = ($page - 1) * $perPage;
    $tickets = TicketService::getFiltered($filters, $perPage, $offset);
    $pagination = [
        'page' => $page,
        'totalPages' => $totalPages,
        'total' => $totalTickets,
        'perPage' => $perPage,
    ];
} else {
    $tickets = TicketService::getFiltered($filters);
}

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
