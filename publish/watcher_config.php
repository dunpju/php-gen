<?php
declare(strict_types=1);

return [
    'excludes' => [

    ],
    'dirs' => [
        BASE_PATH . "/app",
        BASE_PATH . "/config",
        BASE_PATH . "/routes"
    ],
    'files' => [
        BASE_PATH . "/.env",
        BASE_PATH . "/composer.json",
    ],
];