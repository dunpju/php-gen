<?php
declare(strict_types=1);

return [
    "controller" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Hyperf\HttpServer\Annotation\Controller::class,
            Hyperf\HttpServer\Annotation\DeleteMapping::class,
            Hyperf\HttpServer\Annotation\GetMapping::class,
            Hyperf\HttpServer\Annotation\PostMapping::class,
            Hyperf\HttpServer\Annotation\PutMapping::class,
        ],
        // 继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseController::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Controller",
        // 基础命名空间
        "base_namespace" => "App\\Controller\\",
    ],
    "dao" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Constants\ResponseCode::class,
            Dengpju\PhpGen\Abstracts\BaseDao::class,
            Dengpju\PhpGen\Exception\DaoException::class,
        ],
        // 继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseDao::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Dao",
        // 基础命名空间
        "base_namespace" => "App\\Dao\\",
    ],
    "entity" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEntity::class
        ],
        // 继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEntity::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Entity",
        // 基础命名空间
        "base_namespace" => "App\\Entity\\",
    ],
    "enum" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Abstracts\BaseEnum::class
        ],
        // 继承类
        "inheritance" => Dengpju\PhpGen\Abstracts\BaseEnum::class,
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Enum",
        // 基础命名空间
        "base_namespace" => "App\\Enum\\",
    ],
    "service" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
            Hyperf\DbConnection\Db::class
        ],
        // trait
        "traits" => [
            Dengpju\PhpGen\Traits\RequestTrait::class,
        ],
        // 继承类
        "inheritance" => "",
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Services",
        // 基础命名空间
        "base_namespace" => "App\\Services\\",
        // 校验异常
        "validate_exception" => Dengpju\PhpGen\Exception\ValidateException::class,
        // 业务异常
        "business_exception" => Dengpju\PhpGen\Exception\BusinessException::class
    ],
    "validate" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Traits\RuleMessage::class,
        ],
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Validates",
        // 基础命名空间
        "base_namespace" => "App\\Validates\\",
    ],
    "code" => [
        // use命名空间
        "uses" => [
            Dengpju\PhpGen\Annotations\Message::class,
            Dengpju\PhpGen\Traits\CodeMessage::class,
            Hyperf\Constants\Annotation\Constants::class,
        ],
        // trait
        "traits" => [
            Dengpju\PhpGen\Traits\CodeMessage::class,
        ],
        // 继承类
        "inheritance" => Hyperf\Constants\AbstractConstants::class,
        // ymal文件目录
        "ymal_file_directory" => BASE_PATH . "/app/Constants/ymal",
        // 基础存储路径
        "base_store_path" => BASE_PATH . "/app/Constants",
        // 命名空间
        "base_namespace" => "App\\Constants\\",
        // 类名
        "class_name" => "ResponseCode",
    ]
];