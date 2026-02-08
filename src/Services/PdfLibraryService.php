<?php
/**
 * CRUD bibliothèque PDF (métadonnées seules en Phase 2 ; upload en Phase 4)
 * Utilise MySQL si configuré, sinon JSON.
 */

class PdfLibraryService
{
    private static function rowToPdf(array $row): array
    {
        $keywords = $row['keywords'] ?? null;
        if (is_string($keywords)) {
            $decoded = json_decode($keywords, true);
            $keywords = is_array($decoded) ? $decoded : (trim($keywords) !== '' ? [$keywords] : []);
        }
        if (!is_array($keywords)) {
            $keywords = [];
        }
        return [
            'id' => $row['id'],
            'title' => $row['title'] ?? '',
            'description' => $row['description'] ?? '',
            'fileUrl' => $row['file_url'] ?? '',
            'keywords' => $keywords,
            'createdAt' => isset($row['created_at']) ? date('c', strtotime($row['created_at'])) : date('c'),
            'updatedAt' => isset($row['updated_at']) ? date('c', strtotime($row['updated_at'])) : date('c'),
        ];
    }

    public static function getAll(): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->query('SELECT * FROM pdf_library ORDER BY created_at DESC');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_map([self::class, 'rowToPdf'], $rows);
        }
        $list = loadJsonFile(DATA_PDF_LIBRARY);
        return is_array($list) ? $list : [];
    }

    public static function getById(string $id): ?array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('SELECT * FROM pdf_library WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? self::rowToPdf($row) : null;
        }
        $list = self::getAll();
        foreach ($list as $item) {
            if (isset($item['id']) && (string) $item['id'] === (string) $id) {
                return $item;
            }
        }
        return null;
    }

    public static function searchByKeywords(string $query): array
    {
        $list = self::getAll();
        $query = trim($query);
        if ($query === '') {
            return $list;
        }
        $words = preg_split('/\s+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter($list, function ($item) use ($words) {
            $kw = $item['keywords'] ?? '';
            if (is_array($kw)) {
                $kw = implode(' ', $kw);
            }
            $text = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . $kw);
            foreach ($words as $w) {
                if (mb_strpos($text, $w) === false) {
                    return false;
                }
            }
            return true;
        }));
    }

    public static function create(array $data): string
    {
        $pdo = getDb();
        $id = self::generateId();
        $now = date('Y-m-d H:i:s');
        $keywords = $data['keywords'] ?? [];
        if (!is_array($keywords)) {
            $keywords = trim((string) $keywords) !== '' ? [(string) $keywords] : [];
        }
        $keywordsJson = json_encode($keywords);

        if ($pdo !== null) {
            $stmt = $pdo->prepare(
                'INSERT INTO pdf_library (id, title, description, file_url, keywords, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $id,
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['fileUrl'] ?? '',
                $keywordsJson,
                $now,
                $now,
            ]);
            return $id;
        }

        $list = self::getAll();
        $list[] = [
            'id' => $id,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'fileUrl' => $data['fileUrl'] ?? '',
            'keywords' => $keywords,
            'createdAt' => date('c'),
            'updatedAt' => date('c'),
        ];
        saveJsonFile(DATA_PDF_LIBRARY, $list);
        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $pdo = getDb();
        $existing = self::getById($id);
        if (!$existing) {
            return false;
        }
        $keywords = $data['keywords'] ?? $existing['keywords'];
        if (!is_array($keywords)) {
            $keywords = is_array($existing['keywords']) ? $existing['keywords'] : (trim((string) $keywords) !== '' ? [(string) $keywords] : []);
        }
        $keywordsJson = json_encode($keywords);

        if ($pdo !== null) {
            $stmt = $pdo->prepare(
                'UPDATE pdf_library SET title = ?, description = ?, file_url = ?, keywords = ?, updated_at = ? WHERE id = ?'
            );
            $stmt->execute([
                $data['title'] ?? $existing['title'],
                $data['description'] ?? $existing['description'],
                $data['fileUrl'] ?? $existing['fileUrl'],
                $keywordsJson,
                date('Y-m-d H:i:s'),
                $id,
            ]);
            return $stmt->rowCount() > 0;
        }

        $list = self::getAll();
        foreach ($list as $i => $item) {
            if (isset($item['id']) && (string) $item['id'] === (string) $id) {
                $list[$i]['title'] = $data['title'] ?? $item['title'];
                $list[$i]['description'] = $data['description'] ?? $item['description'];
                $list[$i]['fileUrl'] = $data['fileUrl'] ?? $item['fileUrl'];
                $list[$i]['keywords'] = $keywords;
                $list[$i]['updatedAt'] = date('c');
                return saveJsonFile(DATA_PDF_LIBRARY, $list);
            }
        }
        return false;
    }

    public static function delete(string $id): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('DELETE FROM pdf_library WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        }
        $list = self::getAll();
        $out = [];
        foreach ($list as $item) {
            if (!isset($item['id']) || (string) $item['id'] !== (string) $id) {
                $out[] = $item;
            }
        }
        if (count($out) === count($list)) {
            return false;
        }
        return saveJsonFile(DATA_PDF_LIBRARY, $out);
    }

    private static function generateId(): string
    {
        return 'P' . bin2hex(random_bytes(4));
    }
}
