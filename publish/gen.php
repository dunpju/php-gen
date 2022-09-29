<?php
declare(strict_types=1);

return [
    "controller" => [
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Hyperf\HttpServer\Annotation\Controller::class,
            Hyperf\HttpServer\Annotation\DeleteMapping::class,
            Hyperf\HttpServer\Annotation\GetMapping::class,
            Hyperf\HttpServer\Annotation\PostMapping::class,
            Hyperf\HttpServer\Annotation\PutMapping::class,
        ],
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseController::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Controller",
        // 基础命名空间
        "base_namespace" => "App\\Controller\\",
    ],
    "dao" => [
        "uses" => [
            Dengpju\PhpGen\Constants\ResponseCode::class,
            Dengpju\PhpGen\Abstracts\BaseDao::class,
            Dengpju\PhpGen\Exception\DaoException::class,
        ],
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseDao::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Dao",
        // 基础命名空间
        "base_namespace" => "App\\Dao\\",
    ],
    "entity" => [
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEntity::class
        ],
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEntity::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Entity",
        // 基础命名空间
        "base_namespace" => "App\\Entity\\",
    ],
    "enum" => [
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEnum::class
        ],
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEnum::class,
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