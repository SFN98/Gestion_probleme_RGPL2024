<?php
/**
 * CRUD FAQ, recherche par mots clés (titre, solution)
 * Utilise MySQL si configuré, sinon JSON.
 */

class FaqService
{
    private static function rowToFaq(array $row): array
    {
        return [
            'id' => $row['id'],
            'title' => $row['title'] ?? '',
            'category' => $row['category'] ?? '',
            'question' => $row['question'] ?? ($row['title'] ?? ''),
            'solution' => $row['solution'] ?? '',
            'status' => $row['status'] ?? 'resolved',
            'createdAt' => isset($row['created_at']) ? date('c', strtotime($row['created_at'])) : date('c'),
            'updatedAt' => isset($row['updated_at']) ? date('c', strtotime($row['updated_at'])) : date('c'),
        ];
    }

    public static function getAll(): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->query('SELECT * FROM faq ORDER BY created_at DESC');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_map([self::class, 'rowToFaq'], $rows);
        }
        $list = loadJsonFile(DATA_FAQ);
        return is_array($list) ? $list : [];
    }

    public static function getById(string $id): ?array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('SELECT * FROM faq WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? self::rowToFaq($row) : null;
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
            $text = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['question'] ?? '') . ' ' . ($item['solution'] ?? ''));
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

        if ($pdo !== null) {
            $stmt = $pdo->prepare(
                'INSERT INTO faq (id, title, category, question, solution, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $id,
                $data['title'] ?? '',
                $data['category'] ?? '',
                $data['question'] ?? '',
                $data['solution'] ?? '',
                $data['status'] ?? 'resolved',
                $now,
                $now,
            ]);
            return $id;
        }

        $list = self::getAll();
        $list[] = [
            'id' => $id,
            'title' => $data['title'] ?? '',
            'category' => $data['category'] ?? '',
            'question' => $data['question'] ?? '',
            'solution' => $data['solution'] ?? '',
            'status' => $data['status'] ?? 'resolved',
            'createdAt' => date('c'),
            'updatedAt' => date('c'),
        ];
        saveJsonFile(DATA_FAQ, $list);
        return $id;
    }

    public static function update(string $id, array $data): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare(
                'UPDATE faq SET title = ?, category = ?, question = ?, solution = ?, status = ?, updated_at = ? WHERE id = ?'
            );
            $existing = self::getById($id);
            if (!$existing) {
                return false;
            }
            $stmt->execute([
                $data['title'] ?? $existing['title'],
                $data['category'] ?? $existing['category'] ?? '',
                $data['question'] ?? $existing['question'] ?? '',
                $data['solution'] ?? $existing['solution'],
                $data['status'] ?? $existing['status'],
                date('Y-m-d H:i:s'),
                $id,
            ]);
            return $stmt->rowCount() > 0;
        }

        $list = self::getAll();
        foreach ($list as $i => $item) {
            if (isset($item['id']) && (string) $item['id'] === (string) $id) {
                $list[$i]['title'] = $data['title'] ?? $item['title'];
                $list[$i]['category'] = $data['category'] ?? $item['category'] ?? '';
                $list[$i]['question'] = $data['question'] ?? $item['question'] ?? '';
                $list[$i]['solution'] = $data['solution'] ?? $item['solution'];
                $list[$i]['status'] = $data['status'] ?? $item['status'];
                $list[$i]['updatedAt'] = date('c');
                return saveJsonFile(DATA_FAQ, $list);
            }
        }
        return false;
    }

    public static function delete(string $id): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('DELETE FROM faq WHERE id = ?');
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
        return saveJsonFile(DATA_FAQ, $out);
    }

    private static function generateId(): string
    {
        return 'F' . bin2hex(random_bytes(4));
    }
}
