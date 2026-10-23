<?php
require_once dirname(__DIR__, 3) . '/app/config/db.php';
require_once dirname(__DIR__, 3) . '/app/config/constants.php';
require_once dirname(__DIR__, 3) . '/app/helpers/jwt.php';
require_once dirname(__DIR__, 3) . '/app/models/Rating.php';
require_once dirname(__DIR__, 2) . '/response.php';
require_once __DIR__ . '/../middleware/cors.php';
require_once __DIR__ . '/../middleware/auth.php';

$user     = requireAuth();
$runnerId = isset($_GET['runner_id']) ? (int)$_GET['runner_id'] : (int)$user['sub'];

$ratingModel = new Rating();
$stats   = $ratingModel->getRunnerStats($runnerId);
$reviews = $ratingModel->getRecentForRunner($runnerId, 10);

apiSuccess(['stats' => $stats, 'reviews' => $reviews], 'Runner stats retrieved');
