<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/User.php';
require_once dirname(__DIR__, 3) . '/app/models/Rating.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$user      = requireAuth();
$userModel = new User();
$profile   = $userModel->findById((int)$user['sub']);

if (!$profile) apiError('User not found', 404);

$data = ['profile' => $profile];

if ($profile['role'] === 'runner') {
    $data['buildings'] = $userModel->getRunnerBuildings((int)$user['sub']);
    $ratingModel = new Rating();
    $data['stats'] = $ratingModel->getRunnerStats((int)$user['sub']);
    $data['recent_ratings'] = $ratingModel->getRecentForRunner((int)$user['sub'], 5);
}

apiSuccess($data, 'Profile retrieved');
