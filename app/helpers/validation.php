<?php
function sanitizeString(string $input, int $maxLen = 255): string {
    return mb_substr(trim(strip_tags($input)), 0, $maxLen);
}

function validatePhone(string $phone): bool {
    $cleaned = preg_replace('/\D/', '', $phone);
    return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
}

function validateRequired(array $data, array $fields): ?string {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            return "Field '$field' is required.";
        }
    }
    return null;
}

function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function validateEnum(string $value, array $allowed): bool {
    return in_array($value, $allowed, true);
}
