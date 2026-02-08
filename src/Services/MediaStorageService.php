<?php
/**
 * Stockage des médias — tickets (images/vidéos), PDF bibliothèque
 */

class MediaStorageService
{
    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const ALLOWED_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/quicktime'];
    private const ALLOWED_IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const ALLOWED_VIDEO_EXT = ['mp4', 'webm', 'mov'];
    private const ALLOWED_PDF_MIME = ['application/pdf'];
    private const ALLOWED_PDF_EXT = ['pdf'];

    /**
     * Stocke les pièces jointes d'un ticket (images/vidéos)
     */
    public static function storeTicketMedia(string $ticketId, array $files): array
    {
        $errors = [];
        $paths = [];
        $maxSize = UPLOAD_MAX_SIZE_TICKET_MB * 1024 * 1024;
        $ticketDir = UPLOAD_PATH_TICKETS . '/' . $ticketId;

        if (!is_dir($ticketDir)) {
            if (!mkdir($ticketDir, 0755, true)) {
                return ['errors' => ['Impossible de créer le dossier pour les pièces jointes.']];
            }
        }

        if (!isset($files['name']) || !is_array($files['name'])) {
            return ['errors' => ['Aucun fichier fourni.']];
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = self::getUploadErrorMessage($files['name'][$i], $files['error'][$i]);
                continue;
            }

            if (!is_uploaded_file($files['tmp_name'][$i])) {
                $errors[] = "Le fichier '{$files['name'][$i]}' n'est pas un fichier uploadé valide.";
                continue;
            }

            $size = $files['size'][$i];
            if ($size > $maxSize) {
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $fileType = in_array($ext, self::ALLOWED_VIDEO_EXT) ? 'video' : 'image';
                $errors[] = self::formatSizeError($files['name'][$i], $size, UPLOAD_MAX_SIZE_TICKET_MB, $fileType);
                continue;
            }

            $mime = $files['type'][$i];
            $originalName = $files['name'][$i];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Validation type MIME et extension
            $isImage = in_array($mime, self::ALLOWED_IMAGE_TYPES) && in_array($ext, self::ALLOWED_IMAGE_EXT);
            $isVideo = in_array($mime, self::ALLOWED_VIDEO_TYPES) && in_array($ext, self::ALLOWED_VIDEO_EXT);

            if (!$isImage && !$isVideo) {
                $errors[] = "Le fichier '{$originalName}' a un format non autorisé. Formats acceptés : images (JPG, PNG, GIF, WebP), vidéos (MP4, WebM, QuickTime).";
                continue;
            }

            // Validation supplémentaire avec finfo si disponible
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_file($finfo, $files['tmp_name'][$i]);
                finfo_close($finfo);
                if ($isImage && !in_array($detectedMime, self::ALLOWED_IMAGE_TYPES)) {
                    $errors[] = "Le fichier '{$originalName}' n'est pas une image valide.";
                    continue;
                }
                if ($isVideo && !in_array($detectedMime, self::ALLOWED_VIDEO_TYPES)) {
                    $errors[] = "Le fichier '{$originalName}' n'est pas une vidéo valide.";
                    continue;
                }
            }

            // Générer nom unique
            $uniqueName = self::generateUniqueFilename($ext);
            $destination = $ticketDir . '/' . $uniqueName;

            if (!move_uploaded_file($files['tmp_name'][$i], $destination)) {
                $errors[] = "Impossible de déplacer le fichier '{$originalName}'.";
                continue;
            }

            $paths[] = '/uploads/tickets/' . $ticketId . '/' . $uniqueName;
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['paths' => $paths];
    }

    /**
     * Stocke un PDF pour la bibliothèque
     */
    public static function storePdfLibrary(array $file, ?string $oldFileUrl = null): array
    {
        $maxSize = UPLOAD_MAX_SIZE_PDF_MB * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::getUploadErrorMessage($file['name'] ?? 'fichier', $file['error'])];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Le fichier n\'est pas un fichier uploadé valide.'];
        }

        $size = $file['size'];
        if ($size > $maxSize) {
            return ['success' => false, 'error' => self::formatSizeError($file['name'], $size, UPLOAD_MAX_SIZE_PDF_MB, 'pdf')];
        }

        $mime = $file['type'];
        $originalName = $file['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($mime, self::ALLOWED_PDF_MIME) || !in_array($ext, self::ALLOWED_PDF_EXT)) {
            return ['success' => false, 'error' => "Le fichier '{$originalName}' doit être un PDF."];
        }

        // Validation avec finfo si disponible
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if ($detectedMime !== 'application/pdf') {
                return ['success' => false, 'error' => "Le fichier '{$originalName}' n'est pas un PDF valide."];
            }
        }

        // Supprimer ancien fichier si présent
        if ($oldFileUrl !== null && $oldFileUrl !== '') {
            self::deleteFile($oldFileUrl);
        }

        // Générer nom unique
        $uniqueName = self::generateUniqueFilename('pdf');
        $destination = UPLOAD_PATH_PDF_LIBRARY . '/' . $uniqueName;

        if (!is_dir(UPLOAD_PATH_PDF_LIBRARY)) {
            mkdir(UPLOAD_PATH_PDF_LIBRARY, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => "Impossible de déplacer le fichier '{$originalName}'."];
        }

        return ['success' => true, 'fileUrl' => '/uploads/pdf-library/' . $uniqueName];
    }

    /**
     * Stocke un fichier de solution (PDF ou vidéo) pour un ticket résolu
     */
    public static function storeSolutionFile(array $file, string $ticketId, string $type): array
    {
        $maxSize = ($type === 'pdf') ? (UPLOAD_MAX_SIZE_PDF_MB * 1024 * 1024) : (UPLOAD_MAX_SIZE_TICKET_MB * 1024 * 1024);

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::getUploadErrorMessage($file['name'] ?? 'fichier', $file['error'])];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Le fichier n\'est pas un fichier uploadé valide.'];
        }

        $size = $file['size'];
        if ($size > $maxSize) {
            $maxMb = ($type === 'pdf') ? UPLOAD_MAX_SIZE_PDF_MB : UPLOAD_MAX_SIZE_TICKET_MB;
            return ['success' => false, 'error' => self::formatSizeError($file['name'], $size, $maxMb, $type)];
        }

        $mime = $file['type'];
        $originalName = $file['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($type === 'pdf') {
            if (!in_array($mime, self::ALLOWED_PDF_MIME) || !in_array($ext, self::ALLOWED_PDF_EXT)) {
                return ['success' => false, 'error' => "Le fichier '{$originalName}' doit être un PDF."];
            }
            // Validation avec finfo si disponible
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if ($detectedMime !== 'application/pdf') {
                    return ['success' => false, 'error' => "Le fichier '{$originalName}' n'est pas un PDF valide."];
                }
            }
        } elseif ($type === 'video') {
            if (!in_array($mime, self::ALLOWED_VIDEO_TYPES) || !in_array($ext, self::ALLOWED_VIDEO_EXT)) {
                return ['success' => false, 'error' => "Le fichier '{$originalName}' doit être une vidéo (MP4, WebM, MOV)."];
            }
        }

        // Créer le dossier solutions pour le ticket
        $solutionDir = UPLOAD_PATH_TICKETS . '/' . $ticketId . '/solutions';
        if (!is_dir($solutionDir)) {
            if (!mkdir($solutionDir, 0755, true)) {
                return ['success' => false, 'error' => 'Impossible de créer le dossier pour la solution.'];
            }
        }

        // Générer nom unique
        $uniqueName = self::generateUniqueFilename($ext);
        $destination = $solutionDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement du fichier.'];
        }

        $relativeUrl = '/uploads/tickets/' . $ticketId . '/solutions/' . $uniqueName;
        return ['success' => true, 'fileUrl' => $relativeUrl];
    }

    /**
     * Supprime un fichier
     */
    public static function deleteFile(string $fileUrl): bool
    {
        if (empty($fileUrl)) {
            return false;
        }
        $filePath = PUBLIC_PATH . $fileUrl;
        if (is_file($filePath) && strpos(realpath($filePath), realpath(PUBLIC_PATH)) === 0) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Retourne les liens vers outils de compression
     */
    public static function getCompressionLinks(string $fileType): array
    {
        if ($fileType === 'video') {
            return [
                ['name' => 'FreeConvert', 'url' => 'https://www.freeconvert.com/video-compressor'],
                ['name' => 'Clideo', 'url' => 'https://www.clideo.com/compress-video'],
                ['name' => 'CloudConvert', 'url' => 'https://cloudconvert.com/video-compressor'],
            ];
        } elseif ($fileType === 'pdf') {
            return [
                ['name' => 'iLovePDF', 'url' => 'https://www.ilovepdf.com/compress-pdf'],
                ['name' => 'SmallPDF', 'url' => 'https://smallpdf.com/compress-pdf'],
                ['name' => 'PDF24', 'url' => 'https://tools.pdf24.org/fr/compress-pdf'],
            ];
        }
        return [];
    }

    /**
     * Formate un message d'erreur de taille avec liens compression
     */
    public static function formatSizeError(string $filename, int $sizeBytes, int $maxMb, string $fileType): string
    {
        $sizeMb = round($sizeBytes / (1024 * 1024), 2);
        $links = self::getCompressionLinks($fileType);
        $linksHtml = [];
        foreach ($links as $link) {
            $linksHtml[] = '<a href="' . htmlspecialchars($link['url']) . '" target="_blank" rel="noopener">' . htmlspecialchars($link['name']) . '</a>';
        }
        $linksStr = implode(', ', $linksHtml);
        $typeLabel = $fileType === 'video' ? 'vidéo' : 'PDF';
        return "Le fichier '{$filename}' ({$sizeMb} Mo) dépasse la limite de {$maxMb} Mo. Compresser une {$typeLabel} : {$linksStr}.";
    }

    /**
     * Génère un nom de fichier unique
     */
    private static function generateUniqueFilename(string $ext): string
    {
        return bin2hex(random_bytes(16)) . '.' . $ext;
    }

    /**
     * Retourne un message d'erreur selon le code UPLOAD_ERR_*
     */
    private static function getUploadErrorMessage(string $filename, int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => "Le fichier '{$filename}' dépasse la limite de taille définie dans php.ini.",
            UPLOAD_ERR_FORM_SIZE => "Le fichier '{$filename}' dépasse la limite de taille du formulaire.",
            UPLOAD_ERR_PARTIAL => "Le fichier '{$filename}' n'a été que partiellement uploadé.",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été uploadé pour '{$filename}'.",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant pour '{$filename}'.",
            UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier '{$filename}' sur le disque.",
            UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload de '{$filename}'.",
        ];
        return $messages[$errorCode] ?? "Erreur inconnue lors de l'upload de '{$filename}'.";
    }
}
