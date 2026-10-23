<?php
/**
 * Simple file-based rate limiter.
 * Max $maxRequests per $windowSeconds per IP.
 */
function rateLimit(int $maxRequests = 60, int $windowSeconds = 60): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = md5($ip . $_SERVER['SCRIPT_NAME']);
    $file = sys_get_temp_dir() . '/td_rl_' . $key;

    $now = time();
    $data = [];

    if (file_exists($file)) {
        $raw = json_decode(file_get_contents($file), true);
        if (is_array($raw)) $data = $raw;
    }

    // Purge old timestamps
    $data = array_filter($data, fn($ts) => $ts > $now - $windowSeconds);
    $data = array_values($data);

    if (count($data) >= $maxRequests) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'code' => 429, 'message' => 'Rate limit exceeded']);
        exit;
    }

    $data[] = $now;
    file_put_contents($file, json_encode($data), LOCK_EX);
}
