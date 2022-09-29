<?php
declare(strict_types=1);


return [
    "enum" => [
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Enum\EnumBase::class
        ],
        "inheritance" => Dengpju\PhpGen\Enum\EnumBase::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Enum",
        // 基础命名空间
        "base_namespace" => "App\\Enum\\",
    ],
    "service" => [
        "uses" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
            Hyperf\DbConnection\Db::class
        ],
        "traits" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
        ],
        "inheritance" => "",
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
        "inheritance" => "",
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Validates",
        // 基础命名空间
        "base_namespace" => "App\\Validates\\",
    ]
];