<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 2) . '/response.php';

/**
 * Validate JWT and return the decoded payload.
 * Calls apiError and exits on failure.
 */
function requireAuth(array $allowedRoles = []): array {
    $token = getBearerToken();
    if (!$token) {
        apiError('Authentication required', 401);
    }

    $payload = jwtDecode($token, JWT_SECRET);
    if (!$payload) {
        apiError('Invalid or expired token', 401);
    }

    if ($allowedRoles && !in_array($payload['role'], $allowedRoles, true)) {
        apiError('Access denied: insufficient permissions', 403);
    }

    return $payload;
}
