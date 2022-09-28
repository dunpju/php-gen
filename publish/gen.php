<?php
declare(strict_types=1);

return [
    "validate" => [
        "uses" => [
            Dengpju\PhpGen\Traits\RuleMessage::class,
        ],
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Validates",
        // 基础命名空间
        "base_namespace" => "App\\Validates\\"
    ]
];