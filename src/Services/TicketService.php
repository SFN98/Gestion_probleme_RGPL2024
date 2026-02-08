<?php
/**
 * CRUD tickets (sans médias en Phase 2), liste triée, recherche résolus
 * Utilise MySQL si configuré, sinon JSON.
 */

class TicketService
{
    private static function rowToTicket(array $row): array
    {
        $images = $row['images'] ?? '[]';
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }
        return [
            'id' => $row['id'],
            'title' => $row['title'] ?? '',
            'reporterName' => $row['reporter_name'] ?? '',
            'login' => $row['login'] ?? '',
            'phone' => $row['phone'] ?? '',
            'type' => $row['type'] ?? '',
            'desc' => $row['desc'] ?? '',
            'images' => $images,
            'occurrences' => (int) ($row['occurrences'] ?? 1),
            'province' => $row['province'] ?? '',
            'priority' => $row['priority'] ?? 'P4',
            'status' => $row['status'] ?? 'open',
            'assignee' => $row['assignee'] ?? '',
            'response' => $row['response'] ?? '',
            'firstReportedAt' => isset($row['first_reported_at']) ? date('c', strtotime($row['first_reported_at'])) : date('c'),
            'lastUpdatedAt' => isset($row['last_updated_at']) ? date('c', strtotime($row['last_updated_at'])) : date('c'),
        ];
    }

    public static function getAll(): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->query('SELECT * FROM tickets ORDER BY first_reported_at ASC');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_map([self::class, 'rowToTicket'], $rows);
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return [];
        }
        usort($list, function ($a, $b) {
            $t1 = $a['firstReportedAt'] ?? '';
            $t2 = $b['firstReportedAt'] ?? '';
            return strcmp($t1, $t2);
        });
        return $list;
    }

    public static function getById(string $id): ?array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? self::rowToTicket($row) : null;
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return null;
        }
        foreach ($list as $t) {
            if (isset($t['id']) && (string) $t['id'] === (string) $id) {
                return $t;
            }
        }
        return null;
    }

    public static function getResolvedForSearch(string $query): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->query("SELECT * FROM tickets WHERE status = 'resolved' ORDER BY first_reported_at ASC");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $resolved = array_map([self::class, 'rowToTicket'], $rows);
            return self::filterBySearchWords($resolved, $query);
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return [];
        }
        $resolved = array_filter($list, function ($t) {
            return isset($t['status']) && $t['status'] === 'resolved';
        });
        return self::filterBySearchWords(array_values($resolved), $query);
    }

    private static function filterBySearchWords(array $list, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return array_values($list);
        }
        $words = preg_split('/\s+/u', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter($list, function ($t) use ($words) {
            $text = mb_strtolower(
                ($t['title'] ?? '') . ' ' . ($t['desc'] ?? '') . ' ' . ($t['response'] ?? '')
            );
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
                'INSERT INTO tickets (id, title, reporter_name, login, phone, type, `desc`, images, occurrences, province, priority, status, assignee, response, first_reported_at, last_updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $id,
                $data['title'] ?? '',
                $data['reporterName'] ?? '',
                $data['login'] ?? '',
                $data['phone'] ?? '',
                $data['type'] ?? '',
                $data['desc'] ?? '',
                json_encode([]),
                1,
                $data['province'] ?? '',
                $data['priority'] ?? 'P4',
                'open',
                '',
                '',
                $now,
                $now,
            ]);
            return $id;
        }

        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            $list = [];
        }
        $ticket = [
            'id' => $id,
            'title' => $data['title'] ?? '',
            'reporterName' => $data['reporterName'] ?? '',
            'login' => $data['login'] ?? '',
            'phone' => $data['phone'] ?? '',
            'type' => $data['type'] ?? '',
            'desc' => $data['desc'] ?? '',
            'images' => [],
            'occurrences' => 1,
            'province' => $data['province'] ?? '',
            'priority' => $data['priority'] ?? 'P4',
            'status' => 'open',
            'assignee' => '',
            'response' => '',
            'firstReportedAt' => date('c'),
            'lastUpdatedAt' => date('c'),
        ];
        $list[] = $ticket;
        saveJsonFile(DATA_TICKETS, $list);
        return $id;
    }

    private static function generateId(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $s = 'T';
        for ($i = 0; $i < 6; $i++) {
            $s .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $s;
    }

    /**
     * Prendre un ticket (verrouillage atomique)
     */
    public static function takeTicket(string $ticketId, string $assignee): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('UPDATE tickets SET status = ?, assignee = ?, last_updated_at = NOW() WHERE id = ? AND status = ?');
            $stmt->execute(['progress', $assignee, $ticketId, 'open']);
            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Ticket déjà pris ou inexistant'];
        }
        // Fallback JSON avec flock
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return ['success' => false, 'error' => 'Erreur de chargement'];
        }
        foreach ($list as $i => $t) {
            if (isset($t['id']) && (string) $t['id'] === (string) $ticketId) {
                if ($t['status'] !== 'open') {
                    return ['success' => false, 'error' => 'Ticket déjà pris ou inexistant'];
                }
                $list[$i]['status'] = 'progress';
                $list[$i]['assignee'] = $assignee;
                $list[$i]['lastUpdatedAt'] = date('c');
                if (saveJsonFile(DATA_TICKETS, $list)) {
                    return ['success' => true];
                }
                return ['success' => false, 'error' => 'Erreur de sauvegarde'];
            }
        }
        return ['success' => false, 'error' => 'Ticket inexistant'];
    }

    /**
     * Libérer un ticket
     */
    public static function releaseTicket(string $ticketId, string $assignee): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('UPDATE tickets SET status = ?, assignee = ?, last_updated_at = NOW() WHERE id = ? AND assignee = ?');
            $stmt->execute(['open', '', $ticketId, $assignee]);
            return $stmt->rowCount() > 0;
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $i => $t) {
            if (isset($t['id']) && (string) $t['id'] === (string) $ticketId && ($t['assignee'] ?? '') === $assignee) {
                $list[$i]['status'] = 'open';
                $list[$i]['assignee'] = '';
                $list[$i]['lastUpdatedAt'] = date('c');
                return saveJsonFile(DATA_TICKETS, $list);
            }
        }
        return false;
    }

    /**
     * Marquer un ticket comme résolu
     */
    public static function markResolved(string $ticketId, string $response, string $assignee): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('UPDATE tickets SET status = ?, response = ?, last_updated_at = NOW() WHERE id = ? AND assignee = ?');
            $stmt->execute(['resolved', $response, $ticketId, $assignee]);
            return $stmt->rowCount() > 0;
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $i => $t) {
            if (isset($t['id']) && (string) $t['id'] === (string) $ticketId && ($t['assignee'] ?? '') === $assignee) {
                $list[$i]['status'] = 'resolved';
                $list[$i]['response'] = $response;
                $list[$i]['lastUpdatedAt'] = date('c');
                return saveJsonFile(DATA_TICKETS, $list);
            }
        }
        return false;
    }

    /**
     * Réaffecter un ticket
     */
    public static function reassignTicket(string $ticketId, string $newAssignee, string $currentAssignee): bool
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $stmt = $pdo->prepare('UPDATE tickets SET assignee = ?, last_updated_at = NOW() WHERE id = ? AND assignee = ?');
            $stmt->execute([$newAssignee, $ticketId, $currentAssignee]);
            return $stmt->rowCount() > 0;
        }
        $list = loadJsonFile(DATA_TICKETS);
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $i => $t) {
            if (isset($t['id']) && (string) $t['id'] === (string) $ticketId && ($t['assignee'] ?? '') === $currentAssignee) {
                $list[$i]['assignee'] = $newAssignee;
                $list[$i]['lastUpdatedAt'] = date('c');
                return saveJsonFile(DATA_TICKETS, $list);
            }
        }
        return false;
    }

    /**
     * Liste filtrée de tickets
     */
    public static function getFiltered(array $filters = []): array
    {
        $pdo = getDb();
        if ($pdo !== null) {
            $where = [];
            $params = [];
            if (!empty($filters['status'])) {
                $where[] = 'status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['province'])) {
                $where[] = 'province = ?';
                $params[] = $filters['province'];
            }
            if (!empty($filters['type'])) {
                $where[] = 'type = ?';
                $params[] = $filters['type'];
            }
            if (!empty($filters['priority'])) {
                $where[] = 'priority = ?';
                $params[] = $filters['priority'];
            }
            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $where[] = '(title LIKE ? OR `desc` LIKE ? OR login LIKE ?)';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            $sql = 'SELECT * FROM tickets';
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY first_reported_at ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([self::class, 'rowToTicket'], $rows);
        }
        // Fallback JSON
        $list = self::getAll();
        if (empty($filters)) {
            return $list;
        }
        return array_values(array_filter($list, function ($t) use ($filters) {
            if (!empty($filters['status']) && ($t['status'] ?? '') !== $filters['status']) {
                return false;
            }
            if (!empty($filters['province']) && ($t['province'] ?? '') !== $filters['province']) {
                return false;
            }
            if (!empty($filters['type']) && ($t['type'] ?? '') !== $filters['type']) {
                return false;
            }
            if (!empty($filters['priority']) && ($t['priority'] ?? '') !== $filters['priority']) {
                return false;
            }
            if (!empty($filters['search'])) {
                $search = mb_strtolower($filters['search']);
                $text = mb_strtolower(($t['title'] ?? '') . ' ' . ($t['desc'] ?? '') . ' ' . ($t['login'] ?? ''));
                if (mb_strpos($text, $search) === false) {
                    return false;
                }
            }
            return true;
        }));
    }
}
