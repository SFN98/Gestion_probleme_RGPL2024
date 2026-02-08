<?php
/**
 * Règles métier : province depuis login, priorité P1–P4
 * DOCUMENTATION_SITE.md sections 7.1 et 7.2
 */

class ProvinceHelper
{
    private const PREFIXES = [
        '2XA' => 'Estuaire Libreville',
        '2AA' => 'Estuaire Libreville',
        '3AAA' => 'Estuaire Libreville',
        '4AABA' => 'Estuaire Libreville',
        '5CAABA' => 'Moyen Ogooué',
    ];

    private const LETTER_MAP = [
        'A' => 'G1 — Libreville/Akanda/Owendo',
        'B' => 'G2', 'C' => 'G3', 'D' => 'G4', 'E' => 'G5', 'F' => 'G6',
        'G' => 'G7', 'H' => 'G8', 'I' => 'G9', 'J' => 'G1 — reste Estuaire',
    ];

    private const P1_TYPES = [
        "Non accès aux questionnaires",
        "Non accès à l'application",
        "Non accès à la synchronisation des données",
        "Non accès à la synchronisation des paramètres",
    ];
    private const P2_TYPES = [
        "Non visibilité du personnel",
        "Non accès aux zones de travail",
    ];
    private const P3_TYPES = ["Non accès aux outils de contrôle"];

    public static function getProvinceFromLogin(?string $login): string
    {
        if (empty($login)) {
            return 'Inconnu';
        }
        $s = strtoupper(trim($login));
        $keys = array_keys(self::PREFIXES);
        usort($keys, fn($a, $b) => strlen($b) - strlen($a));
        foreach ($keys as $k) {
            if (substr($s, 0, strlen($k)) === $k) {
                return self::PREFIXES[$k];
            }
        }
        $first = $s[0];
        return self::LETTER_MAP[$first] ?? 'Inconnu';
    }

    public static function getPriorityFromType(string $type): string
    {
        if (in_array($type, self::P1_TYPES)) return 'P1';
        if (in_array($type, self::P2_TYPES)) return 'P2';
        if (in_array($type, self::P3_TYPES)) return 'P3';
        return 'P4';
    }

    /** Liste des types de problème (ordre doc 7.3) pour formulaire soumission */
    public static function getProblemTypes(): array
    {
        return [
            "Non accès aux questionnaires",
            "Non accès à l'application",
            "Non visibilité du personnel",
            "Non accès aux zones de travail",
            "Non accès aux outils de contrôle",
            "Non accès à la synchronisation des données",
            "Non accès à la synchronisation des paramètres",
        ];
    }
}
