<?php
/**
 * Soumission de problème (5.2) — formulaire + création ticket
 */

$errors = [];
$success = false;
$ticketId = null;
$province = '';
$problemTypes = ProvinceHelper::getProblemTypes();
$scripts = ['submit.js'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reporterName = trim($_POST['reporterName'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['desc'] ?? '');

    if ($reporterName === '') {
        $errors[] = 'Le nom du soumetteur est requis.';
    }
    if ($login === '') {
        $errors[] = 'Le login est requis.';
    }
    if ($phone === '') {
        $errors[] = 'Le téléphone est requis.';
    }
    if ($type === '') {
        $errors[] = 'Le type de problème est requis.';
    }
    if ($title === '') {
        $errors[] = 'Le titre du problème est requis.';
    }
    if ($desc === '') {
        $errors[] = 'La description est requise.';
    }

    if (empty($errors)) {
        $province = ProvinceHelper::getProvinceFromLogin($login);
        $priority = ProvinceHelper::getPriorityFromType($type);
        $ticketId = TicketService::create([
            'title' => $title,
            'reporterName' => $reporterName,
            'login' => $login,
            'phone' => $phone,
            'type' => $type,
            'desc' => $desc,
            'province' => $province,
            'priority' => $priority,
        ]);

        // Gestion des pièces jointes
        if (isset($_FILES['attachments']) && $_FILES['attachments']['error'][0] !== UPLOAD_ERR_NO_FILE) {
            require_once BASE_PATH . '/src/Services/MediaStorageService.php';
            $uploadResult = MediaStorageService::storeTicketMedia($ticketId, $_FILES['attachments']);
            if (isset($uploadResult['errors']) && !empty($uploadResult['errors'])) {
                $errors = array_merge($errors, $uploadResult['errors']);
            } elseif (isset($uploadResult['paths']) && !empty($uploadResult['paths'])) {
                TicketService::updateImages($ticketId, $uploadResult['paths']);
            }
        }

        if (empty($errors)) {
            $success = true;
        }
    } else {
        $province = $login !== '' ? ProvinceHelper::getProvinceFromLogin($login) : '';
    }
}
