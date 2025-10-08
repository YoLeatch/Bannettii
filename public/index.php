<?php

require_once __DIR__ . '/../autoload.php';

header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");

use Core\Router;

