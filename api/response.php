<?php
function apiResponse(string $status, int $code, $data = null, string $message = ''): void {
    // Discard any PHP warnings/notices buffered before this point
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => $status,
        'code'    => $code,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function apiSuccess($data = null, string $message = 'Success', int $code = 200): void {
    apiResponse('success', $code, $data, $message);
}

function apiError(string $message, int $code = 400, $data = null): void {
    apiResponse('error', $code, $data, $message);
}
