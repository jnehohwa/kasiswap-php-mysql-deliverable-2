<?php
declare(strict_types=1);

$bootstrapPaths = [
    __DIR__ . '/../app/bootstrap.php',
    __DIR__ . '/app/bootstrap.php',
];

foreach ($bootstrapPaths as $bootstrapPath) {
    if (is_file($bootstrapPath)) {
        require_once $bootstrapPath;
        return;
    }
}

http_response_code(500);
exit('KasiSwap could not find the application bootstrap file.');
