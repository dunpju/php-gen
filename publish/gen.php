<?php
declare(strict_types=1);

return [
    "controller" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Hyperf\HttpServer\Annotation\Controller::class,
            Hyperf\HttpServer\Annotation\DeleteMapping::class,
            Hyperf\HttpServer\Annotation\GetMapping::class,
            Hyperf\HttpServer\Annotation\PostMapping::class,
            Hyperf\HttpServer\Annotation\PutMapping::class,
        ],
        // 自定义继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseController::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Controller",
        // 基础命名空间
        "base_namespace" => "App\\Controller\\",
    ],
    "dao" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Constants\ResponseCode::class,
            Dengpju\PhpGen\Abstracts\BaseDao::class,
            Dengpju\PhpGen\Exception\DaoException::class,
        ],
        // 自定义继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseDao::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Dao",
        // 基础命名空间
        "base_namespace" => "App\\Dao\\",
    ],
    "entity" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEntity::class
        ],
        // 自定义继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEntity::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Entity",
        // 基础命名空间
        "base_namespace" => "App\\Entity\\",
    ],
    "enum" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEnum::class
        ],
        // 自定义继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEnum::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Enum",
        // 基础命名空间
        "base_namespace" => "App\\Enum\\",
    ],
    "service" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
            Hyperf\DbConnection\Db::class
        ],
        // 自定义trait
        "traits" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
        ],
        // 自定义继承类
        "inheritance" => "",
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Services",
        // 基础命名空间
        "base_namespace" => "App\\Services\\",
        // 自定义校验异常
        "validate_exception" => Dengpju\PhpGen\Exception\ValidateException::class,
        // 自定义业务异常
        "business_exception" => Dengpju\PhpGen\Exception\BusinessException::class
    ],
    "validate" => [
        // 自定义use命名空间
        "uses" => [
            Dengpju\PhpGen\Traits\RuleMessage::class,
        ],
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Validates",
        // 基础命名空间
        "base_namespace" => "App\\Validates\\",
    ]
];