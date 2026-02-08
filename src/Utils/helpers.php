<?php
/**
 * Utilitaires réutilisables
 */

function escapeHtml(?string $s): string
{
    if ($s === null || $s === '') {
        return '';
    }
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function redirect(string $url, int $code = 302): void
{
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Charge un fichier JSON avec verrou partagé (lecture).
 *
 * @param string $path Chemin absolu vers le fichier .json
 * @return array Données décodées, ou [] si fichier absent / JSON invalide
 */
function loadJsonFile(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $fp = fopen($path, 'rb');
    if ($fp === false) {
        return [];
    }
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        return [];
    }
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    if ($raw === false || $raw === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Enregistre un tableau en JSON avec verrou exclusif (écriture).
 *
 * @param string $path Chemin absolu vers le fichier .json
 * @param array  $data Données à encoder
 * @return bool true si l'écriture a réussi
 */
function saveJsonFile(string $path, array $data): bool
{
    $fp = fopen($path, 'cb');
    if ($fp === false) {
        return false;
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }
    ftruncate($fp, 0);
    $written = fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
    fclose($fp);
    return $written !== false;
}
