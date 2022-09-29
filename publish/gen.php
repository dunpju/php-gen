<?php
declare(strict_types=1);

return [
    "service" => [
        "uses" => [],
        "traits" => [],
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Services",
        // 基础命名空间
        "base_namespace" => "App\\Services\\",
        "validate_exception" => Dengpju\PhpGen\Exception\ValidateException::class,
        "business_exception" => Dengpju\PhpGen\Exception\BusinessException::class
    ],
    "validate" => [
        "uses" => [
            Dengpju\PhpGen\Traits\RuleMessage::class,
        ],
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Validates",
        // 基础命名空间
        "base_namespace" => "App\\Validates\\",
    ]
];