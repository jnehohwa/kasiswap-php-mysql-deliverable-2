<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/services.php';
require_once __DIR__ . '/layout.php';

date_default_timezone_set('Africa/Johannesburg');
